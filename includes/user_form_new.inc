<?
	/*
		03.08.2000 - Neue Regeln für Clan und Name: Sonderzeichen erlaubt
		
	*/
	
	function UserSignupCreateAccount($updateid = 0) {
		global $f_nick, $f_email, $f_clan, $f_name1, $f_name2, $f_password1, $f_password2, $submited, 
			$f_town, $f_homepage, $f_birthyear, $f_freetext,
			$SignUpError, $FormErrorCount, $agb_text, $SignUpErrors, $f_postcount, $f_signature, $f_wwclid, $f_wwcl_clanid;

		$fields = "name='".SQL_Str($f_nick)."', 
			clan='".SQL_Str($f_clan)."',
			email='".SQL_Str($f_email)."',
			realname1='".SQL_Str($f_name1)."',
			realname2='".SQL_Str($f_name2)."',
			pwd=PASSWORD(".SQL_Quot($f_password1)."),
			hometown='".SQL_Str($f_town)."',
			homepage='".SQL_Str($f_homepage)."',
			birthyear='".SQL_Str($f_birthyear)."',
			infotext='".SQL_Str($f_freetext)."',
			wwclid='".SQL_Str(($f_wwclid) ? substr($f_wwclid, 1) : 0)."',
			wwclclanid='".SQL_Str(($f_wwcl_clanid) ? substr($f_wwcl_clanid, 1) : 0)."'";
			
			if (isset($f_postcount))
				$fields .= ",forum_pagecount=$f_postcount";
			if (isset($f_signature))
				$fields .= ",forum_signature='".SQL_Str($f_signature)."'";
			
		if ($updateid) {
			SQL_Query("UPDATE user SET $fields WHERE id=".SQL_Quot($updateid));
			user_login($updateid, $f_password1);
		} else
			SQL_Query("INSERT INTO user SET $fields");
			
		return mysql_insert_id();
	}
	
	function UserSignupLoad ($Player) {
		global $f_nick, $f_email, $f_name1, $f_name2, $f_password1, $f_password2, $f_clan,
			$f_town, $f_homepage, $f_birthyear, $f_freetext, $f_postcount, $f_signature, $f_wwclid, $f_wwcl_clanid;
		
		$f_nick = $Player[name];
		$f_email = $Player[email];
		$f_name1 = $Player[realname1];
		$f_name2 = $Player[realname2];
		$f_clan = $Player[clan];
		
		$f_town = $Player[hometown];
		$f_homepage = $Player[homepage];
		$f_birthyear = $Player[birthyear];
		$f_freetext = $Player[infotext];
		$f_wwclid = ($Player[wwclid]) ? 'P'.$Player[wwclid] : '';
		$f_wwcl_clanid = ($Player[wwclclanid]) ? 'C'.$Player[wwclclanid] : '';
		
		$f_signature = $Player[forum_signature];
		$f_postcount = $Player[forum_pagecount];
	}

	function UserSignupCheck ($updateid = 0) {
		global $f_nick, $f_email, $f_name1, $f_name2, $f_password1, $f_password2, $f_clan, $SignUpError, $SignUpErrors, $f_wwclid, $f_wwcl_clanid;
		
		if ($f_nick == "")
			$SignUpErrors[f_nick] = _("Empty nickname field");
/*		elseif (!ereg("^[-_~0-9 A-Za-z]+$", $f_nick)) {
			$SignUpErrors[f_nick] = "Der Nickname darf keine Sonderzeichen enthalten";
    }*/

    if ($f_clan != "" && !ereg("^[-_0-9 A-Za-z]+$", $f_clan)) {
			$SignUpErrors[f_clan] = _("The clan name may not contain special characters");
    }

    if ($f_wwclid != "" && !ereg("^P([0-9]{4,})$", strtoupper($f_wwclid))) {
			$SignUpErrors[f_wwclid] = _("The WWCL Player ID has an invalid format");
    }

    if ($f_wwcl_clanid != "" && !ereg("^C([0-9]{4,})$", strtoupper($f_wwcl_clanid))) {
			$SignUpErrors[f_wwcl_clanid] = _("The WWCL Clan ID has an invalid format");
    }

		if ($f_email == "")
			$SignUpErrors[f_email] = _("Email address required");
		else {
			$sqlres = SQL_Query("SELECT * FROM user WHERE email='".SQL_Str($f_email)."'");
			$u = mysql_fetch_array($sqlres);
			if (($u && !$updateid) || ($updateid && $u && $u[id] != $updateid)) {
				$SignUpErrors[f_email] = _("A user with this email is already signed up");
			} else {
				if (!ereg("^([-a-zA-Z0-9._]+)@([-a-zA-Z0-9._]+)$", $f_email, $regs)) {
					$SignUpErrors[f_email] = _("Email address is invalid");
				}
			}
		}
			
		if ($f_name1 == "")
			$SignUpErrors[f_name1] = _("First name required");
		if ($f_name2 == "")
			$SignUpErrors[f_name2] = _("Last name required");
		if ($f_password1 == "")
			$SignUpErrors[f_password1] = _("Password required");
		elseif ($f_password1 != $f_password2)
			$SignUpErrors[f_password2] = _("The passwords do not match");
			
		return (count($SignUpErrors)) == 0;
	}

	function UserSignupPrint($show_forum = FALSE) {
		global $f_nick, $f_email, $f_clan, $f_name1, $f_name2, $f_password1, $f_password2, $submited, 
			$f_town, $f_homepage, $f_birthyear, $f_freetext,
			$SignUpError, $FormErrorCount, $agb_text, $SignUpErrors,
			$f_postcount, $f_signature, $f_wwclid, $f_wwcl_clanid;

		if ($submited && count($SignUpErrors)) {
			reset ($SignUpErrors);
  		while (list($field, $text) = each ($SignUpErrors)) {
				FormError($field, $text);
			}
		}
		
		if (!$submited || $FormErrorCount) {
		
			FormValue("submited", "1");
		
			FormGroup(_("Required"));
			
			FormElement("f_nick", _("Nick"), HTMLStr($f_nick), "text", "size=\"25\"");
			FormElement("f_email", _("Email"), $f_email, "text", "size=\"30\"");
			FormElement("f_name1", _("First Name"), $f_name1, "text", "size=\"25\"");
			FormElement("f_name2", _("Last Name"), $f_name2, "text", "size=\"25\"");
			FormElement("f_password1", _("Password"), $f_password1, "password", "size=\"20\"");
			FormElement("f_password2", _("Password (once again)"), $f_password2, "password", "size=\"20\"");

			if ($agb_text)
				FormElement("f_agb", $agb_text, "1", "checkbox");
			
			FormGroup(_("Optional"));
			FormElement("f_clan", _("Clan"), $f_clan, "text", "size=\"25\"");
			FormElement("f_wwclid", _("WWCL Player ID"), $f_wwclid, "text", "size=7 maxlength=7");
			FormElement("f_wwcl_clanid", _("WWCL Clan ID"), $f_wwcl_clanid, "text", "size=7 maxlength=7");
			FormElement("f_town", _("Home town"), $f_town, "text", "size=\"25\"");
			FormElement("f_homepage", _("Homepage"), $f_homepage, "text", "size=\"25\"");
			FormElement("f_birthyear", _("Birth year"), $f_birthyear, "text", "size=\"4\"");
			FormElement("f_freetext", _("Free Text"), HTMLStr($f_freetext), "textarea", "rows=10 cols=30");
			
			if ($show_forum) {
				function PostingSelectItem($cnt) {
					FormSelectItem(sprintf(_("%s Postings"), $cnt), $cnt);
				}
				
				FormGroup(_("Board Settings"));
				FormSelectStart("f_postcount", _("Postings per page"), 25);
					PostingSelectItem(25);
					PostingSelectItem(50);
					PostingSelectItem(75);
					PostingSelectItem(100);
					PostingSelectItem(125);
				FormSelectEnd();
				FormElement("f_signature", _("Signature"), HTMLStr($f_signature), "textarea", "rows=4 cols=30");
			}
			
		}
	}
		


?>