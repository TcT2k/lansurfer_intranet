<?
	/*
	 * LANsurfer - Catering Module Definition
	 */
	
	$info['vendor'] = "LANsurfer";
	$info['vendorURL'] = "http://www.lansurfer.com";
	$info['displayname'] = _("Catering");		// Name used for this module in Menu
	$info['version'] = LS_INTRANET_VERSION;
	$info['builddate'] = LS_INTRANET_BUILD_DATE;

	$info['menuitems'] = array(
		'catering/bestellen.php' => _("Catering"),
	);

	$info['orga_menuitems'] = array(
		"catering/" => "Catering"
	);

?>