<?
	class TRound {
		// Public
		var $Col;
		var $MatchCount;
		var $Type;
		var $Optional;
		
		// Private
		var $tourney;
		var $lastSrcRound = false;
		
		function TRound($tourney, $col) {
			$this->Col = $col;
			$this->tourney = $tourney;
		
			if ($this->Col < 0) {
				if ($this->Col % 2)
					$this->Type = RT_LOSER;
				else
					$this->Type = RT_LOSER_EXTRA;
			} else {
				$this->Type = RT_WINNER;
			}
			
			$this->Optional = $col > $tourney->WinnerRounds;
			
			if ($this->Col <= 0) {
				$col = abs($this->Col);
				if ($col % 2)
					$col++;
				$this->MatchCount = $this->tourney->MaxMatches / pow(2, $col / 2);
			} elseif ($this->Col >= 0)
				$this->MatchCount = $this->tourney->MaxMatches / pow(2, $this->Col);
			if ($this->MatchCount < 1)
				$this->MatchCount = 1;
		}
		
		function DisplayName() {
			if ($this->Col >= 0)
				$s = sprintf(_("Round %s"), $this->Col + 1);
			else
				$s = sprintf(_("Round %s"), (($this->Col - 1) / -2) + 1);
			return $s;
		}

		function PrintFields() {
			$vars = get_object_vars($this);
			reset($vars);
			while (list($key, $value) = each($vars)) {
				echo $key." = ".$value."<br>";
			}
		}
		
		function GetDstWinner($row, $srcSlot = 0) {
			if ($this->Col > $this->tourney->WinnerRounds) {
				$dstCol = 0;
				$dstRow = -1;
				$dstSlot = 0;
			} elseif ($this->Col == $this->tourney->WinnerRounds) {			// Finale
				if ($srcSlot && $this->tourney->DoubleLimit) {
					$dstCol = $this->Col + 1;
					$dstRow = 0;
					$dstSlot = 0;
				} else {
					$dstCol = 0;
					$dstRow = -1;
					$dstSlot = 0;
				}
			} elseif ($this->tourney->LoserRounds && $this->Col == -$this->tourney->LoserRounds) {
				$dstRow = 0;
				$dstCol = $this->tourney->WinnerRounds;
				$dstSlot = 1;
			} elseif ($this->Col >= 0) {
				$dstRow = floor($row / 2);
				$dstCol = $this->Col + 1;
				$dstSlot = $row % 2;
			} elseif ($this->Col % 2) {
				$dstRow = $row;
				$dstCol = $this->Col - 1;
				$dstSlot = 0;
			} else {
				$dstRow = floor($row / 2);
				$dstCol = $this->Col - 1;
				$dstSlot = $row % 2;
			}
			return new TMatchPos($dstCol, $dstRow, $dstSlot);
		}
		
		function GetDstLoser($row, $srcSlot = 0) {
			if ($this->MatchCount > $this->tourney->DoubleLimit) {
				$dstCol = 0;
				$dstRow = -1;
				$dstSlot = 0;
			} elseif ($this->Col > $this->tourney->WinnerRounds) {
				$dstCol = 0;
				$dstRow = -1;
				$dstSlot = 0;
			} elseif ($this->Col == $this->tourney->WinnerRounds) {			// Finale
				if ($srcSlot) {
					$dstCol = 0;
					$dstRow = -1;
					$dstSlot = 0;
				} else {
					$dstCol = $this->Col + 1;
					$dstRow = 0;
					$dstSlot = 1;
				}
			}	elseif ($this->Col < 0) {
				$dstCol = 0;
				$dstRow = -1;
				$dstSlot = 0;
			} elseif ($this->Col == 0) {
				$dstCol = -1;
				$dstRow = floor($row / 2);
				$dstSlot = $row % 2;
			} else {
				switch ($this->Col) {
					case 1:  // A
						$dstRow = $this->MatchCount - 1 - $row;
						break;
					case 2:	 // B
						if ($row < $this->MatchCount / 2)
							$dstRow = $this->MatchCount / 2 - $row - 1;
						else
							$dstRow = $this->MatchCount + $this->MatchCount / 2 - $row - 1;
						break;
					case 3:	// C
						if ($row < $this->MatchCount / 2)
							$dstRow = $row + $this->MatchCount / 2;
						else
							$dstRow = $row - $this->MatchCount / 2;
						break;
					case 4: // D
						if ($this->MatchCount <= 4)
							$dstRow = $row;
						else {
							if ($row % 2)
								$dstRow = $row - 1;
							else
								$dstRow = $row + 1;
						}
						break;
					case 5: // E
						$dstRow = $this->MatchCount - 1 - $row;
						break;
					case 6:	// F
						$dstRow = $row;
						break;
					default:
						$dstRow = $row;
				}
				$dstSlot = 1;
				$dstCol = $this->Col * -2;
				if ($dstRow < 1)
					$dstRow = 0;
			}
			
			return new TMatchPos($dstCol, $dstRow, $dstSlot);
		}
		
		function GetNextLogicalRound() {
			if ($this->Col < 0) {
				if ($this->Col % 2)
					$nextCol = $this->Col - 1;
				else
					$nextCol = $this->Col / -2 + 1;
			} elseif ($this->Col < $this->tourney->WinnerMaxRounds) {
				if (!$this->Col || $this->MatchCount > $this->tourney->DoubleLimit)
					$nextCol = $this->Col + 1;
				else
					$nextCol = $this->Col * -2;
			} else
				$nextCol = 0;
			
			if ($nextCol)
				return new TRound($this->tourney, $nextCol);
			else
				return false;
		}
		
		function GetSrc($row) {
			if ($this->Col < 0)
				$srcCol = $this->Col / -2;
			elseif ($this->Col > 0)
				$srcCol = $this->Col - 1;
			else
			  $srcCol = 0;
			  
//			printf("\n<!--Debug: GetSrc: ThisCol: %d ThisRow: %d  SrcCol: %d ", $this->Col, $row, $srcCol);
			if ($this->Col == -1)
				$srcRow = $this->tourney->MaxMatches / 2 - 1 - $row;
			elseif ($this->Col < - 1) {
				if (!$this->lastSrcRound || $this->lastSrcRound->Col != $srcCol)
					$srcRound = new TRound($this->tourney, $srcCol);
				else
					$srcRound = $this->lastSrcRound;
				$srcRow = -1;
				for ($i = 0; $i < $srcRound->MatchCount && $srcRow == -1; $i++)
				{
					$pos = $srcRound->GetDstLoser($i);
					if ($pos->Row == $row)
						$srcRow = $i;
				}
			} else
				$srcRow = -1;
			
			return new TMatchPos($srcCol, $srcRow);
			$this->lastSrcRound = $srcRound;
		}
		
		function SetTime($newtime) {
			$ctime = $newtime;
			$round = $this;
			while ($round) {
				if ($round->Col > 0 && $round->MatchCount < $this->tourney->DoubleLimit)
					$colWhere = " IN (".$round->Col.", ".(($round->Col * -2) + 1).")";
				else
					$colWhere = "=".$round->Col;
				SQL_Query("UPDATE TourneyMatch SET date=FROM_UNIXTIME($ctime) WHERE tourney=".$this->tourney->id." AND col".$colWhere);
				$ctime += $this->tourney->MatchTime * 60;
				$round = $round->GetNextLogicalRound();
			}
		}
		
	}
	

?>