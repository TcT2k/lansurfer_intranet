<?
	class Match {
		var $id;
		var $tourneyID = 0;
		var $tourney;
		var $team1;
		var $team2;
		var $team1ID;
		var $team2ID;
		var $ready1;
		var $ready2;
		var $status;
		var $time;
		var $result;
	
		function Match($id) {
			$this->id = $id;
			$res = SQL_Query("SELECT *, 
				UNIX_TIMESTAMP(date) as 'dateT', 
				UNIX_TIMESTAMP(ready1) as 'ready1T',
				UNIX_TIMESTAMP(ready2) as 'ready2T'
			FROM TourneyMatch WHERE id=".$id);
			if (!($this->info = mysql_fetch_array($res))) {
				LS_Error(_("Match not found"));
			}
			
			$this->time = $this->info['dateT'];
			$this->team1ID = $this->info['op1'];
			$this->team2ID = $this->info['op2'];
			$this->tourneyID = $this->info['tourney'];
			$this->status = $this->info['status'];
			$this->ready1 = $this->info['ready1T'];
			$this->ready2 = $this->info['ready2T'];
		}
		
		function Load($teamsize = 2) {
			if ($this->tourney)
				$teamsize = $this->tourney->TeamSize;
			$this->team1 = new Team($this->team1ID, $teamsize);
//			echo $this->team1->name.' Loaded '.$this->team1ID.'<br>';
			$this->team2 = new Team($this->team2ID, $teamsize);
//			echo $this->team2->name.' Loaded '.$this->team2ID.'<br>';
			$this->result = array();
			$res = SQL_Query("SELECT * FROM TourneyMatchResult WHERE mtch=".$this->id." ORDER BY time");
			while ($row = mysql_fetch_array($res)) {
				$this->result[] = $row;
			}
		}
		
		function PrintResults($maps = false) {
			if (count($this->result)) {
				echo '<table width=100%>';
				echo '<table width=100%>';
				echo '<tr>';
				echo '<th class=liste width="10%">'._("Game").'</th>';
				if ($maps)
					echo '<th class=liste width="20%">'._("Map").'</th>';
				echo '<th class=liste width="10%">'.HTMLStr($this->team1->name).'</th>';
				echo '<th class=liste width="10%">'.HTMLStr($this->team2->name).'</th>';
				echo '</tr>';
	
				$pt1 = $pt1 = 0;
				$rel1 = $rel2 = 0;
				$sc1 = $sc2 = 0;
				$i = 0;
				foreach ($this->result as $res) {
					$pt1 += $res['point1'];
					$pt2 += $res['point2'];
					$rel1 += $res['rel1'];
					$rel2 += $res['rel2'];
					$sc1 += $res['score1'];
					$sc2 += $res['score2'];
					
					echo '<tr>';
					echo '<td class=liste width="10%" align=right>'.($i + 1).'</td>';
					if ($maps)
						echo '<td class=liste width="20%">'.$res['map'].'</td>';
					echo '<td class=liste width="20%" align=right>'.$res['score1'];
					if ($this->tourney->ScoreType == SCORE_RELATIVE)
						echo ' ('.$res['rel1'].'%)';
					echo '</td>';
					echo '<td class=liste width="20%" align=right>'.$res['score2'];
					if ($this->tourney->ScoreType == SCORE_RELATIVE)
						echo ' ('.$res['rel2'].'%)';
					echo '</td>';
					echo '</tr>';
					$i++;
				}
				$relsum = $rel1 + $rel2;
				echo '<tr>';
				echo '<td class=liste width="10%">'._("Games Won").'</td>';
				if ($maps)
					echo '<td class=liste width="20%"></td>';
				echo '<th class=liste width="20%">'.$pt1.'</th>';
				echo '<th class=liste width="20%">'.$pt2.'</th>';
				echo '</tr>';
	
				if (!($this->tourney->info['options'] & TO_SCORE_COUNT_GAMES)) {
					echo '<tr>';
					echo '<td class=liste width="10%">'._("Score").'</td>';
					if ($maps)
						echo '<td class=liste width="20%"></td>';
					echo '<th class=liste width="20%">';
					if ($this->tourney->ScoreType == SCORE_RELATIVE)
					  echo round(($rel1 / $relsum) * 100).'%';
					else
						echo $sc1;
					echo '</th>';
					echo '<th class=liste width="20%">';
					if ($this->tourney->ScoreType == SCORE_RELATIVE)
					  echo round(($rel2 / $relsum) * 100).'%';
					else
						echo $sc2; 
					echo '</th>';
					echo '</tr>';
				}
	
				echo '</table>';
			}
			echo '<p class=content align=center>';
			if ($this->status == MS_DRAW) {
				echo _("Draw Game, decider Game has to be played.");
			} elseif ($this->status >= MS_PLAYED) {
				printf(_("%s won the game"), '<b>'.(($this->info['flags'] & MF_OP2WON) ? $this->team2->name : $this->team1->name). '</b>');
			}
			if ($this->info['flags'] & MF_TIMEOUT) {
				echo '<br><br>';
				echo _("The winner has been choosen because<br>no result was entered at the required time.<br>If you have another result for this match please<br>report it to a tournament admin.");
			}
			echo '</p>';
		}
		
		function GetStatusDesc() {
			switch ($this->status) {
				case MS_UNINITIALIZED:
					$s = _("Opponents not yet determined");
					break;
				case MS_PLAYABLE:
					$s = _("Match may be played");
					break;
				case MS_DRAW:
					$s = _("Draw Match");
					break;
				case MS_PLAYED:
					$s = _("Match Played");
					break;
				default:
					$s = _("Unknown");
					break;
			}
			return $s;
		}
		
		function SetTime($newtime, $setcol = false) {
			if (!$newtime || $newtime == $this->time)
				return;
			if ($setcol) {
				$round = new TRound($this->tourney, $this->info['col']);
				$round->SetTime($newtime);
			} else
				SQL_Query("UPDATE TourneyMatch SET date=FROM_UNIXTIME($newtime) WHERE id=".$this->id);
		}
	}

?>