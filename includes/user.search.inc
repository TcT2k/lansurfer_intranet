<?
	$type = ($userSearchSingle) ? 'radio' : 'checkbox';
	$uservar = ($userSearchSingle) ? 'f_user' : 'f_user[]';
	$ucount = 0;
	
	if (isset($f_search) && !isset($f_user)) {
		echo _("Search: ").$f_search;
		$res = SQL_Query("SELECT id,name,clan FROM user WHERE name LIKE '%$f_search%' OR clan LIKE '%$f_search%'");
		$ucount = mysql_num_rows($res);
	}

	if ($ucount == 0) {
		echo _("Find User").': <input type="text" name="f_search">';
	} else {
		echo '<table class=liste>';
		echo '<tr><td></td><th class=liste>Name</th><th class=liste>Clan</th></tr>';
		while ($row = mysql_fetch_array($res)) {
			echo '<tr>';
			echo '<td class=liste>';
			echo '<input type="'.$type.'" name="'.$uservar.'" value="'.$row['id'].'">';
			echo '</td>';
			echo '<td class=liste>'.$row['name'].'</td>';
			echo '<td class=liste>'.$row['clan'].'</td>';
			echo '</tr>';
		}
		echo '</table>';
	}
	
	

?>