<?
	$LS_BASEPATH = "";
	include "../includes/ls_base.inc";
	if (!LS_CONFIGURED) {
		Header('Location: _setup.php');
		die;
	}
?>
<html>
<head>
	<title><? echo _("LANsurfer - Intranet"); ?></title>
</head>

<FRAMESET border=0 frameSpacing=0 frameBorder=0 rows=80,*> 
  <FRAME name=navbar marginWidth=0 marginHeight=0 src="top.php" scrolling="NO" frameborder="NO">
  <FRAME name=main src="party/news.php">
</FRAMESET>

</html>
