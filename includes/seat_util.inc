<?

	function RomanNumber($num)
  {  // Erstellt von Ralf Pfeifer (PfeiferR@aol.com, http://members.aol.com/PfeiferR)

        /* Konstante und Variable definieren */
        $EinheitRoemisch = array( "M", "D", "C", "L", "X", "V", "I");
        $EinheitArabisch = array(1000, 500, 100,  50,  10,   5,   1);
        $ArabischeZahl = round($num);      // Umwandeln der Eingabe in eine Ganzzahl
        $RoemischeZahl = "";

        if ($ArabischeZahl <= 0)
        	return "Error";
				
				$RoemischeZahl = "";
	
        for ($Nr = 0; $Nr < count($EinheitArabisch); $Nr++)
        {
           while ($ArabischeZahl >= $EinheitArabisch[$Nr])
           {
              $RoemischeZahl .= $EinheitRoemisch[$Nr];
              $ArabischeZahl -= $EinheitArabisch[$Nr];
           }

           for ($Pos = count($EinheitArabisch); $Pos > $Nr; $Pos--)
           {
              $Wert = $EinheitArabisch[$Nr] - $EinheitArabisch[$Pos];
              if (($EinheitArabisch[$Pos] < $Wert) && ( $Wert <= $ArabischeZahl))
              {
                 $RoemischeZahl .= $EinheitRoemisch[$Pos] . $EinheitRoemisch[$Nr];
                 $ArabischeZahl -= $Wert;
              }
            }
         }

         return $RoemischeZahl;
  }
  

	function PrintBlockDesc($desc, $vertical = FALSE) {
		echo "<td ";
		
		if ($desc) {
			echo "class=liste align=center";
			echo ">";
			
			if ($vertical)
				echo "<span style=\"writing-mode:tb-rl;\">";
			echo $desc;
			if ($vertical)
				echo "</span>";
		}
		else
			echo ">&nbsp;";
		
		echo "</td>";
	}

	function RenderBlock($Block, $edit = FALSE, $open = TRUE, $hrow = -1, $hcol = -1) {
		global $LS_BASEPATH, $SeatSepRow, $SeatSepCol, $user_valid, $user_current, $sess;

		$cols = $Block['cols'];
		$rows = $Block['rows'];
		$vertical = !$Block['orientation'];
		$sep_freq = 2;


		$res = SQL_Query("SELECT * FROM seat_sep WHERE (block=".$Block['id'].") AND (orientation=0) ORDER BY value");
		while ($row = mysql_fetch_array($res)) {
			$SeatSepRow[$row['value']] = 1;
		}

		$res = SQL_Query("SELECT * FROM seat_sep WHERE (block=".$Block['id'].") AND (orientation=1) ORDER BY value");
		while ($row = mysql_fetch_array($res)) {
			$SeatSepCol[$row['value']] = 1;
		}

		$x = 0;
		$y = 0;
		if ($vertical) {
			$CountX = $cols;
			$CountY = $rows;
			$SepX = $SeatSepCol;
			$SepY = $SeatSepRow;
			$SQLSort = "row,col";
		} else {
			$CountX = $rows;
			$CountY = $cols;
			$SepX = $SeatSepRow;
			$SepY = $SeatSepCol;
			$SQLSort = "col,row";
		}
		
		$bl_color = $Block['color'];
		if ($edit)
			$bl_color = "";
		
		unset($row);
		$sres = SQL_Query("SELECT 
			seats.col,
			seats.row,
			seats.status,
			user.name,
			user.clan,
			guest.id
			FROM seats LEFT JOIN guest ON seats.guest=guest.id LEFT JOIN user ON guest.user=user.id
			WHERE (block=".$Block['id'].") ORDER BY $SQLSort");
		$Seat = mysql_fetch_array($sres);

		
		if (!$edit && $user_valid) {
			
			// User infos auswerten

			$res = SQL_Query("SELECT * FROM guest WHERE user=".$user_current['id']);
			$Guest = mysql_fetch_array($res);
			if ($Guest) {
				$res = SQL_Query("SELECT * FROM seats WHERE guest=".$Guest['id']." AND status=".SEAT_OCCUPIED);
				$SeatMine = mysql_fetch_array($res);
				$res = SQL_Query("SELECT COUNT(*) FROM seats WHERE guest=".$Guest['id']." AND status=".SEAT_RESERVED);
				$ReserveCount = mysql_result($res, 0, 0);
				if ($Guest[flags] & GUEST_PAID)
					$guest_paid = TRUE;
			}
		} else {
			$guest_paid = FALSE;
		}
		if (!isset($SeatMine))
			$SeatMine = 0;
			
			
/*		echo "Debug: <br>";
		echo "User: ".$user_current[id].", ";
		if ($Guest)
			echo "Guest Found, ";
		if ($guest_paid)
			echo "Guest Paid, ";*/


		if (!$edit) {
			echo "<table>\n<tr>";
			echo "<td>&nbsp;</td>";
			
			PrintBlockDesc($Block['text_tl']);
			PrintBlockDesc($Block['text_tc']);
			PrintBlockDesc($Block['text_tr']);
						
			echo "<td>&nbsp;</td>";
			echo "</tr><tr>";

			PrintBlockDesc($Block['text_lt'], TRUE);
			echo "<td colspan=3 rowspan=3 valign=top align=center>";
		}

		$imgsize = 14;
		
		echo "<table>";
			$seatindex = 0;
		
			for ($y=-1; $y<$CountY; $y++) {

				if (/*$y > 0 && */isset($SepY[$y-1]))
					echo "<tr><td colspan=".($CountX + 1 + count($SepX))."><img src=\"".$LS_BASEPATH."images/pixel.gif\" width=1 height=6></td></tr>";
				echo "<tr class=liste>";
				
				for ($x=-1; $x<$CountX; $x++) {
					$col = (!$vertical) ? $y : $x;
					$row = (!$vertical) ? $x : $y;
				
					if ($y < 0 && isset($SepX[$x-1])) {
						echo "<td rowspan=".($CountY + 1 + count($SepY))."><img src=\"".$LS_BASEPATH."images/pixel.gif\" width=6 height=1></td>";
					}

					if ($x < 0 && $y < 0)
						echo "<th></th>";
					elseif ($x < 0) {
						echo "<th";
						if ($bl_color)
							echo " style=\"BACKGROUND-COLOR: $bl_color;\"";
						echo " class=liste>";
						if ($vertical)
							echo chr($y + 65);
						else
							echo $y + 1;
						echo "</th>";
					}	elseif ($y < 0) {
						echo "<th";
						if ($bl_color)
							echo " style=\"BACKGROUND-COLOR: $bl_color;\"";
						echo " class=liste>";
						if (!$vertical)
							echo chr($x + 65);
						else
							echo $x + 1;
						echo "</th>";
						
					} else {

						if ($edit) {
							echo "<td class=liste>";
							echo "<input type=checkbox value=1 name=\"f_seats[".$seatindex."]\"";
							if (!($Seat && $Seat['status'] == SEAT_DELETED && $Seat['col'] == $col && $Seat['row'] == $row))
								echo " checked";
							echo ">";
							echo "</td>\n";
							$seatindex++;
						} else {
							if ($Seat && $Seat['status'] == SEAT_DELETED && $Seat['col'] == $col && $Seat['row'] == $row)
								$status = 0;
							else
								$status = ($Seat && $Seat['col'] == $col && $Seat['row'] == $row) ? $Seat['status'] : 1;
						
							if ($status) {
								$text = "Reihe ".chr($row + 65)." Platz ".($col + 1);
								$link = FALSE;

								switch ($status) {
									case 2:
										$pic = "occupied";
										$text .= "\nReserviert von ";
											if (isset($Seat['name']))
												$text .= $Seat['name'];
											else
												$text .= "Unbekannt";
										if (isset($Seat['clan']) && $Seat['clan'])
											$text .= "\nClan: ".$Seat['clan'];
										
										if (isset($Seat['id']) && $Seat['id'] == $Guest['id']) {
											$pic .= "_mine";
											$link = TRUE;
										} elseif (isset($Seat['clan']) && isset($user_current['clan']) && $user_current['clan'] != "" && $Seat['clan'] == $user_current['clan'])
											$pic .= "_clan";
										break;
									case 3:
										$pic = "reserved";
										$text .= "\nVorgemerkt von ";
										if (isset($Seat['name']))
											$text .= $Seat['name'];
										else
											$text .= "Unbekannt";
										if (isset($Seat['clan']) && $Seat['clan'])
											$text .= "\nClan: ".$Seat['clan'];
										if (isset($Seat['clan']) && $Seat['id'] == $Guest['id']) {
											$pic .= "_mine";
											$link = TRUE;
										} elseif (isset($user_current['clan']) && $user_current['clan'] != "" && $Seat['clan'] == $user_current['clan'])
											$pic .= "_clan";
										if (!$SeatMine)
											$link = TRUE;
										break;
									default:
										$pic = "free";
										if ($SeatMine && $ReserveCount >= 4)
											$link = FALSE;
										else
											$link = TRUE;
								}
								/*
									1 Leer
									2 Besetzt
									3 Reserviert
								
								*/
								if (!$open /* || !$guest_paid*/)
									$link = FALSE;
							
								echo "<td class=liste align=center>";
								if ($link) {
									$url = "seat_reserve.phtml?col=$col&row=$row&block=".$Block['id'];
									if (isset($sess))
										$url = $sess->url($url);
									echo "<a href=\"".$url."\">";
								}
								
								if ($hrow == $row && $hcol == $col)
									$thissize = $imgsize * 2;
								else
									$thissize = $imgsize;
								echo "<img border=0 alt=\"$text\" src=\"".$LS_BASEPATH."images/seat/$pic.gif\" width=$thissize height=$thissize>";
								if ($link)
									echo "</a>";
								echo "</td>\n";
							} else {
								echo "<td><img border=0 src=\"".$LS_BASEPATH."images/pixel.gif\" width=$imgsize height=$imgsize></td>";
							}
						}

						if ($Seat && $Seat['col'] == $col && $Seat['row'] == $row)  // Sitz abgearbeitet -> Nächster Sitz
							$Seat = mysql_fetch_array($sres);

					}
				}
				echo "</tr>\n";
			}
		
		echo "</table>";

		if (!$edit) {
			echo "</td>";
			
			PrintBlockDesc($Block['text_rt'], TRUE);
			echo "</tr>";

			echo "<tr>";
			PrintBlockDesc($Block['text_lc'], TRUE);
			PrintBlockDesc($Block['text_rc'], TRUE);
			echo "</tr>";

			echo "<tr>";
			PrintBlockDesc($Block['text_lb'], TRUE);
			PrintBlockDesc($Block['text_rb'], TRUE);
			echo "</tr>";
			
			
			// Bottom Line
			echo "<tr><td>&nbsp;</td>";
			
			PrintBlockDesc($Block['text_bl']);
			PrintBlockDesc($Block['text_bc']);
			PrintBlockDesc($Block['text_br']);
						
			echo "<td>&nbsp;</td>";
			echo "</tr>\n</table>";

		}
		
		
	}	


?>