<?
	$LS_BASEPATH = '../';
	include $LS_BASEPATH.'../includes/ls_base.inc';

	$LS_POPUPPAGE = TRUE;
	StartPage(sprintf(_("%d New Messages"), $cnt));

	echo '<h3 class=content>'._("Intranet Message System").'</h3>';
	echo '<p class=content align=center>';
	printf(_("You have %d new messages"), $cnt);
	echo '<br>';
	echo '<a target=main href="index.php" onClick="opener.focus();window.close();">'._("View Inbox").'</a>';
	echo '</p>';
	
	EndPage();

?>