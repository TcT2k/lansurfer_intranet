<?
	$LS_BASEPATH = '../';
	include $LS_BASEPATH.'../includes/ls_base.inc';

	$LS_POPUPPAGE = TRUE;
	$LS_BODYTAGS = ' ';
	
	StartPage(_("LIMS Buddy List"));

	if ($add) {
		// Search if already in list
		$res = SQL_Query("SELECT * FROM imsUsers WHERE owner=".SQL_Quot($user_current['id'])." AND user=".SQL_Quot($add));
		// add if not in list
		if (!($row = mysql_fetch_array($res)))
			SQL_Query("INSERT INTO imsUsers SET ".SQL_QueryFields(array(
				'user' => $add,
				'owner' => $user_current['id'],
				'type' => 0
				)));
		
	}
	
	if ($remove) {
		// remove user from buddy list
		SQL_Query("DELETE FROM imsUsers WHERE id=".SQL_Quot($remove)." AND owner=".SQL_Quot($user_current['id']));
	}
	
	$newMsgs = array();
	if ($notify) {
		$not = explode(",", $notify);
		foreach ($not as $n)
			$newMsgs[$n] = 1;

	}

	$res = SQL_Query("SELECT
			u.name,
			u.id uid,
			iu.id bid
		FROM
			imsUsers iu
			LEFT JOIN user u ON iu.user=u.id
		WHERE
			iu.owner = ".SQL_Quot($user_current['id'])."
		ORDER BY u.name
		");
		
	echo '<table>';
	while ($row = mysql_fetch_array($res)) {
		echo '<tr>';
		echo '<td class=liste width=180 valign=center>';

		if (isset($not))
			$i = array_search($row['uid'], $not);
		else
			$i = false;
		if (!$i && !($i === 0)) {
			echo '<img src="../images/pixel.gif" border=0 width=16 height=15>';
		} else {
			echo '<img src="../images/ims/msg_blink.gif" border=0 width=16 height=15>';
			unset($not[$i]);
		}
		echo ' ';
		PrintIMSContactLink($row['uid'], HTMLStr($row['name'], 14));
		echo '</td>';
		echo '<td>';
		NavPrintDel($PHP_SELF.'?remove='.$row['bid'], _("Remove"), "onclick=\"return window.confirm('"._("Do you really wish to remove this user from your buddy list?")."');\"");
		echo '</td>';
		echo '</tr>';
	}
	if (count($not)) {
		$nobodies = implode(", ", $not);
		$res = SQL_Query("SELECT id uid,name FROM user WHERE id IN (".$nobodies.") ORDER BY name");
		while ($row = mysql_fetch_array($res)) {
			echo '<tr>';
			echo '<th align=left class=liste width=180 valign=center>';
			echo '<img src="../images/ims/msg_blink.gif" border=0 width=16 height=15>';
			echo ' ';
			PrintIMSContactLink($row['uid'], HTMLStr($row['name'], 14));
			echo '</th>';
			echo '</tr>';
		}
	}
	echo '</table>';
	
	echo '<p class=content>';
	NavPrintAction('javascript:IMSGuestList()', _("View users not on list"));
	echo '</p>';

	echo '<p>&nbsp;</p><p class=content align=center>'._("If you close this window you will <b>not</b> be notified of incoming messages.").'</p>';
	
	EndPage();
?>