<?
	$LS_BASEPATH = '../';
	include $LS_BASEPATH.'../includes/ls_base.inc';

	$LS_POPUPPAGE = TRUE;
	StartPage(_("Send Message"));
	
	echo '<h3 class=content>'._("Send Message").'</h3>';
	
	$res = SQL_Query("SELECT name,id FROM user WHERE id=".SQL_Quot($to));
	if (!($dstUser = mysql_fetch_array($res)))
	  LS_Error(_("Invalid destination."));
	
	if ($submitted) {
		if (!$f_subject)
			FormErrorEx('f_subject', _("The subject may not be empty"));
		
		if (!$FormErrorCount) {
			SQL_Query("INSERT INTO ims SET date=NOW(),".SQL_QueryFields(array(
				'dst' => $to,
				'src' => $user_current['id'],
				'subject' => $f_subject,
				'msg' => $f_msg
				)));
			echo '<p class=content>'._("Message sent.").'</p>';
		}
	}
	
	if (!$submitted || $FormErrorCount) {
		$res = SQL_Query("SELECT * FROM imsUsers WHERE owner=".SQL_Quot($user_current['id'])." AND user=".SQL_Quot($dstUser['id']));
		if (!($row = mysql_fetch_array($res))) {
			echo '<p class=content>';
			NavPrintAction("javascript:IMSBuddyList('?add=".$dstUser['id']."')", sprintf(_("Add %s to my Buddy List"), $dstUser['name']));
			echo '</p>';
		}
		
		FormStart();
			FormValue('submitted', 1);
			FormValue('to', $to);
			FormVisibleValue(_("To"), HTMLStr($dstUser['name']));
			
			FormElement('f_subject', _("Subject"), $f_subject, 'text', 'size=30 maxlength=100');
			FormElement('f_msg', _("Message"), $f_msg, 'textarea', 'cols=30 rows=6');
			
			FormElement('', '', _("Send"), 'submit');
		FormEnd();
	}
	
	NavPrintAction('javascript:window.close();', _("Close Window"));
	
	EndPage();
	
?>