<?
	$LS_BASEPATH = "../";
	include $LS_BASEPATH."../includes/ls_base.inc";

	StartPage(_("News"), 0, $id, STYLE_NOINNER);

	$fp = fopen($LS_BASEPATH.'../includes/design/newsitem.html', 'r');
	if ($fp) {
		$des = fread($fp, filesize($LS_BASEPATH.'../includes/design/newsitem.html'));
		fclose($fp);
	}
	
	$res = SQL_Query("SELECT 
		user.name,
		user.email,
		news.title, 
		news.msg,
		UNIX_TIMESTAMP(news.date) as date
		FROM news LEFT JOIN user ON author=user.id 
		WHERE (news.options&".NEWS_TEAM.")
		ORDER BY news.date DESC");
	while ($row = mysql_fetch_array($res)) {
		$s = $des;
	
		$s = str_replace ("%NEWS_TITLE%", $row[title], $s);
		$s = str_replace ("%NEWS_TEXT%", $row[msg], $s);
		$s = str_replace ("%NEWS_DATE%", strftime('%x', $row[date]), $s);
		$s = str_replace ("%NEWS_TIME%", strftime('%X', $row[date]), $s);
		$s = str_replace ("%AUTHOR_EMAIL%", $row[email], $s);
		$s = str_replace ("%AUTHOR_NAME%", $row[name], $s);
		
		echo $s;
	}
	
	EndPage();

?>