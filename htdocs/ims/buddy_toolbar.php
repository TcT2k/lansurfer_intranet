<?
	$LS_BASEPATH = '../';
	include $LS_BASEPATH.'../includes/ls_base.inc';

	$LS_POPUPPAGE = TRUE;
	StartPage(_("LIMS Toolbar"));

?>
<script language="JavaScript">
<!--
	function IMSOpenAdd() {
		alert("Add");
	}
-->
</script>
<?

	echo '<a href="javascript:IMSOpenAdd()"><img border=0 alt="'._("Add Buddy").'" src="" width=16 height=16></a>';
	echo '<img src="" width=16 height=16> ';
	echo '<img src="" width=16 height=16> ';

	EndPage();
?>