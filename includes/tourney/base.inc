<?
	
	// Round Types
	define('RT_WINNER', 1);
	define('RT_LOSER', 2);
	define('RT_LOSER_EXTRA', 3);
	define('RT_WINNER_EXTRA', 4);
	
	// Tourney Options
	define('TO_STRICTTIMES', 1);
	define('TO_IGNOREGROUP', 2);
	define('TO_SCORE_COUNT_GAMES', 4);
	define('TO_LOSER_SUBMIT',  8);
	define('TO_WWCL',  16);
	define('TO_SKIP_DOUBLEFINAL',  32);
	
	// Tourney Status
	define('TS_CLOSED', 			0);
	define('TS_REGISTRATION',10);
	define('TS_PREMATCH', 	 50);
	define('TS_MATCH', 			 51);
	define('TS_FINISHED', 	100);
	define('TS_CANCELED', 	150);
	
	// Draw Game Handling
	define('DRAW_SINGLE', 0);
	define('DRAW_SINGLE_HALF_TIME', 1);
	define('DRAW_DOUBLE_HALF_TIME', 2);

	// Score Type
	define('SCORE_DEFAULT', 0);
	define('SCORE_RELATIVE', 0);
	define('SCORE_CS_CNC', 1);
	define('SCORE_ABSOLUTE', 2);

	// Group Type
	define('GRP_EXCLUSIVE', 0);
	define('GRP_ALLEXCLUSIVE', 1);
	define('GRP_NORESTRICTION', 2);

	// Match Settings
	define('TS_USER', 0);
	define('TS_ADMIN', 1);
	define('TS_RANDOM', 2);
	
	// Match Status
	define('MS_UNINITIALIZED', 		 0);
	define('MS_PLAYABLE', 				10);
	define('MS_DRAW',							20);
	define('MS_PLAYED',						30);

	// Match Flags
	define('MF_UNHANDLED', 		 1);
	define('MF_OP2WON', 		 	 2);
	define('MF_UNDO', 		 	 	 4);
	define('MF_TIMELESS', 		 8);
	define('MF_TIMEOUT',			16);
	
	require $LS_BASEPATH.'../includes/tourney/round.class.inc';
	require $LS_BASEPATH.'../includes/tourney/tourney.class.inc';
	require $LS_BASEPATH.'../includes/tourney/team.class.inc';
	require $LS_BASEPATH.'../includes/tourney/match.class.inc';
	
	class TMatchPos {
		var $Col;
		var $Row;
		var $Slot;
		var $Out = false;
		
		function TMatchPos($col, $row, $slot = 0) {
			$this->Col = $col;
			$this->Row = $row;
			$this->Slot = $slot;
			
			if ($this->Col == 0 && $this->Row < 0)
				$this->Out = true;
		}

		function toString($format = 0) {
			if ($this->Row < 0)
				$s = '<b>'._("Error").'</b> '.$this->Row;
			else {
				if ($format == 1) {
					$s = $this->Col.',';
				} else {
					if ($this->Col <= 0)
						$s = '';
					else
						$s = chr(abs($this->Col) + 64);
				}
				
				$s .= ($this->Row + 1);
				if ($this->Col == 0 && $this->Row < 0)
					$s = '(Out)';
			}
			
			return $s;
		}
		
		function toDbgString() {
			$s = $this->Col.', '.$this->Row.', '.$this->Slot;
			
			return $s;
		}
	}
	

?>