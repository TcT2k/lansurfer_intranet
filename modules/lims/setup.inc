<?
	/*
	 * LANsurfer - LIMS Module
	 */

	$info['setup'] = array(							// Setup options
	);
	
	$info['sql'] = array(								// SQL Tables
		array(
			'name' => "ims",
			'fields' => "
				id int(10) unsigned NOT NULL auto_increment,
				date datetime NOT NULL default '0000-00-00 00:00:00',
				type tinyint(4) NOT NULL default '0',
				src int(11) NOT NULL default '0',
				dst int(11) NOT NULL default '0',
				msg text NOT NULL,
				flags int(11) NOT NULL default '0',
				PRIMARY KEY  (id)
			"
		),
		array(
			'name' => "imsUsers",
			'fields' => "
				id int(10) unsigned NOT NULL auto_increment,
				owner int(10) unsigned NOT NULL default '0',
				user int(10) unsigned NOT NULL default '0',
				type tinyint(4) NOT NULL default '0',
				PRIMARY KEY  (id)
			"
		),
	);
	
?>