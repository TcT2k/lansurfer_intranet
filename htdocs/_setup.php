<head>
	<title>Setup</title>
	<link rel="StyleSheet" href="intra.css">
</head>
<body>
<h3 class=content>LANsurfer Intranet Setup</h3>
<!-- <? if (false) {  ?> --> <? }  ?>
<p class=content>
<b>Fatal Error:</b> PHP could not be detected.<br>Check your Webserver configuration.<br><br>
<b>Fataler Fehler:</b> PHP konnte nicht benutzt werden.<br>Webserver Konfiguration &uuml;berpr&uuml;fen.</p><!--
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
	echo '<p class=content>';
	PrintSetupStep('', _("Start"));
	PrintSetupStep('modules', _("Module Selection"));
	PrintSetupStep('config', _("Configuration"));
	PrintSetupStep('database', _("Check/Create Database"));
	PrintSetupStep('import', _("Import Registration Data"));
	echo '</p>';

	function LoadModule($s) {
		
		include $s.'/info.inc';
		if (file_exists($s.'/setup.inc'))
			include $s.'/setup.inc';
		
		return $info;
	}

	$d = dir('../modules');
	while ($s = $d->read()) {
		$path = ($d->path.'/'.$s);
		$info = $path .'/info.inc';
		if ($s[0] != '.' && is_dir($path) && file_exists($info)) {
			$mods[$s] = LoadModule($path);
			
			$LSModules[$s] = true;
		}
	}
	$d->close();
	ksort($mods);

	echo '<p class=content>';

	if (!$action) {
		echo '<p class=content><b>'._("Intranet Version").'</b>: '.LS_INTRANET_VERSION;
		echo '<br><b>'._("Intranet Build Date").'</b>: '.LS_INTRANET_BUILD_DATE;
		echo '</p>';

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
		echo '<p class=content><a href="'.$PHP_SELF.'?action=modules">'._("Next").' &gt;&gt;</a></p>';
	} elseif ($action == 'phpinfo') {
		phpinfo();
		echo '<p class=content><a href='.$PHP_SELF.'>'._("Back").'</a></p>';
	} elseif ($action == 'modules') {

		echo '<b>'._("Module Selection").':</b><br>';

		if ($submitted) {
			echo '<hr>';
			echo '<p class=content>';

			$LoadMods['_base'] = true;
			
			$fp = fopen($LS_BASEPATH.'../conf/modules.inc', 'w');
			if ($fp) {
				fwrite($fp, "<?\r\n");

				foreach ($LSModules as $key => $value) {
					fwrite($fp, "  \$LSModules['".$key."'] = ");
					fwrite($fp, (isset($LoadMods[$key])) ? 'true' : 'false');
					fwrite($fp, ";\r\n");
				}
				
				fwrite($fp, "?>");
				fclose($fp);
				echo _("Module selection saved.");
			} else {
				echo '<b>'._("Error").':</b> '.sprintf(_("Module selection could not be saved. Check file permissions on %s."), $LS_BASEPATH.'../conf/modules.inc');
			}
			include $LS_BASEPATH.'../conf/modules.inc';

			echo '</p>';
			echo '<hr>';
		} else {
			if (file_exists($LS_BASEPATH.'../conf/modules.inc'))
				include $LS_BASEPATH.'../conf/modules.inc';
		}
		
		echo '<form action="'.$PHP_SELF.'" method="post">';
		FormValue('action', $action);
		FormValue('submitted', 1);
		
		echo '<p class=content>';

		echo '<table class=liste>';

		echo '<tr>';
		echo '<th class=liste width=200>'._("Name").'</th>';
		echo '<th class=liste width=80>'._("Version").'</th>';
		echo '<th class=liste width=120>'._("Build Date").'</th>';
		echo '<th class=liste width=180>'._("Vendor").'</th>';
		echo '</tr>';
		
		foreach ($mods as $key => $mod) {
			echo '<tr>';
			
			echo '<td class=liste>';
			if ($key != '_base') {
				echo '<input name="LoadMods['.$key.']" value=1 type=checkbox ';
				if ($LSModules[$key])
					echo ' checked';
				echo '> ';
			} else {
				echo '<input type=checkbox checked disabled> ';
			}

			echo $mod['displayname'];
			echo '</td>';

			echo '<td class=liste>';
			echo (!$mod['version']) ? _("N/A") : $mod['version'];
			echo '</td>';

			echo '<td class=liste>';
			echo (!$mod['builddate']) ? _("N/A") : $mod['builddate'];
			echo '</td>';

			echo '<td class=liste>';
			if ($mod['vendorURL'])
				echo '<a target=_new href="'.$mod['vendorURL'].'">'.$mod['vendor'].'</a>';
			else
				echo $mod['vendor'];
			echo '</td>';
			echo '</tr>';
		}
		echo '</table>';
		echo '<input type=submit class=form_btn value='._("Save").'>';
		echo '</p>';

		echo '</form>';

		echo '<p class=content><a href="'.$PHP_SELF.'">&lt;&lt;'._("Back").'</a> | <a href="'.$PHP_SELF.'?action=config">'._("Next").' &gt;&gt;</a></p>';
	} elseif (!file_exists($LS_BASEPATH.'../conf/modules.inc')) {
		echo _("No module selection saved.");
		echo '<br>';
		echo '<a href="'.$PHP_SELF.'?action=modules">'._("Module Selection").'</a>';
		echo '</p>';
	} elseif ($action == 'config') {
		include $LS_BASEPATH.'../conf/modules.inc';
		
		$cfgParams = array();
		foreach ($mods as $key => $mod) {
			if ($LSModules[$key] && isset($mod['setup']))
				$cfgParams = array_merge($cfgParams, $mod['setup']);
		}
		
		echo '<b>'._("Configuration").':</b>';
		
		if ($submitted) {
			$fwerror = false;
			echo '<hr>';
			echo '<p class=content>';
			$fp = fopen($LS_BASEPATH.'../conf/base.inc', 'w');
			if ($fp) {
				fwrite($fp, "<?\r\n  define('LS_CONFIGURED', TRUE);\r\n");
				
				foreach ($cfgParams as $name => $cfg) {
					$newValue = $newCfg[$name];
					
					if ($cfg['type'] == 'bool') {
						$value = ($newValue) ? 'TRUE' : 'FALSE';
					} else {
						$escChars = "\0..\37!@\177..\377";
						if (!get_cfg_var("magic_quotes_gpc"))
							$escChars .= '"';
						$value = '"'.addcslashes($newValue, $escChars).'"';
					}
					fwrite($fp, "  define('".$name."', ".$value.");");
					if ($cfg['caption']) {
						fwrite($fp, " // ".$cfg['caption']);
					}
					fwrite($fp, "\r\n");
				}
				fwrite($fp, "?>");
				fclose($fp);
				echo _("Configuration Saved.");
			} else {
				echo '<b>'._("Error").':</b> '.sprintf(_("Configuration file could not be saved. Check file permissions on %s."), $LS_BASEPATH.'../conf/base.inc');
				$fwerror = true;
			}
			echo '</p>';
			echo '<hr>';
		}
		
		if (!$submitted || $fwerror) {
			FormStart();
				FormValue('action', $action);
				FormValue('submitted', 1);
	
				foreach ($cfgParams as $name => $cfg) {
					$caption = ($cfg['caption']) ? $cfg['caption'] : $name;
					
					switch ($cfg['type']) {
						case 'bool':
							$value = 1;
							$type = 'checkbox';
							if (!defined($name))
								$params = ($cfg['default']) ? ' checked' : '';
							else
								$params = (constant($name)) ? ' checked' : '';
								
							break;
						case 'password':
							$value = (!defined($name)) ? $cfg['default'] : constant($name);
							$params = '';
							$type = 'password';
							break;
						case 'textarea':
							$value = (!defined($name)) ? $cfg['default'] : constant($name);
							$params = ' cols=40 rows=8';
							$type = 'textarea';
							break;
						default:
							$value = (!defined($name)) ? $cfg['default'] : constant($name);
							$params = '';
							$type = 'text';
							break;
					}
					if (is_array($cfg['default'])) {
						FormSelectStart('newCfg['.$name.']', $caption, $value);
							foreach($cfg['default'] as $defvalue => $caption)
								FormSelectItem($caption, $defvalue);
						FormSelectEnd();
					} else
						FormElement('newCfg['.$name.']', $caption, $value, $type, $params);
				}
			
				FormElement('', '', _("Save"), 'submit');
			FormEnd();
		}
		
		echo '<p class=content><a href="'.$PHP_SELF.'?action=modules">&lt;&lt;'._("Back").'</a> | <a href="'.$PHP_SELF.'?action=database">'._("Next").' &gt;&gt;</a></p>';
	} elseif (defined('LS_CONFIGURED') && !constant('LS_CONFIGURED')) {
		echo _("No configuration saved.");
		echo '<br>';
		echo '<a href="'.$PHP_SELF.'?action=config">'._("Configuration").'</a>';
		echo '</p>';
	} elseif ($action == 'database') {
		include $LS_BASEPATH.'../conf/modules.inc';
?>
</p>
<p class=content>
<b><? echo _("Check/Create Database"); ?>:</b><br>
<ul class=content>
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

	foreach ($mods as $key => $mod) {
		if ($LSModules[$key] && $mod['sql']) {
			foreach ($mod['sql'] as $table) {
				if (!CreateTable($table['name'], $table['fields']) && $table['firsttimequery'])
					SQL_Query($table['firsttimequery']);
			}
		}
	}

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
		
		FormStart();
			FormValue('action', 'import');
			FormValue('submitted', 1);
			FormElement('sql_file', _("SQL File"), '', 'file');
			FormElement('', '', _("Import"), 'submit');
		FormEnd();
		echo '<p class=content><a href="'.$PHP_SELF.'?action=database">&lt;&lt;'._("Back").'</a></p>';
	}
	
	die;
?>
-->
