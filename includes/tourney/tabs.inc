<?

	function PrintTab($url, $title) {
		global $PHP_SELF, $id;
		
		$active = !strpos($PHP_SELF, $url);
		
		echo '<td width="100" class=TourneyTab ';
		if ($active) {
			echo " id=TourneyTabSelected";
   		echo ' onmouseover="CellOver(this);" onmouseout="CellOut(this);" onclick="CellClick(this);"';
		}
		echo ">";
		
		if ($active)
			echo "<a href=".$url."?id=".$id.">";
		echo $title;
		if ($active)
			echo "</a>";
		
		echo "</td>";
	}

	function PrintTabs() {	
		global $tourney, $LS_BASEPATH;
?>
<script language="JavaScript">
<!--

function ShowMatch(id) {
	var left =  (screen.availHeight - 500) / 2;
	var top =  (screen.availHeight - 520) / 2;
	window.open("match_details.php?id=" + id, "MatchDetail", "height=500,width=520,screenX="+left+",screenY="+top+",locationbar=0,menubar=0,resizable=1,scrollbars=1,status=0");
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
		echo '<p class=content>';
		
		if (user_auth_ex(AUTH_TOURNEY, $id, 0, false)) {
			echo '<table><tr><td class=content>';
			FormStart('action="admin.php" method="post"');
				FormValue('id', $tourney->info['id']);
				FormSelectStart('setstatus', _("Status"), $tourney->Status);
					FormSelectItem(_("Registration Closed"), TS_CLOSED);
					FormSelectItem(_("Registration Opened"), TS_REGISTRATION);
					FormSelectItem(_("Pre Match"), TS_PREMATCH);
					FormSelectItem(_("Matches"), TS_MATCH);
					FormSelectItem(_("Finish"), TS_FINISHED);
					FormSelectItem(_("Cancel"), TS_CANCELED);
				FormSelectEnd();
			
				FormElement('', '', _("Set"), 'submit');
			FormEnd();
			echo '</td><td class=content valign=top>';
			if (!$tourney->MatchesInitialized) {
				NavPrintAction('admin.php?action=initmatches&id='.$tourney->info['id'], _("Admin: Initialize Matches"));
				echo '<br>';
			}
			echo '</td></tr>';
			echo '</table>';
		} else
		echo '<b>'._("Status").':</b> '.$tourney->GetStatusDesc();
		echo '</p>';

		echo '<p class=contentpanel><table width=500><tr>';
		PrintTab('rules.php', _("Rules"));
		PrintTab('teams.php', _("Participants"));
		PrintTab('matches.php', _("Matches"));
		PrintTab('overview.php', _("Overview"));
		PrintTab('ranking.php', _("Rankings"));
		echo '</tr></table></p>';
	}

	$tourney = new Tourney($id);

?>