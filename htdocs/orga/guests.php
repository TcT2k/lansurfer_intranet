<?
	$LS_BASEPATH = "../";
	include $LS_BASEPATH."../includes/ls_base.inc";

	NavStruct("orga/");

	StartPage(_("Guest list"));
	
	user_auth_ex(AUTH_TEAM);
	
	if (!isset($page))
		$page = $GL_Page;
	else
		$GL_Page = $page;
		
	if (!isset($sort))
		$sort = $GL_Sort;
	else
		$GL_Sort = $sort;
		
	if (!isset($desc))
		$desc = $GL_Desc;
	else
		$GL_Desc = $desc;
		
	$GL_DisplaySearch = TRUE;
	
	$GL_DetailLink = "guest_detail.php?id=%d";
	$GL_DelLink = "guest_detail.php?action=remove&id=%d&page=%d";
	
	include $LS_BASEPATH."../includes/guest_list.inc";
?>
<SCRIPT LANGUAGE="JavaScript">
<!--
document.GuestSearch.f_search.focus();
// -->
</SCRIPT>
<?
	NavPrintBack();

	EndPage();
?>
