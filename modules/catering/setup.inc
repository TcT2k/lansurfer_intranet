<?
	/*
	 * LANsurfer - Catering Module Setup
	 */

	$info['setup'] = array(							// Setup options
		'LS_CATERING_CURRENCY' => array(
			'caption' => _("Currency to use in catering"), 
			'default' => 'EUR'
		), 
	);
	
	$info['sql'] = array(								// SQL Tables
		array(
			'name' => "CatHistory",					// Name of Table
			'fields' => "
				id int(11) NOT NULL auto_increment,
				zeit text,
				group_id int(11) DEFAULT '0' NOT NULL,
				PRIMARY KEY (id)"
		),
		array(
			'name' => "CatHistoryItems",
			'fields' => "
				id int(11) NOT NULL auto_increment,
				group_id int(11) DEFAULT '0' NOT NULL,
				name text,
				size text,
				anzahl int(11),
				bestellung_id int(11),
				PRIMARY KEY (id)"
		),
		array(
			'name' => "CatOrder",
			'fields' => "
				id int(11) NOT NULL auto_increment,
				user_id int(11) DEFAULT '0' NOT NULL,
				angebot_id int(11) DEFAULT '0' NOT NULL,
				eingetroffen int(11) DEFAULT '0' NOT NULL,
				ausgeliefert int(11) DEFAULT '0' NOT NULL,
				anzahl int(11) DEFAULT '0' NOT NULL,
				bearbeitet int(11) DEFAULT '0' NOT NULL,
				time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				wagen int(11) DEFAULT '0' NOT NULL,
				PRIMARY KEY (id)"
		),
		array(
			'name' => "CatProduct",
			'fields' => "
				id int(11) NOT NULL auto_increment,
				name varchar(255) NOT NULL,
				beschreibung text NOT NULL,
				preis double(16,2) DEFAULT '0.00' NOT NULL,
				vorhanden int(11) DEFAULT '0' NOT NULL,
				lieferant int(11) DEFAULT '0' NOT NULL,
				size int(11) DEFAULT '0' NOT NULL,
				nummer int(11) DEFAULT '0' NOT NULL,
				PRIMARY KEY (id)"
		),
		array(
			'name' => "CatSupplier",
			'fields' => "
				id int(11) NOT NULL auto_increment,
				name varchar(255) NOT NULL,
				telefon varchar(255) NOT NULL,
				knr varchar(255) NOT NULL,
				PRIMARY KEY (id)"
		),
		array(
			'name' => "CatStats",
			'fields' => "
				id int(11) NOT NULL auto_increment,
				angebot_id int(11) DEFAULT '0' NOT NULL,
				anzahl_id int(11) DEFAULT '0' NOT NULL,
				anzahl int(11) DEFAULT '0' NOT NULL,
				PRIMARY KEY (id)"
		)
	);
	
?>