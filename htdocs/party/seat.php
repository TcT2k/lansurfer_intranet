<?
	$LS_BASEPATH = "../";
	include $LS_BASEPATH."../includes/ls_base.inc";
	include $LS_BASEPATH."../includes/seat_util.inc";

	if (isset($LS_SeatOverview))
		$showoverview = TRUE;
	
	$LS_OPENPAGE = TRUE;
	
	StartPage(_("Seat plan"));
	
	$sRow = $row;
	
	$res = SQL_Query("SELECT * FROM seat_block ORDER BY name");
	echo "<p class=content>";
	echo '<table><tr>';
	$i = 1;
	while ($row = mysql_fetch_array($res)) {
		if (!isset($id))
			$id = $row[id];
		echo '<td width="100" class=TourneyTab ';
		if ($row[id] == $id)
			echo " id=TourneyTabSelected";
		echo '>';
		if ($row[id] == $id) {
			echo $row[name];
			$Block = $row;
		}
		else
			echo "<a href=$PHP_SELF?id=".$row[id].">".$row[name]."</a>";
		echo '</td>';
		if (!($i % 8))
			echo '</tr><tr>';
		$i++;
	}
	echo '</table>';
	echo "</p>";
	
	$row = $sRow;
	user_auth();

	if ($showoverview) {
		include $LS_SeatOverview;
	} else {
		if (!isset($row))
			$row = -1;
		if (!isset($col))
			$col = -1;
		
		echo "<p><div align=center>";
		RenderBlock($Block, false, true, $row, $col);
		echo "</div></p>";
	}

	EndPage();
?>
