<?
	$LS_BASEPATH = '../';
	require $LS_BASEPATH.'../includes/ls_base.inc';
  require $LS_BASEPATH."../includes/tourney/base.inc";

	if (!isset($action)) $action = "show";
	
	$Team = new Team($id);
	$Tourney = new Tourney($Team->tourneyID);

	user_auth();

	$TeamAdmin = $user_valid && ($Team->leader == $user_current['id'] || user_auth_ex(AUTH_TOURNEY, -1, 0, FALSE));

  NavStruct("tourney");
	$LS_POPUPPAGE = TRUE;
	
	$title = ($Tourney->TeamSize == 1) ? _("Player Details") : _("Team Details");
	StartPage($title);

	if ($action == "show") {
		echo '<h3 class=content>'.$title.'</h3>';
		
		echo '<p class=content>';
		echo '<b>'._("Name").'</b>: ';
		if ($Tourney->TeamSize == 1)
			PrintIMSContactLink($Team->leader, $Team->name);
		else
			echo HTMLStr($Team->name);
		echo '<br>';

		if ($Tourney->TeamSize == 1) {
			echo '<b>'._("Clan").'</b>: ';
			echo ($Team->clan) ? ($Team->clan) : '('._("None").')';
			echo '<br>';
		}

		echo '<b>'._("Seat").'</b>: ';
		if ($team->leader)
			PrintSeat($team->leader);
		else
			echo '('._("Unknown").')';
		echo '<br>';
		
		echo '</p>';

		if ($Tourney->TeamSize > 1) {
			echo '<h3 class=content>'._("Members").'</h3>';
			echo '<p class=content>';
			if ($user_valid && $Tourney->Status < TS_PREMATCH) {
				if ($Tourney->UserCanRegisterForTeam($user_current['id'], $Team)) {
					NavPrintAction($PHP_SELF.'?action=join&id='.$id, _("Join team"));
					echo '<br>';
				}
				if ($TeamAdmin) {
					if ($tourney->Status < TS_PREMATCH) {
						NavPrintAction($PHP_SELF.'?action=unregister&id='.$id, ($Tourney->TeamSize > 1) ? _("Unregister team from tournament") : _("Unregister from tournament"));
						echo '<br>';
					}
					NavPrintAction($PHP_SELF.'?action=add&id='.$id, _("Add Member"));
					echo '<br>';
				} 
				if ($tourney->Status < TS_PREMATCH && $Team->UserCanLeaveTeam($user_current['id'])) {
					NavPrintAction($PHP_SELF.'?action=leave&id='.$id, _("Leave team"));
					echo '<br>';
				}
			}
			
			echo '</p>';
			echo '<table width="100%">';
			echo '<tr>';
			echo '<th class=liste width="50%">'._("Name").'</th>';
			echo '<th class=liste width="50%">'._("Clan").'</th>';
			echo '</tr>';
			
			foreach ($Team->member as $m) {
				echo '<tr>';
				echo '<td class=liste>';
				
				if ($m['uid'] == $Team->leader)
					$name = '<b>'.$m['name'].'</b>';
				else
					$name = $m['name'];
				PrintIMSContactLink($m['uid'], $name);
				echo '</td>';
				echo '<td class=liste>'.$m['clan'].'</td>';
				if ($TeamAdmin && $m['uid'] != $Team->leader) {
					echo '<td class=content>';
					NavPrintDel($PHP_SELF.'?action=remove&id='.$id.'&mid='.$m['mid']);
					echo '</td>';
				}
				echo '</tr>';
			}
			
			echo '</table>';
		} else {
			if ($user_valid && $Tourney->Status < TS_PREMATCH) {
				if ($TeamAdmin) {
					if ($tourney->Status < TS_PREMATCH) {
						NavPrintAction($PHP_SELF.'?action=unregister&id='.$id, ($Tourney->TeamSize > 1) ? _("Unregister from tournament") : _("Unregister from tournament"));
						echo '<br>';
					}
				} 
			}
		}
		echo '<p class=content>';
		NavPrintAction("javascript:window.close()", _("Close Window"));
		echo '</p>';
	} elseif ($action == 'unregister') {
		echo '<h3 class=content>'._("Unregister from tournament").'</h3>';

		if ($tourney->Status >= TS_PREMATCH)
			LS_Error(_("Invalid tournament status."));
		
		if (!$submitted) {
			echo '<p class=content>'._("Are you sure you want to unregister?").'</p>';
			FormStart();
				FormValue('action', $action);
				FormValue('id', $id);
				FormValue('submitted', 1);
				
				FormElement('', '', _("Unregister"), 'submit');
			FormEnd();
			
			echo '<p class=content>';
			NavPrintAction($PHP_SELF.'?id='.$id, _("Back"));
			echo '</p>';
		} else {
			SQL_Query("DELETE FROM TourneyTeamMember WHERE team=".$id);
			SQL_Query("DELETE FROM TourneyTeam WHERE id=".$id);
			echo '<p class=content>'._("Unregistration successfull.").'</p>';
		
			echo '<p class=content>';
			NavPrintAction("javascript:window.close()", _("Close Window"));
			echo '</p>';
		}
		
	} elseif ($action == 'remove') {

		if (!$submitted) {
			echo '<p class=content>'._("Are you sure you want to remove this team member?").'</p>';
			FormStart();
				FormValue('action', $action);
				FormValue('id', $id);
				FormValue('mid', $mid);
				FormValue('submitted', 1);
				
				FormElement('', '', _("Remove"), 'submit');
			FormEnd();
		} else {
			SQL_Query("DELETE FROM TourneyTeamMember WHERE id=".$mid);
			echo '<p class=content>'._("Member successfully removed.").'</p>';
		}
		
		echo '<p class=content>';
		NavPrintAction($PHP_SELF.'?id='.$id, _("Back"));
		echo '</p>';
	} elseif ($action == 'add') {

		if (isset($f_user)) {
			foreach ($f_user as $u) {
				$res = SQL_Query("SELECT name FROM user WHERE id=".$u);
				$row = mysql_fetch_array($res);
				echo $row['name'].': ';
				if ($Tourney->UserCanRegisterForTeam($u, $Team)) {
					SQL_Query("INSERT INTO TourneyTeamMember SET ".SQL_QueryFields(array(
						'team' => $Team->id,
						'user' => $u
						)));
					$Team->memberCount++;
					echo _("Added");
				} else {
					echo _("Could not be added");
				}
				
				echo '<br>';
			}
		} else {
			echo '<form action='.$PHP_SELF.' method="post">';
			FormValue('action', $action);
			FormValue('id', $id);
			FormValue('tourney', $tourney->id);
		
//			$userSearchSingle = TRUE;
			require $LS_BASEPATH.'../includes/user.search.inc';
				
			echo '<input type="submit" value="';
			echo ($ucount) ? _("Add") : _("Search");
			echo '">';
			echo '</form>';
		}

		echo '<p class=content>';
		NavPrintAction($PHP_SELF.'?id='.$id, _("Back"));
		echo '</p>';
	} elseif ($action == 'leave') {
		if ($tourney->Status < TS_PREMATCH && $Team->UserCanLeaveTeam($user_current['id'])) {
			SQL_Query("DELETE FROM TourneyTeamMember WHERE user=".$user_current['id']." AND team=".$Team->id);
			echo '<p class=content>'._("You have been removed from the team.").'</p>';
		} else {
			echo '<p class=content>'._("You could not be removed from the team.").'</p>';
		}
		
		echo '<p class=content>';
		NavPrintAction($PHP_SELF.'?id='.$id, _("Back"));
		echo '</p>';
	} elseif ($action == 'join') {
		if ($Tourney->UserCanRegisterForTeam($user_current['id'], $Team)) {
			SQL_Query("INSERT INTO TourneyTeamMember SET ".SQL_QueryFields(array(
				'user' => $user_current['id'],
				'team' => $Team->id
				)));
			
			echo '<p class=content>'._("You have been added to the team.").'</p>';
		} else {
			echo '<p class=content>'._("You could not be added to the team.").'</p>';
		}
		
		
		echo '<p class=content>';
		NavPrintAction($PHP_SELF.'?id='.$id, _("Back"));
		echo '</p>';
	}
	

	EndPage();
?>