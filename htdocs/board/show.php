<?
	$LS_BASEPATH = '../';
	require $LS_BASEPATH."../includes/ls_base.inc";

	NavAdd(_("Board Overview"), "");
	
	$res = SQL_Query("SELECT * FROM forum WHERE (id='$id')");
	$forum = SQL_fetch_array($res);

	$LS_OPENPAGE = ($forum['options'] & FORUM_TEAM) ? false : true;

	StartPage(sprintf(_("Board: %s"),$forum['name']), 0, $forum[team]);
	$user_current['PrevLoginTime'] = time();

	if (!$forum)
		LS_Error(_('Forum not found.'));
	
/*	if ($forum['options'] & FORUM_TEAM)
		$auth->RequirePermission(PERM_TEAM, $forum['team']);*/
	
	if ($user_valid && ($PageSize = $user_current[forum_pagecount]) > 25) {
	
	} else
		$PageSize = 25;
		
	$ForumAdmin = user_auth_ex(AUTH_TEAM, $forum[team], TEAM_FORUM, FALSE);

	// Search Function

	if ($action == "search") {
		
		if ($f_submitted) { 
			$f_searchA = explode (" ", $f_search);
	
			$SQL = 0;
			$SQL = "AND (ft.title LIKE ";	
			$SQLOR = " OR fpb.body LIKE ";
	
			$searchitem = " AND (((ft.title LIKE '%" . implode("%' AND ft.title LIKE '%", SQL_Str($f_searchA)) . "%'))" . 
				" OR ((fpb.body LIKE '%" . implode("%' AND fpb.body LIKE '%", SQL_Str($f_searchA)) . "%')))";
			
			$SQLTables = "
				forum_topic ft
				LEFT JOIN forum_posting fp ON ft.id=fp.topic
				LEFT JOIN	forum_posting_body fpb ON fpb.id=fp.id
				LEFT JOIN forum_posting lp ON ft.LastPosting=lp.id
				LEFT JOIN user u ON u.id=lp.ls_id
				WHERE (forum=$id) $searchitem
				GROUP BY ft.id
				";
		}
	} else {
		$SQLTables = "
				forum_topic ft
				LEFT JOIN forum_posting lp ON ft.LastPosting=lp.id
				LEFT JOIN user u ON u.id=lp.ls_id
			WHERE (forum=$id)
		";
	}

	
// Search Field

	echo '<form action="'.$PHP_SELF.'" method="post" name="Search">
		  <input type=hidden name=f_submitted value="1">
		  <input type=hidden name="id" value='.$id.'>
		  <input type=hidden name="action" value="search">
		  <input type=text name=f_search value="'.$f_search.'">
		  <input type=submit class=form_btn value="'._("Search").'">
		   </form>';

	echo '<p class=content>';
	NavPrintAction("topic.php?action=new&forum=$id", _("New Topic"));
	echo '</p>';
?>	
	<p>
	<table class="liste" width="96%">
		<tr class="liste">
			<th class="liste" width=200><? echo _("Topic"); ?></th>
			<th class="liste" width=40><? echo _("Postings"); ?></th>
			<th class="liste" width=80><? echo _("Pages"); ?></th>
			<th class="liste" width=200><? echo _("Last Posting"); ?></th>
		</tr>
		<?
		flush();
		
		$res = SQL_Query("SELECT COUNT(*) FROM forum_topic WHERE forum=$id");
		$TopicCount = SQL_result($res, 0, 0);

		$PageCount = ceil($TopicCount / $PageSize);

		if ($action == 'search')
			$SQLLimit = '';
		else
			$SQLLimit = sprintf("LIMIT %d, %d", $page * $PageSize, $PageSize);
		

		$res = SQL_Query("SELECT 
				u.name as 'username',
				u.id as 'userid',
				u.clan,
				ft.title,
				ft.id,
				ft.pcount,
				ft.toptions,
				ft.LastPosting,
				lp.name, lp.email, lp.ls_id,
				UNIX_TIMESTAMP(lp.date) as 'date'
			FROM 
				$SQLTables
			ORDER BY lp.date DESC
			$SQLLimit");

		while ($row = SQL_fetch_array($res)) {
		?>
		<tr>
			<td class="liste"><? echo '<a href="'."postings.php?id=".$row[id].'#new">'.HTMLStr($row[title], 30)."</a>"; 
				if ($user_valid) {
					if ($row['date'] > $user_current['PrevLoginTime'])
						echo ' <b>['._("New").']</b>';
				}
			
			?></td>
			<td class="liste" align="right"><? echo $row[pcount]; ?></td>
			<td class="liste" align=center><?
				for ($i = 0; $i < ceil($row[pcount] / $PageSize); $i++) {
					if ($i > 0)
						echo " ";
					echo '<a href="'."postings.php?id=".$row[id]."&page=".($i)."\">".($i + 1)."</a>";
				}
			?></td>
			<td class="liste"><?
					if (!$row['LastPosting']) {
						echo '('._("Unknown").')';
					} elseif ($row['toptions'] & FTO_CLOSED) {
						echo '('._("Thread Closed").')';
					} else {
						echo DisplayDate($row[date]);
						echo " von ";
					  if ($row[ls_id] > 0)
					  	echo HTMLStr($row[username]);
					  else
					    echo $row[name];
					}
				    
				  if ($ForumAdmin) {
				  	printf(' <a title="'._("Remove this topic").'" href="topic.php?action=remove&id=%d">['._("Del").']</a>', $row[id]);
				  }
				  	
			?></td>
		</tr>
		
		<?
		}
		?>
		<tr>
			<td class="content" colspan=2>
			<?
				if ($page && $action != 'search')
					NavPrintPrevPage(sprintf("%s?page=%d&id=%d", $PHP_SELF, $page - 1, $id), _("Newer Topics"));
				else
					echo "&nbsp;";
			?>
			</td>
			<td class="content" colspan=2 align=right>
			<?
				if ($page < $PageCount - 1 && $action != 'search')
					NavPrintNextPage(sprintf("%s?page=%d&id=%d", $PHP_SELF, $page + 1, $id), _("Older Topics"));
				else
					echo "&nbsp;";
			?>
			</td>
		</tr>
	</table>
	</p>
<?	
	if ($PageCount > 1 && $action != 'search') {
		echo '<p class="content">';
		echo _("Pages with older topics");
		echo ' ';
		for ($i = 0; $i < $PageCount; $i++)
			if ($i == $page)
				echo ($i + 1)." ";
			else {
				echo '<a href="';
				echo $PHP_SELF.'?id='.$id.'&page='.$i;
				echo '">'.($i + 1).'</a> ';
//				printf("<a href=\"%s?id=%d&page=%d\">%d</a> ", $sess->url($PHP_SELF), $id, $i, $i+1);
			}
		echo "</p>";
	}

?>
<SCRIPT LANGUAGE="JavaScript">
<!--
document.Search.f_search.focus();
//  -->
</SCRIPT>
<?
	EndPage();
?>
