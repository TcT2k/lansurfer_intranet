<?
	$LS_BASEPATH = '../';
	include $LS_BASEPATH.'../includes/ls_base.inc';

	$LS_POPUPPAGE = TRUE;
	
	
	user_auth();

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

	$cnt = mysql_num_rows($res);

	$refdelay = (!$prevcnt) ? 10 : 30;
	
	if (!isset($test))
		$test = -1;
	$test++;
	
	Header('refresh: '.$refdelay.'; URL='.$PHP_SELF.'?test='.$test.'&to='.$to.'&prevcnt='.$cnt);
	
	StartPage(_("Send Message"));
	
	if ($prevcnt && $cnt > $prevcnt) {
?>
<script language="JavaScript">
<!--
	parent.window.focus();
	//window.alert("New Message Arrived");
// -->
</script>
<?		
	}
	SQL_Query("UPDATE ims SET flags=flags|".IMS_READ." WHERE src=".SQL_Quot($to)." AND dst=".SQL_Quot($user_current['id'])." AND NOT flags & ".IMS_READ);
	
		
	echo '<div class=content><b>'._("History").'</b></div>';
	echo '<table width=100%>';
		
	while ($row = mysql_fetch_array($res)) {
		
		echo '<tr>';
		echo '<th valign="top" class="liste" width="50">';
		echo DisplayDate($row['dateT'], DD_TIME);
		echo '</th>';
		$c = ($row['dst'] == $to) ? 'd' : 'h';
		
		echo '<t'.$c.' class=liste align=left>';
		echo Text2HTML($row['msg']);
		echo '</t'.$c.'>';
		echo '</tr>';

	}
	echo '</p>';
		
	EndPage();
?>