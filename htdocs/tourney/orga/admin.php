<?
	$LS_BASEPATH = '../../';
	require $LS_BASEPATH.'../includes/ls_base.inc';
  require $LS_BASEPATH."../includes/tourney/base.inc";
	require $LS_BASEPATH."../includes/tourney/tabs.inc";
	
  NavStruct("tournaments/singletourney", array('tid' => $id));
	StartPage(_("Tournament Admin"));

	user_auth_ex(AUTH_TOURNEY, $id);

	$tourney = new Tourney($id);

	if (isset($setstatus)) {
		echo '<h3 class=content>'._("Set Status").'</h3>';
		$oldStatus = $tourney->Status;
		$newStatus = $setstatus;
		
		echo '<p class=content>';
		echo _("Previous Status").': '.$tourney->GetStatusDesc($oldStatus).'<br>';
		echo '</p>';
		
		echo '<p class=content>';
		if ($newStatus == TS_REGISTRATION) {
			SQL_Query("UPDATE Tourney SET status=$newStatus WHERE id=$id");
			echo _("Registration Opened");
		} elseif ($newStatus == TS_CLOSED) {
			if (!$submitted) {
				FormStart();
					FormValue('setstatus', $setstatus);
					FormValue('id', $id);
					FormValue('submitted', 1);
					FormElement('f_remove', _("Remove current registrations?"), 1, 'checkbox');
					FormElement('', '', _("Set"), 'submit');
				FormEnd();
			} else {
				if ($f_remove) {
					$res = SQL_Query("SELECT id FROM TourneyTeam WHERE tourney=$id");
					while ($row = mysql_fetch_array($res)) {
						SQL_Query("DELETE FROM TourneyTeamMember WHERE team=".$row['id']);
					}
					SQL_Query("DELETE FROM TourneyTeam WHERE tourney=$id");
					echo _("Removed current registrations.").'<br><br>';
				}
				SQL_Query("UPDATE Tourney SET status=$newStatus WHERE id=$id");
				echo _("Registration Closed.");
			}
		} elseif ($newStatus == TS_PREMATCH) {
			if ($oldStatus < TS_PREMATCH) {
				// Start Bracket bestimmen
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
				while ($row = mysql_fetch_array($res)) {
					$teams[] = $row['id'];
				}
				srand ((double)microtime()*1000000);
				shuffle($teams);
				for ($slots = 1; $slots < count($teams); $slots*=2) ;
				printf(_("%d Teams, %d Slots required."), count($teams), $slots);
				echo '<br>';
				$i = 1;
				while (count($teams) < $slots) {
					$newteams = array();
					for($j=0; $j<$i; $j++)
						$newteams[] = $teams[$j];
					$newteams[] = -1;
					for($j=$i; $j<count($teams); $j++)
						$newteams[] = $teams[$j];
					$teams = $newteams;
					$i+=2;
				}
				SQL_Query("DELETE FROM TourneyBracket WHERE tourney=".$id);
				for ($i=0; $i<count($teams); $i++) {
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
			} elseif ($oldStatus == TS_MATCH) {
			if (!$submitted) {
				FormStart();
					FormValue('setstatus', $setstatus);
					FormValue('id', $id);
					FormValue('submitted', 1);
					FormElement('f_remove', _("Remove Games?"), 1, 'checkbox');
					FormElement('', '', _("Set"), 'submit');
				FormEnd();
			} else {
				if ($f_remove) {
					SQL_Query("DELETE FROM TourneyMatch WHERE tourney=$id");
					echo _("Removed current matches.").'<br>';
				} else {
					SQL_Query("UPDATE TourneyMatch SET op1=0, op2=0, score1=0, score2=0, flags=0, status=0 WHERE tourney=$id");
				}
				SQL_Query("UPDATE Tourney SET status=$newStatus WHERE id=$id");
				echo _("Pre Match Set.");
			}
				
			}
		} elseif ($newStatus == TS_MATCH) {
			if ($oldStatus == TS_PREMATCH) {
				if (!$tourney->MatchesInitialized)
					$tourney->InitMatches();

				$pos = new TMatchPos(0, 0, 0);
				
				$res = SQL_Query("SELECT team, options FROM TourneyBracket WHERE tourney=".$tourney->id.' ORDER BY position');
				while ($row = mysql_fetch_array($res)) {
					$tourney->InsertTeamToMatch($row['team'], $pos);
					
					if ($pos->Slot == 1) {
						$pos->Slot = 0;
						$pos->Row++;
					} else {
						$pos->Slot = 1;
					}
				}
				SQL_Query("UPDATE Tourney SET status=$newStatus WHERE id=$id");
				$tourney->Check();
				echo _("Players Moved to Bracket.");
			}
		} else {
			echo '<p class=content>';
			SQL_Query("UPDATE Tourney SET status=$newStatus WHERE id=$id");
			echo _("New Tournament status set");
			echo '</p>';
		}
	} elseif ($action == 'initmatches') {
		$tourney->InitMatches();
	}

	NavPrintBack();

	EndPage();
?>