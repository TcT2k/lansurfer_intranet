<?
// Script edited by -=[LCO]=-[LAZ]Neo.

	$LS_BASEPATH = "../";
	include $LS_BASEPATH."../includes/ls_base.inc";

if ($z==false) {
$z=0;
}

else { 
}

$y=$z;
++$y;

/* $y==5 means, that the last 5 news posted will be displayed, one news-entry per page. */

if($y==5)
$url="beamer_example.php";
else
$url="beamer.php?z=".$y;

?>

<html>
<head>
	<meta http-equiv="refresh" content="2; URL=<? echo $url; ?>">
<?

/* replace beamer_example.php (l.20) with second site, or replace it with beamer.php to only display news. 
   content="10" (l.28) is the number in seconds before that page loads or this one refreshes. */

?>
	<title>Beamer</title>
	<link rel="StyleSheet" href="../intra.css">
	</head>

<body>
<h3 class=content>News</h3>

<?
	$fp = fopen($LS_BASEPATH.'../includes/design/newsitem_beamer.html', 'r');
	if ($fp) {
		$des = fread($fp, filesize($LS_BASEPATH.'../includes/design/newsitem_beamer.html'));
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
		ORDER BY news.date DESC LIMIT $z,1");
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
