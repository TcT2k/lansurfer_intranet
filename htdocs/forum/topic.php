<?
	$LS_BASEPATH = "../";
	include $LS_BASEPATH."../includes/ls_base.inc";

	if ($action == "edit" || $action == "removepost") {
		$res = SQL_Query("SELECT * FROM forum_posting WHERE id=$id");
		$Post = mysql_fetch_array($res);
		
		$topic = $Post[topic];
		$res = SQL_Query("SELECT * FROM forum_topic WHERE id=$topic");
		$Topic = mysql_fetch_array($res);
		$forum = $Topic[forum];
	} elseif ($action == "remove") {
		$topic = $id;
		$res = SQL_Query("SELECT * FROM forum_topic WHERE id=$id");
		$Topic = mysql_fetch_array($res);
		$forum = $Topic[forum];
	}
	
	NavAdd(_("Board"), "index.php");
	
	if ($action == "reply" || $action == "edit" || $action == "removepost") {
		switch ($action) {
				case "edit":
					$PageTitle = _("Edit Posting");
					break;
				case "reply":
					$PageTitle = _("Create Posting");
					break;
				case "removepost":
					$PageTitle = _("Remove Posting");
					break;
		}
		$res = SQL_Query("SELECT * FROM forum_topic WHERE (id=$topic)");
		$TopicInfo = mysql_fetch_array($res);
		NavAdd($TopicInfo[title], "postings.php?id=".$TopicInfo[id]);
		
	} elseif ($action == "remove") {
		$PageTitle = _("Remove Posting");
	} else {
		$PageTitle = _("Create Topic");
	}
	
	$LS_OPENPAGE = TRUE;
	
	StartPage($PageTitle);

	$ForumAdmin = user_auth_ex(AUTH_TEAM, 0, TEAM_FORUM, FALSE);

	if ($action == "edit" && $Post[ls_id] != $user_current[id] && !$ForumAdmin)
		LS_Error(_("Invalid User."));
	
	if ($action == "new" || $action == "edit" || $action == "reply") {
		
		if ($action == "edit" && $user_current[id] != $Post[ls_id]) {
			if (!$Post[ls_id])
				$PostingUserName = $Post[name];
			else {
				$res = SQL_Query("SELECT name FROM user WHERE id=".$Post[ls_id]);
				$PostUser = mysql_fetch_array($res);
				$PostingUserName = $PostUser[name];
			}
		}
		
		FormStart();
		if ($submited) {
			if ($f_title == "" && $action == "new")
				FormError("f_title", _("The title must not be empty"));
			if ($action != "edit" && !$f_ls_id) {
				if ($f_name == "")
					FormError("f_name", _("Your have to specify a name or login before posting."));
			}
			if ($f_text == "")
				FormError("f_text", "The posting may not be empty");
				
			
			
			if (!$FormErrorCount) {
				if (isset($f_ls_id) && ($f_ls_id > 0))
					$userfields = ",ls_id=$f_ls_id";
				else
					$userfields = ", name='".SQL_Str($f_name)."', email='".SQL_Str($f_email)."'";

				if ($action == "new") {
					SQL_Query("INSERT INTO forum_topic SET title='".SQL_Str($f_title)."', pcount=1");
					$topicid="LAST_INSERT_ID()";
					
				} elseif ($action != "edit") {
					$topicid = $topic;
					
					SQL_Query("UPDATE forum_posting SET flags=0 WHERE (topic=$topicid)");

					$res = SQL_Query("SELECT COUNT(*) FROM forum_posting WHERE (topic=$topic)");
					$cnt = mysql_result($res, 0, 0);
					$cnt++;
					SQL_Query("UPDATE forum_topic SET pcount=$cnt WHERE (id=$topic)");
				}
				
				
				if ($action == "edit") {
					$f_text = $f_text."\n\n[i](".sprintf(_("This Posting has been edited %s.)"), DisplayDate(time(), DD_NORELATIVE | DD_TIME | DD_DATE))."[/i]\n";
				
					SQL_Query("UPDATE forum_posting SET text='".SQL_Str($f_text)."' WHERE id=$id");
					echo _("The changes were saved.")."<br>";
				}
				else {
					if ($user_valid && $user_current[forum_signature] && !$f_hide_signature) {
						$f_text .= "\n\n".$user_current[forum_signature];
					}

					$fields = "topic=$topicid,text='".SQL_Str($f_text)."', flags=1, date=NOW()";
				
					SQL_Query("INSERT INTO forum_posting SET ".$fields.$userfields);
					echo _("The posting was added.")."<br>";
				}
				
			}
		} else {
			if ($action == "edit") {
				$f_text = $Post[text];
			}
		}
	
		if (!$submited || $FormErrorCount) {
			FormValue("submited", 1);
			FormValue("action", $action);
			FormValue("forum", $forum);
			FormValue("id", $id);
			FormValue("topic", $topic);
			
			if ($PostingUserName)
				FormVisibleValue(_("Name"), HTMLStr($PostingUserName));
			elseif ($user_valid) {
				FormVisibleValue(_("Name"), HTMLStr($user_current[name]));
				FormVisibleValue(_("Email"), HTMLStr($user_current[email]));
				if ($action != "edit" && $user_current[forum_signature])
					FormElement("f_hide_signature", _("Hide my signature"), 1, "checkbox");
				FormValue("f_ls_id", $user_current[id]);
			} else {
				FormElement("f_name", _("Name"), $f_name);
				FormElement("f_email", _("Email"), $f_email);
			}
			if ($action == "new")
				FormElement("f_title", _("Title"), $f_title, "text", "size=30");
			FormElement("f_text", _("Text"), $f_text, "textarea", "cols=70 rows=20");
			
			
			FormElement("", "", ($action == "edit") ? _("Save") : _("Create"), "submit");
		}

		FormEnd();

		if (!$submited || $FormErrorCount) {
?>
	<p class="content">
	<?
		echo _("HTML Code will be ignored.<br>The following commands can be used for formatting your text:");
	?><br>
	<ul>
		<li><? echo _("[b]Text[/b] for <b>bold</b> Text"); ?></li>
		<li><? echo _("[i]Text[/i] for <i>italic</i> Text"); ?></li>
		<li><? echo _("[u]Text[/u] for <u>underlined</u> Text"); ?></li>
		<li><? echo _("[url=http://www.url.de]Page Title[/url] to display a link.<br>URLs beginning with ftp:// or http:// are converted to links without this command."); ?></li>
	</ul>
	</p>
<?
		}
		
	} elseif ($action == "remove") {
		user_auth_ex(AUTH_TEAM, 0, TEAM_FORUM);


		if ($submited) {	
			SQL_Query("DELETE FROM forum_posting WHERE topic=$id");
			SQL_Query("DELETE FROM forum_topic WHERE id=$id");
			echo '<p class="content">';
			printf(_("The topic <i>%s</i> was removed."), $Topic[title]);
			echo '</p>';
		} else {
			echo '<p class="content">';
			printf(_("Are your sure you want to remove the <b>hole</b> Topic <i>%s</i>?"), $Topic[title]);
			echo '</p>';
			
			FormStart();
				FormValue("action", $action);
				FormValue("id", $id);
				FormValue("submited", 1);
				FormElement("", "", _("Remove"), "submit");
			FormEnd();
		}
	} elseif ($action == "removepost") {
		user_auth_ex(AUTH_TEAM, 0, TEAM_FORUM);


		if ($submited) {
			SQL_Query("DELETE FROM forum_posting WHERE id=$id");
			if ($Post[flags] & 1) {  	// remove last posting
				$res = SQL_Query("SELECT id FROM forum_posting WHERE (topic=".$Post[topic].") ORDER BY date DESC LIMIT 1");
				$LastPost = mysql_fetch_array($res);
				$res = SQL_Query("UPDATE forum_posting SET flags=1 WHERE id=".$LastPost[id]);
			}
			$res = SQL_Query("SELECT COUNT(*) FROM forum_posting WHERE (topic=".$Post[topic].")");
			$cnt = mysql_result($res, 0, 0);
			SQL_Query("UPDATE forum_topic SET pcount=$cnt WHERE id=".$Post[topic]);

			echo '<p class="content">';
			printf(_("The posting has been removed."));
			echo '</p>';
		} else {
			echo '<p class="content">';
			printf(_("Are you sure you want to <b>remove</b> this posting?"));
			echo '</p>';
			
			FormStart();
				FormValue("action", $action);
				FormValue("id", $id);
				FormValue("submited", 1);
				FormElement("", "", _("Remove"), "submit");
			FormEnd();
		}
	}
	
	NavPrintBack();

	EndPage();
?>