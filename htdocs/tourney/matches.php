<?
	$LS_BASEPATH = '../';
	require $LS_BASEPATH.'../includes/ls_base.inc';
  require $LS_BASEPATH."../includes/tourney/base.inc";
	require $LS_BASEPATH."../includes/tourney/tabs.inc";
	
  NavStruct("tournaments");

	StartPage($tourney->info['name'], 1, $LS_BASEPATH.'images/tourney/icons/'.$tourney->info['icon']);

	PrintTabs();

	function StartRound($round) {
		echo '<h3 class=content>';
		printf(_("Round %s"), $round);
		echo '</h3>';
		
		echo '<p class=content>';
		echo '<table>';
	}
	
	function EndRound() {
		echo '</table>';
		echo '</p>';
	}


	if ($tourney->TeamSize == 1) {
		$SQLFields = "
			u1.name 'op1name',
			u2.name 'op2name'
		";
		$SQLJoin = "
		LEFT JOIN user u1 ON u1.id=tt1.leader
		LEFT JOIN user u2 ON u2.id=tt2.leader
		";
	} else {
		$SQLFields = "
			tt1.name 'op1name',
			tt2.name 'op2name'
		";
		$SQLJoin = "";
	}
	
	$res = SQL_Query("SELECT
			(IF(tm.col >= 0, tm.col, -tm.col / 2)) * 2 as 'round',
			tm.row,
			tm.col,
			tm.id,
			tm.op1 'team1',
			tm.op2 'team2',
			tm.score1,
			tm.score2,
			tm.status,
			tm.flags,
			UNIX_TIMESTAMP(DATE_ADD(tm.date, INTERVAL ".$tourney->MatchTime." MINUTE)) as 'starttime',
			$SQLFields
		FROM 
			TourneyMatch tm
			LEFT JOIN TourneyTeam tt1 ON tm.op1=tt1.id
			LEFT JOIN TourneyTeam tt2 ON tm.op2=tt2.id
			$SQLJoin
		WHERE tm.tourney=".$tourney->id." AND tm.status >= ".MS_PLAYABLE." ORDER BY round DESC, status, row");

	$prevRound = -99999;
	while ($match = mySQL_fetch_array($res)) {
		$rnd = $match['round'] / 2;
		//echo 'Rnd: '.$match['round'].'<br>';
		if ($prevRound != $rnd) {
			if ($prevRound >= 0)
				EndRound();
			StartRound($rnd + 1);
		}
		$prevRound = $rnd;
		if ($match['team1'] == -1)
			$op1name = '<i>'._("Die").'</i>';
		elseif ($match['team1'] == 0)
			$op1name = '???';
		else
			$op1name = HTMLStr($match['op1name'], 15);
		if ($match['team2'] == -1)
			$op2name = '<i>'._("Die").'</i>';
		elseif ($match['team2'] == 0)
			$op2name = '???';
		else
			$op2name = HTMLStr($match['op2name'], 15);

		if ($match['status'] < MS_PLAYED) {
			$leftID = 'MatchPlayable';
			$centerID = 'MatchPlayable';
			$rightID = 'MatchPlayable';
		} elseif ($match['status'] >= MS_PLAYED) {
			$leftID = ($match['flags'] & MF_OP2WON) ?  'MatchPlayed' : 'TourneyMatchWinner' ;
			$centerID = 'MatchPlayed';
			$rightID = ($match['flags'] & MF_OP2WON) ?  'TourneyMatchWinner' : 'MatchPlayed' ;
		}
		
		echo '<tr>';
		echo '<td class=liste id='.$leftID.' width=160 valign=top><span id=TourneyTeamName>'.$op1name.'</span></td>';
		echo '<td class=liste id='.$centerID.' width=200 align=center>';
		if ($match['status'] < MS_PLAYED) {
			echo _("End Time").': '.DisplayDate($match['starttime'] + $MatchTime * 60);
		} elseif ($match['status'] >= MS_PLAYED) {
			echo '<span id=TourneyScoreLarge>'.$match['score1'].':'.$match['score2'].'</span>';
		}
		echo '<br>';
		echo '<a href="javascript:ShowMatch('.$match['id'].')">'._("Details").'</a>';
		
		echo '</td>';
		echo '<td class=liste id='.$rightID.' width=160 valign=top><span id=TourneyTeamName>'.$op2name.'</span>';
		echo '<br>&nbsp;';
		echo '</td>';
		echo '</tr>';
		//echo $match['round'].': '.$op1name . ' vs ' . $op2name.'<br>';
	}
	EndRound();

	EndPage();

?>