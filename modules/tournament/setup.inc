<?
	/*
	 * LANsurfer - Tournament Module
	 */

	$info['setup'] = array(							// Setup options
		'LS_TOURNEY_UPLOAD' => array('caption' => _("Tournament file upload"), 'default' => true, 'type' => 'bool'),
	);
	
	$info['sql'] = array(								// SQL Tables
		array(
			'name' => "Tourney",
			'fields' => "
				id int(11) NOT NULL auto_increment,
				MaxTeams smallint(5) unsigned DEFAULT '0' NOT NULL,
				DELimit int(11) DEFAULT '0' NOT NULL,
				status tinyint(3) unsigned DEFAULT '0' NOT NULL,
				name varchar(255) NOT NULL,
				rules varchar(255) NOT NULL,
				icon varchar(255) NOT NULL,
				TeamSize tinyint(3) unsigned DEFAULT '0' NOT NULL,
				grp smallint(5) unsigned DEFAULT '0' NOT NULL,
				StartTime datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				MatchPause int(10) unsigned DEFAULT '0' NOT NULL,
				Games int(10) unsigned DEFAULT '0' NOT NULL,
				GameLength int(10) unsigned DEFAULT '0' NOT NULL,
				ScoreName varchar(255) NOT NULL,
				DrawHandling tinyint(3) unsigned DEFAULT '0' NOT NULL,
				MatchSettings tinyint(3) unsigned DEFAULT '0' NOT NULL,
				MapList text NOT NULL,
				TeamType text NOT NULL,
				ScoreType int(11) DEFAULT '0' NOT NULL,
				GroupSize int(11) DEFAULT '0' NOT NULL,
				GroupRanks int(11) DEFAULT '0' NOT NULL,
				options int(10) unsigned DEFAULT '0' NOT NULL,
				WWCLType int(10) unsigned DEFAULT '0' NOT NULL,
				PRIMARY KEY (id)
			"
		),
		array(
			'name' => "TourneyAdmin",
			'fields' => "
				id int(11) NOT NULL auto_increment,
				tourney int(11) NOT NULL default '0',
				user int(11) NOT NULL default '0',
				rights int(11) NOT NULL default '0',
				PRIMARY KEY  (id)
			"
		),
		array(
			'name' => "TourneyTempPlayer",
			'fields' => "
				id int(11) NOT NULL auto_increment,
				type tinyint(3) unsigned NOT NULL default '0',
				name char(100) NOT NULL default '',
				email char(100) NOT NULL default '',
				PRIMARY KEY  (id)
			"
		),
		array(
			'name' => "TourneyMatchComment",
			'fields' => "
				id int(10) unsigned NOT NULL auto_increment,
				tourney int(11) DEFAULT '0' NOT NULL,
				mtch int(10) unsigned NOT NULL default '0',
				date datetime NOT NULL default '0000-00-00 00:00:00',
				user int(10) unsigned NOT NULL default '0',
				text text NOT NULL,
				PRIMARY KEY  (id)
			"
		),
		array(
			'name' => "TourneyMatchFile",
			'fields' => "
				id int(10) unsigned NOT NULL auto_increment,
				tourney int(11) DEFAULT '0' NOT NULL,
				mtch int(10) unsigned NOT NULL default '0',
				date datetime NOT NULL default '0000-00-00 00:00:00',
				user int(10) unsigned NOT NULL default '0',
				filename varchar(255) NOT NULL default '',
				dsc varchar(100) NOT NULL default '',
				size int(10) unsigned NOT NULL default '0',
				PRIMARY KEY  (id)
			"
		),
		array(
			'name' => "TourneyBracket",
			'fields' => "
				id int(11) NOT NULL auto_increment,
				tourney int(11) DEFAULT '0' NOT NULL,
				team int(11) DEFAULT '0' NOT NULL,
				position int(11) DEFAULT '0' NOT NULL,
				options int(11) DEFAULT '0' NOT NULL,
				phase int(11) DEFAULT '0' NOT NULL,
				PRIMARY KEY (id)
			"
		),
		array(
			'name' => "TourneyGroup",
			'fields' => "
				id int(11) NOT NULL auto_increment,
				name varchar(255) NOT NULL,
				type int(11) DEFAULT '0' NOT NULL,
				note varchar(255) NOT NULL,
				PRIMARY KEY (id)
			"
		),
		array(
			'name' => "TourneyMatch",
			'fields' => "
				id int(11) NOT NULL auto_increment,
				tourney int(11) DEFAULT '0' NOT NULL,
				op1 int(11) DEFAULT '0' NOT NULL,
				op2 int(11) DEFAULT '0' NOT NULL,
				row int(11) DEFAULT '0' NOT NULL,
				col int(11) DEFAULT '0' NOT NULL,
				date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				status int(11) DEFAULT '0' NOT NULL,
				options int(11) DEFAULT '0' NOT NULL,
				score1 int(11) DEFAULT '0' NOT NULL,
				score2 int(11) DEFAULT '0' NOT NULL,
				ready1 datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				ready2 datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				flags int(11) DEFAULT '0' NOT NULL,
				PRIMARY KEY (id)
			"
		),
		array(
			'name' => "TourneyMatchResult",
			'fields' => "
				id int(11) NOT NULL auto_increment,
				tourney int(11) DEFAULT '0' NOT NULL,
				mtch int(11) DEFAULT '0' NOT NULL,
				time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				score1 int(11) DEFAULT '0' NOT NULL,
				score2 int(11) DEFAULT '0' NOT NULL,
				map varchar(255) NOT NULL,
				options int(11) DEFAULT '0' NOT NULL,
				rel1 int(11) DEFAULT '0' NOT NULL,
				rel2 int(11) DEFAULT '0' NOT NULL,
				point1 int(11) DEFAULT '0' NOT NULL,
				point2 int(11) DEFAULT '0' NOT NULL,
				PRIMARY KEY (id)
			"
		),
		array(
			'name' => "TourneyTeam",
			'fields' => "
				id int(10) unsigned NOT NULL auto_increment,
				tourney int(10) unsigned DEFAULT '0' NOT NULL,
				leader int(10) unsigned DEFAULT '0' NOT NULL,
				name varchar(255) NOT NULL,
				DefMap varchar(255) NOT NULL,
				DefTeam varchar(255) NOT NULL,
				type smallint(5) unsigned DEFAULT '0' NOT NULL,
				wwclid int(10) unsigned NOT NULL default '0',
				PRIMARY KEY (id)
			"
		),
		array(
			'name' => "TourneyTeamMember",
			'fields' => "
				id int(10) unsigned NOT NULL auto_increment,
				team int(10) unsigned DEFAULT '0' NOT NULL,
				user int(10) unsigned DEFAULT '0' NOT NULL,
				PRIMARY KEY (id)
			"
		),
	);
	
?>