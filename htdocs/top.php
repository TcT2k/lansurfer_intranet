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
		$des = fread($fp, filesize($LS_BASEPATH.'../includes/design/top.html'));
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
		fclose($fp);
	}
?>