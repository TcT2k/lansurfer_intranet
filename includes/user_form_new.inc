<?
	/*
		03.08.2000 - Neue Regeln für Clan und Name: Sonderzeichen erlaubt
		
	*/
	
	function UserSignupCreateAccount($updateid = 0) {
		global $f_nick, $f_email, $f_clan, $f_name1, $f_name2, $f_password1, $f_password2, $submited, 
			$f_town, $f_homepage, $f_birthyear, $f_freetext,
			$SignUpError, $FormErrorCount, $agb_text, $SignUpErrors, $f_postcount, $f_signature;

		$fields = "name='".SQL_Str($f_nick)."', 
			clan='".SQL_Str($f_clan)."',
			email='".SQL_Str($f_email)."',
			realname1='".SQL_Str($f_name1)."',
			realname2='".SQL_Str($f_name2)."',
			pwd=PASSWORD(".SQL_Quot($f_password1)."),
			hometown='".SQL_Str($f_town)."',
			homepage='".SQL_Str($f_homepage)."',
			birthyear='".SQL_Str($f_birthyear)."',
			infotext='".SQL_Str($f_freetext)."'";
			
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
			$f_town, $f_homepage, $f_birthyear, $f_freetext, $f_postcount, $f_signature;
		
		$f_nick = $Player[name];
		$f_email = $Player[email];
		$f_name1 = $Player[realname1];
		$f_name2 = $Player[realname2];
		$f_clan = $Player[clan];
		
		$f_town = $Player[hometown];
		$f_homepage = $Player[homepage];
		$f_birthyear = $Player[birthyear];
		$f_freetext = $Player[infotext];
		
		$f_signature = $Player[forum_signature];
		$f_postcount = $Player[forum_pagecount];
		
//		$f_password1 = $f_password2 = $Player[password_de];
	}

	function UserSignupCheck ($updateid = 0) {
		global $f_nick, $f_email, $f_name1, $f_name2, $f_password1, $f_password2, $f_clan, $SignUpError, $SignUpErrors;
		
		if ($f_nick == "")
			$SignUpErrors[f_nick] = "Kein Nickname angegeben";
/*		elseif (!ereg("^[-_~0-9 A-Za-z]+$", $f_nick)) {
			$SignUpErrors[f_nick] = "Der Nickname darf keine Sonderzeichen enthalten";
    }*/

    if ($f_clan != "" && !ereg("^[-_0-9 A-Za-z]+$", $f_clan)) {
			$SignUpErrors[f_clan] = "Der Clanname darf keine Sonderzeichen enthalten";
    }

		if ($f_email == "")
			$SignUpErrors[f_email] = "Keine Emailaddresse angegeben";
		else {
			$sqlres = SQL_Query("SELECT * FROM user WHERE email='".SQL_Str($f_email)."'");
			$u = mysql_fetch_array($sqlres);
			if (($u && !$updateid) || ($updateid && $u && $u[id] != $updateid)) {
				$SignUpErrors[f_email] = "Ein Benutzer mit dieser Emailaddresse ist bereits angemeldet";
			} else {
				if (!ereg("^([-a-zA-Z0-9._]+)@([-a-zA-Z0-9._]+)$", $f_email, $regs)) {
					$SignUpErrors[f_email] = "Keine g&uuml;ltige Emailaddresse";
				}
			}
		}
			
		if ($f_name1 == "")
			$SignUpErrors[f_name1] = "Kein Vorname angegeben";
		if ($f_name2 == "")
			$SignUpErrors[f_name2] = "Kein Nachname angegeben";
		if ($f_password1 == "")
			$SignUpErrors[f_password1] = "Kein Passwort angegeben";
		elseif ($f_password1 != $f_password2)
			$SignUpErrors[f_password2] = "Die beiden Passw&ouml;rter stimmen nicht &uuml;berein";
			
		return (count($SignUpErrors)) == 0;
	}

	function UserSignupPrint($show_forum = FALSE) {
		global $f_nick, $f_email, $f_clan, $f_name1, $f_name2, $f_password1, $f_password2, $submited, 
			$f_town, $f_homepage, $f_birthyear, $f_freetext,
			$SignUpError, $FormErrorCount, $agb_text, $SignUpErrors,
			$f_postcount, $f_signature;

		if ($submited && count($SignUpErrors)) {
			reset ($SignUpErrors);
  		while (list($field, $text) = each ($SignUpErrors)) {
				FormError($field, $text);
			}
		}
		
		if (!$submited || $FormErrorCount) {
		
			FormValue("submited", "1");
		
			FormGroup("Pflichtangaben:");
			
			FormElement("f_nick", "Nickname", HTMLStr($f_nick), "text", "size=\"25\"");
			FormElement("f_email", "Email", $f_email, "text", "size=\"30\"");
			FormElement("f_name1", "Vorname", $f_name1, "text", "size=\"25\"");
			FormElement("f_name2", "Nachname", $f_name2, "text", "size=\"25\"");
			FormElement("f_password1", "Passwort", $f_password1, "password", "size=\"20\"");
			FormElement("f_password2", "Passwort (zur Sicherheit)", $f_password2, "password", "size=\"20\"");

			if ($agb_text)
				FormElement("f_agb", $agb_text, "1", "checkbox");
			
			FormGroup("Optionale Angaben");
			FormElement("f_clan", "Clan", $f_clan, "text", "size=\"25\"");
			FormElement("f_town", "Wohnort", $f_town, "text", "size=\"25\"");
			FormElement("f_homepage", "Homepage", $f_homepage, "text", "size=\"25\"");
			FormElement("f_birthyear", "Geburtsjahr", $f_birthyear, "text", "size=\"4\"");
			FormElement("f_freetext", "Freier Text", HTMLStr($f_freetext), "textarea", "rows=10 cols=30");
			
			if ($show_forum) {
				FormGroup("Forum Einstellungen");
				FormSelectStart("f_postcount", "Beitr&auml;ge pro Seite", 25);
					FormSelectItem("25 Beitr&auml;ge", 25);
					FormSelectItem("50 Beitr&auml;ge", 50);
					FormSelectItem("75 Beitr&auml;ge", 75);
					FormSelectItem("100 Beitr&auml;ge", 100);
					FormSelectItem("125 Beitr&auml;ge", 125);
				FormSelectEnd();
				FormElement("f_signature", "Signatur", HTMLStr($f_signature), "textarea", "rows=4 cols=30");
			}
			
		}
	}
		


?>
