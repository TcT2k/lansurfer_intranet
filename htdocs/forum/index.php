<?
	$LS_BASEPATH = "../";
	include $LS_BASEPATH."../includes/ls_base.inc";

	StartPage(_("Board"));
	
	if ($user_valid && ($PageSize = $user_current[forum_pagecount]) > 25) {
	
	} else
		$PageSize = 25;
		
	$ForumAdmin = user_auth_ex(AUTH_TEAM, 0, TEAM_FORUM, FALSE);
	
	function utime()
	{
	    $time = explode(" ", microtime());
	    $usec = (double)$time[0];
	    $sec = (double)$time[1];
	    return $sec + $usec;
	}

	NavPrintAction("topic.php?action=new", _("New Topic"));
?>	
	<br>
	<p>
	<table class="liste" width="96%">
		<tr class="liste">
			<th class="liste" width=200><? echo _("Title"); ?></th>
			<th class="liste" width=40><? echo _("Postings"); ?></th>
			<th class="liste" width=80><? echo _("Pages"); ?></th>
			<th class="liste" width=200><? echo _("Last Posting"); ?></th>
		</tr>
		<?
		flush();
		$starttime=utime();
		
		$res = SQL_Query("SELECT COUNT(*) FROM forum_topic");
		$TopicCount = mysql_result($res, 0, 0);

		$PageCount = ceil($TopicCount / $PageSize);

		$SQLLimit = sprintf("LIMIT %d, %d", $page * $PageSize, $PageSize);

		$res = SQL_Query("SELECT 
				u.name as 'username',
				u.id as 'userid',
				u.clan,
				ft.title,
				ft.id,
				ft.pcount,
				lp.name, lp.email, lp.ls_id,
				UNIX_TIMESTAMP(lp.date) as 'date'
			FROM 
				forum_topic AS ft,
				forum_posting AS lp
				LEFT JOIN user AS u ON u.id=lp.ls_id
			WHERE (lp.topic=ft.id) AND (lp.flags=1)
			ORDER BY lp.date DESC
			$SQLLimit");

		printf("<!-- DEBUG: Done, took %.2f seconds. -->", ($idle_while=utime()-$starttime));
		
		while ($row = mysql_fetch_array($res)) {
		?>
		<tr>
			<td class="liste"><? echo "<a href=\"postings.php?id=".$row[id]."\">".HTMLStr($row[title], 30)."</a>"; ?></td>
			<td class="liste" align="right"><? echo $row[pcount]; ?></td>
			<td class="liste" align=center><?
				for ($i = 0; $i < ceil($row[pcount] / $PageSize); $i++) {
					if ($i > 0)
						echo " ";
					echo "<a href=\"postings.php?id=".$row[id]."&page=".($i)."\">".($i + 1)."</a>";
				}
			?></td>
			<td class="liste"><?
					printf("%s von %s", DisplayDate($row[date]), HTMLStr(($row[ls_id] > 0) ? $row[username] : $row[name] ));
				    
				  if ($ForumAdmin) {
				  	printf(' <a title="%s" href="topic.php?action=remove&id=%d">[%s]</a>', _("Remove this topic"), $row[id], _("Del"));
				  }
				  	
			?></td>
		</tr>
		
		<?
		}
		?>
		<tr>
			<td class="content" colspan=2>
			<?
				if ($page)
					NavPrintPrevPage(sprintf("%s?page=%d", $PHP_SELF, $page - 1), _("Newer Topics"));
				else
					echo "&nbsp;";
			?>
			</td>
			<td class="content" colspan=2 align=right>
			<?
				if ($page < $PageCount - 1 && $PageCount)
					NavPrintNextPage(sprintf("%s?page=%d", $PHP_SELF, $page + 1), _("Older Topics"));
				else
					echo "&nbsp;";
			?>
			</td>
		</tr>
	</table>
	</p>
<?	
	if ($PageCount > 1) {
		echo "<p class=\"contentpanel\">"._("Pages with older topics")." ";
		for ($i = 0; $i < $PageCount; $i++)
			if ($i == $page)
				echo ($i + 1)." ";
			else
				printf("<a href=\"%s?page=%d\">%d</a> ", $PHP_SELF, $i, $i+1);
		echo "</p>";
	}

	EndPage();
?>
