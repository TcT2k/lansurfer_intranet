<?
	$LS_BASEPATH = '../';
	require $LS_BASEPATH.'../includes/ls_base.inc';
  require $LS_BASEPATH."../includes/tourney/base.inc";
  require $LS_BASEPATH."../includes/tourney/tabutil.inc";

	if (!isset($action)) $action = "show";
	
	$match = new Match($id);
	$tourney = new Tourney($match->tourneyID);
	$match->tourney = $tourney;

	user_auth();
	
	$tourneyAdmin = user_auth_ex(AUTH_TOURNEY, $tourney->id, 0, false);

	if (!isset($action)) 
		$action = 'show';

  NavStruct("tourney");
	$LS_POPUPPAGE = TRUE;

	function RefreshMainWindow() {
?>
<script language="JavaScript">
<!--
  opener.location.reload();
// -->
</script>
<?			
	}

	function PrintOpponent($team, $winner = false, $readytime) {
		global $tourney;
		
		echo '<td valign=top class=liste';
		if (!$winner)
			echo ' id=MatchPlayed';
		echo ' width=50%>';
		echo '<span id=TourneyTeamName>';
		if ($winner)
			echo '<span id=TourneyMatchWinner>';
		echo $team->name;
		if ($winner)
			echo '</span>';
		echo '</span><br><br>';
		
		echo '<b>'._("Seat").'</b>:<br>';
		if ($team->leader)
			PrintSeat($team->leader);
		else
			echo '('._("Unknown").')';
		echo '<br>';
		
		if ($tourney->TeamSize == 1) {
			echo '<b>'._("Clan").'</b>:<br>';
			echo ($team->clan) ? ($team->clan) : '('._("None").')';
		} else {
			$i = 0;
			echo '<b>'._("Members").'</b>:<br>';
			foreach ($team->member as $m) {
				echo '&nbsp;&nbsp;&nbsp;';
				if ($m['uid'] == $team->leader)
					echo '<b>';
				echo $m['name'].'<br>';
				if ($m['uid'] == $team->leader)
					echo '</b>';
				$i++;
			}
			for ($i = $i; $i< $tourney->TeamSize; $i++)
				echo '<br>';
		}
		if ($readytime) {
			echo '<br><b>'._("Ready since").'</b>:<br>'.DisplayDate($readytime);
		}
		echo '</td>';
	}
	
	StartPage(_("Match Details"));
	
	$match->Load($tourney->TeamSize);
	$teamAdmin = $user_valid && ($match->team1->leader == $user_current['id'] || $match->team2->leader == $user_current['id'] || $tourneyAdmin);

	PrintMatchTabs();
	
	echo '<table width=100%>';
	echo '<tr><th colspan=2 class=liste>'._("Opponents").'</th></tr>';
	echo '<tr>';
	PrintOpponent($match->team1, $match->status >= MS_PLAYED && !($match->info['flags'] & MF_OP2WON), $match->ready1);
	PrintOpponent($match->team2, $match->status >= MS_PLAYED && $match->info['flags'] & MF_OP2WON, $match->ready2);
	echo '</tr>';
	
	if ($action == 'show') {
		echo '<tr><th colspan=2 class=liste>'._("Status").'</th></tr>';
		echo '<tr>';
		echo '<td colspan=2 class=liste>';
		
		// -----------------------------------------------
		/*echo '<br>';
		echo 'Row: '.$match->info['row'].' Col: '.$match->info['col'].'<br>';
		
		$round = new TRound($tourney, $match->info['col']);
		$pos = $round->GetDstWinner($match->info['row']);
		echo 'Winner: '.$pos->toDbgString().'<br>';
		$pos = $round->GetDstLoser($match->info['row']);
		echo 'Loser: '.$pos->toDbgString();
		
		echo '<br><br>';*/
		// -----------------------------------------------
		
		echo '<b>'._("Status").'</b>: '.$match->GetStatusDesc().'<br>';
		echo '<b>'._("Scheduled Time").'</b>: '.DisplayDate($match->time).' - '.DisplayDate($match->time + $tourney->MatchTime * 60).'<br>';
		switch ($match->status) {
			case MS_PLAYABLE:
				if ($user_valid && (
					($match->team1->leader == $user_current['id'] && !$match->ready1) 
					|| ($match->team2->leader == $user_current['id'] && !$match->ready2) )) {
					echo '<p align=center>';
					FormStart();
						FormValue('id', $id);
						FormValue('action', 'ready');
						
						FormElement('', '', _("Ready to Play"), 'submit');
					FormEnd();
					echo '</p>';
				}
			case MS_DRAW:
				if ($teamAdmin) {
					echo '<p align=center>';
					if ($tourney->info['options'] & TO_STRICTTIMES) {
						printf(_("The Result has to be submitted until %s.<br>If no result has been submitted at this time the winner will be choosen randomly."), 
							DisplayDate($match->time + ($tourney->MatchTime + 5) * 60));
					}
					FormStart();
						FormValue('id', $id);
						FormValue('action', 'submitresult');
						
						FormElement('', '', _("Submit Result"), 'submit');
					FormEnd();
					echo '</p>';
				}
				break;
			case MS_PLAYED:
				$match->PrintResults();
				if ($tourneyAdmin && $match->team1ID > 0 && $match->team2ID > 0) {
					echo '<p align=center>';
					FormStart();
						FormValue('id', $id);
						FormValue('action', 'undo');
						
						FormElement('', '', _("Undo"), 'submit');
					FormEnd();
					echo '</p>';
				}
				break;
			
		}
		echo '</td>';
		echo '</tr>';
		
		if ($tourneyAdmin && $match->status <= MS_PLAYABLE) {
			echo '<tr><th colspan=2 class=liste>'._("Time Settings").'</th></tr>';
			echo '<tr><td class=liste colspan=2>';
			echo '<p class=content>';
			FormStart();
				FormValue('action', 'settime');
				FormValue('id', $id);
				
				$f_start = $match->time;
				FormElement('f_start', _("Match Start Time"), $f_start, "datetime");
				FormElement('f_setcol', _("Set this time for all matches in this round"), 1, 'checkbox', 'checked');
				FormElement('f_timeless', _("Ignore Time"), 1, 'checkbox', ($match->info['flags'] & MF_TIMELESS) ? 'checked' : '');
				
				FormElement('', '', _("Set"), 'submit');
			FormEnd();
			echo '</p>';
			echo '</td></tr>';
		}
	} elseif ($action == 'submitresult') {
		echo '<tr><th colspan=2 class=liste>'._("Submit Result").'</th></tr>';
		echo '<tr>';
		echo '<td colspan=2 class=liste>';

		if ($submitted) {
			
			// Load allready entered results
			$match->Load($tourney->TeamSize);

			$Results = array();
			for ($i = 0; $i < $f_entryGames; $i++) {
				$score1 = $f_score[$i][0];
				$score2 = $f_score[$i][1];
				$rel1 = $rel2 = 0;
				switch ($tourney->ScoreType) {
					case SCORE_RELATIVE: {
						if ($f_score[$i][0] <= 0) {
							$score1 += abs($f_score[$i][0]) + 1;
							$score2 += abs($f_score[$i][0]) + 1;
						}
						if ($f_score[$i][1] <= 0) {
							$score1 += abs($f_score[$i][1]) + 1;
							$score2 += abs($f_score[$i][1]) + 1;
						}
						$scoresum = $score1 + $score2;
						$rel1 = round(($score1 / $scoresum) * 100);
						$rel2 = round(($score2 / $scoresum) * 100);
						$pt1 = ($rel1 > $rel2) ? 1 : 0;
						$pt2 = ($rel2 > $rel1) ? 1 : 0;
						break;
					}
					case SCORE_ABSOLUTE: {
						$pt1 = ($score1 > $score2) ? 1 : 0;
						$pt2 = ($score2 > $score1) ? 1 : 0;
						
						break;
					}
					default:
						LS_Error(sprintf(_("Score Type %d not supported."), $tourney->ScoreType));
				}
				
				$newRes = array(
					'mtch' => $match->id,
					'tourney' => $tourney->id,
					'map' => $f_map[$i],
					'score1' => $f_score[$i][0],
					'score2' => $f_score[$i][1],
					'rel1' => $rel1,
					'rel2' => $rel2,
					'point1' => $pt1,
					'point2' => $pt2,
					);
				$Results[] = $newRes;
				$match->result[] = $newRes;
			}
			
			$pt1 = $pt2 = 0;
			$sc1 = $sc2 = 0;
			$rel1 = $rel2 = 0;
			$i = 0;
			foreach ($match->result as $res) {
				$pt1 += $res['point1'];
				$pt2 += $res['point2'];
				$rel1 += $res['rel1'];
				$rel2 += $res['rel2'];
				$sc1 += $res['score1'];
				$sc2 += $res['score2'];
			}
			$relsum = $rel1 + $rel2;
			switch ($tourney->ScoreType) {
				case SCORE_RELATIVE:
					$score1 = round(($rel1 / $relsum) * 100);
					$score2 = round(($rel2 / $relsum) * 100);
					break;
				case SCORE_ABSOLUTE:
					$score1 = $sc1;
					$score2 = $sc2;
					break;
			}
			if ($tourney->info['options'] & TO_SCORE_COUNT_GAMES) {
				$score1 = $pt1;
				$score2 = $pt2;
			}
			
			$flags = 0;
			if ($score1 > $score2) {
				$winner = 'team1';
				$loser = 'team2';
				if (!($tourney->info['options'] & TO_SCORE_COUNT_GAMES))
					$pt1++;
			} elseif ($score2 > $score1) {
				$winner = 'team2';
				$loser = 'team1';
				$flags |= MF_OP2WON;
				if (!($tourney->info['options'] & TO_SCORE_COUNT_GAMES))
					$pt2++;
			} else {
				$winner = '';
				$loser = '';
				$flags |= MF_TIMELESS;
			}
			
			if ($winner != '') {
				$newStatus = MS_PLAYED;
				$flags |= MF_UNHANDLED;
			} else {
				$newStatus = MS_DRAW;
			}

			if ($tourney->info['options'] & TO_LOSER_SUBMIT && $winner && !$tourneyAdmin) {
				if ($match->$loser->leader != $user_current['id']) {
					$submitError = true;
				}
			}

			if (!$submitError) {
				foreach ($Results as $res) {
					SQL_Query("INSERT INTO TourneyMatchResult SET time=NOW(),".SQL_QueryFields($res));
				}
				
				SQL_Query("UPDATE TourneyMatch SET ".SQL_QueryFields(array(
					'status' => $newStatus,
					'score1' => $pt1,
					'score2' => $pt2,
					'flags' => $flags
					)).' WHERE id='.$match->id);
	
				$tourney->Check();
				RefreshMainWindow();
	
				echo _("The result has been submitted.").'<br>';
	
				$match = new Match($id);
				$tourney = new Tourney($match->tourneyID);
				$match->tourney = $tourney;
				$match->Load();
				$match->PrintResults();
			}
		}
		
		if (!$submitted || $submitError) {
			echo '<br>';
			if ($tourney->info['options'] & TO_LOSER_SUBMIT)
				echo _("The result must be submitted by the loser of this match.").'<br>';
			echo '<form action="'.$PHP_SELF.'" method="post">';
			FormValue('id', $id);
			FormValue('action', $action);
			FormValue('submitted', 1);
	
			echo '<table width=100%>';
			echo '<tr>';
			echo '<th class=liste width="10%">'._("Game").'</th>';
			if (count($tourney->Maps) > 1)
				echo '<th class=liste width="20%">'._("Map").'</th>';
			echo '<th class=liste width="20%">'.HTMLStr($match->team1->name).'</th>';
			echo '<th class=liste width="20%">'.HTMLStr($match->team2->name).'</th>';
			echo '</tr>';

			$entryGames = $tourney->info['Games'];
			if ($match->status == MS_DRAW) {
				switch ($tourney->info['DrawHandling']) {
					case DRAW_SINGLE:
					case DRAW_SINGLE_HALF_TIME:
						$entryGames = 1;
						break;
					case DRAW_DOUBLE_HALF_TIME:
						$entryGames = 2;
						break;
				}
			}
			echo '<input type=hidden name=f_entryGames value='.$entryGames.'>';
			
			for ($i = 0; $i < $entryGames; $i++) {
				$f_score[$i][0] = 0;
				$f_score[$i][1] = 0;
				echo '<tr>';
				echo '<td class=liste width="10%" align=right>'.($i + 1).'</td>';
				if (count($tourney->Maps) > 1)
					echo '<td class=liste width="20%"><input class=form_field size=14 name="f_map['.$i.']" value="'.$f_map[$i].'"></td>';
				echo '<td class=liste width="20%" align=right><input class=form_field size=4 name="f_score['.$i.'][0]" value="'.$f_score[$i][0].'"></td>';
				echo '<td class=liste width="20%" align=right><input class=form_field size=4 name="f_score['.$i.'][1]" value="'.$f_score[$i][1].'"></td>';
				echo '</tr>';
			}
			echo '</table>';		
			echo '<div align=right><br><input type=submit class=form_btn value="'._("Submit Result").'">&nbsp;</div>';
		}
		
		
		echo '</form>';
		echo '</td>';
		echo '</tr>';
	} elseif ($action == 'undo') {
		echo '<tr><th colspan=2 class=liste>'._("Status").'</th></tr>';
		echo '<tr>';
		echo '<td colspan=2 class=liste>';
		if (!$tourneyAdmin)
			LS_Error(_("This Function requires you to be logged in as tournament admin."));
			
		SQL_Query("UPDATE TourneyMatch SET flags=flags | ".(MF_UNHANDLED | MF_UNDO | MF_TIMELESS)." WHERE id=".$match->id);
		
		$tourney->Check();
		
		echo _("Result Undone").'<br>';
		RefreshMainWindow();
		echo '</td>';
		echo '</tr>';
	} elseif ($action == 'settime') {
		if (!$tourneyAdmin)
			LS_Error(_("This Function requires you to be logged in as tournament admin."));
		echo '<tr><th colspan=2 class=liste>'._("Match Time").'</th></tr>';
		echo '<tr>';
		echo '<td colspan=2 class=liste>';
		
		$f_start = FormDateTime($f_start);
		
		if ($f_start != $match->time) {
			echo _("New Time:").' '.DisplayDate($f_start);
			$match->SetTime($f_start, $f_setcol);
		}
		
		/*if ($f_time) {
			SQL_Query("UPDATE TourneyMatch SET date='".$f_time."' WHERE tourney=".$tourney->id." AND col=".$match->info['col']);
		}*/
		
		$newFlags = $match->info['flags'];
		if ($f_timeless)
			$newFlags |= MF_TIMELESS;
		else
			$newFlags &= ~MF_TIMELESS;
		SQL_Query("UPDATE TourneyMatch SET flags=$newFlags WHERE id=".$match->id);
		
		echo '<br>';
		echo _("Time Set.");
		echo '</td>';
		echo '</tr>';
	} elseif ($action == 'ready') {
		echo '<tr><th colspan=2 class=liste>'._("Ready to Play").'</th></tr>';
		echo '<tr>';
		echo '<td colspan=2 class=liste>';
		if ($user_valid) {
			if ($match->team1->leader == $user_current['id']) {
				$field = 'ready1';
			} elseif ($match->team2->leader == $user_current['id']) {
				$field = 'ready2';
			} else {
				$field = '';
			}
			if ($field) {
				SQL_Query("UPDATE TourneyMatch SET $field=NOW() WHERE id=".$match->id);
			}
		}
		echo _("You have been marked as ready for this match.<br><br>If the score time is reached and no result<br>has been entered <b>you</b> will get a default win.<br>(Only if the opponent has not been marked as ready)");
		echo '</td>';
		echo '</tr>';
		
	}
	echo '</table>';
	
	echo '<p class=content>';
	if ($action == 'show')
		NavPrintAction("javascript:window.close()", _("Close Window"));
	else
		NavPrintAction($PHP_SELF."?id=".$id, _("Back"));
	echo '</p>';

	EndPage();
?>