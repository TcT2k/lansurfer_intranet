<?
	$LS_BASEPATH = '../';
	include $LS_BASEPATH.'../includes/ls_base.inc';
?>
<html>
<head>
	<title><? echo _("LIMS Buddy List"); ?></title>
</head>

<FRAMESET border="0" frameSpacing="0" frameBorder="0" rows="*,18"> 
  <FRAME marginWidth=2 marginHeight=2 name=list src="buddy_list.php<? if ($QUERY_STRING) echo '?'.$QUERY_STRING; ?>">
  <FRAME name=status marginWidth=0 marginHeight=0 src="buddy_status.php" scrolling="NO" frameborder="NO">
</FRAMESET>

</html>
