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

?>
<script language="JavaScript">
<!--

function ShowMatch(id) {
	var left =  (screen.availHeight - 500) / 2;
	var top =  (screen.availHeight - 520) / 2;
	window.open("match_details.php?id=" + id, "MatchDetail", "height=500,width=520,left="+left+",top="+top+",locationbar=0,menubar=0,resizable=1,scrollbars=1,status=0");
}

var oldcolor;
var oldid;

function CellOver(cell)
{
   if (!cell.contains(event.fromElement))
   {
   		oldid = cell.id;
      cell.id = 'TourneyHover';
   }
}

function CellOut(cell)
{
   if (!cell.contains(event.toElement))
   {
      cell.id = oldid;
   }
}

function CellClick(cell)
{
   if(event.srcElement.tagName=='TD')
   {
      cell.children.tags('A')[0].click();
   }
}

// -->
</script>
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