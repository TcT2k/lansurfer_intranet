<?
	$LS_BASEPATH = "../";
	include $LS_BASEPATH."../includes/ls_base.inc";
	
	include $LS_BASEPATH."../includes/user_form_new.inc";
	
	NavStruct("orga/");

	StartPage(_("Create User/Guest"));
	
	user_auth_ex(AUTH_TEAM, TEAM_GUEST, 0);
	
	if ($submited) {
		if (UserSignupCheck()) {
			$newuser = UserSignupCreateAccount();
			printf(_("User was created (ID: %d)"), $newuser);
			echo '<br>';
			if (!$f_useronly) {
				SQL_Query("INSERT INTO guest SET user=$newuser,flags=0");
				NavPrintAction("guest_detail.php?id=".mysql_insert_id(), _("Guest Details"));
			}
		};
	}
	
	if (!$submited || $SignUpErrors) {
		FormStart();
			UserSignupPrint();
			FormElement("f_useronly", _("Create only user"), 1, "checkbox");
			FormElement("", "", _("Create"), "submit");
		FormEnd();
	}

	NavPrintBack();
	
	EndPage();

?>