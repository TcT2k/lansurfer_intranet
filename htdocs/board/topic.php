<?
	$LS_BASEPATH = '../';
	require $LS_BASEPATH."../includes/ls_base.inc";

	function UpdateForum() {
		global $forum;
		
		$res = SQL_Query("SELECT
				fp.id
			FROM
				forum_topic ft
				LEFT JOIN forum_posting fp ON fp.topic=ft.id
			WHERE
				ft.forum=".$forum."
			ORDER BY fp.date DESC
			LIMIT 1
			");
		if ($lp = mysql_fetch_array($res)) {
			SQL_Query("UPDATE LOW_PRIORITY forum SET LastPosting=".$lp['id']." WHERE id=".$forum);
		}
	}

	if (!isset($newsitem))
		NavAdd(_("Boards"), "");

	if ($action == "edit" || $action == "removepost") {
		$res = SQL_Query("SELECT * FROM forum_posting WHERE id=$id");
		$Post = SQL_fetch_array($res);
		$res = SQL_Query("SELECT * FROM forum_posting_body WHERE id=$id");
		$PostBody = SQL_fetch_array($res);
		
		$topic = $Post[topic];
		$res = SQL_Query("SELECT * FROM forum_topic WHERE id=$topic");
		$Topic = SQL_fetch_array($res);
		$forum = $Topic[forum];
	} elseif ($action == "remove") {
		$topic = $id;
		$res = SQL_Query("SELECT * FROM forum_topic WHERE id=$id");
		$Topic = SQL_fetch_array($res);
		$forum = $Topic[forum];
	}
	
	if ($forum) {
		$res = SQL_Query("SELECT * FROM forum WHERE (id=$forum)");
		$ForumInfo = SQL_fetch_array($res);
		NavAdd($ForumInfo[name], "show.php?id=".$ForumInfo[id]);
		$designteam = $ForumInfo[team];
	} else {
		$designteam = $team;
		if (isset($team))
			NavAdd(_("News Item"), $LS_BASEPATH."party/news_comment.php?id=".$newsitem);
	}
	
	if ($action == "reply" || $action == "edit" || $action == "removepost") {
		switch ($action) {
				case "edit":
					$PageTitle = _("Edit Posting");
					break;
				case "reply":
					if (isset($newsitem))
						$PageTitle = _("Create Comment");
					else
						$PageTitle = _("Create Posting");
					break;
				case "removepost":
					$PageTitle = _("Remove Posting");
					break;
		}
		if (isset($newsitem)) {
			
		} else {
			$res = SQL_Query("SELECT * FROM forum_topic WHERE (forum=$forum) AND (id=$topic)");
			$Topic = SQL_fetch_array($res);
			NavAdd($Topic[title], "postings.php?id=".$Topic[id]);
		}
		
	} elseif ($action == "remove") {
		$PageTitle = _("Remove Topic");
	} else {
		$PageTitle = _("Create Topic");
	}

	if (($forum && $ForumInfo['options']	 & FORUM_NOANONYMOUS || $ForumInfo['options']	 & FORUM_TEAM) || ($newsitem && $team == 2))
		$LS_OPENPAGE = FALSE;
	else
		$LS_OPENPAGE = TRUE;
	
	StartPage($PageTitle, 0, $designteam);
	
	if ($Topic['toptions'] & FTO_CLOSED && $action != "removepost")
		LS_Error(_("This topic is currently closed"));

/*	if ($ForumInfo['options'] & FORUM_TEAM)
		$auth->RequirePermission(PERM_TEAM, $ForumInfo['team']);*/

	if (isset($ForumInfo))
		$ForumAdmin = user_auth_ex(AUTH_TEAM, $ForumInfo[team], TEAM_FORUM, FALSE);

	if ($action == "edit" && $Post[ls_id] != $user_current[id] && !$ForumAdmin)
		LS_Error(_("Invalid User."));
	
	if ($action == "new" || $action == "edit" || $action == "reply") {
		
		if ($action == "edit" && $user_current[id] != $Post[ls_id]) {
			if (!$Post[ls_id])
				$PostingUserName = $Post[name];
			else {
				$res = SQL_Query("SELECT name FROM user WHERE id=".$Post[ls_id]);
				$PostUser = SQL_fetch_array($res);
				$PostingUserName = $PostUser[name];
			}
		}
		
		FormStart();
		if ($submited) {
			if ($f_title == "" && $action == "new")
				FormError("f_title", _("The title may not be empty"));
			if ($action != "edit" && !$f_ls_id) {
				if ($f_name == "")
					FormError("f_name", _("You must specify a name or log in before using this function"));
			}
			if ($f_text == "")
				FormError("f_text", _("The message must contain text"));
			
			if (!$FormErrorCount) {
				if (isset($f_ls_id) && ($f_ls_id > 0))
					$userfields = ",ls_id=$f_ls_id";
				else
					$userfields = ", name='".SQL_Str($f_name)."', email='".SQL_Str($f_email)."'";

				if ($action == "new") {
					SQL_Query("INSERT INTO forum_topic SET forum=$forum, title='".SQL_Str($f_title)."', pcount=1");
					$topicid= mysql_insert_id();
					
				} else
					$topicid = $topic;
				
				
				if ($action == "edit") {
					$f_text = $f_text."\n\n[i](".sprintf(_("This posting has been edited at %s"), DisplayDate(time(), DD_NORELATIVE | DD_TIME | DD_DATE)).')[/i]';
				
					SQL_Query("UPDATE forum_posting_body SET body='".SQL_Str($f_text)."' WHERE id=$id");
					echo _("The changes were saved.");
					echo '<br>';
				}
				else {
					if ($user_valid && $user_current[forum_signature] && !$f_hide_signature) {
						$f_text .= "\n\n".$user_current[forum_signature];
					}

					$newflags = POSTING_LATEST;
					if ($newsitem)
						$newflags |= POSTING_NEWS;

					$fields = "topic=$topicid, flags=".$newflags.", date=NOW()";
				
					SQL_Query("INSERT INTO forum_posting SET ".$fields.$userfields);
					$newPostID = mysql_insert_id();
					SQL_Query("INSERT INTO forum_posting_body SET id=$newPostID, body=".SQL_Quot($f_text));
					
					echo _("The posting has been saved.");
					echo '<br>';
				}

				// Update Topic
				if ($action != "edit") {
					$topic = $topicid;
					
					/*if ($newsitem)
						$WhereAdd = " AND (flags&".POSTING_NEWS.")";
					else
						$WhereAdd = " AND NOT (flags&".POSTING_NEWS.")";
					SQL_Query("UPDATE forum_posting SET flags=flags&".(~POSTING_LATEST)." WHERE (topic=$topicid) $WhereAdd");*/
					

					if ($newsitem) {
						$res = SQL_Query("SELECT COUNT(*) FROM forum_posting WHERE (topic=$topic) AND (flags&".POSTING_NEWS.")");
						$cnt = SQL_result($res, 0, 0);
						//$cnt++;
						SQL_Query("UPDATE news SET CommentCount=$cnt WHERE (id=$topic)");
					} else {
						$res = SQL_Query("SELECT COUNT(*) FROM forum_posting WHERE (topic=$topic) AND NOT (flags&".POSTING_NEWS.")");
						$cnt = SQL_result($res, 0, 0);
						//$cnt++;
						SQL_Query("UPDATE forum_topic SET pcount=$cnt,LastPosting=$newPostID WHERE (id=$topic)");
						SQL_Query("UPDATE forum SET LastPosting=$newPostID WHERE id=".$forum);
					}
				}
				
			}
		} else {
			if ($action == "edit") {
				$f_text = $PostBody['body'];
			}
		}
	
		if (!$submited || $FormErrorCount) {
			FormValue("submited", 1);
			FormValue("action", $action);
			FormValue("forum", $forum);
			FormValue("id", $id);
			if (isset($team))
				FormValue("team", $designteam);
				
			if ($newsitem) {
				FormValue("newsitem", $newsitem);
				FormValue("topic", $newsitem);
			}	else			
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
	<div class=content>
	<ul>
		<li><? echo _("[b]Text[/b] for <b>bold</b> Text"); ?></li>
		<li><? echo _("[i]Text[/i] for <i>italic</i> Text"); ?></li>
		<li><? echo _("[u]Text[/u] for <u>underlined</u> Text"); ?></li>
		<li><? echo _("[url=http://www.url.de]Page Title[/url] to display a link.<br>URLs beginning with ftp:// or http:// are converted to links without this command."); ?></li>
<?
		if (LS_USE_EMOTICONS) {
			echo '<li>';
			echo _("Emoticons are converted to graphic");
			echo ' ';
			NavPrintAction('smilies.php', _("List of all Emoticons"));
			echo '</li>';
		}
?>
	</ul>
	</div>
	</p>
<?
		}
		
	} elseif ($action == "remove") {
		user_auth_ex(AUTH_TEAM, $ForumInfo[team], TEAM_FORUM);


		if ($submited) {
			$res = SQL_Query("SELECT id FROM forum_posting WHERE topic=$id AND NOT (flags&".POSTING_NEWS.")");
			while ($row = mysql_fetch_array($res)) {
				SQL_Query("DELETE LOW_PRIORITY FROM forum_posting_body WHERE id=".$row['id']);
			}
			
			SQL_Query("DELETE FROM forum_posting WHERE topic=$id AND NOT (flags&".POSTING_NEWS.")");
			SQL_Query("DELETE FROM forum_topic WHERE id=$id");
			
			UpdateForum();
			
			echo '<p class="content">';
			printf(_("The topic <i>%s</i> was removed."), $Topic[title]);
			echo '</p>';
		} else {
			printf(_("Are your sure you want to remove the <b>hole</b> Topic <i>%s</i>?"), $Topic[title]);
			
			FormStart();
				FormValue("action", $action);
				FormValue("id", $id);
				FormValue("submited", 1);
				FormElement("", "", _("Remove"), "submit");
			FormEnd();
		}
	} elseif ($action == "removepost") {
		user_auth_ex(AUTH_TEAM, $ForumInfo[team], TEAM_FORUM);


		if ($submited) {
			SQL_Query("DELETE FROM forum_posting WHERE id='$id'");
			SQL_Query("DELETE FROM forum_posting_body WHERE id='$id'");
			
			$res = SQL_Query("SELECT COUNT(*) FROM forum_posting WHERE (topic=".$Post[topic].") AND NOT (flags&".POSTING_NEWS.")");
			$cnt = SQL_result($res, 0, 0);
			$res = SQL_Query("SELECT id FROM forum_posting WHERE topic=".$Post['topic']." AND NOT (flags&".POSTING_NEWS.") ORDER BY date DESC");
			if ($lp = mysql_fetch_array($res)) {
				SQL_Query("UPDATE forum_topic SET pcount=$cnt,LastPosting=".$lp['id']." WHERE (id=".$Post[topic].")");
			}
			
			UpdateForum();

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
