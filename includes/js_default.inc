<script language="JavaScript">
<!--
	function IMSOpenSend(to) {
		var t = LSPopup("<? echo $LS_BASEPATH; ?>ims/send.php?to=" + to, "ims_send", 400, 460, 0);
		
		t.focus();
	}
	
	function IMSBuddyList(params) {
		var t = LSPopup("<? echo $LS_BASEPATH; ?>ims/buddy.php" + params, "ims_buddy", 200, 400, 1);
		t.focus();
	}
	
	function LSPopup(url, name, width, height, type) {
		var options = "resizable=1, width="+width+", height=" + height;
		
		if (type == 0) {
		  options += "scrollbar=1, top="+((screen.availHeight - height) / 2)+", left="+((screen.availWidth - width) / 2);
		} else if (type == 1) {
			options += ", top="+(screen.availHeight - height - 100)+", left="+(screen.availWidth - width - 30);
		}
		
		var w = window.open(url, name, options);
		return w;
	}
//-->
</script>
