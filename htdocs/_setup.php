<head>
	<title>Setup</title>
	<style>
	<!--
	BODY {
		FONT-FAMILY: Verdana, Helvetica;
		FONT-SIZE: 12px;
	}
	
	TH.liste {
		FONT-FAMILY: Verdana,Helvetica;
		FONT-SIZE: 12px;
		FONT-WEIGHT: normal;
    BACKGROUND-COLOR: #333333;
    COLOR: WHITE;
	}
	
	TD.liste {
		FONT-FAMILY: Verdana,Helvetica;
		FONT-SIZE: 12px;
		FONT-WEIGHT: normal;
	  BACKGROUND-COLOR: #555555;
	  COLOR: WHITE;
	}
	
	H2 {
		FONT-FAMILY: Verdana,Helvetica;
		FONT-SIZE: 24px;
		FONT-WEIGHT: bold;
	}
	-->
	</style>
</head>
<body>
<h2>LANsurfer Intranet Setup</h2>
<!-- <? if (false) {  ?> --> <? }  ?>
<b>Fatal Error:</b> PHP could not be detected.<br>Check your Webserver configuration.<br><br>
<b>Fataler Fehler:</b> PHP konnte nicht benutzt werden.<br>Webserver Konfiguration &uuml;berpr&uuml;fen.<!--
<? echo '-'.'-'.'>';  
	$LS_BASEPATH = "";
	require $LS_BASEPATH.'../includes/ls_base.inc';

	$step = 0;
	function PrintSetupStep($laction, $caption) {
		global $step, $action;
		if ($step) {
			echo ' | ';
		  $caption = $step.'. '.$caption;
		}
		if ($action == $laction)
			echo $caption;
		else {
			echo '<a href="'.$PHP_SELF.'?action='.$laction.'">'.$caption.'</a>';
		}

		$step++;
	}

	echo '<p class=content>';
	echo _("<b>Warning:</b> This file <i>_setup.php</i> must be deleted or moved out of the document root after the initial setup.");
	echo '</p>';
	PrintSetupStep('', _("Start"));
	PrintSetupStep('config', _("Configuration"));
	PrintSetupStep('database', _("Check/Create Database"));
	PrintSetupStep('import', _("Import Registration Data"));

	echo '<p class=content>';

	if (!$action) {
		echo '<p class=content><b>'._("Intranet Version").'</b>: '.LS_INTRANET_VERSION.'</p>';
		
		echo '<p class=content>';
		echo '<b>'._("PHP Configuration").'</b> (<a href="'.$PHP_SELF.'?action=phpinfo">'._("More...").'</a>)<br>';
		echo _("Magic Quotes").': <b>';
		echo (get_cfg_var("magic_quotes_gpc")) ? _("On") : _("Off");
		echo '</b><br>';
		echo _("Register Globals").': <b>';
		echo (get_cfg_var("register_globals")) ? _("On") : _("Off");
		echo '</b><br>';
		echo _("GNU Gettext Support").': ';
		if (!$GETTEXT_WORKAROUND) {
			echo '<b>'._("On").'</b>';
		} else {
			echo '<b>'._("Off").'</b> '._("Workaround enabled (only english language available)");
		}
		echo '<p class=content><a href="'.$PHP_SELF.'?action=config">'._("Next").' &gt;&gt;</a></p>';
	} elseif ($action == 'config') {
	
		$cfgParams = array(
			'LS_LANGUAGE' => array('caption' => _("Intranet Language"), 'default' => array(
				'de_DE' => _("German"),
				'en_EN' => _("English"),
				)), 
			'SQL_USER' => array('caption' => _("SQL user"), 'default' => 'root'), 
			'SQL_PASSWORD' => array('caption' => _("SQL user password"), 'default' => 'root', 'type' => 'password'), 
			'SQL_DB' => array('caption' => _("SQL database"), 'default' => 'db_intra2'), 
			'SQL_HOST' => array('caption' => _("SQL Server"), 'default' => 'localhost'),
			'LS_IP_PUBLIC' => array('caption' => _("Show IPs on public guest list"), 'default' => false, 'type' => 'bool'), 
			'LS_IP_WARN' => array('caption' => _("Warn user if wrong IP is set"), 'default' => false, 'type' => 'bool'), 
		
			'LS_CATERING' => array('caption' => _("Show catering in navigation"), 'default' => true, 'type' => 'bool'), 
			'LS_CATERING_CURRENCY' => array('caption' => _("Currency to use in catering"), 'default' => 'EUR'), 
		);
		
		
		echo '<b>'._("Configuration").':</b>';
		
		if ($submitted) {
			echo '<hr>';
			$fp = fopen($LS_BASEPATH.'../includes/ls_conf.inc', 'w');
			if ($fp) {
				fwrite($fp, "<?\ndefine('LS_CONFIGURED', TRUE);");
				
				foreach ($cfgParams as $name => $cfg) {
					$newValue = $newCfg[$name];
					
					if ($cfg['type'] == 'bool') {
						$value = ($newValue) ? 'TRUE' : 'FALSE';
					} else
						$value = "'".$newValue."'";
					fwrite($fp, "define('".$name."', ".$value.");\n");
				}
				fwrite($fp, "?>\n");
				fclose($fp);
				echo _("Configuration Saved.");
			} else {
				echo '<b>'._("Error").':</b> '.sprintf(_("Configuration file could not be saved. Check file permissions on %s."), $LS_BASEPATH.'../includes/ls_conf.inc');
			}
			echo '<hr>';
		}
		
		echo '<form action="'.$PHP_SELF.'" method="post"><table>';
		echo '<input type=hidden name=action value='.$action.'>';
		echo '<input type=hidden name=submitted value=1>';
		foreach ($cfgParams as $name => $cfg) {
			$caption = ($cfg['caption']) ? $cfg['caption'] : $name;
			
			switch ($cfg['type']) {
				case 'bool':
					$value = 1;
					$type = 'checkbox';
					$params = (constant($name)) ? ' checked' : '';
					break;
				case 'password':
					$value = constant($name);
					$params = '';
					$type = 'password';
					break;
				default:
					$value = constant($name);
					$params = '';
					$type = 'text';
					break;
			}
			
			echo '<tr class=liste>';
			echo '<th class=liste width=200 align=left>'.$caption.'</th>';
			echo '<td class=liste width=100>';
			if (is_array($cfg['default'])) {
				echo '<select name=newCfg['.$name.']>';
				foreach($cfg['default'] as $defvalue => $caption) {
					echo '<option value="'.$defvalue.'"';
					if ($defvalue == $value)
						echo ' selected';
					echo '>'.$caption.'</option>';
				}
				echo '</selct>';
			} else
				echo '<input type='.$type.' name="newCfg['.$name.']" value="'.$value.'"'.$params.'>';
			echo '</td>';
			echo '</tr>';
		}
		echo '<tr class=liste>';
		echo '<th class=liste colspan=2 align=right><input type="submit" value="'._("Save").'"></th>';
		echo '</tr>';
		echo '</table></form>';
		
		echo '<p class=content><a href="'.$PHP_SELF.'">&lt;&lt;'._("Back").'</a> | <a href="'.$PHP_SELF.'?action=database">'._("Next").' &gt;&gt;</a></p>';
	} elseif ($action == 'database') {
?>
</p>
<p class=content>
<b><? echo _("Check/Create Database"); ?>:</b><br>
<ul>
<?  
	SQL_Init(false);
  echo '<li>'._("Search for databases...");
  $res = SQL_Query("show databases");
  while ($row = mysql_fetch_row($res)) {
  	$databases[$row[0]] = TRUE;
  }
  echo _("OK").'</li><br><li>';
  if ($databases[SQL_DB])
  	printf(_("Database %s exists."), SQL_DB);
  else {
  	printf(_("Creating database %s ... "), SQL_DB);
  	SQL_Query("CREATE DATABASE ".SQL_DB);
  	echo _("OK");
  }
  echo '</li>';

	mysql_select_db(SQL_DB);
	
  echo '<li>'.sprintf(_("Searching for existing tables in database %s"), SQL_DB).' ';
  flush();
  $res = mysql_list_tables(SQL_DB);
  for ($i = 0; $i < mysql_num_rows ($res); $i++) {
  	$tables[] = mysql_tablename ($res, $i);
  	echo '.';
  }
  echo "OK</li><br>";
  flush();
?>
<li><? echo _("Checking existing tables and creating required tables"); ?>:<br>
<table>
	<tr>
		<th class=liste width=200><? echo _("Table"); ?></th>
		<th class=liste width=200><? echo _("Status"); ?></th>
	</tr>
<?

   function CreateTable($table, $fields) {
   		global $tables;

 			$exists = false;
 			
 			if (is_array($tables)) {
	 			reset($tables);
	 			
	 			while (!$exists && list(, $etable) = each($tables))
	 				$exists = strcasecmp($etable, $table) == 0;
	 		}
	 		
   		echo "<tr>";
   		printf("<td class=liste>%s</td>", $table);
 			echo '<td class=liste>';
 			if (!$exists) {
	   		SQL_Query("CREATE TABLE $table ( $fields )");
 				echo _("Created");
 			} else {	// Table Struktur prüfen
 				echo _("Exists").'<br>';
				// Felder des Tables hohlen
				$res = SQL_Query("describe $table");
				while ($row=mysql_fetch_array($res))
				{
					$currentFields[$row['Field']] = 1;
				}
	
	   		$reqfields = split(",\n", $fields);
	   		$cFields = array();
	   		
	   		foreach($reqfields as $rf) {
	   			$fieldType = trim($rf);
	   			$p = strpos($fieldType, " ");
	   			$rf = substr($fieldType, 0, $p);
	   			if (strcasecmp ($rf, 'PRIMARY') != 0 && strcasecmp ($rf, 'KEY')) {
		   			$cFields[] = $rf;
		   			if (!$currentFields[$rf]) {
		   				SQL_Query("ALTER TABLE $table ADD ".$fieldType);
		   				printf(_("Field %s added").'<br>', $rf);
		   			}
		   		}
	   		}
	   	}
   		echo "</td></tr>";
   		
   		flush();
   		
   		return $exists;
   }

// General Tables

CreateTable("forum_posting", "
  id int(11) DEFAULT '0' NOT NULL auto_increment,
  date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
  text text,
  ls_id int(11) DEFAULT '0',
  name varchar(100) DEFAULT '0',
  email varchar(200) DEFAULT '0',
  topic int(11) DEFAULT '0' NOT NULL,
  flags int(11) DEFAULT '0',
  PRIMARY KEY (id),
  KEY date_index (date)
");

CreateTable("forum_topic", "
  id int(11) DEFAULT '0' NOT NULL auto_increment,
  forum int(11) DEFAULT '0' NOT NULL,
  title varchar(255) DEFAULT '',
  pcount int(11) DEFAULT '0',
  PRIMARY KEY (id)
");

CreateTable("guest", "
  id int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
  user int(11),
  flags int(11),
  PRIMARY KEY (id)
");

CreateTable("news", "
  id int(11) DEFAULT '0' NOT NULL auto_increment,
  author int(11) DEFAULT '0' NOT NULL,
  title varchar(255) DEFAULT '' NOT NULL,
  msg text,
  date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
  options int(11) DEFAULT '0' NOT NULL,
  PRIMARY KEY (id)
");

CreateTable("orga", "
  id int(11) DEFAULT '0' NOT NULL auto_increment,
  user int(11) DEFAULT '0' NOT NULL,
  rights int(11) DEFAULT '0' NOT NULL,
  date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
  PRIMARY KEY (id)
");

CreateTable("seat_block", "
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
");

CreateTable("seat_sep", "
  id int(11) DEFAULT '0' NOT NULL auto_increment,
  block int(11) DEFAULT '0',
  orientation int(11) DEFAULT '0',
  value int(11) DEFAULT '0',
  PRIMARY KEY (id)
");

CreateTable("seats", "
  id int(11) DEFAULT '0' NOT NULL auto_increment,
  block int(11) DEFAULT '0' NOT NULL,
  col int(11) DEFAULT '0' NOT NULL,
  row int(11) DEFAULT '0' NOT NULL,
  status int(11) DEFAULT '0' NOT NULL,
  guest int(11) DEFAULT '0',
  PRIMARY KEY (id)
");

CreateTable("user", "
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
");

// Catering Tables

CreateTable("CatHistory", "
   id int(11) NOT NULL auto_increment,
   zeit text,
   group_id int(11) DEFAULT '0' NOT NULL,
   PRIMARY KEY (id)
");

CreateTable("CatHistoryItems", "
   id int(11) NOT NULL auto_increment,
   group_id int(11) DEFAULT '0' NOT NULL,
   name text,
   size text,
   anzahl int(11),
   bestellung_id int(11),
   PRIMARY KEY (id)
");

CreateTable("CatOrder", "
   id int(11) NOT NULL auto_increment,
   user_id int(11) DEFAULT '0' NOT NULL,
   angebot_id int(11) DEFAULT '0' NOT NULL,
   eingetroffen int(11) DEFAULT '0' NOT NULL,
   ausgeliefert int(11) DEFAULT '0' NOT NULL,
   anzahl int(11) DEFAULT '0' NOT NULL,
   bearbeitet int(11) DEFAULT '0' NOT NULL,
   time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
   wagen int(11) DEFAULT '0' NOT NULL,
   PRIMARY KEY (id)
");

CreateTable("CatProduct", "
   id int(11) NOT NULL auto_increment,
   name varchar(255) NOT NULL,
   beschreibung text NOT NULL,
   preis double(16,2) DEFAULT '0.00' NOT NULL,
   vorhanden int(11) DEFAULT '0' NOT NULL,
   lieferant int(11) DEFAULT '0' NOT NULL,
   size int(11) DEFAULT '0' NOT NULL,
   nummer int(11) DEFAULT '0' NOT NULL,
   PRIMARY KEY (id)
");

CreateTable("CatSupplier", "
   id int(11) NOT NULL auto_increment,
   name varchar(255) NOT NULL,
   telefon varchar(255) NOT NULL,
   knr varchar(255) NOT NULL,
   PRIMARY KEY (id)
");

CreateTable("CatStats", "
   id int(11) NOT NULL auto_increment,
   angebot_id int(11) DEFAULT '0' NOT NULL,
   anzahl_id int(11) DEFAULT '0' NOT NULL,
   anzahl int(11) DEFAULT '0' NOT NULL,
   PRIMARY KEY (id)
");

// Tourney Tables

CreateTable("Tourney", "
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
");

CreateTable("TourneyTempPlayer", "
  id int(11) NOT NULL auto_increment,
  type tinyint(3) unsigned NOT NULL default '0',
  name char(100) NOT NULL default '',
  email char(100) NOT NULL default '',
  PRIMARY KEY  (id)
");

CreateTable("TourneyBracket", "
   id int(11) NOT NULL auto_increment,
   tourney int(11) DEFAULT '0' NOT NULL,
   team int(11) DEFAULT '0' NOT NULL,
   position int(11) DEFAULT '0' NOT NULL,
   options int(11) DEFAULT '0' NOT NULL,
   phase int(11) DEFAULT '0' NOT NULL,
   PRIMARY KEY (id)
");

CreateTable("TourneyGroup", "
   id int(11) NOT NULL auto_increment,
   name varchar(255) NOT NULL,
   type int(11) DEFAULT '0' NOT NULL,
   note varchar(255) NOT NULL,
   PRIMARY KEY (id)
");

CreateTable("TourneyMatch", "
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
");

CreateTable("TourneyMatchResult", "
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
");

CreateTable("TourneyTeam", "
	id int(10) unsigned NOT NULL auto_increment,
	tourney int(10) unsigned DEFAULT '0' NOT NULL,
	leader int(10) unsigned DEFAULT '0' NOT NULL,
	name varchar(255) NOT NULL,
	DefMap varchar(255) NOT NULL,
	DefTeam varchar(255) NOT NULL,
	type smallint(5) unsigned DEFAULT '0' NOT NULL,
	wwclid int(10) unsigned NOT NULL default '0',
	PRIMARY KEY (id)
");

CreateTable("TourneyTeamMember", "
   id int(10) unsigned NOT NULL auto_increment,
   team int(10) unsigned DEFAULT '0' NOT NULL,
   user int(10) unsigned DEFAULT '0' NOT NULL,
   PRIMARY KEY (id)
");



?>
	</table>
</ul>
</p>
<?
		echo '<p class=content><a href="'.$PHP_SELF.'?action=config">&lt;&lt;'._("Back").'</a> | <a href="'.$PHP_SELF.'?action=import">'._("Next").' &gt;&gt;</a></p>';
	} elseif ($action == 'import') {
?>
<p class=content>
<b><? echo _("Import Registration Data") ?>:</b><br>
<?

	function split_sql($sql) {
		$ret = array();
		
		$in_comment = false;
		$in_string = false;
		$queryStart = 0;
		
		for ($i=0; $i < strlen($sql); $i++) {
			// begin of comment
			if (!$in_comment && !$in_string && $sql[$i] == '#') {
				$in_comment = true;
				continue;
			}
			// end of comment
			if ($in_comment && $sql[$i] == "\n") {
				$queryStart = $i;
				$in_comment = false;
			}
			// begin of string
			if (!$in_comment && !$in_string && $sql[$i] == "'") {
				$in_string = true;
				continue;
			}
			// during string
			if ($in_string) {
				if ($sql[$i] == "\\") {
					$i++;
					continue;
				} elseif ($sql[$i] == "'")
					$in_string = false;
			}
			
			// End of query
			if (!$in_comment && !$in_string && $sql[$i] == ';') {
				$ret[] = trim(substr($sql, $queryStart, $i - $queryStart));
				$queryStart = $i + 1;
			}
			
		}
		
		return $ret;
	}

	if (isset($action) && $submitted) {
		echo _("Importing data...").'<br>';
		flush();
		@set_time_limit(0);
		
		
		if(!empty($sql_file) && $sql_file != "none")
		{
		    $sql_query = fread(fopen($sql_file, "r"), filesize($sql_file));
		}
		
		$pieces  = split_sql($sql_query);
		
		$cnt = count($pieces);
		for ($i=0; $i<$cnt; $i++)
	  {
			$q = $pieces[$i];
			if ($q)
	    	$result = SQL_Query($q);
	  }
	  echo _("Data imported.");
		
	}
?>
<form method="post" action="<? echo $PHP_SELF; ?>" enctype="multipart/form-data">
<input type="hidden" name="action" value="import">
<input type="hidden" name="submitted" value="1">
<? echo _("SQL File"); ?>:<br>
<input type="file" name="sql_file"><br>
<input type="submit" name="SQL" value="<? echo _("Import"); ?>">
</form>

<?
	
		echo '<p class=content><a href="'.$PHP_SELF.'?action=database">&lt;&lt;'._("Back").'</a></p>';
	} elseif ($action == 'phpinfo') {
		phpinfo();
		echo '<p class=content><a href='.$PHP_SELF.'>'._("Back").'</a></p>';
	}
	die;
?>
-->
