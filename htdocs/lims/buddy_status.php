<?
	$LS_BASEPATH = '../';
	include $LS_BASEPATH.'../includes/ls_base.inc';

	user_auth();
	
	$res = SQL_Query("SELECT src FROM ims WHERE dst=".SQL_Quot($user_current['id'])." AND NOT (flags & ".IMS_READ.") GROUP BY src");

	while ($row = mysql_fetch_array($res)) {
		$not[] = $row['src'];	
	}
	
	$msg = sprintf(_("%d unread Message(s)"), count($not));
		
	Header('refresh: 10; URL='.$PHP_SELF.'?prevcnt='.count($not));
?>
<html>
<head>
</head>

<body bgcolor="#D4D0C8">
<font face=Arial size=2>&nbsp;<? echo $msg; ?></font>

<?
	if ($prevcnt != count($not)) {
		$url = 'buddy_list.php';
		
		if (is_array($not)) {
			$notify = implode(",", $not);
			$url .= '?notify='.$notify;
		}
		
		if (count($not) > $prevcnt) {
			echo '<embed name="notify" src="'.$LS_BASEPATH.'sound/notify.wav" loop=false autostart=true mastersound hidden=true></embed>';
		}
		
		echo '<script language="JavaScript"><!-- 
			parent.frames[0].location.replace("'.$url.'");';
		
		if (count($not) > $prevcnt)
		echo '
			parent.focus();
		';
		echo '
		// --></script>';	
	}	
?>

</body>
</html>