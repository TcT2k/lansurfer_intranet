<?
	$LS_BASEPATH = "../";
	include $LS_BASEPATH."../includes/ls_base.inc";
	include $LS_BASEPATH."../includes/user_form_new.inc";  

	NavStruct("user");
	user_auth();
	
	if ($submited) {
		if (UserSignupCheck($uid)) {
			if ($user_current[id] == $uid) {
				UserSignupCreateAccount($uid);
				user_login($uid, $f_password1);
			}
		}
	} else
		UserSignupLoad($user_current);
	
	StartPage(_("Change Profile"), $party);

	
	if (!$submited || count($SignUpErrors)) {
		FormStart("action =\"".$PHP_SELF."\" method=\"post\"");
			FormValue("party", $party);
			FormValue("uid", $user_current[id]);
	
 			UserSignupPrint(TRUE);
 			FormElement("", "", _("Save"), "submit");
 		
 		FormEnd();
 	} else {
 		echo _("The changes were saved.");
 	}
	
	NavPrintBack();
	
	EndPage();

?>