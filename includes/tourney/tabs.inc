<?
	include $LS_BASEPATH.'../includes/tourney/tabutil.inc';

	function PrintTabs() {	
		global $tourney, $LS_BASEPATH;

		InitTabs();
		
		echo '<p class=content>';
		
		if (user_auth_ex(AUTH_TOURNEY, $id, 0, false)) {
			echo '<table><tr><td class=content>';
			FormStart('action="admin.php" method="post"');
				FormValue('id', $tourney->info['id']);
				FormSelectStart('setstatus', _("Status"), $tourney->Status);
					FormSelectItem(_("Registration Closed"), TS_CLOSED);
					FormSelectItem(_("Registration Opened"), TS_REGISTRATION);
					FormSelectItem(_("Pre Match"), TS_PREMATCH);
					FormSelectItem(_("Matches"), TS_MATCH);
					FormSelectItem(_("Finish"), TS_FINISHED);
					FormSelectItem(_("Cancel"), TS_CANCELED);
				FormSelectEnd();
			
				FormElement('', '', _("Set"), 'submit');
			FormEnd();
			echo '</td><td class=content valign=top>';
			if (!$tourney->MatchesInitialized) {
				NavPrintAction('admin.php?action=initmatches&id='.$tourney->info['id'], _("Admin: Initialize Matches"));
				echo '<br>';
			}
			echo '</td></tr>';
			echo '</table>';
		} else
		echo '<b>'._("Status").':</b> '.$tourney->GetStatusDesc();
		echo '</p>';

		echo '<p class=content><table width=500><tr>';
		PrintTab('rules.php', _("Rules"));
		PrintTab('teams.php', _("Participants"));
		PrintTab('matches.php', _("Matches"));
		PrintTab('overview.php', _("Overview"));
		PrintTab('ranking.php', _("Rankings"));
		echo '</tr></table></p>';
	}

	$tourney = new Tourney($id);

?>