<?
	$LS_BASEPATH = '../';
	require $LS_BASEPATH."../includes/ls_base.inc";

	NavAdd(_("Board Overview"), "");
	
	$res = SQL_Query("SELECT 
			forum.name, ft.title, forum.id, forum.options, ft.toptions, ft.views, ft.LastPosting
		FROM forum_topic ft
			LEFT JOIN forum ON (forum.id=ft.forum) 
		WHERE (ft.id='$id')");
	$TopicInfo = SQL_fetch_array($res);
	NavAdd($TopicInfo[name], "show.php?id=".$TopicInfo[id]);

	$LS_OPENPAGE = ($TopicInfo['options'] & FORUM_TEAM) ? false : true;

	
	StartPage(sprintf(_("Board: %s: %s"), $TopicInfo[name], $TopicInfo[title]),0,$TopicInfo[team]);
	$user_current['PrevLoginTime'] = time();
	
	$ForumAdmin = user_auth_ex(AUTH_TEAM, $TopicInfo[team], TEAM_FORUM, FALSE);
	
	if ($TopicInfo['options'] & FORUM_TEAM)
		$auth->RequirePermission(PERM_TEAM, $TopicInfo['team']);
		
	if ($user_valid && ($PageSize = $user_current[forum_pagecount]) > 25) {

	} else
		$PageSize = 25;

	if (!$TopicInfo)
		LS_Error("Invalid topic");
		
	if ($ForumAdmin && $action == 'toggle') {
		if ($TopicInfo['toptions'] & FTO_CLOSED)
			$TopicInfo['toptions'] &= ~FTO_CLOSED;
		else
			$TopicInfo['toptions'] |= FTO_CLOSED;
		$fields = ", toptions=".$TopicInfo['toptions'];
	} else {
		$fields = "";
	}

	SQL_Query("UPDATE LOW_PRIORITY forum_topic SET views=views+1 $fields WHERE id='".$id."'");

	$PageCount = 0;

	?>
	<p class=content>
	<?
		$res = SQL_Query("SELECT COUNT(*) AS 'cnt' FROM forum_posting WHERE (topic=$id) AND NOT (flags&".POSTING_NEWS.")");
		$row = SQL_fetch_array($res);
		$PageCount = ceil($row[cnt] / $PageSize);
		
		if (!isset($page))
			$page = $PageCount - 1;
		
		if ($PageCount > 1) {
			printf(_("This topic is %d pages long"), $PageCount);
			echo ':';
			for ($i = 0; $i < $PageCount; $i++) {
				echo " ";
				if ($i == $page)
					echo $i + 1;
				else
					echo "<a href=\"$PHP_SELF?id=$id&page=$i\">".($i + 1)."</a>";
			}
			echo "<br><br>";
		}

		echo '<p class=content>';
		printf(_("This Thread has been viewed %d times."), $TopicInfo['views']);
		echo '</p>';

		if ($ForumAdmin) {
			FormStart();
				FormValue('id', $id);
				FormValue('action', 'toggle');
				FormElement('', '', ($TopicInfo['toptions'] & FTO_CLOSED) ? _("Open Thread") : _("Close Thread"), 'submit');
			FormEnd();
		}
		
		if (!($TopicInfo['toptions'] & FTO_CLOSED)) {
			echo '<div class=content>';
			NavPrintAction("topic.php?action=reply&forum=".$TopicInfo[id]."&topic=$id", _("Post Reply")); 
			echo '</div><br>';
		}
	?>
	<div align="center">
	<table class="liste" width="96%">
		<tr class="liste">
			<th class="liste" width=160><? echo _("Author"); ?></th>
			<th class="liste"><? echo _("Topic"); ?>: <? echo $TopicInfo[title]; ?></th>
		<tr>
	<?
		flush();
		$newPrinted = false;
	
		$res = SQL_Query("SELECT 
			fp.id,
			pb.body text, 
			UNIX_TIMESTAMP(fp.date) as 'date',
			fp.ls_id,
			user.name as 'username',
			user.id as 'userid',
			user.clan,
			fp.name,
			fp.email
			
			FROM 
				forum_posting AS fp 
				LEFT JOIN forum_posting_body pb ON pb.id=fp.id
				LEFT JOIN user ON (user.id=fp.ls_id)
			WHERE (topic=$id) AND NOT (fp.flags&".POSTING_NEWS.")
			ORDER BY fp.date
			LIMIT ".($page * $PageSize).",$PageSize");
		while ($row = SQL_fetch_array($res)) {
		?>
		<tr class="liste">
			<td class="liste" valign="top" align=left>
				<? echo _("From"); ?>:<br>
				<?
					if ($row[ls_id] > 0) {
						echo $row[username];
						if ($row[clan]) {
							echo "<br>";
							echo _("Clan");
							echo ":<br>";
							echo $row[clan];
						}
					} else {
						printf("<a href=\"mailto:%s\">%s</a>", $row[email], HTMLStr($row[name]));
					}
				
				echo '<br><br>';
				echo _("Date"); 
				echo ':<br>';
				echo DisplayDate($row[date]);
				echo '<br>';
				if ($user_valid && $user_current[id] == $row[userid])
					echo '<br><a href="topic.php?action=edit&id='.$row[id].'">'._("Edit").'</a>';
				if ($ForumAdmin)
					printf('<br><a href="topic.php?action=removepost&id=%d">'._("Remove").'</a>', $row[id]);

				if ($user_valid) {
					if ($row['date'] > $user_current['PrevLoginTime']) {
						if (!$newPrinted) {
							echo '<a name="new">';
							$newPrinted = true;
						}
						echo '<br><b>['._("New").']</b>';
					}
				}
			 ?>
			</td>
			<td class="liste" valign="top" align=left><? 
				PrintText2HTML($row[text]);
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
	<? 
		if (!($TopicInfo['toptions'] & FTO_CLOSED)) {
			echo '<div class=content>';
			NavPrintAction("topic.php?action=reply&forum=".$TopicInfo[id]."&topic=$id", _("Post Reply")); 
			echo '</div>';
		}
	?>
	</p>
	<?
	
	NavPrintBack();

	EndPage();
	
?>
