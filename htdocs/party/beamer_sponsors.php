<?
	$LS_BASEPATH = "../";
	include $LS_BASEPATH."../includes/ls_base.inc";
?>

<html>
<head>
	<meta http-equiv="refresh" content="10; URL=beamer.php">
	<link rel="StyleSheet" href="../intra_beamer.css">
</head>

<body>

<?
$res = SQL_Query("SELECT sponsors FROM beamer");
$row = mysql_fetch_array($res);
echo $row[sponsors];
?>

<?
	EndPage();
?>
