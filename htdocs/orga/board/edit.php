<?
	$LS_BASEPATH = '../../';
	require $LS_BASEPATH."../includes/ls_base.inc";

	NavStruct("orga/");
	NavAdd(_("Board Configuration"), 'index.php');

	StartPage(_("Board Configuration"));
	
	if (!isset($action))
		$action = "edit";

	if ($action == "new")
		$ateam = $team;
	else {
		$res = SQL_Query("SELECT * FROM forum WHERE id=$id");
		$Forum = SQL_fetch_array($res);
		$ateam = $Forum[team];
	}

	user_auth_ex(AUTH_TEAM, 0, TEAM_FORUM, true);

	if ($action == "new" || $action == "edit") {

		FormStart();

		if ($submited) {
			if ($f_name == "")
				FormError("f_name", _("You have to specify a name"));
			if (!$FormErrorCount) {
				$f_opts = 0;
				if (is_array($f_options)) {
					foreach ($f_options as $opt => $v) {
						if ($v)
							$f_opts |= $opt;
					}
				}
				
				$fields = SQL_QueryFields(array(
					'name' => $f_name,
					'description' => $f_desc,
					'options' => $f_opts
					));
				
				if ($action == "new")
					SQL_Query("INSERT INTO forum SET $fields");
				else
					SQL_Query("UPDATE forum SET $fields WHERE id=$id");
				echo '<p class=content>';
				echo _("The board has been saved/created.");
				echo '</p>';
			}
		} else {
			if ($action == "edit") {
				$f_name = $Forum[name];
				$f_desc = $Forum['description'];
				$f_options[FORUM_PRIVATE] = $Forum['options'] & FORUM_PRIVATE;
				$f_options[FORUM_TEAM] = $Forum['options'] & FORUM_TEAM;
				$f_options[FORUM_NOANONYMOUS] = $Forum['options'] & FORUM_NOANONYMOUS;
			}
		}
		
		if (!$submited || $FormErrorCount) {
				FormValue("action", $action);
				FormValue("id", $id);
				FormValue("team", $team);
				FormValue("submited", 1);
				
				FormElement("f_name", _("Name"), $f_name);
				FormElement('f_desc', _("Description"), $f_desc, 'textarea', 'cols=40, rows=5');
				
				FormGroup("Options");
				FormElement('f_options['.FORUM_PRIVATE.']', _("Private Board (will not be shown in overview pages)"), 1, 'checkbox', ($f_options[FORUM_PRIVATE]) ? 'checked' : '');
				FormElement('f_options['.FORUM_TEAM.']', _("Only this OrgaTeam may access the board"), 1, 'checkbox', ($f_options[FORUM_TEAM]) ? 'checked' : '');
				FormElement('f_options['.FORUM_NOANONYMOUS.']', _("Users must login before posting"), 1, 'checkbox', ($f_options[FORUM_NOANONYMOUS]) ? 'checked' : '');
				
				FormElement("", "", _("Save"), "submit");
		}
		
		FormEnd();
	} elseif ($action == "remove") {
		
		if ($submitted) {
			function DeleteBodies($id) {
				$res = SQL_Query("SELECT id FROM forum_posting WHERE topic=".$id);
				while ($row = mysql_fetch_array($res)) {
					SQL_Query("DELETE FROM forum_posting_body WHERE id=".$row['id']);
				}
				mysql_free_result($res);
			}
			
			// Postings Entfernen
			$res = SQL_Query("SELECT id FROM forum_topic WHERE forum=$id");
			while ($row = SQL_fetch_array($res)) {
				DeleteBodies($row['id']);
				
				SQL_Query("DELETE FROM forum_posting WHERE topic=".$row[id]." AND NOT (flags & ".POSTING_NEWS.")");
			}
			SQL_Query("DELETE FROM forum_topic WHERE forum=$id");
			SQL_Query("DELETE FROM forum WHERE id=$id");
			
			echo '<p class=content>';
			printf(_("The board <i>%s</i> and all contained topics were deleted."), $Forum[name]);
			echo '</p>';
		} else {
			echo '<p class=content>';
			printf(_("Do you really wish to delete the board <i>%s</i> and all contained topics?"), $Forum[name]);
			echo '</p>';
			
			FormStart();
				FormValue("id", $id);
				FormValue("submitted", 1);
				FormValue("action", $action);
				
				FormElement("", "", _("Remove"), "submit");
			FormEnd();
		}
	
	}

	NavPrintBack();

	EndPage();

?>