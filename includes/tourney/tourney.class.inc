<?

	class Tourney {
		// Tourney Parameter
		var $id;
		var $MaxTeams;
		var $MaxMatches;
		var $DoubleLimit;
		var $StartTeams;			// Teams in start bracket including Dies
		var $RealStartTeams;	// Teams in start bracked without dies
		var $StartTime;
		var $MatchTime;		// (Games * RoundTime) + PauseTime
		var $TeamSize = 1;
		var $info;
		var $Maps;
		var $TeamTypes;
		var $TeamCount;
		var $ScoreType;
		
		var $MatchesInitialized = false;
		
		// Private Fields
		var $WinnerRounds = 0;
		var $WinnerMaxRounds = 0;
		var $LoserRounds = 0;
		
		
		// Round Fields
		var $rCol;
		
		function Tourney($id = 0) {
			$this->id = $id;
			if ($id) {
				$res = SQL_Query("SELECT *, UNIX_TIMESTAMP(StartTime) as 'startT' FROM Tourney WHERE id='$id'");
				if ($this->info = mysql_fetch_array($res)) {
					$this->id = $id;
					$this->StartTime = $this->info['startT'];
					$this->MaxTeams = $this->info['MaxTeams'];
					$this->DoubleLimit = $this->info['DELimit'];
					$this->MatchTime = $this->info['MatchPause'] + $this->info['Games'] * $this->info['GameLength'];
					$this->TeamSize = $this->info['TeamSize'];
					$this->Status = $this->info['status'];
					$this->MaxTeamSize = $this->TeamSize;
					$this->MinTeamSize = $this->TeamSize;
					$this->ScoreType = $this->info['ScoreType'];
					$a = split("\n", trim($this->info['MapList']));
					foreach ($a as $m)
						$this->Maps[] = trim($m);
					$a = split("\n", trim($this->info['TeamType']));
					foreach ($a as $m)
						$this->TeamTypes[] = trim($m);
					$res = SQL_Query("SELECT COUNT(*) as 'c' FROM TourneyTeam WHERE tourney=".$id);
					$this->TeamCount = mysql_result($res, 0, 0);
					$res = SQL_Query("SELECT COUNT(*) as 'c' FROM TourneyBracket WHERE tourney=".$id);
					$this->StartTeams = mysql_result($res, 0, 0);
					$res = SQL_Query("SELECT COUNT(*) as 'c' FROM TourneyBracket WHERE team!=-1 AND tourney=".$id);
					$this->RealStartTeams = mysql_result($res, 0, 0);
					$res = SQL_Query("SELECT COUNT(*) as 'c' FROM TourneyMatch WHERE tourney=".$id);
					$this->MatchesInitialized = mysql_result($res, 0, 0);
					
					if (!$this->StartTeams) {
						$this->StartTeams = $this->MaxTeams;
					}
				} else 
					LS_Error(_("Invalid tournament ID"));
			} else {
				$this->StartTime = mktime(0, 0, 0, 4, 1, 2001);
				$this->MaxTeams = 256;
				$this->DoubleLimit = 2048;
				$this->MatchTime = 30;
			}
			$this->MaxMatches = $this->StartTeams / 2;
			
			$this->WinnerRounds = log($this->MaxMatches) / log(2);
			if ($this->DoubleLimit > 0) {
				$this->LoserRounds = $this->WinnerRounds * 2;
				$this->WinnerRounds++;
				$this->WinnerMaxRounds = $this->WinnerRounds + 1;
			} else {
				$this->LoserRounds = 0;
				$this->WinnerMaxRounds = $this->WinnerRounds;
			}
			$this->CheckTimeouts();
		}
		
		function CheckTimeouts() {
			if ($this->info['status'] != TS_MATCH || !($this->info['options'] & TO_STRICTTIMES))
				return;
			srand ((double) microtime() * 1000000);
			//return;
	
			do {
				$timeouts = 0;
				
				$res = SQL_Query("SELECT 
						id,
						flags,
						UNIX_TIMESTAMP(ready1) as 'ready1', 
						UNIX_TIMESTAMP(ready2) as 'ready2'
					FROM TourneyMatch WHERE tourney=".$this->id." AND 
						status IN (".MS_PLAYABLE.", ".MS_DRAW.") AND
						((flags & ".MF_TIMELESS.")=0) AND
						((flags & ".MF_UNHANDLED.")=0) AND
						DATE_ADD(date, INTERVAL ".($this->MatchTime + 5)." MINUTE) <= NOW()");
				while ($row = mysql_fetch_array($res)) {
					if ($match['ready1'] && !$match['ready2'])
						$r = 1;
					elseif ($match['ready2'] && !$match['ready1'])
						$r = 0;
					else
						$r = (rand(0, 100) > 50) ? 1 : 0;
					$newFlags = $row['flags'];
					$newFlags |= MF_TIMEOUT | MF_UNHANDLED;
					if ($r) {
						$score1 = 1;
						$score2 = 0;
					} else {
						$score1 = 0;
						$score2 = 1;
						$newFlags |= MF_OP2WON;
					}
					SQL_Query("UPDATE TourneyMatch SET ".SQL_QueryFields(array(
						'score1' => $score1,
						'score2' => $score2,
						'status' => MS_PLAYED,
						'flags' => $newFlags
						))." WHERE id=".$row['id']);
					
					$timeouts++;
				}
				$this->Check();
				/*if ($timeouts)
					echo '<script> alert("TimeOuts: '.$timeouts.'");</script>';
				$timeouts = 0;*/
			} while ($timeouts);
		}
		
		function GetStatusDesc($status = -1) {
			if ($status == -1)
				$status = $this->Status;
			switch ($status) {
				case TS_CLOSED:
					$s = _("Registration closed");
					break;
				case TS_REGISTRATION:
					$s = _("Registration open");
					break;
				case TS_PREMATCH:
					$s = _("Pre Match ");
					break;
				case TS_MATCH:
					$s = _("Matches are beeing played");
					break;
				case TS_FINISHED:
					$s = _("Tournament finished");
					break;
				case TS_CANCELED:
					$s = _("Tournament canceled");
					break;
				default:
					$s = _("Unknown");
					break;
			}
			return $s;
		}
		
		function UserRegistered($id, $ingroup = false) {
			if ($ingroup) {
				$res = SQL_Query("SELECT * FROM TourneyGroup WHERE id=".$this->info['grp']);
				$grp = mysql_fetch_array($res);
				if ($grp['type'] == GRP_EXCLUSIVE) {
					$query = "SELECT tt.id FROM
						TourneyTeamMember tm
						LEFT JOIN TourneyTeam tt ON tt.id=tm.team 
						LEFT JOIN Tourney t on tt.tourney=t.id
						WHERE tm.user=$id AND t.grp=".$this->info['grp'];
				} elseif ($grp['type'] == GRP_ALLEXCLUSIVE) {
					$query = "SELECT tt.id FROM
						TourneyTeamMember tm
						LEFT JOIN TourneyTeam tt ON tt.id=tm.team 
						WHERE tm.user=$id";
				} else {
					$query = '';
				}
			} else {
				$query = "SELECT tt.id FROM
				TourneyTeamMember tm
				LEFT JOIN TourneyTeam tt ON tt.id=tm.team 
				WHERE tm.user=$id AND tt.tourney=".$this->id;
			}
			if ($query) {
				$res = SQL_Query($query);
				if ($u = mysql_fetch_array($res))
					return $u['id'];
				else
					return FALSE;
			} else {
				return FALSE;
			}
		}
		
		function UserCanRegister($id) {
			if ($this->UserRegistered($id, true)) {
				$can = false;
			} else {
				$can = $this->TeamCount < $this->MaxTeams;
			}
			
			return $can;
		}
		
		function UserCanRegisterForTeam($uid, $team) {
			return !$this->UserRegistered($uid) && !$team->UserIsMember($uid) && $team->memberCount < $this->MaxTeamSize;
		}
		
		function PrintFields() {
			$vars = get_object_vars($this);
			reset($vars);
			while (list($key, $value) = each($vars)) {
				echo $key." = ".$value."<br>";
			}
		}
		
		function RoundInit() {
			$this->rCol = -$this->LoserRounds - 1;
		}
		
		function RoundNext() {
			$this->rCol++;
			if ($this->rCol > $this->WinnerMaxRounds) return false;

			$r = new TRound($this, $this->rCol);
			if ($r->Col < 0 && $r->MatchCount >= $this->DoubleLimit)
				$r = $this->RoundNext();
			
			return $r;
		}
		
		function InitMatches() {
			SQL_Query('DELETE FROM TourneyMatch WHERE tourney='.$this->id);
			
			echo _("Initializing Matches...").'<br>';
			$this->RoundInit();
			while ($round = $this->RoundNext()) {
				for ($row = 0; $row < $round->MatchCount; $row++)
					SQL_Query('INSERT INTO TourneyMatch SET '.SQL_QueryFields(array(
						'tourney' => $this->id,
						'op1' => 0,
						'op2' => 0,
						'row' => $row,
						'col' => $round->Col
						)));
			}
			$round = new TRound($this, 0);
			$round->SetTime($this->StartTime);
		}
		
		function ClearMatches() {
			
		}
		
		function InsertTeamToMatch($teamID, $pos, $undo = false) {
			if ($pos->Out /*|| $teamID == 0*/)
				return;
			$res = SQL_Query("SELECT * FROM TourneyMatch WHERE tourney=".$this->id." AND col=".$pos->Col." AND row=".$pos->Row);
			if (!($match = mysql_fetch_array($res)))
				LS_Error(sprintf(_("Invalid Position Row %d Col %d"), $pos->Row, $pos->Col));
			
			if ($pos->Slot == 1) {
				$slot = 'op2';
				$otherSlot = 'op1';
			} else {
				$slot = 'op1';
				$otherSlot = 'op2';
			}
			$match[$slot] = $teamID;
			
			/*printf('Team: %d Row: %d Col: %d Slot: %d Match: %d', $teamID, $pos->Row, $pos->Col, $pos->Slot, $match['id']);
			if ($undo)
				echo ' <b>Undo</b>';
			echo '<br>';
			flush();*/
			
			$newStatus=$match['status'];
			$newFlags=$match['flags'];
			$fields = '';
			// Handle Die
			if ($match['op1'] == -1 && $match['op2'] != 0) {
				$newStatus = MS_PLAYED;
				$newFlags = MF_UNHANDLED | MF_OP2WON;
				$fields = ', score2=1, score1=0';
			} elseif ($match['op2'] == -1 && $match['op1'] != 0) {
				$newStatus = MS_PLAYED;
				$newFlags = MF_UNHANDLED;
				$fields = ', score1=1, score2=0';
			} elseif ($match['op1'] > 0 && $match['op2'] > 0) {
				$newStatus = MS_PLAYABLE;
			} else /*if ($match['op1'] == 0 || $match['op2'] == 0) */{
				$newStatus = MS_UNINITIALIZED;
			}
			if ($undo && $match['status'] >= MS_DRAW) {
				$newFlags |= MF_UNDO | MF_UNHANDLED;
			}
			
			SQL_Query("UPDATE TourneyMatch SET ".SQL_QueryFields(array(
				$slot => $teamID,
				'flags' => $newFlags,
				'status' => $newStatus
				)).$fields." WHERE id=".$match['id']);
		}
		
		function Check() {
			
			do {
				$modcount = 0;
				flush();
				$res = SQL_Query("SELECT *, UNIX_TIMESTAMP(date) as 'dateT' FROM TourneyMatch WHERE tourney=".$this->id." AND flags & ".MF_UNHANDLED);
				while ($match = mysql_fetch_array($res)) {
					/*echo $match['id'].'<br>';
					flush();*/
					
					if ($match['status'] == MS_PLAYED || $match['flags'] & MF_UNDO) {
						if ($match['flags'] & MF_UNDO) {
							SQL_Query("DELETE FROM TourneyMatchResult WHERE mtch=".$match['id']);
							$winner = 0;
							$loser = 0;
						} elseif ($match['flags'] & MF_OP2WON) {
							$winner = $match['op2'];
							$loser = $match['op1'];
						} else {
							$loser = $match['op2'];
							$winner = $match['op1'];
						}
					
						$round = new TRound($this, $match['col']);
						
						$wPos = $round->GetDstWinner($match['row'], ($match['op1'] == $winner) ? 0 : 1);
						$lPos = $round->GetDstLoser($match['row'], ($match['op1'] == $loser) ? 0 : 1);
						//echo 'W: '.$wPos->toString().'<br>';
						//echo 'L: '.$lPos->toString().'<br>';
						$this->InsertTeamToMatch($winner, $wPos, $match['flags'] & MF_UNDO);
						$this->InsertTeamToMatch($loser, $lPos, $match['flags'] & MF_UNDO);
						
						$newflags = $match['flags'];
						$newflags &= ~MF_UNHANDLED;
						$newflags &= ~MF_UNDO;
						if ($match['flags'] & MF_UNDO)
							$newflags &= ~MF_OP2WON;
						$newStatus = $match['status'];
						if ($match['flags'] & MF_UNDO) {
							if (time() > $match['dateT'])
								$newFlags |= MF_TIMELESS;
							if ($match['op1'] > 0 && $match['op2'] > 0)
								$newStatus = MS_PLAYABLE;
							else
								$newStatus = MS_UNINITIALIZED;
						}
						
						SQL_Query("UPDATE TourneyMatch SET flags=".$newflags.", status=".$newStatus." WHERE id=".$match['id']);
						
						$modcount++;
					}
				}
			} while ($modcount);
			
		}
		
		function GetRankings() {
			if ($this->TeamSize == 1) {
				$SQLFields = "
					u1.name 'op1name',
					u2.name 'op2name',
					u1.wwclid 'op1WWCLID',
					u2.wwclid 'op1WWCLID'
				";
				$SQLJoin = "
				LEFT JOIN user u1 ON u1.id=tt1.leader
				LEFT JOIN user u2 ON u2.id=tt2.leader
				";
			} else {
				$SQLFields = "
					tt1.name 'op1name',
					tt2.name 'op2name',
					tt1.wwclid 'op1WWCLID',
					tt2.wwclid 'op2WWCLID'
				";
				$SQLJoin = "";
			}
		
			if ($this->DoubleLimit) {
				$SQLRound = "IF(tm.col >= 0, tm.col, tm.col / -2)";
				$SQLWhere = "(tm.col >= ".$this->WinnerRounds." OR tm.col < 0) AND";
			} else {
				$SQLRound = "tm.col";
				$SQLWhere = "";
			}

			$place = $this->StartTeams;
			$Rankings = array();
			$prevRound = -1;
			
			$res = SQL_Query("SELECT 
					$SQLRound round,
					tm.op1,
					tm.op2,
					tm.flags,
					tm.col,
					$SQLFields
				FROM TourneyMatch tm
					LEFT JOIN TourneyTeam tt1 ON tm.op1=tt1.id
					LEFT JOIN TourneyTeam tt2 ON tm.op2=tt2.id
					$SQLJoin
				WHERE $SQLWhere tm.tourney=".$this->id." AND tm.status >= ".MS_PLAYED."
				ORDER BY round, row");
			while ($row = mysql_fetch_array($res)) {
				if ($prevRound != $row['round'] && ($row['round'] < $this->WinnerMaxRounds || !$this->DoubleLimit)) {
					$round = new TRound($this, $row['col']);
					$place -= $round->MatchCount;
		//			echo $row['round'].'('.$place.';'.$row['col'].';'.$round->MatchCount.')<br>';
				}
					
				if ($row['flags'] & MF_OP2WON) {
					$loser = 'op1';
					$winner = 'op2';
					if ($this->DoubleLimit && $row['round'] == $this->WinnerRounds)
						$winner = false;
				} else {
					$loser = 'op2';
					$winner = 'op1';
				}
				
				if ($row['round'] == $this->WinnerMaxRounds) {
					unset($Rankings[$place + 1]);
					unset($Rankings[$place]);
				}
				
				if ($winner) {
					$new['id'] = $row[$loser];
					$new['name'] = $row[$loser.'name'];
					if ($new['id'] >= 0)
						$Rankings[$place + 1][] = $new;
					if ($row['round'] >= $this->WinnerRounds) {
						$new['id'] = $row[$winner];
						$new['name'] = $row[$winner.'name'];
						if ($new['id'] >= 0)
							$Rankings[$place][] = $new;
					}
				}
				
				$prevRound = $row['round'];
			}
			
			ksort($Rankings, SORT_NUMERIC);
			
			return $Rankings;
		}
		
		function RenderTree() {
			global $LS_BASEPATH;

			$tourneyAdmin = user_auth_ex(AUTH_TOURNEY, $this->id, 0, false);

			if ($this->TeamSize == 1) {
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
					tm.row,
					tm.col,
					tm.id,
					tm.op1 'team1',
					tm.op2 'team2',
					tm.flags,
					tm.status,
					$SQLFields
				FROM 
					TourneyMatch tm
					LEFT JOIN TourneyTeam tt1 ON tm.op1=tt1.id
					LEFT JOIN TourneyTeam tt2 ON tm.op2=tt2.id
					$SQLJoin
				WHERE tm.tourney=".$this->id." ORDER BY col, row");
			$currentMatch = mysql_fetch_array($res);
			$col = 0;
			$row = 0;
			$this->RoundInit();
			echo '<table border=0 cellspacing=0 cellpadding=0><tr>';
			while ($round = $this->RoundNext()) {
				if (!$round->Optional)
					echo '<td class=content align=center width=90 valign=top nowrap>'.$round->DisplayName().'</td>';
			}
			echo '</tr><tr>';
			$this->RoundInit();
			
			while ($round = $this->RoundNext()) {
				if ($round->Optional) {
					$currentMatch = mysql_fetch_array($res);
					$skip = !($currentMatch && $currentMatch['status'] >= MS_PLAYABLE);
				} else
					$skip = false;
				if (!$skip) {
					echo '<td class=content>';
					echo '<table border=0 cellspacing=0 cellpadding=0>';
	
					for ($row = 0; $row < $round->MatchCount; $row++) {
						if ($round->Col < 0) {
							$imgRightFill = '';
							$imgRight = 'middle';
							
							if ($round->Type == RT_LOSER) {
								$imgLeft = 'top_right';
								$imgLeftFill = 'blank';
							} else {
								if ($row % 2 == 0) {
									$imgLeft = 'top_right';
									$imgLeftFill = 'blank';
								} else {
									$imgLeft = 'bottom_right';
									$imgLeftFill = 'right';
								}
							}
						} else {
							$imgLeftFill = '';
							
							if ($round->Col == 0) {
								if ($this->DoubleLimit < $round->MatchCount)
									$imgLeft = 'blank';
								elseif ($row % 2)
									$imgLeft = 'bottom_right';
								else
									$imgLeft = 'top_right';
							} else
								$imgLeft = 'middle';
							if ($row % 2) {
								$imgRightFill = 'left';
								$imgRight = 'bottom_left';
							} else {
								$imgRight = 'top_left';
								$imgRightFill = 'blank';
							}
						}
						
						if ($imgLeftFill)
							$imgLeftFill = $LS_BASEPATH.'images/tourney/'.$imgLeftFill.'.gif';
						if ($imgRightFill)
							$imgRightFill = $LS_BASEPATH.'images/tourney/'.$imgRightFill.'.gif';
						if ($imgLeft)
							$imgLeft = $LS_BASEPATH.'images/tourney/'.$imgLeft.'.gif';
						if ($imgRight)
							$imgRight = $LS_BASEPATH.'images/tourney/'.$imgRight.'.gif';
	
						if ($row > 0) {
							$emptyfill = ($this->MaxMatches - $round->MatchCount) / $round->MatchCount;
							if ($round->Type == RT_LOSER)
								$emptyfill--;
						} else {
							if ($round->Col <= -1) {
								$emptyfill = floor(abs($round->Col / 2));
								if ($round->Type == RT_LOSER_EXTRA)
									$emptyfill++;
								
								$imgLeftFill = $LS_BASEPATH.'images/tourney/blank.gif';
							} else {
								
								$emptyfill = 0;
							}
						}
						if ($emptyfill) {
							echo '<tr>';
							if ($imgLeftFill)
								echo '<td><img width=8 height='.(48 * $emptyfill).' src="'.$imgLeftFill.'"></td>';
							else
							  echo '<td></td>';
							if ($round->Type == RT_WINNER) {
								echo '<td class=TourneyLabel align=right valign=top>'.chr($round->Col + 64).$row.'</td>';
							} else {
								echo '<td></td>';
							}
							if ($imgRightFill)
								echo '<td><img width=8 height='.(48 * $emptyfill).' src="'.$imgRightFill.'"></td>';
							else
							  echo '<td></td>';
							
							echo '</tr>';
						}
							
						while ($currentMatch && ($currentMatch['row'] < $row || $currentMatch['col'] < $round->Col)) {
							$currentMatch = mysql_fetch_array($res);
						}
	
						echo '<tr><td><img width=8 height=48 src="'.$imgLeft.'"></td>';
						$matchValid = $currentMatch && $currentMatch['row'] == $row && $currentMatch['col'] == $round->Col;
						$matchActive = $matchValid && ($currentMatch['status'] >= MS_PLAYABLE || $tourneyAdmin);
						
						echo '<td class=tourney width=90 background="'.$LS_BASEPATH.'images/tourney/match.gif" nowrap align=center';
						if ($matchValid) {
							switch ($currentMatch['status']) {
								case MS_PLAYABLE:
								case MS_DRAW:
									$id = 'Playable';
									break;
								case MS_PLAYED:
									$id = 'Played';
								 	break;
								default:
									$id = 'Uninitialized';
									break;
							}
							echo ' id="Match'.$id.'"';
						}
		      	if ($matchActive)
		      		echo ' onmouseover="CellOver(this);" onmouseout="CellOut(this);" onclick="CellClick(this);"';
						echo '>';
	/*					if ($round->Col > 0)
							echo chr($round->Col + 64).' '.($row + 1);
						else
							echo 'Match '.($row + 1);
						echo '<br>';
						$pos = $round->GetDstWinner($row);
						echo 'W: '.$pos->toString(1);
	
						echo '<br>';
						$pos = $round->GetDstLoser($row);
						echo 'L: '.$pos->toString(1);*/
	
						if ($matchValid) {
							switch ($currentMatch['team1']) {
								case 0:
									$op1name = '???';
									break;
								case -1:
									$op1name = '<i>'._("Die").'<i>';
									break;
								default:
									$op1name = HTMLStr($currentMatch['op1name'], 11);
									break;
							}
							
							switch ($currentMatch['team2']) {
								case 0:
									$op2name = '???';
									break;
								case -1:
									$op2name = '<i>'._("Die").'<i>';
									break;
								default:
									$op2name = HTMLStr($currentMatch['op2name'], 11);
									break;
							}
							if ($currentMatch['status'] >= MS_PLAYED) {
								if ($currentMatch['flags'] & MF_OP2WON) {
									$op1CSS = 'Loser';
									$op2CSS = 'Winner';
								} else {
									$op2CSS = 'Loser';
									$op1CSS = 'Winner';
								}
							} else {
								$op2CSS = 'Player';
								$op1CSS = 'Player';
							}
							
							if ($matchActive)
								echo '<a class=TMatch href="javascript:ShowMatch('.$currentMatch['id'].')">';
							echo '<span id=Match'.$op1CSS.'>'.$op1name.'</span><br><span id=MatchVs>vs</span><br><span id=Match'.$op2CSS.'>'.$op2name.'</span>';
							if ($matchActive)
								echo '</a>';
						} else {
							echo '???';
						}
						
						echo '</td>';
						echo '<td><img width=8 height=48 src='.$imgRight.'></td></tr>';
						
						if ($round->Type == RT_LOSER || ($row == $round->MatchCount - 1)) {
							$attr = '';
							if ($round->Type != RT_LOSER) {
								$imgLeft = $LS_BASEPATH.'images/tourney/blank.gif';
								if ($round->Col > 0) {
									$srcText = chr($round->Col + 64).($row + 1);
									$attr = ' align=right valign=top';
								} else
									$srcText = '';
							} else {
								$imgLeft = $LS_BASEPATH.'images/tourney/bottom_right.gif';
								$pos = $prevRound->GetSrc($row);
								$srcText = sprintf(_("From %s"), $pos->toString());
							}
							echo '<tr><td><img width=8 height=48 src="'.$imgLeft.'"></td>';
							echo '<td nowrap class=TourneyLabel'.$attr.'>'.$srcText.'</td>';
							echo '<td></td></tr>';
						}
					}
					echo '</table>';
					
					echo '</td>';
				}
				$prevRound = $round;
			}
			
			echo '</table></tr>';
		}
	}


?>