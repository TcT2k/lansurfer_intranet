<?
	$LS_BASEPATH = "../";
	include $LS_BASEPATH."../includes/ls_base.inc";

	$res = SQL_Query("SELECT * FROM seat_block WHERE id=$block");
	$Block = mysql_fetch_array($res);
	
	NavAdd(_("Seat Plan"), "seat.php?id=".$Block[id]);
	
	StartPage(_("Seat reservation"));

	if ($row > $Block[rows] || $col > $Block[cols] || $col < 0 || $row < 0)
		LS_Error(_("Invalid parameter"));
		
	// =================== Eigenen Status Ermittlen ===========================

	$res = SQL_Query("SELECT * FROM guest WHERE user=".$user_current[id]." AND (flags & ".GUEST_PAID.")");
	$Guest = mysql_fetch_array($res);
	if (!$Guest)
		LS_Error(_("You have to register for this party and pay the entrance fee in order to reserve a seat."), TRUE, TRUE);

	$res = SQL_Query("SELECT * FROM seats WHERE guest=".$Guest[id]." AND (status=".SEAT_OCCUPIED.")");
	$Seat_Own = mysql_fetch_array($res);
	$res = SQL_Query("SELECT COUNT(*) FROM seats WHERE guest=".$Guest[id]." AND status=".SEAT_RESERVED);
	$ReserveCount = mysql_result($res, 0, 0);
	
	// Status Des Platzes
	$res = SQL_Query("SELECT * FROM seats WHERE block=".$Block[id]." AND col=$col AND row=$row");
	$Seat = mysql_fetch_array($res);
	
	if ($Seat)
		$seat_status = $Seat[status];
	else
		$seat_status = 0;

	if ($seat_status == SEAT_DELETED)
		LS_Error(_("This seat can not be reserved."));
		
	$seatname = sprintf('%s, Row: %s, Seat: %d', $Block['name'], chr($row + 65), $col + 1);

	if ($submited) {
		if ($action == "use") {
			if ($Seat[status] == SEAT_OCCUPIED)
				LS_Error(_("The seat is allready reserved."));
		
			if (!$Seat_Own) {
				$new_status = SEAT_OCCUPIED;
				$text = sprintf(_("The seat %s has been reserved for you."), $seatname);
			} else {
				$new_status = SEAT_RESERVED;
				$text = sprintf(_("The seat %s has been noted for you. This specification is obligatory on nobody and is only meant for orientation."), $seatname);
			}
			$fields = "guest=".$Guest[id].",status=".$new_status;
			if ($Seat)
				SQL_Query("UPDATE seats SET $fields WHERE id=".$Seat[id]);
			else
				SQL_Query("INSERT INTO seats SET $fields,col=$col,row=$row,block=$block");
			echo $text."<br>";
		} elseif ($action == "free") {
			SQL_Query("DELETE FROM seats WHERE col=$col AND row=$row AND block=$block");
			echo _("The seat was released.");
		}
	
	} else {
		if ($Seat[guest] == $Guest[id]) {
			$action = "free";
			$btn_caption = _("Release");
			if ($Seat[status] == SEAT_OCCUPIED)
			  printf(_("The seat %s is reserved for you. If you want you can release it and to take another seat."), $seatname);
			elseif ($Seat[status] == SEAT_RESERVED)
			  printf(_("The seat %s is noted for you. If you want you can release it."), $seatname);
		} elseif ($seat_status == SEAT_OCCUPIED) {
			LS_Error(_("The seat is allready reserved."));
		} elseif ($seat_status == SEAT_RESERVED || $seat_status == 0) {
			$action = "use";
			if ($seat_status == 0)
				printf(_("The seat %s is still free."), $seatname);
			elseif ($seat_status == SEAT_RESERVED)
				printf(_("The seat %s is allready noted by another guest."), $seatname);
			echo '<br>';
			if ($Seat_Own) {
				if ($ReserveCount >= 4)
					LS_Error(_("You can note only up to 4 seats."));
				$btn_caption = _("Note Seat");
				echo _("You can note it now.");
			} else {
				$btn_caption = _("Reserve");
				echo _("You can reserve it now for you.");
			}
		}

		FormStart();
			FormValue("action", $action);
			FormValue("block", $block);
			FormValue("row", $row);
			FormValue("col", $col);
			FormValue("submited", 1);
			FormElement("", "", $btn_caption, "submit");
		FormEnd();
	}

	NavPrintBack();
	
	EndPage();
?>
