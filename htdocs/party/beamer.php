<?
// Script edited by -=[LCO]=-[LAZ]Neo.

$LS_BASEPATH = "../";
include $LS_BASEPATH."../includes/ls_base.inc";

$res = SQL_Query("SELECT msg_no FROM beamer");
$row = mysql_fetch_array($res);
$msg_no = $row[msg_no];

if ($z==false) {
$z=0;
}

else { 
}

$y=$z;
++$y;

if($y==$msg_no)
$url="beamer_sponsors.php";
else
$url="beamer.php?z=".$y;

?>

<html>
<head>
	<meta http-equiv="refresh" content="2; URL=<? echo $url; ?>">
	<link rel="StyleSheet" href="../intra_beamer.css">
</head>

<body>

<br><br><br>

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
		WHERE (news.options&".NEWS_PUBLIC.")
		ORDER BY news.date DESC LIMIT $z,1");
	while ($row = mysql_fetch_array($res)) {
		$s = $des;
	
		$s = str_replace ("%NEWS_TITLE%", $row[title], $s);
		$s = str_replace ("%NEWS_TEXT%", $row[msg], $s);
		$s = str_replace ("%NEWS_DATE%", strftime('%x', $row[date]), $s);
		$s = str_replace ("%NEWS_TIME%", strftime('%X', $row[date]), $s);
		$s = str_replace ("%AUTHOR_EMAIL%", $row[email], $s);
		$s = str_replace ("%AUTHOR_NAME%", $row[name], $s);
		$news_content = $row[msg];
		echo $s;
	}
	if ($news_content != '') {
	EndPage();
	}
?>
