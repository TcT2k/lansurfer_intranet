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
	echo '<p class=content>';
	printf(($tourney->TeamSize > 1) ?  _("%d Team(s).") : _("%d Player(s)."), $tourney->RealStartTeams);
	echo '</p>';

	$Rankings = $tourney->GetRankings();

	echo '<table>';
	echo '<tr>';
	echo '<th class=liste width=10>'._("Place").'</th>';
	echo '<th class=liste width=200>'._("Name").'</th>';
	echo '</tr>';
	
	foreach ($Rankings as $rank => $teams) {
		echo '<tr>';
		echo '<td class=liste align=right valign=top rowspan='.count($teams).'>'.$rank.'</td>';
		$i = 0;
		sort($teams, SORT_STRING);
		foreach ($teams as $team) {
			if ($i)
				echo '<tr>';
			echo '<td class=liste><a href="javascript:ShowTeam('.$team['id'].')">'.HTMLStr($team['name']).'</a></td>';
			echo '</tr>';
			$i++;
		}
	}
	
	echo '</table>';

	EndPage();
?>