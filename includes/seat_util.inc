<?
	class BlockDisplay {
		
		var $Block;
		var $Edit = FALSE;
		var $Open = TRUE;
		var $FreeClick = FALSE;
		var $HighlightRow = -1;
		var $HighlightCol = -1;
		var $ShowNames = FALSE;
		var $ClickUrl = 'seat_reserve.php?col=%d&row=%d&block=%d';
		var $AllOpen = FALSE;
		
		function BlockDisplay($blockid = 0) {
			if ($blockid) {
				$res = SQL_Query("SELECT * FROM seat_block WHERE id=".$blockid);
				$this->Block = mysql_fetch_array($res);
			}
		}
		
		function Render() {
			global $LS_BASEPATH, $SeatSepRow, $SeatSepCol, $user_valid, $user_current, $sess;
	
			$cols = $this->Block[cols];
			$rows = $this->Block[rows];
			$vertical = !$this->Block[orientation];
			$sep_freq = 2;
	
	
			$res = SQL_Query("SELECT * FROM seat_sep WHERE (block=".$this->Block[id].") AND (orientation=0) ORDER BY value");
			while ($row = mysql_fetch_array($res)) {
				$SeatSepRow[$row[value]] = 1;
			}
	
			$res = SQL_Query("SELECT * FROM seat_sep WHERE (block=".$this->Block[id].") AND (orientation=1) ORDER BY value");
			while ($row = mysql_fetch_array($res)) {
				$SeatSepCol[$row[value]] = 1;
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
			
			$bl_color = $this->Block[color];
			if ($this->Edit)
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
				WHERE (block=".$this->Block[id].") ORDER BY $SQLSort");
			$Seat = mysql_fetch_array($sres);
	
			if (!$this->Edit && $user_valid) {
				
				// User infos auswerten
	
				$res = SQL_Query("SELECT * FROM guest WHERE user=".$user_current[id]);
				$Guest = mysql_fetch_array($res);
				if ($Guest) {
					$res = SQL_Query("SELECT * FROM seats WHERE guest=".$Guest[id]." AND status=".SEAT_OCCUPIED);
					$SeatMine = mysql_fetch_array($res);
					$res = SQL_Query("SELECT COUNT(*) FROM seats WHERE guest=".$Guest[id]." AND status=".SEAT_RESERVED);
					$ReserveCount = mysql_result($res, 0, 0);
					if ($Guest[flags] & GUEST_PAID)
						$guest_paid = TRUE;
				}
			} else 
				$guest_paid = FALSE;
				
				
	/*		echo "Debug: <br>";
			echo "User: ".$user_current[id].", ";
			if ($Guest)
				echo "Guest Found, ";
			if ($guest_paid)
				echo "Guest Paid, ";*/
	
	
			if (!$this->Edit) {
				echo "<table>\n<tr>";
				echo "<td>&nbsp;</td>";
				
				$this->PrintBlockDesc($this->Block[text_tl]);
				$this->PrintBlockDesc($this->Block[text_tc]);
				$this->PrintBlockDesc($this->Block[text_tr]);
							
				echo "<td>&nbsp;</td>";
				echo "</tr><tr>";
	
				$this->PrintBlockDesc($this->Block[text_lt], TRUE);
				echo "<td colspan=3 rowspan=3 valign=top align=center>";
			}
	
			$imgsize = 14;
			
			echo "<table>";
				$seatindex = 0;
			
				for ($y=-1; $y<$CountY; $y++) {
	
					if (/*$y > 0 && */$SepY[$y-1])
						echo "<tr><td colspan=".($CountX + 1 + count($SepX))."><img src=\"".$LS_BASEPATH."images/pixel.gif\" width=1 height=6></td></tr>";
					echo "<tr class=liste>";
					
					for ($x=-1; $x<$CountX; $x++) {
						$col = (!$vertical) ? $y : $x;
						$row = (!$vertical) ? $x : $y;
					
						if ($y < 0 && $SepX[$x-1]) {
							echo "<td rowspan=".($CountY + 1 + count($SepY))."><img src=\"".$LS_BASEPATH."images/pixel.gif\" width=6 height=1></td>";
						}
						
						$speciallink = '';
	
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
	
							if ($this->Edit) {
								echo "<td class=liste>";
								echo "<input type=checkbox value=1 name=\"f_seats[".$seatindex."]\"";
								if (!($Seat && $Seat[status] == SEAT_DELETED && $Seat[col] == $col && $Seat[row] == $row))
									echo " checked";
								echo ">";
								echo "</td>\n";
								$seatindex++;
							} else {
								if ($Seat && $Seat[status] == SEAT_DELETED && $Seat[col] == $col && $Seat[row] == $row)
									$status = 0;
								else
									$status = ($Seat && $Seat[col] == $col && $Seat[row] == $row) ? $Seat[status] : 1;
							
								if ($status) {
									$text = sprintf(_("Row %s Seat %d"), chr($row + 65), $col + 1);
									$link = FALSE;
	
									switch ($status) {
										case 2:
											$pic = "occupied";
											$text .= "\n".sprintf(_("Reserved by %s"), $Seat[name]);
											if ($Seat[clan]) {
												$text .= "\n"._("Clan").": ".$Seat[clan];
											}
											
											if ($Seat[id] == $Guest[id]) {
												$pic .= "_mine";
												$link = TRUE;
											} elseif ($user_current[clan] != "" && $Seat[clan] == $user_current[clan])
												$pic .= "_clan";
											/* TODO: IMS Link
											
											if ($Seat[id] != $Guest[id] && !$this->AllOpen) {
												$speciallink = 'contact.phtml?id='.$Seat[id];
												$link = TRUE;
											}*/
											break;
										case 3:
											$pic = "reserved";
											$text .= "\n".sprintf(_("Noted by %s"), $Seat[name]);
											if ($Seat[clan])
												$text .= "\n"._("Clan").": ".$Seat[clan];
											if ($Seat[id] == $Guest[id]) {
												$pic .= "_mine";
												$link = TRUE;
											} elseif ($user_current[clan] != "" && $Seat[clan] == $user_current[clan])
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
									if (!$this->Open && !$speciallink)
										$link = FALSE;
									if ($this->FreeClick)
										$link = TRUE;
								
									echo "<td class=content align=center>";
									if ($link) {
										if ($speciallink)
											$url = $speciallink;
										else
											$url = sprintf($this->ClickUrl, $col, $row, $this->Block['id']);
										if (isset($sess))
											$url = $sess->url($url);
										echo '<a href="'.$url.'">';
									}
									
									if ($this->HighlightRow == $row && $this->HighlightCol == $col)
										$thissize = $imgsize * 2;
									else
										$thissize = $imgsize;
									echo "<img border=0 alt=\"$text\" src=\"".$LS_BASEPATH."images/seat/$pic.gif\" width=$thissize height=$thissize>";
									if ($link)
										echo "</a>";
									if ($this->ShowNames && $status > 1) {
										echo '<br>'.$Seat[clan].'<br>'.$Seat[name];
									}
									echo "</td>\n";
								} else {
									echo "<td><img border=0 src=\"".$LS_BASEPATH."images/pixel.gif\" width=$imgsize height=$imgsize></td>";
								}
							}
	
							if ($Seat && $Seat[col] == $col && $Seat[row] == $row)  // Sitz abgearbeitet -> Nächster Sitz
								$Seat = mysql_fetch_array($sres);
	
						}
					}
					echo "</tr>\n";
				}
			
			echo "</table>";
	
			if (!$this->Edit) {
				echo "</td>";
				
				$this->PrintBlockDesc($this->Block[text_rt], TRUE);
				echo "</tr>";
	
				echo "<tr>";
				$this->PrintBlockDesc($this->Block[text_lc], TRUE);
				$this->PrintBlockDesc($this->Block[text_rc], TRUE);
				echo "</tr>";
	
				echo "<tr>";
				$this->PrintBlockDesc($this->Block[text_lb], TRUE);
				$this->PrintBlockDesc($this->Block[text_rb], TRUE);
				echo "</tr>";
				
				
				// Bottom Line
				echo "<tr><td>&nbsp;</td>";
				
				$this->PrintBlockDesc($this->Block[text_bl]);
				$this->PrintBlockDesc($this->Block[text_bc]);
				$this->PrintBlockDesc($this->Block[text_br]);
							
				echo "<td>&nbsp;</td>";
				echo "</tr>\n</table>";
	
			}
			
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

	}

	function RenderBlock($Block, $edit = FALSE, $open = TRUE, $hrow = -1, $hcol = -1, $names = FALSE) {
		
		$bd = new BlockDisplay();
		$bd->Block = $Block;
		$bd->Edit = $edit;
		$bd->Open = $open;
		$bd->HighlightRow = $hrow;
		$bd->HighlightCol = $hcol;
		$bd->ShowNames = $names;
		$bd->Render();
	}	

?>