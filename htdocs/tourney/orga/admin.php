<?
	$LS_BASEPATH = '../../';
	require $LS_BASEPATH.'../includes/ls_base.inc';
	require $LS_BASEPATH."../includes/tourney/base.inc";
	require $LS_BASEPATH."../includes/tourney/tabs.inc";
	require ("seeding.php");
	
  NavStruct("tournaments/singletourney", array('tid' => $id));
	StartPage(_("Tournament Admin"));

	user_auth_ex(AUTH_TOURNEY, $id);

	$tourney = new Tourney($id);

	if (isset($setstatus))
	{
		echo '<h3 class=content>'._("Set Status").'</h3>';
		$oldStatus = $tourney->Status;
		$newStatus = $setstatus;
		
		echo '<p class=content>';
		echo _("Previous Status").': '.$tourney->GetStatusDesc($oldStatus).'<br>';
		echo '</p>';
		
		echo '<p class=content>';

		$proceed=true;
		if (($newStatus==TS_CLOSED) && ($oldStatus>TS_CLOSED) && ($oldStatus<TS_CANCELED)) $proceed=$submitted;
		if (($newStatus<TS_MATCH) && ($oldStatus>=TS_MATCH) && ($oldStatus<TS_CANCELED)) $proceed=$submitted;

		if (!$proceed)
		{
			if ($newStatus==TS_CLOSED)
			{
				FormStart();
					FormValue('setstatus', $setstatus);
					FormValue('id', $id);
					FormValue('submitted', 1);
					FormElement('f_remove', _("Remove current registrations?"), 1, 'checkbox');
					FormElement('', '', _("Set"), 'submit');
				FormEnd();
			}
			else if ($oldStatus>=TS_MATCH)
			{
				FormStart();
					FormValue('setstatus', $setstatus);
					FormValue('id', $id);
					FormValue('submitted', 1);
					FormElement('f_remove', _("Remove Games?"), 1, 'checkbox');
					FormElement('', '', _("Set"), 'submit');
				FormEnd();
			}
		}
		else
		{
			if (($newStatus==TS_REGISTRATION) && ($newStatus<TS_CANCELED) && (oldStatus<TS_CANCELED))
			{
				SQL_Query("UPDATE Tourney SET status=$newStatus WHERE id=$id");
				echo _("Registration Opened")."<br>";
			}
			if (($newStatus==TS_CLOSED) && ($newStatus<TS_CANCELED) && (oldStatus<TS_CANCELED))
			{
				if ($f_remove)
				{
					$res = SQL_Query("SELECT id FROM TourneyTeam WHERE tourney=$id");
					while ($row = mysql_fetch_array($res)) SQL_Query("DELETE FROM TourneyTeamMember WHERE team=".$row['id']);
					SQL_Query("DELETE FROM TourneyTeam WHERE tourney=$id");
					echo _("Removed current registrations.").'<br>';
				}
				SQL_Query("UPDATE Tourney SET status=$newStatus WHERE id=$id");
				echo _("Registration Closed.")."<br>";
			}
			if (($newStatus<TS_MATCH) && ($oldStatus>=TS_MATCH) && ($newStatus<TS_CANCELED) && (oldStatus<TS_CANCELED))
			{
				if ($f_remove)
				{
					SQL_Query("DELETE FROM TourneyMatch WHERE tourney=$id");
					echo _("Removed current matches.").'<br>';
				}
				else
				{
					SQL_Query("UPDATE TourneyMatch SET op1=0, op2=0, score1=0, score2=0, flags=0, status=0 WHERE tourney=$id");
				}
				SQL_Query("UPDATE Tourney SET status=$newStatus WHERE id=$id");
				echo _("Match Mode cancelled.")."<br>";
			}
			if (($newStatus<TS_POSTDRAW) && ($oldStatus>=TS_POSTDRAW) && ($newStatus<TS_CANCELED) && (oldStatus<TS_CANCELED))
			{
				if ($tourney->eflags & TEF_BLINDDRAW)
				{
					$res=SQL_Query("	select u.name, tm.user, tm.id
								from tourneyteam tt, tourneyteammember tm, user u
								where tm.team=tt.id and tt.tourney=".$tourney->id." and tt.leader<>tm.user and u.id=tm.user");
					while ($row=mysql_fetch_array($res))
					{
						$ires=SQL_Query("insert into tourneyteam (name, leader, tourney) values ('".addslashes($row['name'])."',".$row['user'].",".$tourney->id.")");
						SQL_Query("update tourneyteammember set team=".mysql_insert_id()." where id=".$row['id']);
					}
					echo _("Teams splitted.")."<br>";
				}
			}
			if (($newStatus>=TS_POSTDRAW) && ($oldStatus<TS_POSTDRAW) && ($newStatus<TS_CANCELED) && (oldStatus<TS_CANCELED))
			{
				if ($tourney->eflags & TEF_BLINDDRAW)
				{
					$res=SQL_Query("select tt.id, count(*) as count from tourneyteam tt, tourneyteammember ttm where tt.id=ttm.team and tt.tourney=".$tourney->id." GROUP BY tt.id ORDER BY 2 DESC");
					if ($row=mysql_fetch_array($res))
					{
						if ($row['count']==1)
						{
							$res=SQL_Query("select ifnull(max(tm.handicap)+1,0) as nullhandicap, count(*) as numplayers from tourneyteam tt, tourneyteammember tm where tm.team=tt.id and tt.tourney=".$tourney->id);
							$row=mysql_fetch_array($res);
							$nullhandicap=$row['nullhandicap'];

							$numteams=floor($row['numplayers']/$tourney->TeamSize);
							$numfetches=$numteams*$tourney->TeamSize;

							// Make ordered unshuffled player arrays
							$arymember=array();
							$aryhand=array();
							$i=0;
							$j=-1;
							$oldhand="";
							$res=SQL_Query("select tm.handicap, tm.handicap is null as nullhandicap, tm.id from tourneyteam tt, tourneyteammember tm where tm.team=tt.id and tt.tourney=".$tourney->id." order by tm.handicap is null, tm.handicap, tm.id limit 0, $numfetches");
							while ($row=mysql_fetch_array($res))
							{
								if ((!$i) or ($oldhand<>$row['handicap']))
								{
									$j++;
									$k=0;
									$arymember[$j]=array();
									$oldhand=$row['handicap'];
									$aryhand[$j]= ($row['handicap']=="") ? $nullhandicap : $row['handicap'] ;
								}
								$arymember[$j][$k++]=$row['id'];
								$i++;
							}

							// Make ordered shuffled player array
							$aryplayers=array();
							$aryplhand=array();
							$k=0;
							for ($i=0; $i<sizeof($arymember); $i++)
							{
								srand ((double)microtime()*1000000);
								shuffle($arymember[$i]);
								for ($j=0; $j<sizeof($arymember[$i]); $j++)
								{
									$aryplayers[$k]=$arymember[$i][$j];
									$aryplhand[$k]=$aryhand[$i];
									$k++;
								}
							}
//							for ($i=0;$i<sizeof($aryplayers); $i++) echo "#".$i."#".$aryplayers[$i]."#".$aryplhand[$i]."<br>";

							// Add team leaders
							$aryteam=array();
							$arytvalue=array();
							for ($i=0; $i<$numteams; $i++)
							{
								$aryteam[$i]=array();
								$aryteam[$i][0]=$aryplayers[$i];
								$arytvalue[$i]=$aryplhand[$i];
							}

							// Add team members
							for ($i=1; $i<$tourney->TeamSize; $i++)
							{

								echo "--".$i."--<br>";
								$listvalues=$arytvalue;	// list of team values
								sort($listvalues);	// sorted list of team values
								$listuvalues=array();	// sorted list of unique team values
								$oldhand="";
								for ($j=0;$j<$numteams;$j++)
								{
									if (($listvalues[$j])!=$oldhand)
									{
										$listuvalues[]=$listvalues[$j];
										$oldhand=$listvalues[$j];
									}
								}
								$aryindteams=array();	// array of teams, indirect index
								for ($j=0;$j<sizeof($listuvalues);$j++)
								{
									$aryt=array();
									for ($k=0;$k<$numteams;$k++)
									{
										if ($listuvalues[$j]==$arytvalue[$k]) $aryt[]=$k;
									}
									shuffle($aryt);
									for ($k=0;$k<sizeof($aryt);$k++) $aryindteams[]=$aryt[$k];
								}

//								for ($j=0;$j<$numteams;$j++) echo "*".$aryteam[$j][0]."*".$arytvalue[$j]."<br>";
//								for ($j=0;$j<$numteams;$j++) echo $aryindteams[$j]." ";
//								echo "<br>";

								for ($j=0;$j<$numteams;$j++)
								{
									if ($i & 1)
									{
										$index=$numfetches-1-$numteams*(floor($i/2))-$j;
									}
									else
									{
										$index=$numteams*(1+$i/2)-1-$j;
									}
//									echo $aryteam[$aryindteams[$j]][0]."-".$arytvalue[$aryindteams[$j]]."+".$aryplhand[$index]."-".$index."-".$aryplayers[$index]."<br>";
									$aryteam[$aryindteams[$j]][$i]=$aryplayers[$index];
									$arytvalue[$aryindteams[$j]]+=$aryplhand[$index];
								}

							}
//							echo "<hr>";
							for ($i=0;$i<$numteams;$i++)
							{
								$query="select tm.team from tourneyteam tt, tourneyteammember tm where tm.team=tt.id and tt.tourney=".$tourney->id." and tm.id=".$aryteam[$i][0];
								$res=SQL_Query($query);
								if (!($row=mysql_fetch_array($res))) LS_Error (_("Unknown error occured."));

								for ($j=1;$j<sizeof($aryteam[$i]);$j++)
								{
									$iquery="select tm.team from tourneyteam tt, tourneyteammember tm where tm.team=tt.id and tt.tourney=".$tourney->id." and tm.id=".$aryteam[$i][$j];
									$ires=SQL_Query($iquery);
									if (!($irow=mysql_fetch_array($ires))) LS_Error (_("Unknown error occured."));
									$query="update tourneyteammember set team=".$row['team']." where id=".$aryteam[$i][$j];
									echo $query."<br>";
									SQL_Query($query);
									$query="delete from tourneyteam where id=".$irow['team']." and tourney=".$tourney->id;
									echo $query."<br><br>";
									SQL_Query($query);
								}
							}
/*							echo "<hr>";
							for ($i=0;$i<$numteams;$i++)
							{
								echo $arytvalue[$i]."*";
								for ($j=0;$j<sizeof($aryteam[$i]);$j++)
								{
									echo " ".$aryteam[$i][$j];
								}
								echo "<br>";
							}
*/
							echo _("Teams merged.")."<br>";
						}
						else LS_Error(_("You have to go back to 'Registration closed' or 'Registration open'"));
					}
				}
			}	
			if (($newStatus>=TS_PREMATCH) && ($oldStatus<TS_PREMATCH) && ($newStatus<TS_CANCELED) && (oldStatus<TS_CANCELED))
			{
				// Start Bracket bestimmen

				mktteams($id,&$teams);
				seeding($teams);

// lansurfer original code
/*
				$res = SQL_Query("SELECT
						COUNT(tm.id) as 'tmc',
						tt.name as 'TeamName',
						l.name as 'LeaderName',
						l.clan as 'ClanName',
						tt.id
					FROM 
						TourneyTeam tt
						LEFT JOIN TourneyTeamMember tm ON tm.team=tt.id
						LEFT JOIN user l ON tt.leader=l.id
					WHERE 
						tt.tourney=$id
					GROUP BY tt.id
					ORDER BY tmc, tt.name, l.name");
				while ($row = mysql_fetch_array($res))
				{
					$teams[] = $row['id'];
				}
				srand ((double)microtime()*1000000);
				shuffle($teams);
				for ($slots = 1; $slots < count($teams); $slots*=2) ;
				printf(_("%d Teams, %d Slots required."), count($teams), $slots);
				echo '<br>';
				$i = 1;
				while (count($teams) < $slots)
				{
					$newteams = array();
					for($j=0; $j<$i; $j++)
						$newteams[] = $teams[$j];
					$newteams[] = -1;
					for($j=$i; $j<count($teams); $j++)
						$newteams[] = $teams[$j];
					$teams = $newteams;
					$i+=2;
				}
*/
// ende lansurfer code
				SQL_Query("DELETE FROM TourneyBracket WHERE tourney=".$id);
				for ($i=0; $i<count($teams); $i++)
				{
					SQL_Query("INSERT INTO TourneyBracket SET ".SQL_QueryFields(array(
						'tourney' => $id,
						'team' => $teams[$i],
						'position' => $i,
						'options' => 0
						)));
				}

				SQL_Query("UPDATE Tourney SET status=$newStatus WHERE id=$id");
				echo _("Bracked shuffled.").'<br>';
				//NavPrintAction('', _("View/Edit Bracket"));
// --> 2003-08-18 Disruptor (allows editing of the time schedule for every tourney round during pre-matches mode)
				if (!$tourney->MatchesInitialized)
					$tourney->InitMatches();
// <-- 2003-08-18 Disruptor
			}
			if (($newStatus>=TS_MATCH) && ($oldStatus<TS_MATCH) && ($newStatus<TS_CANCELED) && (oldStatus<TS_CANCELED))
			{
				if (!$tourney->MatchesInitialized)
					$tourney->InitMatches();

				$pos = new TMatchPos(0, 0, 0);
				
				$res = SQL_Query("SELECT team, options FROM TourneyBracket WHERE tourney=".$tourney->id.' ORDER BY position');
				while ($row = mysql_fetch_array($res))
				{
					$tourney->InsertTeamToMatch($row['team'], $pos);
					
					if ($pos->Slot == 1)
					{
						$pos->Slot = 0;
						$pos->Row++;
					}
					else
					{
						$pos->Slot = 1;
					}
				}
				SQL_Query("UPDATE Tourney SET status=$newStatus WHERE id=$id");
				$tourney->Check();
				echo _("Players Moved to Bracket.")."<br>";
			}
			echo '<p class=content>';
			SQL_Query("UPDATE Tourney SET status=$newStatus WHERE id=$id");
			echo _("New Tournament status set")."<br>";
			echo '</p>';
		}
	}

	NavPrintBack();

	EndPage();
?>



