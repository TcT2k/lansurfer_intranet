<?
	/*
	 * LANsurfer - Base Module
	 */

	$info['setup'] = array(							// Setup options
		'LS_LANGUAGE' => array('caption' => _("Intranet Language"), 'default' => array(
			'de_DE' => _("German"),
			'en_EN' => _("English"),
			)), 
		'SQL_USER' => array('caption' => _("SQL user"), 'default' => 'root'), 
		'SQL_PASSWORD' => array('caption' => _("SQL user password"), 'default' => '', 'type' => 'password'), 
		'SQL_DB' => array('caption' => _("SQL database"), 'default' => 'db_intra3'), 
		'SQL_HOST' => array('caption' => _("SQL Server"), 'default' => 'localhost'),
		'LS_IP_PUBLIC' => array('caption' => _("Show IPs on public guest list"), 'default' => false, 'type' => 'bool'), 
		'LS_IP_WARN' => array('caption' => _("Warn user if wrong IP is set"), 'default' => false, 'type' => 'bool'), 
		'LS_USE_EMOTICONS' => array('caption' => _("Translate Emoticons to graphic"), 'default' => false, 'type' => 'bool')
	);
	
	$info['sql'] = array(								// SQL Tables
		array(
			'name' => "guest",
			'fields' => "
				id int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
				user int(11),
				flags int(11),
				PRIMARY KEY (id)
			"
		),
		array(
			'name' => "guest_comment",
			'fields' => "
				id int(10) unsigned NOT NULL auto_increment,
				guest int(10) unsigned NOT NULL default '0',
				author int(10) unsigned NOT NULL default '0',
				date datetime NOT NULL default '0000-00-00 00:00:00',
				text text NOT NULL,
				PRIMARY KEY  (id)
			"
		),
		array(
			'name' => "news",
			'fields' => "
				id int(11) DEFAULT '0' NOT NULL auto_increment,
				author int(11) DEFAULT '0' NOT NULL,
				title varchar(255) DEFAULT '' NOT NULL,
				msg text,
				date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				options int(11) DEFAULT '0' NOT NULL,
				PRIMARY KEY (id)
			"
		),
		array(
			'name' => "orga",
			'fields' => "
				id int(11) DEFAULT '0' NOT NULL auto_increment,
				user int(11) DEFAULT '0' NOT NULL,
				rights int(11) DEFAULT '0' NOT NULL,
				date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				PRIMARY KEY (id)
			"
		),
		array(
			'name' => "seat_block",
			'fields' => "
				id int(11) DEFAULT '0' NOT NULL auto_increment,
				party int(11) DEFAULT '0',
				rows int(11) DEFAULT '0',
				cols int(11) DEFAULT '0',
				orientation int(11) DEFAULT '0',
				name varchar(255) DEFAULT '',
				text_tl varchar(255) DEFAULT '',
				text_tc varchar(255) DEFAULT '',
				text_tr varchar(255) DEFAULT '',
				text_lt varchar(255) DEFAULT '',
				text_lc varchar(255) DEFAULT '',
				text_lb varchar(255) DEFAULT '',
				text_rt varchar(255) DEFAULT '',
				text_rc varchar(255) DEFAULT '',
				text_rb varchar(255) DEFAULT '',
				text_bl varchar(255) DEFAULT '',
				text_bc varchar(255) DEFAULT '',
				text_br varchar(255) DEFAULT '',
				color varchar(100) DEFAULT '0',
				PRIMARY KEY (id)
			"
		),
		array(
			'name' => "seat_sep",
			'fields' => "
				id int(11) DEFAULT '0' NOT NULL auto_increment,
				block int(11) DEFAULT '0',
				orientation int(11) DEFAULT '0',
				value int(11) DEFAULT '0',
				PRIMARY KEY (id)
			"
		),
		array(
			'name' => "seats",
			'fields' => "
				id int(11) DEFAULT '0' NOT NULL auto_increment,
				block int(11) DEFAULT '0' NOT NULL,
				col int(11) DEFAULT '0' NOT NULL,
				row int(11) DEFAULT '0' NOT NULL,
				status int(11) DEFAULT '0' NOT NULL,
				guest int(11) DEFAULT '0',
				PRIMARY KEY (id)
			"
		),
		array(
			'name' => "user",
			'fields' => "
				id int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
				clan varchar(255),
				name varchar(255),
				email varchar(255),
				pwd varchar(255) binary,
				realname1 varchar(255),
				realname2 varchar(255),
				homepage varchar(255),
				hometown varchar(255),
				birthyear int(11),
				flags int(11) DEFAULT '0',
				infotext varchar(255),
				forum_pagecount int(11) DEFAULT '0' NOT NULL,
				forum_signature text NOT NULL,
				wwclid int(10) unsigned NOT NULL default '0',
				wwclclanid int(10) unsigned NOT NULL default '0',
				
				ip_address VARCHAR (15),
				kontostand double(16,4) DEFAULT '0.0000' NOT NULL,
				PRIMARY KEY (id)
			"
		)
	);
	
?>