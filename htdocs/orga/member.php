<?
	$LS_BASEPATH = "../";
	include $LS_BASEPATH."../includes/ls_base.inc";

	$res = SQL_Query("SELECT user.id, user.name, orga.rights FROM orga LEFT JOIN user ON orga.user=user.id WHERE orga.id=$id");
	$OrgaInfo = mysql_fetch_array($res);

	NavStruct("orga");
	
	StartPage(_("Member"));
	
	user_auth_ex(AUTH_TEAM);

	if ($action == "edit") {
		function FormRightCheck($right, $caption) {
			global $f_right, $OrgaInfo;
		
			FormElement("f_right[".$right."]", $caption, 1, "checkbox", ($OrgaInfo[rights] & $right) ? "checked" : "");
		}
		
		if ($submited) {
			$newrights = 0;

			reset ($f_right);
			while (list($key, $value) = each ($f_right)) {
				if ($f_right[$key])
					$newrights |= $key;
  		}
  		SQL_Query("UPDATE orga SET rights=$newrights WHERE id=$id");
  		echo "<p class=content>"._("The modifications were stored.")."</p>";
		} else {
			echo '<p class=content>';
			echo "<b>"._("Name").": </b>".HTMLStr($OrgaInfo[name])."<br><br>";
			echo '</p>';

			FormStart("action=\"$PHP_SELF\" method=\"post\"");
				FormValue("action", $action);
				FormValue("submited", 1);
				FormValue("id", $id);
				
				FormGroup(_("Intranet permissions"));
				FormRightCheck(TEAM_USER,			_("Add/Edit/Remove Users"));     
				FormRightCheck(TEAM_GUEST,		_("Add/Edit/Remove Guests"));    
				FormRightCheck(TEAM_NEWS,			_("Add/Edit/Remove news"));      
				FormRightCheck(TEAM_NEWS_ALL,	_("Add/Edit/Remove all news"));
				FormRightCheck(TEAM_FORUM,		_("Add/Edit/Remove Board"));     
				FormRightCheck(TEAM_TOURNEY,	_("Add/Edit/Remove tourneys"));  
				FormRightCheck(TEAM_CATERING,	_("Catering system admin"));  

				FormGroup(_("Internet permissions (unused)"));
				FormRightCheck(TEAM_PARTY,		_("Add/Edit/Remove Parties"));   
				FormRightCheck(TEAM_DESIGN,		_("Add/Edit/Remove Designs"));   
			
				FormElement("", "", _("Save"), "submit");
			FormEnd();
		}
	} elseif ($action == "remove") {
		if ($submitted) {
			SQL_Query("DELETE FROM orga WHERE id=$id");
			echo '<p class=content>';
			echo _("Member removed.");
			echo '</p>';
		} else {
			echo '<p class=content>';
			printf(_("Are you sure you want to remove %s from the team?"), HTMLStr($OrgaInfo[name]));
			echo '</p>';
			
			FormStart();
				FormValue("action", $action);
				FormValue("submitted", 1);
				FormValue("id", $id);
				
				FormElement("", "", _("Remove"), "submit");
			FormEnd();	
		}
	}

	echo '<p class=content>';
	NavPrintBack();
	echo '</p>';

	EndPage();
?>