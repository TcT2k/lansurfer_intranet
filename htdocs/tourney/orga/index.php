<?
	$LS_BASEPATH = '../../';
	require $LS_BASEPATH.'../includes/ls_base.inc';
  require $LS_BASEPATH."../includes/tourney/base.inc";
	
  NavStruct("tournaments");

	StartPage(_("Tournament Administration"));
	
	user_auth_ex(AUTH_TOURNEY, -1, 0, TRUE);

	$acEdit = 'edit';
	$acRemove = 'remove';
	$acNew = 'create';
	$acNewDef = 'createdef';

	$res = SQL_Query("SELECT COUNT(*) FROM TourneyGroup");
	if (!mysql_result($res, 0, 0)) {
		SQL_Query("INSERT INTO TourneyGroup SET ".SQL_QueryFields(array(
			'name' => _("Teamplay Tournaments"),
			'type' => GRP_EXCLUSIVE
			)));
		SQL_Query("INSERT INTO TourneyGroup SET ".SQL_QueryFields(array(
			'name' => _("1on1 Tournaments"),
			'type' => GRP_EXCLUSIVE
			)));
	}
	
	echo '<p class=content>';
	NavPrintAction('edit.php?action='.$acNewDef, _("Create from template"));
	echo '<br>';
	NavPrintAction('edit.php?action='.$acNew, _("Create tournament"));
  echo '<br><br>';
	
	$res = SQL_Query("SELECT t.id,grp,t.name,icon,t.options,t.status,tg.name grpName FROM Tourney t LEFT JOIN TourneyGroup tg ON t.grp=tg.id ORDER BY tg.name,name");
	
	$prevGrp = false;
	
	$showWWCL = false;
	if (mysql_num_rows($res)) {
  	echo '<table>';
		while ($row = mysql_fetch_array($res)) {
			if ($prevGrp != $row['grp'])
				echo '<tr><th width=300 class=liste>'.$row['grpName'].'</th></tr>';
			echo '<tr>';
			echo '<td class=liste><a href="'.'edit.php?id='.$row['id'].'&action='.$acEdit.'">';
			if ($row['icon'])
				echo '<img border=0 width=16 height=16 src="'.$LS_BASEPATH.'images/tourney/icons/'.$row['icon'].'"> ';
			echo HTMLStr($row['name']).'</td>';
			echo '<td class=liste><a href="admins.php?id='.$row['id'].'">'._("Admins").'</td>';
			if ($row['options'] & TO_WWCL && $row['status'] >= TS_MATCH) {
				echo '<td class=liste><a href="wwcl.php?id='.$row['id'].'">'._("Export").'</td>';
				$showWWCL = true;
			} else
				echo '<td class=liste>'._("Export").'</td>';
			echo '<td>';
			NavPrintDel('edit.php?id='.$row['id'].'&action='.$acRemove);
			echo '</td>';
			echo '</tr>';
			$prevGrp = $row['grp'];
			
		}
  	echo '</table>';
  }
  echo '</p>';
  
  if ($showWWCL) {
  	echo '<p class=content>';
  	echo '<b>'._("WWCL Export").'</b><br>';
  	echo _("If you want to export the tournament results to the WWCL application you have to dowload all 'Export' links to the party folder of the WWCL application. Additionally you have to download the unkown players DB below.");
  	echo '<br>';
  	NavPrintAction('wwcl.php?action=unknowns', _("Unknown Players Data Base"));
  	echo '</p>';
  }
  
  echo '<h3 class=content>'._("Tournament Groups").'</h3>';
  
  $res = SQL_Query("SELECT id,name FROM TourneyGroup ORDER BY name");
  
  echo '<p class=content>';
  NavPrintAction('group.php?action=new', _("Create Group"));
  echo '<br><br>';
  
	if (mysql_num_rows($res)) {
  	echo '<table>';
  	echo '<th class=liste width=300>'._("Name").'</th>';
		while ($row = mysql_fetch_array($res)) {
			echo '<tr>';
			echo '<td class=liste><a href="group.php?id='.$row['id'].'&action=edit'.'">';
			echo HTMLStr($row['name']).'</td>';
			echo '<td>';
			NavPrintDel('group.php?id='.$row['id'].'&action=remove');
			echo '</td></tr>';
		}
  	echo '</table>';
	}
	echo '</p>';
		
	EndPage();
?>