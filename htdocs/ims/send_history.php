<?
	$LS_BASEPATH = '../';
	include $LS_BASEPATH.'../includes/ls_base.inc';

	$LS_POPUPPAGE = TRUE;
	StartPage(_("Send Message"));
	
	$res = SQL_Query("SELECT 
		  *,
		  UNIX_TIMESTAMP(date) dateT
		FROM
			ims
		WHERE
			(dst=$to AND src=".$user_current['id'].") OR
			(src=$to AND dst=".$user_current['id'].")
		ORDER BY date DESC
		");
	echo '<p class=content>';
		
	while ($row = mysql_fetch_array($res)) {
		if ($row['dst'] == $to)
			echo '<i>';
		echo DisplayDate($row['dateT']).' '.$row['subject'].'<br>';
		echo Text2HTML($row['msg']);
		if ($row['dst'] == $to)
			echo '</i>';
		echo '<hr>';
	}
	echo '</p>';

	/*for ($i = 0; $i < 4000; $i++)
		echo $i.'.<br>';*/
		
	EndPage();
?>