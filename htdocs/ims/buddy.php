<?
	// Prevents browser from reloading hole frameset on refresh
	//Header('Last-Modified: Thu, 13 Jan 2000 00:00:00 GMT');

	$LS_BASEPATH = "../";
	include $LS_BASEPATH."../includes/ls_base.inc";
	
?>
<html>
<head>
	<title><? echo _("LIMS Buddy List"); ?></title>
</head>

<FRAMESET border=2 frameSpacing=2 frameBorder=2 rows="*,32"> 
  <FRAME name=history marginWidth=2 marginHeight=2 src="buddy_list.php">
  <FRAME name=content marginWidth=0 marginHeight=0 src="buddy_toolbar.php">
</FRAMESET>

</html>
