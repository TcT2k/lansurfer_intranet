<?
	$LS_BASEPATH = "../";
	include $LS_BASEPATH."../includes/ls_base.inc";

	function PrintDel($url, $caption = "") {
		if (!$caption)
			$caption = _("Delete");
		NavPrintDel($url, $caption);
	}
	
	StartPage(_("Orga Team"));
	
	user_auth_ex(AUTH_TEAM);
?>
<script language="JavaScript">
<!--
function BeamerStart() {
	window.open ("../party/beamer.php", "name", "fullscreen=yes,scrollbars=no");
}
//-->
</script>
<?
	
	NavPrintAction("guests.php", _("Guest list"));
	echo "<br>";
	NavPrintAction("new_guest.php", _("Create user/guest"));
	echo "<br>";
	NavPrintAction("guest_stat.php", _("Current stats"));
	echo "<br>";
	NavPrintAction("javascript:BeamerStart()", _("Start Beamer Presentation"));
	echo "<br>";
	NavPrintAction("ip_adder.php", "IP-Vergabe");
	echo "<br>";
	if (user_auth_ex(AUTH_TEAM, 0, TEAM_CATERING, FALSE)) {
		NavPrintAction("catering/", "Catering");
		echo "<br>";
	}

	$news_status = 0;
	if (user_auth_ex(AUTH_TEAM, $id, TEAM_NEWS, FALSE))
		$news_status = 1;
	if (user_auth_ex(AUTH_TEAM, $id, TEAM_NEWS_ALL, FALSE))
		$news_status = 2;
	
	if ($news_status): ?>
	<h3 class="content"><? echo _("News"); ?></h3>
	<p class=content>
		<? NavPrintAction("news.php?action=new", _("New message")) ?><br>
		<br>
		<?
			$header = FALSE;
			$res = SQL_Query("SELECT *, 
				DATE_FORMAT(news.date, '%e.%m.%Y') as 'disp_date',
				news.id as 'news_id'
				FROM news LEFT JOIN user ON author=user.id
				ORDER BY news.date DESC");
			while ($row = mysql_fetch_array($res)) {
				if (!$header) {
					$header = TRUE;
					?>
				<table class="liste">
					<tr>
						<th class="liste" width="180"><? echo _("Title"); ?></th>
						<th class="liste" width="80"><? echo _("Date"); ?></th>
						<th class="liste" width="100"><? echo _("Author"); ?></th>
					</tr>
					<?
				}
				
				?>
				<tr class="liste">
					<td class="liste"><? 
					$not_own_post = $news_status != 2 && $row[author] != $user_current[id];
					if ($not_own_post)
						echo $row[title];
					else
						echo "<a href=\"news.php?action=edit&team=$id&id=".$row[news_id]."\">".$row[title]."</a>"; 
					
					?></td>
					<td class="liste" align="right"><? echo $row[disp_date]; ?></td>
					<td class="liste"><? echo $row[name]; ?></td>
					<td><? 
						if (!$not_own_post)
							PrintDel("news.php?action=remove&team=$id&id=".$row[news_id]); 
					?></td>
				</tr>
				<?
			}
			if ($header) {
			?>
			</table>
			<?
			}
		?>
	</p>
	
	<? endif; 
		$member_readonly = !user_auth_ex(AUTH_TEAM, $id, TEAM_USER, FALSE);
	?>
	<h3 class="content"><? echo _("Member"); ?></h3>
	<p class=content>
		<? if (!$member_readonly): ?>
		<? NavPrintAction("team.php?action=neworga&team=$id", _("Add member")) ?><br>
		<br>
		<? endif; ?>
		<table class="liste">
			<tr class="liste">
				<th width="180" class="liste"><? echo _("Name"); ?></th>
				<th width="80" class="liste"><? echo _("Permissions"); ?></th>
			</tr>
		<?
			function PrintRight($right, $letter, $title = "") {
				global $row;
				
				if ($title)
					echo "<span title=\"$title\">";
				echo ($row[rights] & $right) ? $letter : "-";
				if ($title)
					echo "</span>";
			}
		
			$res = SQL_Query("SELECT 
				orga.id,
				user.email, 
				user.name,
				orga.rights 
				FROM orga LEFT JOIN user ON user=user.id");
			
			while ($row = mysql_fetch_array($res)) {
		?>
			<tr class="liste">
				<td class="liste">
					<? if ($member_readonly): ?>
					<? echo $row[name]; ?>
					<? else: ?>
					<a href="member.php?action=edit&id=<? echo $row[id]; ?>"><? echo $row[name]; ?>
					<? endif; ?>
				
				</td>
				<td class="liste"><font face="Courier New" size=2><? 
				// echo $row[rights]; 
					PrintRight(TEAM_USER,		"U", _("Add/Edit/Remove Users"));
					//PrintRight(TEAM_PARTY,	"P", _("Add/Edit/Remove Parties"));
					PrintRight(TEAM_GUEST,	"G", _("Add/Edit/Remove Guests"));
					PrintRight(TEAM_NEWS,		"n", _("Add/Edit/Remove news"));
					PrintRight(TEAM_NEWS_ALL, "N", _("Add/Edit/Remove all news"));
					//PrintRight(TEAM_DESIGN,	"D", _("Add/Edit/Remove Designs"));
					PrintRight(TEAM_FORUM,	"F", _("Add/Edit/Remove Board"));
					PrintRight(TEAM_TOURNEY,"T", _("Add/Edit/Remove tourneys"));
					PrintRight(TEAM_CATERING,	"C",_("Catering system admin"));  
				?></font></td>
				<td><? 
					if (!$member_readonly)
						PrintDel("member.php?action=remove&id=".$row[id], _("Remove")); 
					?></td>
			</tr>
		<?
			}
		?>	
		</table>
	</p>
<?
	EndPage();
?>
