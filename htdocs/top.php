<?
	$LS_BASEPATH = "";
	include '../includes/lsi_base.inc';
	
	function AddModule($title, $url, $enabled = TRUE) {
		global $LS_BASEPATH, $modstr;
		
		if ($modstr)
			$modstr .= ' | ';
		$modstr .= '<a class=NavModule href="'.$url.'">'.$title.'</a>';
	}
	
	user_auth();

	function PrintModule($title, $url, $enabled = TRUE) {
		global $LS_BASEPATH, $modstr;
		
		echo "&nbsp;";
		NavPrintAction($url, $title);
		echo "<br>";
	}
	
	$fp = fopen($LS_BASEPATH.'../includes/design/top.html', 'r');
	if ($fp) {
		
		if ($user_valid) {
			$res = SQL_Query("SELECT COUNT(*) FROM ims WHERE dst=".$user_current['id']." AND NOT (flags & ".IMS_READ.")");
			if ($cnt = mysql_result($res, 0, 0))
				$msgstr = sprintf(_("Inbox (%d)"), $cnt);
			else
				$msgstr = _("Inbox");
			$msgstr = '<a class=NavModule href="ims/">'.$msgstr.'</a>';

			Header('refresh: 30; URL='.$PHP_SELF.'?prevcnt='.$cnt);
		} else {
			$msgstr = _("Messages");
		}
		
		$des = fread($fp, filesize($LS_BASEPATH.'../includes/design/top.html'));
		$des = str_replace('%IMS%', $msgstr, $des);
		$des = str_replace('%DATE%', HTMLStr(strftime('%a, %X'), 15), $des);
		$usrstring = ($user_valid) ? $user_current['name'] : _("Login");
		$usrstring = '<a class=NavModule href="party/details.php">'.$usrstring.'</a>';
		$des = str_replace('%NAME%', $usrstring, $des);

		$modstr = '';

		AddModule(_("News"), "party/news.php");
		AddModule(_("Guest List"), "party/guests.php");
		AddModule(_("Seat Plan"), "party/seat.php");
		AddModule(_("Tournaments"), "tourney/");
		AddModule(_("Board"), "forum/index.php");
		if (LS_CATERING && $user_valid)
			AddModule("Catering","catering/bestellen.php");
		if (user_auth_ex(AUTH_TEAM, 0, 0, FALSE))
			AddModule(_("Admin Page"), "orga/index.php");

		$des = str_replace('%MODULES%', $modstr, $des);
		
		echo $des;
		if ($user_valid && isset($prevcnt) && $cnt > $prevcnt) {
		require $LS_BASEPATH.'../includes/js_default.inc';
?>
<embed name="notify" src="sound/notify.wav" loop=false autostart=true mastersound hidden=true></embed>
<script language="JavaScript">
<!--
		//var t = LSPopup("<? echo $LS_BASEPATH; ?>ims/buddy.php?notify=<? echo $cnt ?>", "ims_buddy", 200, 400, 1);
		//t.focus();

	IMSBuddyList('?notify=<? echo $cnt ?>');
	/*window.open ("ims/notify.php?prev=<? echo $prevcnt; ?>&cnt=<? echo $cnt; ?>", "ims_notify", 
		"width=300, height=30, top=" + (screen.availHeight - 180) + ",left = "+(screen.availWidth-350)+",scrollbars=no", true);*/

  var main_frame = parent.frames["main"];
  if (main_frame.location.pathname.indexOf('ims/index.php') >= 0) {
  	main_frame.location.reload();
  }

//-->
</script>
<?
		} 
		
		fclose($fp);
	}
?>