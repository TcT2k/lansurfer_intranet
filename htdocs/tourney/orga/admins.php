<?
	$LS_BASEPATH = '../../';
	require $LS_BASEPATH.'../includes/ls_base.inc';
  require $LS_BASEPATH."../includes/tourney/base.inc";
	
  NavStruct("tournaments/tourney_orga");
  
  if ($action)
  	NavAdd(_("Tournament Admins"), $PHP_SELF.'?id='.$id);

	StartPage(($action) ? _("Edit Tournament Admin") : _("Tournament Admins"));
	
	user_auth_ex(AUTH_TOURNEY, -1, 0, TRUE);

	if ($action == 'add') {
		if (isset($f_user)) {
			SQL_Query("INSERT INTO TourneyAdmin SET ".SQL_QueryFields(array(
				'tourney' => $id,
				'user' => $f_user
				)));
			echo '<p class=content>';
			echo _("Administrator added.");
			echo '</p>';
		} else {
			echo '<form action='.$PHP_SELF.' method="post">';
			FormValue('action', $action);
			FormValue('id', $id);
		
			$userSearchSingle = TRUE;
			require $LS_BASEPATH.'../includes/user.search.inc';
				
			echo '<input type="submit" value="';
			echo ($ucount) ? _("Add") : _("Search");
			echo '">';
			echo '</form>';
		}
	} else {
		if ($remove) {
			SQL_Query("DELETE FROM TourneyAdmin WHERE id=".$remove);
		}
		
		echo '<h3 class=content>'._("Global Tournament Administrators").'</h3>';
		echo '<p class=content>';
		
		$res = SQL_Query("SELECT
				u.name,
				u.id uid
			FROM orga o
				LEFT JOIN user u ON u.id=o.user
			WHERE o.rights&".TEAM_TOURNEY." 
			ORDER BY u.name");
		
		echo '<table>';
		echo '<tr>';
		echo '<th width=200 class=liste>'._("Name").'</th>';
		echo '</tr>';
			
		while ($row = mysql_fetch_array($res)) {
			echo '<tr>';
			echo '<td class=liste>';
			echo PrintIMSContactLink($row['uid'], HTMLStr($row['name']));
			echo '</td>';
			echo '</tr>';
		}
		echo '</table>';
		echo '</p>';
		
		echo '<h3 class=content>'._("Administrators for this tournament").'</h3>';
		echo '<p class=content>';
		
		
		$res = SQL_Query("SELECT
				u.name,
				u.id uid,
				ta.id
			FROM TourneyAdmin ta
				LEFT JOIN user u ON u.id=ta.user
			WHERE ta.tourney=".$id."
			ORDER BY u.name");
		
		NavPrintAction($PHP_SELF.'?action=add&id='.$id, _("Add Tournament Admin"));
		echo '<br>';
		
		if (mysql_num_rows($res)) {
			echo '<table>';
			echo '<tr>';
			echo '<th width=200 class=liste>'._("Name").'</th>';
			echo '</tr>';
			while ($row = mysql_fetch_array($res)) {
				echo '<tr>';
				echo '<td class=liste>';
				echo PrintIMSContactLink($row['uid'], HTMLStr($row['name']));
				echo '</td>';
				echo '<td>';
				NavPrintDel($PHP_SELF.'?remove='.$row['id'].'&id='.$id, _("Remove"), "onclick=\"return window.confirm('"._("Do you really wish to remove this admin from the tournament?")."');\"");
				echo '</td>';
				echo '</tr>';
			}
			echo '</table>';
		} else 
			echo _("No Administrators for this tournament");
			
		echo '</p>';
	}
	
	NavPrintBack();
	
	EndPage();
	
?>
