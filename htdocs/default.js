

	function IMSOpenSend(to) {
		var t = LSPopup(BasePath + "lims/send.php?to=" + to, "ims_send" + to, 400, 460, 0);
		
		t.focus();
	}
	
	function IMSBuddyList(params) {
		var t = LSPopup(BasePath + "lims/buddy.php" + params, "ims_buddy", 200, 400, 1);
		t.focus();
	}
	
	function IMSGuestList() {
		window.open(BasePath + "?content=party/guests.php", "LSIntraMain");
	}
	
	function LSPopup(url, name, width, height, type) {
		var options = "resizable=1, width="+width+", height=" + height;
		
		if (type == 0) {
		  options += "scrollbar=1, top="+((screen.availHeight - height) / 2)+", left="+((screen.availWidth - width) / 2);
		  
		} else if (type == 1) {
			options += ", scrollbar=1, top="+(screen.availHeight - height - 100)+", left="+(screen.availWidth - width - 30);
		}

		var w = window.open(url, name, options);
		
		return w;
	}
