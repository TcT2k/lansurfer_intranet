<?
	$LS_BASEPATH = "../";
	include $LS_BASEPATH."../includes/ls_base.inc";

	$res = SQL_Query("SELECT forum_topic.title
		FROM forum_topic
		WHERE (forum_topic.id=$id)");
	$TopicInfo = mysql_fetch_array($res);

	NavAdd(_("Board"), "index.php");

	$LS_OPENPAGE = TRUE;
	
	StartPage($TopicInfo[title]);

	$ForumAdmin = user_auth_ex(AUTH_TEAM, 0, TEAM_FORUM, FALSE);
	
	if ($user_valid && ($PageSize = $user_current[forum_pagecount]) > 25) {

	} else
		$PageSize = 25;

	$PageCount = 0;
	
	?>
	<p class=content>
	<?
		$res = SQL_Query("SELECT COUNT(*) AS 'cnt' FROM forum_posting WHERE (topic=$id)");
		$row = mysql_fetch_array($res);
		$PageCount = ceil($row[cnt] / $PageSize);
		
		if (!isset($page))
			$page = $PageCount - 1;
		
		if ($PageCount > 1) {
			printf(_("The topic is %d pages long:"), $PageCount);
			for ($i = 0; $i < $PageCount; $i++) {
				echo " ";
				if ($i == $page)
					echo $i + 1;
				else
					echo "<a href=\"$PHP_SELF?id=$id&page=$i\">".($i + 1)."</a>";
			}
			echo "<br><br>";
		}
	?>
	<? NavPrintAction("topic.php?action=reply&forum=".$TopicInfo[id]."&topic=$id", _("Post reply")); ?><br>
	<br>
	<div align="center">
	<table class="liste" width="96%">
		<tr class="liste">
			<th class="liste" width=160><? echo _("Author"); ?></th>
			<th class="liste"><? echo _("Topic"); ?>: <? echo $TopicInfo[title]; ?></th>
		<tr>
	<?
		flush();
	
		$res = SQL_Query("SELECT 
			fp.id,
			fp.text,
			UNIX_TIMESTAMP(date) as 'date',
			fp.ls_id,
			user.name as 'username',
			user.id as 'userid',
			user.clan,
			fp.name,
			fp.email
			
			FROM forum_posting AS fp LEFT JOIN user ON (user.id=fp.ls_id)
			WHERE (topic=$id)
			ORDER BY fp.date
			LIMIT ".($page * $PageSize).",$PageSize");
		while ($row = mysql_fetch_array($res)) {
		?>
		<tr class="liste">
			<td class="liste" valign="top">
				<? echo _("From"); ?>:<br>
				<?
					if ($row[ls_id] > 0) {
						echo $row[username];
						if ($row[clan]) {
							echo "<br>"._("Clan").":<br>";
							echo $row[clan];
						}
						
					} else {
						echo "<a href=\"mailto:".$row[email]."\">".HTMLStr($row[name])."</a>";
					}
				
				?><br>
				<br>
				<? 
				echo _("Date").'<br>';
				echo DisplayDate($row[date]);
				echo '<br>';
				if ($user_current[id] == $row[userid])
					echo '<br><a href="topic.php?action=edit&id='.$row[id].'">'._("Edit").'</a>';
				if ($ForumAdmin)
					printf('<br><a href="topic.php?action=removepost&id=%d">'._("Remove").'</a>', $row[id]);
				 ?>
			</td>
			<td class="liste" valign="top"><? 
				PrintText2HTML($row['text']);
			?></td>
		</tr>
		<?
		}
	?>		
	<tr>
		<td align="left" class="content"><? 
			if ($page > 0 && $PageCount)
				NavPrintPrevPage($PHP_SELF."?id=$id&page=".($page - 1));
		?></td>
		<td align="right" class="content"><?
			if ($page < ($PageCount - 1))
				NavPrintNextPage($PHP_SELF."?id=$id&page=".($page + 1));
		?></td>
	</tr>
	</table>
	</div>
	<br>
	<div class="content"><? NavPrintAction("topic.php?action=reply&forum=".$TopicInfo[id]."&topic=$id", _("Post reply")); ?></div>
	</p>
<?
	NavPrintBack();

	EndPage();
?>
