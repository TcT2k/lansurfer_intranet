<?

	function PrintTab($url, $title, $enabled = true) {
		global $PHP_SELF, $id;
		
		$active = !strpos($PHP_SELF, $url) && $enabled;
		
		echo '<td width="100" class=TourneyTab ';
		if ($active || !$enabled)
			echo " id=TourneyTabSelected";
		if ($active) {
   		echo ' onmouseover="CellOver(this);" onmouseout="CellOut(this);" onclick="CellClick(this);"';
		}
		echo ">";
		
		if ($active)
			echo "<a href=".$url."?id=".$id.">";
		echo $title;
		if ($active && $enabled)
			echo "</a>";
		
		echo "</td>";
	}
	
	function InitTabs() {
		global $LS_BASEPATH;
?>
<script language="JavaScript" src="<? echo $LS_BASEPATH; ?>tourney/tournament.js"></script>
<?
	}
	
	function PrintMatchTabs() {
		InitTabs();
		echo '<p class=content><table width=300><tr>';
		PrintTab('match_details.php', _("Opponents"));
		PrintTab('match_comments.php', _("Comments"));
		if (LS_TOURNEY_UPLOAD)
			PrintTab('match_files.php', _("Files"));
		echo '</tr></table></p>';
	}

?>