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
		PrintIMSContactLink($row['uid'], HTMLStr($row['name'], 30));
		echo '</td>';
		echo '<td>';
		NavPrintDel($PHP_SELF.'?remove='.$row['bid'], _("Remove"), "onclick=\"return window.confirm('"._("Do you really wish to remove this user from your buddy list?")."');\"");
		echo '</td>';
		echo '</tr>';
	}
	echo '</table>';
/*
?>

<script>
<!--
	function Debug() {
		if (opener && opener.parent && opener.parent.location != "") {
			alert("Opener " + opener.parent.location);
			return false;
		} else {
			return true;
		}
	}

// -->
</script>
<a href="<? echo $LS_BASEPATH; ?>" target="_new" onclick="return Debug();"><img src="../images/ims/msg_blink.gif" border=0> Debug!</a>

<?
*/
	//echo '<h3 class=content>'._("Send Message").'</h3>';



	EndPage();
	
?>