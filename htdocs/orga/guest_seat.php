<?
	$LS_BASEPATH = '../';
	require $LS_BASEPATH.'../includes/ls_base.inc';
	require $LS_BASEPATH.'../includes/seat_util.inc';

	$res = SQL_Query("SELECT user.name, user.clan, user.realname1, user.realname2, guest.flags FROM guest LEFT JOIN user ON user.id=user WHERE (guest.id=$id)");
	$Guest = mysql_fetch_array($res);

	NavStruct("orga/guestlist/guestdetail/", array('party' => $Party[id], 'guestid' => $id));
	
	StartPage(_("Assign Seat"));
	user_auth_ex(AUTH_TEAM, 0, TEAM_GUEST);

	echo '<h3 class=content>';
	echo _("Assign Seat");
	echo '</h3>';

	$res = SQL_Query("SELECT name FROM guest g LEFT JOIN user u ON g.user=u.id WHERE g.id='".$id."'");
	$Guest = mysql_fetch_array($res);
	echo '<p class=content><b>';
	echo _("Guest");
	echo '</b>: ';
	echo $Guest['name'];
	echo '</p>';

	if ($action == 'reserve' || $action == 'note') {
		if ($action == 'reserve')
			$newstatus = SEAT_OCCUPIED;
		else
			$newstatus = SEAT_RESERVED;

		if ($submitted) {
			if ($action == 'reserve')
				SQL_Query("DELETE FROM seats WHERE guest='".$id."' AND status='".SEAT_OCCUPIED."'");

			$res = SQL_Query("SELECT 
					s.id sid,
					g.id gid,
					u.name,
					s.status
				FROM seats s 
				LEFT JOIN seat_block sb ON sb.id=s.block 
				LEFT JOIN guest g ON g.id=s.guest
				LEFT JOIN user u ON g.user=u.id
				WHERE s.block='".$block."' AND s.row='".$row."' AND s.col='".$col."'");
			$Seat = mysql_fetch_array($res);
			
			echo '<p class=content>';
			
			
			if ($Seat) {
				SQL_Query("UPDATE seats SET ".SQL_QueryFields(array(
					'guest' => $id,
					'status' => $newstatus
					))." WHERE id=".$Seat['sid']);
				$OccSeatID = $Seat['sid'];
				echo 'Der Sitzplatz wurde dem Gast zugewiesen.';
				if ($Seat['gid'] && $Seat['gid'] != $id) {
					echo '<br>';
					printf(($Seat[status] == SEAT_OCCUPIED) ?  _("The assigned seat was noted by %s.") : _("The assigned seat was reserved by %s."), '<a href="guest_detail.phtml?id='.$Seat['gid'].'">'.$Seat['name'].'</a>');
				}
			} else {
				SQL_Query("INSERT INTO seats SET ".SQL_QueryFields(array(
					'row' => $row,
					'col' => $col,
					'block' => $block,
					'guest' => $id,
					'status' => $newstatus
					)));
				$OccSeatID = mysql_insert_id();
				echo _("The seat has been assign to this guest.");
			}
			
			echo '</p>';
		} elseif (isset($block)) {
			$res = SQL_Query("SELECT * FROM seat_block WHERE id=".$block);
			$Block = mysql_fetch_array($res);
			echo '<h3 class=content>';
			echo $Block['name'];
			echo '</h3>';
			echo '<p class=content>';
			echo _("Click on a seat that you whish to assign to this guest:");
			echo '<br>';
			$bd = new BlockDisplay();
			$bd->Block = $Block;
			$bd->Open = TRUE;
			$bd->FreeClick = TRUE;
			$bd->AllOpen = TRUE;
			$bd->ClickUrl = $PHP_SELF.'?submitted=1&col=%d&row=%d&block=%d&action='.$action.'&id='.$id;
			$bd->Render();
			echo '</p>';
			
		} elseif ($remove == 1) {
			echo '<h3 class=content>';
			echo _("Free seat");
			echo '</h3>';

			echo '<p class=content>';
			SQL_Query("DELETE FROM seats WHERE guest='".$id."' AND status='".$newstatus."'");
			echo _("The seat has been freed.");
			echo '</p>';
		} else {
			echo '<h3 class=content>';
			echo _("Available blocks for this party");
			echo '</h3>';
			echo '<p class=content>';
			echo _("Select the block where you wish to assign the seat");
			echo ':<br>';
			$res = SQL_Query('SELECT id,name FROM seat_block ORDER BY name');
			echo '<ul class=content>';
			while ($row = mysql_fetch_array($res)) {
				echo '<li><a href="'.$PHP_SELF.'?action='.$action.'&id='.$id.'&block='.$row['id'].'">';
				echo $row['name'];
				echo '</a></li>';
			}
			
			echo '</ul>';
			echo '</p>';
		}
	}

	echo '<p class=content>';
	NavPrintBack();
	echo '</p>';
	
	EndPage();
?>
