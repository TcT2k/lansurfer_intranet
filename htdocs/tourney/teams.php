<?
	$LS_BASEPATH = '../';
	require $LS_BASEPATH.'../includes/ls_base.inc';
  require $LS_BASEPATH."../includes/tourney/base.inc";
	require $LS_BASEPATH."../includes/tourney/tabs.inc";
	
  NavStruct("tournaments");

	StartPage($tourney->info['name'], 1, $LS_BASEPATH.'images/tourney/icons/'.$tourney->info['icon']);

	PrintTabs();

?>
<script language="JavaScript">
<!--

	function ShowTeam(id) {
		var left =  (screen.availHeight - 500) / 2;
		var top =  (screen.availHeight - 520) / 2;
		window.open("team_detail.php?id=" + id, "TeamDetail", "height=500,width=520,screenX="+left+",screenY="+top+",locationbar=0,menubar=0,resizable=1,scrollbars=1,status=0");
	}

// -->
</script>
<?

	echo '<p class=contentpanel>';
	if ($user_valid && $tourney->Status == TS_REGISTRATION) {
		if ($tourney->UserCanRegister($user_current['id'])) {
			NavPrintAction('register.php?action=register&tourney='.$id, ($tourney->TeamSize > 1) ? _("Create new team") : _("Register for this tourney"));
		}
	}
	$tourneyAdmin = false;
	if (user_auth_ex(AUTH_TOURNEY, $id, 0, false)) {
		echo '<br>';
		NavPrintAction('register.php?action=add&tourney='.$id, ($tourney->TeamSize > 1) ? _("Admin: Create Team") : _("Admin: Add User"));
		echo '<br>';
		NavPrintAction('debug.php?action=add&tourney='.$id, _("Admin: Debug Teams"));
		$tourneyAdmin = true;
	}
	echo '</p>';
	
	echo '<p class=content>';
	$res = SQL_Query("SELECT
			COUNT(tm.id) as 'tmc',
			tt.name as 'TeamName',
			l.name as 'LeaderName',
			l.clan as 'ClanName',
			tt.wwclid as 'TeamWWCLid',
			l.wwclid as 'LeaderWWCLid',
			tt.id
		FROM 
			TourneyTeam tt
			LEFT JOIN TourneyTeamMember tm ON tm.team=tt.id
			LEFT JOIN user l ON tt.leader=l.id
		WHERE 
			tt.tourney=$id
		GROUP BY tt.id
		ORDER BY tmc, tt.name, l.name");
	
	echo '<table class=liste>';
	echo '<tr class=liste>';
	echo '<th class=liste width=180>'._("Name").'</th>';
	if ($tourney->TeamSize == 1) {
		echo '<th class=liste width=100>'._("Clan").'</th>';
	} else {
		echo '<th class=liste width=180>'._("Leader").'</th>';
		echo '<th class=liste width=80>'._("Members").'</th>';
	}
	if ($tourneyAdmin && $tourney->info['options'] & TO_WWCL)
		echo '<th class=liste width=80>'._("WWCL ID").'</th>';
	echo '</tr>';
	$teamCount = 0;
	while ($row = mysql_fetch_array($res)) {
		echo '<tr class=liste>';
		if ($row['tmc'] >= $tourney->TeamSize)
			$extraID = ' id="TourneyFinished"';
		else
			$extraID = '';

		echo '<td class=liste'.$extraID.'><a href="javascript:ShowTeam('.$row['id'].')">';
		echo HTMLStr(($tourney->TeamSize == 1) ? $row['LeaderName'] : $row['TeamName']);
		echo '</a></td>';
		
		if ($tourney->TeamSize == 1) {
			echo '<td class=liste'.$extraID.'>';
			echo ($row['ClanName']) ? HTMLStr($row['ClanName']) : '&nbsp;';
			echo '</td>';
		} else {
			echo '<td class=liste'.$extraID.'>'.HTMLStr($row['LeaderName']).'</td>';
			echo '<td class=liste'.$extraID.' align=right>'.$row['tmc'].'</td>';
		}
		if ($tourneyAdmin && $tourney->info['options'] & TO_WWCL) {
			echo '<td class=liste'.$extraID.' align=right>';
			if ($tourney->TeamSize == 1)
				echo ($row['LeaderWWCLid']) ? 'P'.$row['LeaderWWCLid'] : _("Unknown");
			else
				echo ($row['TeamWWCLid']) ? 'C'.$row['TeamWWCLid'] : _("Unknown");
			
			echo '</td>';
		}
		
		echo '</tr>';
		$teamCount++;
	}
	echo '</table>';

	
	printf(($tourney->TeamSize > 1) ?  _("%d of %d Team(s) registered.") : _("%d of %d Player(s) registered."), $tourney->TeamCount, $tourney->MaxTeams);

	
	echo '</p>';

	EndPage();
?>