<?
	$LS_BASEPATH = "../";
	include $LS_BASEPATH."../includes/ls_base.inc";

	NavStruct("orga/");
	StartPage(_("Beamer Config"));
	
	user_auth_ex(AUTH_TEAM);

	if ($submited) {
		SQL_Query("UPDATE beamer SET msg_no='$f_beamer_msg_no', sponsors='$f_sponsors'");
		echo _("Die neuen Einstellungen wurden übernommen!");
	}

	if (!$submited || $SignUpErrors) {
		$res_msg_no = SQL_Query("SELECT msg_no FROM beamer");
		$row_msg_no = mysql_fetch_array($res_msg_no);
		$res_sponsors = SQL_Query("SELECT sponsors FROM beamer");
		$row_sponsors = mysql_fetch_array($res_sponsors);
		FormStart();
			FormValue("submited", "1");
			FormGroup(_("Required"));
			FormElement("f_beamer_msg_no", _("News Number"), "$row_msg_no[msg_no]", "text", "size=3 maxlength=3");
			FormElement("f_sponsors", _("Sponsors"), "$row_sponsors[sponsors]", "textarea", "rows=15 cols=50");
			FormElement("", "", _("Create"), "submit");
		FormEnd();
	}

	NavPrintBack();
	EndPage();
?>
