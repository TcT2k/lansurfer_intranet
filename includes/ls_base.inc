<?
// LANSURFER Basis script

// Scripte
	require $LS_BASEPATH."../includes/ls_const.inc";
	$cfgFile = $LS_BASEPATH."../conf/base.inc";
	if (!file_exists($cfgFile)) {
		define ('LS_CONFIGURED', false);
		define ('LS_LANGUAGE', 'en_EN');
	} else
		require $cfgFile;
  require $LS_BASEPATH."../includes/sql_util.inc";
	require $LS_BASEPATH."../includes/ls_printing.inc";
	require $LS_BASEPATH."../includes/user_util.inc";
	require $LS_BASEPATH."../includes/ims.inc";

	setlocale(LC_ALL, LS_LANGUAGE);

	$gt_funcs = get_extension_funcs ("gettext");
	if ($gt_funcs && In_array('_', $gt_funcs)) {
		putenv('LANG='.LS_LANGUAGE);
		bindtextdomain ("messages", $LS_BASEPATH."../includes/locale");
	} else {
		// Enable Gettext workaround
		if (!$GETTEXT_WORKAROUND) {
			$GETTEXT_WORKAROUND = TRUE;
			function _($s) {
				return $s;
			}
		}
	}

// Funktionen

	function LS_Error($msg, $die = TRUE) {
		echo $msg;
		echo "</table>";
		EndPage();
		flush();
		if ($die)
			die;
	}

	function SetLang($lang = "") {

		global $HTTP_ACCEPT_LANGUAGE, $LS_BASEPATH;
		
		$langs = split(',', $HTTP_ACCEPT_LANGUAGE);
		$lang = $langs[0];
		echo "<!-- LANG: $lang -->";
		if ($lang == 'de')
		  $lang = 'de_DE';
		else
		  $lang = 'en';

		bindtextdomain("messages",  $LS_BASEPATH."../locale");
		textdomain("messages");
		setlocale(LC_ALL, $lang);
		
	}

	function StartPage($title = "", $innerstyle = 1, $icon = '') {
		global $LS_BASEPATH, $LS_POPUPPAGE, $LS_BODYTAGS;

		user_auth();
?>
<html>
<head>
	<title><? echo $title; ?></title>
	<link rel="StyleSheet" href="<? echo $LS_BASEPATH; ?>intra.css">
	<? if ($innerstyle == STYLE_NAVBAR): ?>
	<base target="main">
	<? endif; ?>

	<script language="JavaScript">
	<!--
		var BasePath = "<? echo $LS_BASEPATH; ?>";
	//-->
	</script>
	<script language="JavaScript" src="<? echo $LS_BASEPATH; ?>default.js"></script>
</head>

<body background="<? echo $LS_BASEPATH; ?>images/preview.gif" <? echo $LS_BODYTAGS; ?>>
<? if (!$LS_POPUPPAGE): ?>
	<h3 class=content><?  
		if ($icon && $icon != $LS_BASEPATH.'images/tourney/icons/')
			echo '<img width=32 height=32 border=0 src="'.$icon.'"> ';
		echo $title; 
	?></h3>
	<?
	if ($title)
		NavPrint($title);
	?>
<? endif; ?>
<p class="content">
<?		
		
	}
	
	function EndPage() {
		global $LS_BASEPATH, $LS_TITLEPAGE, $LS_POPUPPAGE;
		
		echo "</p>";
		if (!$LS_POPUPPAGE)
			echo "<p>&nbsp;</p><p>&nbsp;</p><p class=content align=right>Powered by <a target=_new href=\"http://www.lansurfer.com/\">LANsurfer.com</a></p>";
		echo '</body></html>';
		//echo "<!-- End Content -->\n";
		SQL_Done();
	}
	
	function deStrToDate($date, $time = "") {
		$thedate = -1;
		
		list( $day, $month, $year ) = split( '[/.-]', $date );
		
		if ($time)
			list($hour, $minute) = split( '[:.-]', $time );
		
		if (checkdate($month, $day, $year))
			$thedate = mktime($hour, $minute, 0, $month, $day, $year);
		else
			$thedate = -1;
		
		return $thedate;
	}
	
	function DateToStr($date) {
		return date('d.m.Y', $date);
	}

	function TimeToStr($time) {
		return date('H:i', $time);
	}
	
	function StrToDate($date, $time = '') {
		$thedate = -1;
		
		list( $day, $month, $year ) = split( '[/.-]', $date );
		
		if ($time)
			list($hour, $minute) = split( '[:.-]', $time );
		
		if (checkdate($month, $day, $year))
			$thedate = mktime($hour, $minute, 0, $month, $day, $year);
		else
			$thedate = -1;
		
		return $thedate;
	}
	
	function LSLoadModules($setupinfo = false) {
		global $LS_BASEPATH, $LSCurrentModules;

		function LoadModule($s, $si) {
			
			include $s.'/info.inc';
			if ($si && file_exists($s.'/setup.inc'))
				include $s.'/setup.inc';
			
			return $info;
		}
	
		$d = dir($LS_BASEPATH.'../modules');
		while ($s = $d->read()) {
			$path = ($d->path.'/'.$s);
			$info = $path .'/info.inc';
			if ($s[0] != '.' && is_dir($path) && file_exists($info)) {
				$mods[$s] = LoadModule($path, $setupinfo);
				
				$LSModules[$s] = true;
			}
		}
		$d->close();
		ksort($mods);
		include $LS_BASEPATH.'../conf/modules.inc';
		
		foreach ($mods as $key => $mod) {
			if ($LSModules[$key])
				$LSCurrentModules[$key] = $mod;
		}
		
	}

	$LS_PLATFORM = $OSTYPE;
?>
