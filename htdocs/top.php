<?
	$LS_BASEPATH = "";
	include '../includes/ls_base.inc';
	
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
		fclose($fp);


		$modstr = '';

		LSLoadModules();
		
		foreach ($LSCurrentModules as $key => $mod) {
			
			if ($mod['menuitems'])
				foreach ($mod['menuitems'] as $url => $caption)
					AddModule($caption, $LS_BASEPATH.$url);
		}
		
		if (user_auth_ex(AUTH_TEAM, 0, 0, FALSE))
			AddModule(_("Admin Page"), "orga/index.php");
			

		if ($LSCurrentModules['lims']) {
			$limsdisp = ($user_valid) ? '<a class=NavModule href="javascript:IMSBuddyList(\'\')">'._("Buddy List").'</a>' : _("Buddy List");
		}
		else
			$limsdisp = '<a class=NavModule href="party/news.php">'._("News").'</a>';
		$des = str_replace('%IMS%', $limsdisp, $des);
		$des = str_replace('%DATE%', HTMLStr(strftime('%a, %X'), 15), $des);
		$usrstring = ($user_valid) ? $user_current['name'] : _("Login");
		$usrstring = '<a class=NavModule href="party/details.php">'.$usrstring.'</a>';
		$des = str_replace('%NAME%', $usrstring, $des);
			
		$des = str_replace('%MODULES%', $modstr, $des);
		
		echo $des;
		
	}
?>