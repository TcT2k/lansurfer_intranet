<?
	// Tourney Status
	define("TS_CLOSED", 	1);
	define("TS_OPEN", 		2);
	define("TS_RUNNING", 	3);
	define("TS_ENDED", 		4);
	define("TS_CANCELED",	5);

	// Tourney Flags
	$TF_STARTED = 1;
	$TF_OPEN = 2;
	$TF_DEFMAPS = 4;
	$TF_TIMECHECK = 8;
	$TF_NOJOINCHECK = 16;

	define("TF_STARTED", 1);
	define("TF_OPEN", 2);
	define("TF_DEFMAPS", 4);
	define("TF_TIMECHECK", 8);
	define("TF_NOJOINCHECK", 16);
	define("TF_CANCELED", 32);

	
	// Match Flags
	$MF_PLAYED		=	1;			// Das match wurde gespielt (Ergebniss eingetragen, kein draw)
	$MF_OP2WON		=	2;			// Gegner 2 hat das match gewonnen
	$MF_DRAW			= 4;			// Das Spiel war unentschieden, das Ergebniss der Entscheidungsrunde wird erwartet
	$MF_CHECKED		= 8;			// Das spiel wurde bereits auf Gewinn/Verlusst geprüft
	$MF_TIMEOUT		= 16;			// Das spiel wurde nicht bis runden endzeit + Extrazeit gespielt und der gewinner wurde gelost

	define("MF_PLAYED",   1);	
	define("MF_OP2WON",   2);	
	define("MF_DRAW", 	  4);	
	define("MF_CHECKED",  8);	
	define("MF_TIMEOUT", 16);	
	define("MF_ADMINREQUEST", 32);			// Das Ergebniss wurde festgesetzt

  // Unentschieden Handling
  define("DRAW_SINGLE", 					0);		// Eine einzelne Runde mit Voller Zeit
  define("DRAW_SINGLE_HALF_TIME", 1);		// Einzelne Runde mit Halber Zeit
  define("DRAW_DOUBLE_HALF_TIME", 2);		// 2 Runden mit Halber Zeit
  
  function ShortPlayerName($clan, $name) {
  	if (strlen($clan)>3)
  		$clan = substr($clan, 0, 2)."...";
  	if (strlen($name)>12)
  		$name = substr($name, 0, 11)."...";
  	
  	if ($clan)
  		return "[$clan]$name";
  	else
  	  return $name;
  }


  function shortdate($timestamp) {
  	$dinfo = getdate($timestamp);
  	switch ($dinfo[wday]) {
  		case 0:
  			$day = "So";
  			break;
  		case 1:
  			$day = "Mo";
  			break;
  		case 2:
  			$day = "Di";
  			break;
  		case 3:
  			$day = "Mi";
  			break;
  		case 4:
  			$day = "Do";
  			break;
  		case 5:
  			$day = "Fr";
  			break;
  		case 6:
  			$day = "Sa";
  			break;
  	}
  	
  	$str = $day." ".date("H:i", $timestamp);
  	return $str;
  }
  
  class Round {
  	var $tourney;		// Das Turnier zu dem diese Runde gehört
  	var $index;				// die interene spalte dieses turniers
  	var $dispindex;

  	var	$starttime;
  	var	$endtime;
  	var $scoreTime;
  	var	$length;
  	var $matchcount;
  	
  	// Anzeige
  	var	$endtimeStr;
  	var	$starttimeStr;
  	
  	function Round($atourney, $aindex, $aendtime = 0) {
  		$this->tourney = $atourney;
  		$this->index = $aindex;
  		$this->endtime = $aendtime;
    	$this->dispindex = ($aindex < 0) ? (abs($aindex) + 3) / 2 : abs($aindex) + 1;

			$mul = (abs($this->dispindex) < $atourney->doubletimecols) ? 2 : 1;
			$this->length = $atourney->collength * $mul;
			$this->starttime = $aendtime - $this->length * 60;
  		
  		$this->endtimeStr = shortdate($this->endtime);
  		$this->starttimeStr = shortdate($this->starttime);
  		
  		$this->settingsTime = $this->starttime + (5 * 60);
  		$this->scoreTime = $this->endtime + (5 * 60);
  		
  		$this->defmap = $atourney->maplist[($atourney->colcount + 2) - floor($this->dispindex)]." ";
  	}
  	
  }
	
	class Match {
		// Public
		var $info;
		var $op1_displayname;
		var $op2_displayname;
		var $op1_clan;
		var $op2_clan;
		var $leader1;
		var $leader2;
		var $settings;
		var $round;
		var $members;
		
		// Private
		var $tourney;
		
		function Match($matchid = 0) {
			if ($matchid) {
				$res = SQL_Query("SELECT 
						tm.tourney,
						tm.flags,
						score1,
						score2,
						tm.col,
						op1,
						op2,
						tt1.def_map as 'def_map1',
						tt1.def_teamtype as 'def_teamtype1',
						tt1.def_settings as 'def_settings1',
						tt2.def_map as 'def_map2',
						tt2.def_teamtype as 'def_teamtype2',
						tt2.def_settings as 'def_settings2',
						tm.extratime,
						tt1.name as 'team1_name',
						tt2.name as 'team2_name',
						u1.id as 'leader1',
						u1.name as 'leader1_name',
						u1.clan as 'leader1_clan',
						u2.id as 'leader2',
						u2.name as 'leader2_name',
						u2.clan as 'leader2_clan'
					FROM 
						tourney_matches AS tm
						LEFT JOIN tourney_teams AS tt1 ON tm.op1=tt1.id
						LEFT JOIN user AS u1 ON tt1.leader=u1.id
						LEFT JOIN tourney_teams AS tt2 ON tm.op2=tt2.id
						LEFT JOIN user AS u2 ON tt2.leader=u2.id
					WHERE tm.id=$matchid");
				$this->info = mysql_fetch_array($res);
				$this->leader1 = $this->info[leader1];
				$this->leader2 = $this->info[leader2];
				if (!isset($this->info[extratime]))
					$this->info[extratime] = 0;
				
				// Settings Hohlen
				// Gegner 1
				$res = SQL_Query("SELECT * FROM tourney_settings WHERE team=".$this->info[op1]." AND mtch=".$matchid);
				$this->settings[1] = mysql_fetch_array($res);
				
				// Gegner 2
				$res = SQL_Query("SELECT * FROM tourney_settings WHERE team=".$this->info[op2]." AND mtch=".$matchid);
				$this->settings[2] = mysql_fetch_array($res);
				
				// Unentschieden
				$res = SQL_Query("SELECT * FROM tourney_settings WHERE team=0 AND mtch=".$matchid);
				$this->settings[0] = mysql_fetch_array($res);
			}
		}
		
		function GetMember() {
			$res = SQL_Query("SELECT u.name, u.clan, u.id FROM tourney_teammember AS tm LEFT JOIN user AS u ON tm.user=u.id WHERE team=".$this->info[op1]." ORDER BY u.name");
			for ($i=0; $row=mysql_fetch_array($res); $i++)
				$this->member[1][$i] = $row;
			$res = SQL_Query("SELECT u.name, u.clan, u.id FROM tourney_teammember AS tm LEFT JOIN user AS u ON tm.user=u.id WHERE team=".$this->info[op2]." ORDER BY u.name");
			for ($i=0; $row=mysql_fetch_array($res); $i++)
				$this->member[2][$i] = $row;
		}
		
		function SetTourney($tourney, $result = FALSE) {
			$this->tourney = $tourney;
			if ($tourney) {
				$this->round = $this->tourney->GetRound($this->info[col]);

				// Default Settings verwenden
				$now = time();
				if ($result || $now > $this->round->starttime + (5*60) || $this->info[flags] & MF_PLAYED || $this->info[flags] & MF_DRAW) {

					for ($i = 1; $i<=2; $i++) {
						if (!$this->settings[$i]) {
							$this->settings[$i][teamtype] = $this->info["def_teamtype".$i];
							$this->settings[$i][map] = $this->info["def_map".$i];
							$this->settings[$i][settings] = $this->info["def_settings".$i];
						}
					}
				}
				
				if ($tourney->info[teamsize] <= 1) {
					$this->op1_displayname = $this->info[leader1_name];
					$this->op2_displayname = $this->info[leader2_name];
					$this->op1_clan = $this->info[leader1_clan];
					$this->op2_clan = $this->info[leader2_clan];
				} else {
					$this->op1_displayname = $this->info[team1_name];
					$this->op2_displayname = $this->info[team2_name];
				}
			}
		}
	}
	
	class Tourney {
    // allgemeine Angaben
    var $id;
	  var $endtime;
	  var $teamcount;
	  var $colcount;
	  var $lcolcount;
	  var $maxmatches;
	  var $doubletimecols;
	  var	$info;
		var $status;		

		// Spiel Variablen
		var $matchlength;
		var $matchcount;
		var $matchpause;
		
		// Zeit Informationen
		var $collength;					// Zeit pro Runde
		var	$length;			// Zeit für das ganze Turnier
		
		// Private Info
		var $timeinfovalid;
		var	$currentcol;
		var	$currenttime;
	  
		function Tourney ($id = 0) {
			$this->id = $id;
			if ($id) {
				// allgemeine infos hohlen
			  if ($result=SQL_Query("SELECT *, UNIX_TIMESTAMP(endtime) as 'ending' FROM tourneys WHERE (id=$id)")) {
			    if ($this->info=mysql_fetch_array($result)) {
			    	$this->endtime = $this->info[ending];
			    	$this->doubletimecols = $this->info[doubletimecols];
			    	$this->matchlength = $this->info[roundtime];
			    	$this->matchcount = $this->info[rounds];
			    	$this->matchpause = $this->info[roundpause];
			    }
				}

				// Anzahl der Start Matches Bestimmen
				if ($result=SQL_Query("SELECT COUNT(*) FROM tourney_matches WHERE (tourney=$id) AND (col=0)")) {
				  $this->maxmatches = mysql_result($result,0,0);
				  if ($this->maxmatches == 0)
				  	$this->maxmatches = $this->info[maxteams] / 2;
				  $this->teamcount = $this->maxmatches * 2;
				}
				
        // Spieler anzahl ermitteln
				if ($result=mysql_query("SELECT COUNT(*) FROM tourney_matches WHERE (tourney=$id) AND (col=0) AND ((op1<10) OR (op2<10))")) {
				  $this->teamcount = $this->teamcount - mysql_result($result,0,0);
				}
				
				$maps = $this->info[maplist];
				$start = 0;
				$this->mapcount = 0;
				
				for ($i=0; $i = strpos($maps, "\n", $start); $start = $i + 1) {
					$this->maplist[$this->mapcount] = substr($maps, $start, $i - 1 - $start);
					$this->mapcount++;
				}
				
				global $TF_OPEN, $TF_STARTED;
				
				if ($this->info[flags] & $TF_STARTED)
					$this->status = TS_RUNNING;
				elseif ($this->info[flags] & $TF_OPEN)
					$this->status = TS_OPEN;
				else
					$this->status = TS_CLOSED;
					
			}	else	{
				$this->endtime = mktime(0,0,0,4,2,2000);
				$this->teamcount = 8;
				$this->matchcount = 4;
				$this->doubletimecols = 0;
			}
    }
    
    function InitTiming($debug = false) {
			$this->colcount = 0;
			$matches = 1;
			while ($matches * 2 < $this->teamcount ) {
			  $matches*=2;
			  $this->colcount++;
			}
			$this->maxmatches = $matches;
			
			$this->lcolcount = (($this->colcount * 2) + 2);
			$this->collength = ($this->matchcount * $this->matchlength) + $this->matchpause;
			$this->length = ($this->lcolcount * $this->collength);
			for ($i=0; $i<$this->doubletimecols; $i++) 
				$this->length += $this->collength;
			
			$this->starttime = $this->endtime - ($this->length * 60);
			
			$this->timeinfovalid = true;
			$this->currentcol = -($this->colcount * 2);
			$this->currenttime = $this->endtime - ($this->collength * 60);

			if ($debug) {			
				echo "
					<p>
					<font face=\"Verdana\" size=\"2\">
					
					<b>$this->teamcount</b> Teams<br>
					<b>".($this->colcount + 2)."</b> Runden<br>
					<b>$this->maxmatches</b> Spiele werden in der ersten Runde gespielt<br>
					Pro Runde <b>$this->matchcount</b> Spiele mit jeweils <b>$this->matchlength</b> Minuten Spielzeit und <b>$this->matchpause</b> Minuten Pause<br>
					<b>$this->collength</b> Minuten werden pro Runde gebraucht.<br>
					<b>$this->doubletimecols</b> Runden werden mit doppelter Zeit gespielt<br>
					Das Ganze Turnier dauert <b>".floor($this->length / 60)."</b> Stunden ";
				if ($this->length % 60) 
					echo "und <b>".($this->length % 60)."</b> Minuten";
				echo "<br>
					Das geplante Ende ist <b>".date("H:i d.m.Y", $this->endtime)."</b>.<br>
					&nbsp;&nbsp;Wenn noch der Gewinner 2 mal geschlagen werden muss, dauert das Turnier bis <b>".date("H:i d.m.Y", $this->endtime + ($this->collength * 60))."</b><br>
					Die 1. Runde startet <b>".date("H:i d.m.Y", $this->starttime)."</b><br>
				  </font>
					</p>";
			}
    }
    
    function GetNextRound() {
    	$round = new Round($this, $this->currentcol, $this->currenttime);

			$collength = $this->collength * 60;
			
    	if ($this->currentcol < 0) {
    		$this->currenttime = $round->starttime;

    		if (abs($this->currentcol) < $this->doubletimecols) {
    			$round->starttime -= $collength;
    			$this->currenttime -= $collength;
    		}
    	}
    	else {
    		$this->currenttime = $round->endtime + $collength;

    		if ($this->currentcol > 0) 
    			$this->currenttime += $collength;
    		
    		if ($this->currentcol + 1 < $this->doubletimecols)
    			$this->currenttime += $collength;
    	}

    	$this->currentcol++;

    	return $round;
    }
    
    function GetRound($col) {
    	$this->InitTiming();
			$currentcol = -($this->colcount * 2);

	    while ($currentcol <= $this->colcount + 1 && $currentcol <= $col) {
	    	$round = $this->GetNextRound();
	
	    	$currentcol++;
	    }
	    if ($round && !$round->index == $col)
	    	unset ($round);
	   	
	   	return $round;
    }

		function InsertPlayerToMatch($playerid, $tourney, $col, $row, $pos, $inherited_extratime) {
			global $MF_CHECKED, $tourney_checkcount;
			$query = "SELECT * FROM tourney_matches WHERE (tourney=$tourney) AND (col='$col') AND (row='$row')";
			if ($result=SQL_Query($query)) {
	    	if ($prow=mysql_fetch_array($result)) {
	    		$query = "UPDATE tourney_matches SET op";
	    		if ($pos)	
	    			$query = $query."2";
	    		else
	    			$query = $query."1";
	    		$query = $query."=$playerid, extratime=IF(extratime < $inherited_extratime, extratime + $inherited_extratime, extratime) WHERE (id=".$prow[id].")";
	    	} else {
	    	  $query = "INSERT INTO tourney_matches SET ".SQL_QueryFields(array(
	    	  	"tourney" => $tourney, 
	    	  	"col" => $col, 
	    	  	"row" => $row,
	    	  	"flags" => 0,
	    	  	"extratime" => $inherited_extratime)).", ";
	    		if ($pos)	
	    			$query = $query."op1=1, op2=$playerid";
	    		else
	    			$query = $query."op1=$playerid, op2=1";
	    	}
				if (($query != "" && $result=SQL_Query($query))) {
					$tourney_checkcount++;
				}				
			}
		}
    
	  function clean() {
			global $MF_PLAYED, $MF_CHECKED, $MF_DRAW, $MF_OP2WON, $MF_TIMEOUT, $tourney_checkcount, $TF_TIMECHECK;
	
			
	    // Turnier Ausmaße bestimmen
			if ($result=SQL_Query("SELECT COUNT(*) FROM tourney_matches WHERE (tourney=$this->id) AND (col=0)")) {
			  $maxmatches=mysql_result($result,0,0);
			}
		
			$colcount = 0;
			$matches = 1;
			$matchcount = 0;
			while ($matches  < $maxmatches) {
			  $matches*=2;
				$matchcount+=$matches;
			  $colcount++;
			}
	
	    $mquery = "SELECT
					tm.id as 'matchid',
					op1,
					op2, 
					col, 
					row,
					flags,
					extratime
				FROM 
					tourney_matches as tm, 
					tourney_teams as tt1, 
					tourney_teams as tt2
				WHERE 
					(tm.tourney=$this->id) AND 
				  (tt1.id=op1) AND (tt2.id=op2)
				  AND !(flags & $MF_CHECKED)
				ORDER BY col, row";
	    
	    do {
//	    	echo "Checking...<br>";
		    $tourney_checkcount = 0;
		    $checkstr = "";
		    
		    $prevcol = 0;
		    $ctime = time();
				srand((double)microtime()*1000000);
        
				if ($result=SQL_Query($mquery)) {
						while ($row = mysql_fetch_array($result)) {
							$new_score1 = 0;
							$new_score2 = 0;
							
						  if (!($row[flags] & MF_CHECKED)) {
						  	if (!$row[extratime])
						  		$row[extratime] = 0;
						  	$matchflags = $row[flags];
						    
						    if (!$round || $round->index != $row[col])
						    	$round = $this->GetRound($row[col]);
						    
						  	$checked = FALSE;
						  	
								if ($row[op1] == 2) {
									$looser = $row[op1];
									$winner = $row[op2];
									//EXP
									$matchflags |= MF_PLAYED | MF_OP2WON;

									$new_score1 = 0;
									$new_score2 = 3;
								}
								elseif ($row[op2] == 2) {
									$looser = $row[op2];
									$winner = $row[op1];
									//EXP
									$matchflags |= MF_PLAYED | MF_OP1WON;

									$new_score1 = 3;
									$new_score2 = 0;
								}
								else
								{
									$looser = 0;
									$winner = 0;
								}
								
								if ($row[flags] & MF_PLAYED) {
									if ($row[flags] & MF_OP2WON) {
										$looser = $row[op1];
										$winner = $row[op2];
	//									if ($row[col] == $colcount + 1)
	//										echo "Final Played: Extra Match Needed<br>";
										
									} else {
										$looser = $row[op2];
										$winner = $row[op1];
										if ($row[col] == $colcount + 1) {
	//										echo "Final Played: Winner is known<br>";
											$checked = TRUE;
										}
										
									}
									if ($row[col] == $colcount + 2) {
	//									echo "Extra Match: Played<br>";
										$checked = TRUE;
									}
								}
								elseif ($this->info[flags] & $TF_TIMECHECK && $row[op1] > 9 && $row[op2] > 9 && $ctime > $round->scoreTime + ($row[extratime] * 60)) {
								  $matchflags |= MF_TIMEOUT | MF_PLAYED;
									$rwinner = rand(0, 1);
									if ($rwinner)
									{
									  $winner = $row[op1];
									  $looser = $row[op2];
										$new_score1 = 3;
										$new_score2 = 0;
//										echo "Match Time Out -> <b>".$row[op1]."</b> vs ".$row[op2]."<br>";
									} else {
										$matchflags |= $MF_OP2WON;
									  $winner = $row[op2];
									  $looser = $row[op1];

										$new_score1 = 0;
										$new_score2 = 3;
//										echo "Match Time Out -> ".$row[op1]." vs <b>".$row[op2]."</b><br>";
									}
								}
								
								if (!$checked && $looser > 1 && $winner > 1) {
									if ($row[col] >= 0)
									  $newcol1 = $row[col] + 1;
									elseif ($row[col] == -(2*$colcount))  // Gewinner der loosers Bracket ins finale packen
									{	
										$newcol1 = $colcount+1;
									}
									else
									  $newcol1 = $row[col] - 1;
									  
									if ($row[col] >= 0 || ($row[col] < 0) && ($row[col] % 2) == 0 ) {
										$orgrow = $row[row] + 1;
										if ($orgrow % 2) $orgrow ++;
										$newrow1 = $orgrow / 2;
	
										if ($row[col] < 0)
											$newrow2 = -1;
										elseif ($row[col] > 0) {
											$newrow2 = $row[row] + 1; 
										} else
											$newrow2 = $newrow1;
									} else {
										$newrow1 = $row[row] + 1;
										$newrow2 = -1;
									}
	
									if ($row[col] >= $colcount + 1)
										$newcol2 = $newcol1;									  
									elseif ($row[col] == 0)
										$newcol2 = $row[col] - 1;
									elseif ($row[col] > 0)
										$newcol2 = $row[col] * -2;
										
									// Inverses einfügen
									if ($newcol2 < -1 && $newrow2 >= 0) {
										$matchcount = .5;
										$k = -$colcount * 2;
										$domul = false;
										while ($k <= $newcol2 || $k == 0) {
											if (!(abs($k) % 2)) {
												$matchcount *= 2;
											  $domul = false;
											}
											else
												$domul = true;
											$k++;
										}
	
	                  if ($newcol2 < -2) {
	                  
								    	if ($matchcount > 1)
								    		$looserSrcRow = $matchcount / 2;
								    	else
								    		$looserSrcRow = $matchcount;
	
								    	$k=1;
								    	while ($newrow2 != $looserSrcRow) {
								    		$looserSrcRow--;
								    		if ($looserSrcRow == 0)
								    			$looserSrcRow = $matchcount;
								    		$k++;
								    	}
								    	$newrow2 = $k;
								    }
								    else
								    	$newrow2 = $matchcount - $newrow2 + 1;
									}
				
									$matchpos = $row[row] % 2;
									if ($row[col] < 0 && abs($row[col]) % 2)
										$matchpos = 0;
									if ($row[col] == -(2*$colcount))
										$matchpos = 1;
									elseif ($row[col] == $colcount + 1)
										$matchpos = 1;
				          $this->InsertPlayerToMatch($winner, $this->id, $newcol1, $newrow1 - 1, $matchpos, $row[extratime]);
	
									if ($row[col] == $colcount + 1)
										$matchpos = 0;
									elseif ($row[col] > 0)
										$matchpos = 1;
				          if ($row[col] >= 0 && $row[col] < $colcount + 2)
				          	$this->InsertPlayerToMatch($looser, $this->id, $newcol2, $newrow2 - 1, $matchpos, $row[extratime]);
	
									/* if ($checkstr)
										$checkstr = $checkstr.", ";
									$checkstr = $checkstr.$row[matchid]; */

									$matchflags |= $MF_CHECKED;
				          $query = "UPDATE tourney_matches SET flags=flags | $matchflags ";
				          if ($new_score1 || $new_score2) {
				          	$query .= ", score1=$new_score1, score2=$new_score2";
				          }
				          $query .= " where (id=".$row[matchid].")";
				          SQL_Query($query);

								}
								if ($checked) {
									$matchflags |= $MF_CHECKED;
				          $query = "UPDATE tourney_matches SET flags=flags | $matchflags where (id=".$row[matchid].")";
				          SQL_Query($query);
								}
								
							}
						}
					mysql_free_result($result);
					$result = 0;
					if ($checkstr) {
	          $query = "UPDATE tourney_matches SET flags=flags | $MF_FLAGS where (id IN ($checkstr))";
	          SQL_query($query);
					}
					
				}
			} while ($tourney_checkcount > 0);
			
			$this->InitTiming();
	  }
    

	}
	
?>