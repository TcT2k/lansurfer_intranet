<?
	$LS_BASEPATH = "../";
	include $LS_BASEPATH."../includes/ls_base.inc";
	
	NavStruct("orga");
	StartPage(_("Add Member"));
	
	if ($action=="neworga") {
		FormStart("action=\"$PHP_SELF\" method=\"post\"");
			if ($submited) {
				if ($f_name == "")
					FormError("f_name", _("You must specify a Name."));
				else {
					$res = SQL_Query("SELECT * FROM user WHERE (name like '%".addslashes($f_name)."%')");
					$itemcount = 0;

					while ($row = mysql_fetch_array($res)) {
						if (!$itemcount)
							FormSelectStart("f_user", _("Select user"), $f_user,"size=10");
						FormSelectItem($row[name], $row[id], ($itemcount) ? "" : "checked");
						$itemcount++;
					}
					if ($itemcount) {
						FormSelectEnd();
						
						FormValue("action", "neworga_selected");
						FormValue("team", $team);
						FormElement("", "", _("Add"), "submit");
					} else 
						FormError("f_name", _("No user with this name could be found."));
				}
			}
				
			if (!$submited || $FormErrorCount) {	
				FormValue("action", $action);
				FormValue("submited", 1);
				FormValue("team", $team);
				
				FormElement("f_name", _("Name"), $f_name);
				FormElement("", "", _("Search"), "submit");
			}
		
		FormEnd();
	
	} elseif ($action=="neworga_selected") {
		SQL_Query("INSERT INTO orga SET user=$f_user");
		echo _("The user has been added to the team.");
		NavPrintBack();
	}
	
	EndPage();
?>