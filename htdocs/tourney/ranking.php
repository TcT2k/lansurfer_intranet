<?
	$LS_BASEPATH = '../';
	require $LS_BASEPATH.'../includes/ls_base.inc';
  require $LS_BASEPATH."../includes/tourney/base.inc";
	require $LS_BASEPATH."../includes/tourney/tabs.inc";
	
  NavStruct("tournaments");

	StartPage($tourney->info['name'], 1, $LS_BASEPATH.'images/tourney/icons/'.$tourney->info['icon']);

	PrintTabs();

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