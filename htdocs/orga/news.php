<?
/*
Funktion:
	News Posten, Bearbeiten und Löschen

Parameter:
	action:
		"new"
		"edit"
		"remove"
	Team:
		Orga Team zu dem die News einträge gehören
	id:
		id des news posting


*/

	$LS_BASEPATH = "../";
	include $LS_BASEPATH."../includes/ls_base.inc";

	NavStruct("orga");

	StartPage(_("News"));
	
	if (!isset($action))
		LS_Error(_("Invalid parameter"));

	user_auth_ex(AUTH_TEAM, $team, TEAM_NEWS);

	if ($action == "new" || $action == "edit") {
	
	?><h3 class="content"><? echo ($action == "new") ? _("New Message") : _("Edit Message");  ?></h3>
	<p><?
	
		FormStart("action=\"".$PHP_SELF."\" method=\"post\"");
			if ($submited) {
				if ($f_title == "")
					FormError("f_title", _("The title may not be empty"));

				$date = deStrToDate($f_date, $f_time);
				if ($date < 0)
					FormError("f_time", "The date or time in the field date is not valid.");

				
				if (!$FormErrorCount) {
					$fields = SQL_QueryFields(array(
						"title" => $f_title,
						"msg" => $f_msg,
						"options" => ($f_public | $f_orgateam))).",
						date=FROM_UNIXTIME($date)";
					
					if ($action == "new")
						$query = "INSERT INTO news SET author=".$user_current[id].", $fields";
					else
						$query = "UPDATE news SET $fields WHERE (id=$id)";
					
					SQL_Query($query);
					
					echo '<p class=content>'._("The message was stored.")."</p>";
				}
			} else {
				if ($action == "edit") {
					$res = SQL_Query("SELECT *, UNIX_TIMESTAMP(date) as ts FROM news WHERE (id=$id)");
					$row = mysql_fetch_array($res);
					$f_title = $row[title];
					$f_msg = $row[msg];
					$f_public = $row[options] & NEWS_PUBLIC;
					$f_orgateam = $row[options] & NEWS_TEAM;

					$f_date = date("d.m.Y", $row[ts]);
					$f_time = date("H:i", $row[ts]);
					
				} else {
					$now = time();
					$f_date = date("d.m.Y", $now);
					$f_time = date("H:i", $now);
					$f_orgateam = true;
				}
			}
			
			if ($FormErrorCount || !$submited) {
				FormValue("action", $action);
				FormValue("submited", 1);
				FormValue("team", $team);
				FormValue("id", $id);
				
				FormElement("f_title", _("Title"), $f_title, "text", "size=40");
				FormElement("f_date", _("Date"), $f_date, "text", "size=10 maxlength=10", 1);
					FormElement("f_time", "", $f_time, "text", "size=5 maxlength=5", -1);
				FormElement("f_msg", _("Text"), $f_msg, "textarea", "cols=\"60\" rows=\"20\"");
				FormElement("f_orgateam", _("Visible on news page"), NEWS_TEAM, "checkbox", ($f_orgateam) ? "checked" : "");
				
				FormElement("", "", _("Save"), "submit");
			}
		FormEnd();
	} elseif ($action == "remove") {
		if ($submited)
		{
			SQL_Query("DELETE FROM news WHERE (id=$id)");
			echo _("The message was removed.");
		} else {
			$res = SQL_Query("SELECT * FROM news WHERE (id=$id)");
			$item = mysql_fetch_array($res);
			if (!$item)
				LS_Error(_("The message could not be found."));
			
			printf(_("Do you really want to <b>permantly</b> remove the message <i>%s</i>."), $item[title]);
			
			FormStart();
				FormValue("team", $team);
				FormValue("id", $id);
				FormValue("action", $action);
				FormValue("submited", 1);
				FormElement("", "", _("Remove"), "submit");
			FormEnd();
		}
	}
?>
	</p>
<?

	NavPrintBack();
	
	EndPage();
?>
