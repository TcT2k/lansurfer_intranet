<?
	include $LS_BASEPATH.'../includes/tourney/tabutil.inc';

	function PrintTabs() {	
		global $tourney, $LS_BASEPATH;

		InitTabs();
		
		echo '<p class=content>';
		
		echo '<b>'._("Administrators").':</b> ';
		$cnt = 0;
		$res = SQL_Query("SELECT
				u.name,
				u.id uid
			FROM orga o
				LEFT JOIN user u ON u.id=o.user
			WHERE o.rights&".TEAM_TOURNEY." 
			ORDER BY u.name");
		
		while ($row = mysql_fetch_array($res)) {
			if ($cnt)
				echo ', ';
			PrintIMSContactLink($row['uid'], HTMLStr($row['name']));
			$cnt++;
		}

		$res = SQL_Query("SELECT
				u.name,
				u.id uid,
				ta.id
			FROM TourneyAdmin ta
				LEFT JOIN user u ON u.id=ta.user
			WHERE ta.tourney=".$tourney->id."
			ORDER BY u.name");

		while ($row = mysql_fetch_array($res)) {
			if ($cnt)
				echo ', ';
			PrintIMSContactLink($row['uid'], HTMLStr($row['name']));
			$cnt++;
		}
		echo '<br>';

		if (user_auth_ex(AUTH_TOURNEY, $tourney->id, 0, false)) {
			echo '<table><tr><td class=content>';
			FormStart('action="orga/admin.php" method="post"');
				FormValue('id', $tourney->info['id']);
				FormSelectStart('setstatus', _("Status"), $tourney->Status);
					FormSelectItem(_("Registration Closed"), TS_CLOSED);
					FormSelectItem(_("Registration Opened"), TS_REGISTRATION);
					if ($tourney->eflags & TEF_BLINDDRAW) FormSelectItem(_("Post Draw"), TS_POSTDRAW);
					FormSelectItem(_("Pre Match"), TS_PREMATCH);
					FormSelectItem(_("Matches"), TS_MATCH);
					FormSelectItem(_("Finish"), TS_FINISHED);
					FormSelectItem(_("Cancel"), TS_CANCELED);
				FormSelectEnd();
			
				FormElement('', '', _("Set"), 'submit');
			FormEnd();
/*
// no longer neccessary
			echo '</td><td class=content valign=top>';
			if (!$tourney->MatchesInitialized) {
				NavPrintAction('orga/admin.php?action=initmatches&id='.$tourney->info['id'], _("Admin: Initialize Matches"));
				echo '<br>';
			}
			if (($tourney->eflags & TEF_BLINDDRAW) and 1) {
				NavPrintAction('orga/admin.php?action=teamsmerge&id='.$tourney->info['id'], _("Admin: Merge Teams"));
				echo '<br>';
			}
			if (($tourney->eflags & TEF_BLINDDRAW) and 1) {
				NavPrintAction('orga/admin.php?action=teamssplit&id='.$tourney->info['id'], _("Admin: Split Teams"));
				echo '<br>';
			}
*/
			echo '</td></tr>';
			echo '</table>';
		} else
			echo '<b>'._("Status").':</b> '.$tourney->GetStatusDesc();
		
		echo '</p>';

		echo '<p class=content><table width=500><tr>';
		PrintTab('rules.php', _("Rules"));
		PrintTab('teams.php', _("Participants"));
		PrintTab('matches.php', _("Matches"), $tourney->Status >= TS_MATCH);
		PrintTab('overview.php', _("Overview"));
		PrintTab('ranking.php', _("Rankings"), $tourney->Status >= TS_MATCH);
		echo '</tr></table></p>';
	}

	$tourney = new Tourney($id);

?>