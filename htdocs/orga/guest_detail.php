<?
	$LS_BASEPATH = "../";
	include $LS_BASEPATH."../includes/ls_base.inc";

	$res = SQL_Query("SELECT user.id as 'uid', user.email, user.name, user.clan, user.realname1, user.realname2, guest.flags, pwd 
		FROM guest 
			LEFT JOIN user ON user.id=user WHERE guest.id=$id");
	$Guest = mysql_fetch_array($res);
//	$Party = LS_GetParty($Guest[party]);

	
	NavStruct("orga/guestlist/");

	if ($action == 'login') {
		user_login($Guest['uid'], $Guest['pwd'], true);
	}
	
	StartPage(_("Guest Details"));

 	if ($action == 'login') {
?>
  <script language="JavaScript">
  <!--
  var status_frame = parent.frames["navbar"];
  if (status_frame.location) {
  	var url = status_frame.location.href;
  	status_frame.location.replace(url);
  }
  //-->
  </script>
<?
		echo '<p class=content>'._("You are now logged in as this guest.").'</p>';
		die;
	}

	user_auth_ex(AUTH_TEAM, 0, TEAM_GUEST);

	if ($action == "setpaid") {
		switch ($f_paid) {
			case 0:
				$f_oldflags = 0;
				break;
			case 1:
				$f_oldflags = GUEST_PAID;
				break;
			case 2:
				$f_oldflags = GUEST_PAID | GUEST_VIP;
				break;
			case 3:
				$f_oldflags = GUEST_PAID | GUEST_EVENING;
				break;
			case 4:
				$f_oldflags = GUEST_PAID | GUEST_PREPAID;
				break;
		}
		if ($f_onlocation)
			$f_oldflags |= GUEST_ONLOCATION;
		$Guest[flags] = $f_oldflags;
	  
		SQL_Query("UPDATE guest SET flags=$f_oldflags WHERE id=$id");
	} else {
		if ($Guest[flags] & GUEST_PREPAID)
			$f_paid = 4;
		elseif ($Guest[flags] & GUEST_EVENING)
			$f_paid = 3;
		elseif ($Guest[flags] & GUEST_VIP)
			$f_paid = 2;
		elseif ($Guest[flags] & GUEST_PAID)
			$f_paid = 1;
		else
			$f_paid = 0;
	}
	
	echo "<p class=content>";
	echo '<table>';
	echo '<tr><td class=content align=right><b>'._("Name").':</b></td><td class=content>'.HTMLStr($Guest[name]).'</td></tr>';
	echo '<tr><td class=content align=right><b>'._("Clan").':</b></td><td class=content>'.HTMLStr($Guest[clan]).'</td></tr>';
	echo '<tr><td class=content align=right><b>'._("First Name").':</b></td><td class=content>'.HTMLStr($Guest[realname1]).'</td></tr>';
	echo '<tr><td class=content align=right><b>'._("Last Name").':</b></td><td class=content>'.HTMLStr($Guest[realname2]).'</td></tr>';
	echo '<tr><td class=content align=right><b>'._("Email").':</b></td><td class=content>'.HTMLStr($Guest[email]).'</td></tr>';
	echo '<tr><td class=content align=right><b>'._("Birth year").':</b></td><td class=content>'.HTMLStr(($Guest[birthyear] && $Guest[birthyear] != 19) ? $Guest[birthyear] : _("None")).'</td></tr>';
	echo '<tr><td class=content align=right valign=top><b>'._("Seat").':</b></td><td class=content>';

	$resCount = $noteCount = 0;
	$res = SQL_Query('SELECT row,col, sb.name, s.status, sb.id BlockID FROM seats s LEFT JOIN seat_block sb ON s.block=sb.id WHERE guest='.$id.' ORDER BY s.status');
	while ($row = mysql_fetch_array($res)) {
	  switch ($row[status]) {
	  	case SEAT_RESERVED:
	  	  echo _("Noted");
	  	  $noteCount++;
	  	  break;
	  	case SEAT_OCCUPIED:
	  	  echo _("Reserved");
	  	  $resCount++;
	  	  break;
	  }
	  echo ': <a href="../party/seat.php?'.sprintf('id=%d&row=%d&col=%d', $row['BlockID'], $row['row'], $row['col']).'">'.$row[name].', '.sprintf(_("Row: %s Seat: %d"), chr($row[row] + 65), ($row[col] + 1)).'</a>';
	  echo '<br>';
	}
	
	NavPrintAction('guest_seat.php?action=reserve&id='.$id, _("Assign seat"));
	echo '<br>';
	NavPrintAction('guest_seat.php?action=note&id='.$id, _("Note seat"));
	echo '<br>';
	if ($resCount) {
		NavPrintAction('guest_seat.php?action=reserve&remove=1&id='.$id, _("Clear seat"), NAT_REMOVE);
		echo '<br>';
	}
	if ($noteCount) {
		NavPrintAction('guest_seat.php?action=note&remove=1&id='.$id, _("Clear notes"), NAT_REMOVE);
		echo '<br>';
	}

	echo '</table>';
	
	echo "<br>";

	if ($action == 'setpw') {
		if ($submitted) {
			if (!$f_pw1 || !$f_pw2)
				FormError('f_pw1', _("Password required"));
			if ($f_pw1 != $f_pw2)
				FormError('f_pw1', _("The passwords do not match"));
			if (!$FormErrorCount) {
				SQL_Query("UPDATE user SET pwd=PASSWORD(".SQL_Quot($f_pw1).") WHERE id=".SQL_Quot($Guest['uid']));
				echo '<p class=content>'._("Password saved.").'</p>';
			}
		}
		
		if (!$submitted || $FormErrorCount) {
			FormStart();
				FormValue('id', $id);
				FormValue('action', $action);
				FormValue('submitted', 1);
				
				FormElement('f_pw1', _("New Password"), '', 'password');
				FormElement('f_pw2', _("New Password (once again)"), '', 'password');
				FormElement('', '', _("Save"), 'submit');
			FormEnd();
		}
	} elseif ($action == 'remove') {
		if ($submited) {
			SQL_Query("DELETE FROM guest WHERE id=$id");
			SQL_Query("DELETE FROM seats WHERE guest=$id");
			echo "<p class=content>"._("The guest has been removed from the guest list.")."</p>";
		} else {
			echo "<p class=content>"._("Are you sure you really want to <b>remove</b> this guest from the guest list?")."</p>";
			FormStart();
				FormValue("id", $id);
				FormValue("action", $action);
				FormValue("submited", 1);
				FormElement("", "", _("Remove"), "submit");
			FormEnd();
		}
	} else {
		NavPrintAction($PHP_SELF.'?id='.$id.'&action=setpw', _("Set New Password"));		
		echo '<br>';
		NavPrintAction($PHP_SELF.'?id='.$id.'&action=login', _("Login as this guest"));		
		
		FormStart();
			FormValue("id", $id);
			FormValue("f_oldflags", $Guest[flags]);
			FormValue("action", "setpaid");
			FormSelectStart("f_paid", "Zahlungs Status", $f_paid);
				FormSelectItem(_("Not Paid"), 0);
				FormSelectItem(_("Paid"), 1);
				FormSelectItem(_("VIP"), 2);
				FormSelectItem(_("Evening Checkout"), 3);
				FormSelectItem(_("Payment Noted"), 4);
			FormSelectEnd();
			FormElement("f_onlocation", _("On Location"), 1, "checkbox", ($Guest[flags] & GUEST_ONLOCATION) ? "checked" : "");
			FormElement("", "", _("Save"), "submit");
		FormEnd();
	}
	echo "</p>";

	NavPrintBack();
	
	EndPage();
?>
