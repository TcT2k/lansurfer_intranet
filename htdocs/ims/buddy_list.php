<?
	$LS_BASEPATH = '../';
	include $LS_BASEPATH.'../includes/ls_base.inc';

	$LS_POPUPPAGE = TRUE;
	StartPage(_("IMS Buddy List"));

	if ($add) {
		$res = SQL_Query("SELECT * FROM imsUsers WHERE owner=".SQL_Quot($user_current['id'])." AND user=".SQL_Quot($add));
		if (!($row = mysql_fetch_array($res)))
			SQL_Query("INSERT INTO imsUsers SET ".SQL_QueryFields(array(
				'user' => $add,
				'owner' => $user_current['id'],
				'type' => 0
				)));
		
	}
	
	if ($remove) {
		SQL_Query("DELETE FROM imsUsers WHERE id=".SQL_Quot($remove));
	}
	
	$newMsgs = array();
	if ($notify) {
		$res = SQL_Query("SELECT src, COUNT(id) msgCnt FROM ims 
			WHERE dst=".$user_current['id']." AND NOT (flags & ".IMS_READ.")
			GROUP BY src");
		while ($row = mysql_fetch_array($res)) {
			$newMsgs[$row['src']] = $row['msgCnt'];
		}
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
		echo '<td class=liste width=180>';
		if ($newMsgs[$row['uid']]) {
			echo '<img src="../images/ims/msg_blink.gif" border=0 width=16 height=16>';
			unset($newMsgs[$row['uid']]);
		} else {
			echo '<img src="../images/pixel.gif" border=0 width=16 height=16>';
		}
		echo ' ';
		PrintIMSContactLink($row['uid'], HTMLStr($row['name'], 14));
		echo '</td>';
		echo '<td>';
		NavPrintDel($PHP_SELF.'?remove='.$row['bid'], _("Remove"), "onclick=\"return window.confirm('"._("Do you really wish to remove this user from your buddy list?")."');\"");
		echo '</td>';
		echo '</tr>';
	}
	echo '</table>';
	
	if (count($newMsgs)) {
		echo '<p class=content align=center>';
		printf(_("You have %d new messages"), count($newMsgs));
		echo '<br>';
		echo '<a target=main href="index.php" onClick="opener.focus();window.location.reload();">'._("View Inbox").'</a>';
		echo '</p>';
	}

	EndPage();
?>