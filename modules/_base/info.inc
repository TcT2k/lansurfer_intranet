<?
	/*
	 * LANsurfer - Base Module
	 */
	
	$info['vendor'] = "LANsurfer";
	$info['vendorURL'] = "http://www.lansurfer.com";
	$info['displayname'] = _("Base Intranet System");		// Name used for this module in Menu
	$info['version'] = LS_INTRANET_VERSION;
	$info['builddate'] = LS_INTRANET_BUILD_DATE;

	$info['menuitems'] = array(
		"party/news.php" => _("News"),
		"party/guests.php" => _("Guest List"),
		"party/seat.php" => _("Seat Plan")
	);
	
	$info['orga_menuitems'] = array(
		"guests.php" => _("Guest list"),
		"new_guest.php" => _("Create user/guest"),
		"guest_stat.php" => _("Current stats"),
	  "ip_adder.php" => _("IP Adress Adder"),
		"rcon.php" => _("RCON")
	);

?>