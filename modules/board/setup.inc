<?
	/*
	 * LANsurfer - Board Module
	 */

	$info['setup'] = array(							// Setup options
	);
	
	$info['sql'] = array(								// SQL Tables
		array(
			'name' => "forum",
			'fields' => "
				id int(11) NOT NULL auto_increment,
				name varchar(255) NOT NULL default '',
				options int(11) NOT NULL default '0',
				description text NOT NULL,
				LastPosting int(11) NOT NULL default '0',
				PRIMARY KEY  (id)
			"
		),
		array(
			'name' => "forum_posting",
			'fields' => "
				id int(11) NOT NULL auto_increment,
				date datetime NOT NULL default '0000-00-00 00:00:00',
				ls_id int(11) default '0',
				name varchar(100) default '0',
				email varchar(200) default '0',
				topic int(11) NOT NULL default '0',
				flags int(11) NOT NULL default '0',
				PRIMARY KEY  (id),
				KEY topic (topic)
			"
		),
		array(
			'name' => "forum_posting_body",
			'fields' => "
				id int(11) NOT NULL default '0',
				body text NOT NULL,
				PRIMARY KEY  (id)
			"
		),
		array(
			'name' => "forum_topic",
			'fields' => "
				id int(11) NOT NULL auto_increment,
				forum int(11) NOT NULL default '0',
				title char(90) NOT NULL default '',
				pcount int(11) NOT NULL default '0',
				views int(11) NOT NULL default '0',
				toptions int(11) NOT NULL default '0',
				LastPosting int(11) NOT NULL default '0',
				PRIMARY KEY  (id),
				KEY forum (forum)
			"
		)
	);
	
?>