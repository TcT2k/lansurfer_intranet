<?
	$LS_BASEPATH = '../';
	include $LS_BASEPATH.'../includes/ls_base.inc';
	
	if ($id)
		NavAdd(_("Inbox"), $PHP_SELF);

	//Header('refresh: 240');
	
	StartPage(($id) ? _("Message")  : _("Inbox"));
	
	if ($id) {
		$res = SQL_Query("SELECT
				UNIX_TIMESTAMP(ims.date) date,
				sender.name SenderName,
				sender.id SenderID,
				ims.subject,
				ims.msg,
				ims.flags,
				ims.id,
				ims.date SQLDate
			FROM
			  ims
			  LEFT JOIN user sender ON ims.src=sender.id
			WHERE
			  ims.dst = '".$user_current['id']."' AND ims.id=$id
			");
		if (!($msg = mysql_fetch_array($res)))
			LS_Error(_("Invalid message id."));
			
		if (!($msg[flags] & IMS_READ)) {
			SQL_Query("UPDATE ims SET flags=flags | ".IMS_READ." WHERE id=".$id);

			$res = SQL_Query("SELECT COUNT(*) FROM ims WHERE dst=".$user_current['id']." AND NOT flags & ".IMS_READ);
			$url = '../top.php?prevcnt='.mysql_result($res, 0, 0);
	
	?>
	  <script language="JavaScript">
	  <!--
	  var status_frame = parent.frames["navbar"];
	  if (status_frame.location) {
	  	status_frame.location.replace('<? echo $url; ?>');
	  }
	  //-->
	  </script>
	<?		
		
		}
		$pres = SQL_Query("SELECT id FROM ims WHERE dst = '".$user_current['id']."' AND id!=".$msg['id']." AND date<'".$msg['SQLDate']."' ORDER BY date DESC LIMIT 1");
		$prevMsg = mysql_fetch_array($pres);
	
		$nres = SQL_Query("SELECT id FROM ims WHERE dst = '".$user_current['id']."' AND id!=".$msg['id']." AND date>'".$msg['SQLDate']."' ORDER BY date LIMIT 1");
		$nextMsg = mysql_fetch_array($nres);

?>
<script language="JavaScript">
<!--
	function Reply() {

		var t = LSPopup("<? echo $LS_BASEPATH; ?>ims/send.php?to=<? echo $msg['SenderID']; ?>&f_subject=<?
			if (strncasecmp(_("RE:"), $msg['subject'], 3))
				echo _("RE:").' ';
			echo $msg['subject'];
			?>", "ims_send", 400, 360, 0);
		
		t.focus();
	}
//-->
</script>
<?

		echo '<p class=content>';
		
		echo '<table width=500>';
		echo '<tr>';
		if ($prevMsg) {
			echo '<td class=content>';
			NavPrintPrevPage($PHP_SELF.'?id='.$prevMsg['id'], _("Previous Message"));
			echo '</td>';
		} else 
			echo '<td></td>';
		if ($nextMsg) {
			echo '<td class=content align=right>';
			NavPrintNextPage($PHP_SELF.'?id='.$nextMsg['id'], _("Next Message"));
			echo '</td>';
		}
		echo '</tr>';
		
		echo '<tr><th class=liste width=160 align=left>'._("From").':</th><td class=liste width=340>';
		PrintIMSContactLink($msg['SenderID'], HTMLStr($msg['SenderName']));
		echo '</td></tr>';
		echo '<tr><th class=liste width=160 align=left>'._("Subject").':</th><td class=liste width=340>'.HTMLStr($msg['subject']).'</td></tr>';
		echo '<tr><th class=liste colspan=2 align=left>'._("Message").':</th></tr><tr><td class=liste colspan=2>'.Text2HTML(($msg['msg']) ? $msg['msg'] : _("(Empty Message)")).'</td></tr>';
		echo '</table>';
		
		NavPrintAction('javascript:Reply()', _("Reply"));
		
		NavPrintBack();
		
		echo '</p>';
	} else {
		if ($remove) {
			SQL_Query("DELETE FROM ims WHERE id=".SQL_Quot($remove));
		}
		
		if (!isset($type))
			$type = 'inbox';
		
		function PrintType($ntype, $caption) {
			global $type;
			
			echo '<td width=160 class=TourneyTab ';
			if ($type == $ntype)
				echo " id=TourneyTabSelected>";
			else
				echo '><a href="'.$PHP_SELF.'?type='.$ntype.'">';
			echo $caption;
			if ($type != $ntype)
				echo '</a>';
			echo '</td>';
		}
		
		echo '<p class=content>';
		NavPrintAction('../party/guests.php', _("Send message to another guest"));
		echo '<br>';
		NavPrintAction("javascript:IMSBuddyList('');", _("Open Buddy List"));
		echo '</p>';
		
		echo '<table><tr>';
			PrintType('inbox', _("Inbox"));
			PrintType('sent', _("Sent Items"));
		echo '</tr></table>';
		
		echo '<p class=content>';
		
		if ($type == 'inbox') {
			$SQLWhere = "ims.dst = '".$user_current['id']."'";
			$SQLJoin = "ims.src=sender.id";
		} else {
			$SQLWhere = "ims.src = '".$user_current['id']."'";
			$SQLJoin = "ims.dst=sender.id";
		}
		
		
		$res = SQL_Query("SELECT
				UNIX_TIMESTAMP(ims.date) date,
				sender.name SenderName,
				sender.id SenderID,
				ims.subject,
				ims.msg,
				ims.flags,
				ims.id
			FROM
			  ims
			  LEFT JOIN user sender ON $SQLJoin
			WHERE
			  $SQLWhere
			ORDER BY date DESC
			");
		
		//printf(_("%d Message(s)"), mysql_num_rows($res));
		
		echo '<br>';
		echo '<table>';
		echo '<tr>';
		echo '<th></th>';
		echo '<th class=liste width=300>'._("Subject").'</th>';
		echo '<th class=liste width=160>';
		echo ($type == 'inbox') ? _("From") : _("To");
		echo '</th>';
		echo '<th class=liste width=100>'._("Date").'</th>';
		echo '</tr>';
		
		while ($row = mysql_fetch_array($res)) {
			echo '<tr>';
			$bold = !($row['flags'] & IMS_READ) && $type == 'inbox';
			if (!$bold)
				$icon = '_read';
			else
			  $icon = '';
			echo '<td><img width=16 height=16 src="'.$LS_BASEPATH.'images/ims/msg'.$icon.'.gif"></td>';
			echo '<td class=liste>';
			echo '<a href="'.$PHP_SELF.'?id='.$row['id'].'">';
			if ($bold)
				echo '<b>';
			echo HTMLStr($row['subject']);
			if ($bold)
				echo '</b>';
			echo '</a></td>';
			echo '<td class=liste>';
			PrintIMSContactLink($row['SenderID'], HTMLStr($row['SenderName'], 30));
			echo '</td>';
			echo '<td class=liste>'.DisplayDate($row['date']).'</td>';
			if ($type == 'inbox') {
				echo '<td>';
				NavPrintDel($PHP_SELF.'?remove='.$row['id'], _("Remove"), "onclick=\"return window.confirm('"._("Do you really wish to remove this message?")."');\"");
				echo '</td>';
			}
			echo '</tr>';
		}
		echo '</table>';
		echo '</p>';
	}
	
	
	EndPage();
	
?>