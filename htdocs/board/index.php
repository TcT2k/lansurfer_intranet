<?
	$LS_BASEPATH = '../';
	require $LS_BASEPATH."../includes/ls_base.inc";

	$LS_OPENPAGE = TRUE;


	StartPage(_("Board Overview"), 0, $id);
	$user_current['PrevLoginTime'] = time();

	$res = SQL_Query("
		SELECT
				f.name,
				f.id,
				f.description,
				
				lp.id lpId,
				lp.name lpName, 
				lp.email lpEmail, 
				lp.ls_id,
				UNIX_TIMESTAMP(lp.date) lpDate,
				lpt.id lpTopic,
				lpt.title lpTopicTitle,
				
				u.name uname
			FROM forum f
				LEFT JOIN forum_posting lp ON f.LastPosting=lp.id
				LEFT JOIN user u ON u.id=lp.ls_id
				LEFT JOIN forum_topic lpt ON lp.topic=lpt.id
			ORDER BY f.name
		");
		
	$prevTeamID = -1;

	echo '<table>';
	echo '<tr><th class=liste width=180>'._("Name").'</th>';
	//echo '<th class=liste width=30>'._("Topics").'</th>';
	echo '<th class=liste width=200>'._("Last Posting").'</th>';
	echo '</tr>';
	
	while ($row = mysql_fetch_array($res)) {
		echo '<tr>';
		
		$s = ($row['name']) ? $row['name'] : _("Board");
		echo '<td class=liste><a href="'.'show.php?id='.$row['id'].'">'.HTMLStr($s).'</a><br>';
		echo ($row['description']) ? ($row['description']) : _("No Description");
		echo '</td>';
		echo '<td class=liste>';
		if ($row['lpId']) {
			echo '<a href="'.'postings.php?id='.$row['lpTopic'].'">'.HTMLStr($row['lpTopicTitle'], 25).'</a><br>';
			printf(_("%s from %s"),DisplayDate($row['lpDate']), HTMLStr(($row['ls_id']) ? $row['uname'] : $row['lpName'], 12));
		} else {
			echo '('._("Unknown").')';
		}
		
		echo '</td>';
		echo '</tr>';
	}
	echo '</table>';

	EndPage();
?>
