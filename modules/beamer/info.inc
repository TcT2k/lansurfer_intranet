<?
	/*
	 * LANsurfer - Beamer Module
	 */
	
	$info['vendor'] = "LANsurfer";
	$info['vendorURL'] = "http://www.lansurfer.com";
	$info['displayname'] = _("Beamer Presentation");		// Name used for this module in Menu
	$info['version'] = LS_INTRANET_VERSION;
	$info['builddate'] = LS_INTRANET_BUILD_DATE;

	$info['orga_menuitems'] = array(
		"javascript:BeamerStart()" => _("Start Beamer Presentation")  // not very modular to use predefined javascript here, but who cares :)
	);


?>