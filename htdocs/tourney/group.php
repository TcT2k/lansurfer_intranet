<?
	$LS_BASEPATH = '../';
	require $LS_BASEPATH.'../includes/ls_base.inc';
  require $LS_BASEPATH."../includes/tourney/base.inc";
	
  NavStruct("tournaments");
  
 	NavAdd(_("Tournament Administration"), 'edit.php');
  
	StartPage((!$action) ? _("Tournament Groups") : _("Edit Group"));
	
	user_auth_ex(AUTH_TOURNEY, -1, 0, TRUE);
	$acEdit = 'edit';
	$acRemove = 'remove';
	$acNew = 'new';
	
	if (!isset($action))
		$action = 'show';

  if ($action == $acEdit || $action == $acNew) {
		if ($submitted) {
			$fields = SQL_QueryFields(array(
				'name' => $f_name,
				'type' => $f_type,
				'note' => $f_note
				));
			if ($action == $acNew) {
				SQL_Query("INSERT INTO TourneyGroup SET $fields");
			} else {
				SQL_Query("UPDATE TourneyGroup SET $fields WHERE id=".$id);
			}
			echo '<p class=content>'._("Group Saved.").'</p>';
		} else {
			if ($action == $acNew) {
				$f_type = GRP_EXCLUSIVE;
			} else {
				$res = SQL_Query("SELECT * FROM TourneyGroup WHERE id=".$id);
				$row = mySQL_fetch_array($res);
				$f_name = $row['name'];
				$f_type = $row['type'];
				$f_note = $row['note'];
			}
		}
		
		if (!$submitted) {
			FormStart();
					FormValue('action', $action);
					FormValue('id', $id);
					FormValue('submitted', 1);
					FormElement('f_name', _("Name"), $f_name, 'text', 'size=30');
					FormElement('f_note', _("Note"), $f_note, 'text', 'size=30');
					FormSelectStart('f_type', _("Type"), $f_type);
						FormSelectItem(_("Group Exclusive"), GRP_EXCLUSIVE);
						FormSelectItem(_("All Exclusive"), GRP_ALLEXCLUSIVE);
						FormSelectItem(_("No registration restriction"), GRP_NORESTRICTION);
					FormSelectEnd();
			
					FormElement('', '', _("Save"), 'submit');
			FormEnd();
		}
	} elseif ($action == $acRemove) {
		if ($submitted) {
			SQL_Query("DELETE FROM TourneyGroup WHERE id=".$id);
			echo '<p class=content>'._("Group removed.").'</p>';
		} else {
			echo '<p class=content>';
			echo _("Are you sure you want to remove this tournament group?");
			echo '</p>';
			
			FormStart();
				FormValue('submitted', 1);
				FormValue('id', $id);
				FormValue('action', $action);
			
				FormElement('', '', _("Remove"), 'submit');
			FormEnd();
		}
	}

	NavPrintBack();

	EndPage();
?>