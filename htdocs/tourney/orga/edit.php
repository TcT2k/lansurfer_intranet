<?
	$LS_BASEPATH = '../../';
	require $LS_BASEPATH.'../includes/ls_base.inc';
  require $LS_BASEPATH."../includes/tourney/base.inc";
	
  NavStruct("tournaments/tourney_orga");

	StartPage(_("Edit Tournament"));
	
	user_auth_ex(AUTH_TOURNEY, -1, 0, TRUE);
	
	$acEdit = 'edit';
	$acRemove = 'remove';
	$acNew = 'create';
	$acNewDef = 'createdef';

  // Create Default Groups
	if ($action == $acNewDef) {
		include $LS_BASEPATH.'../includes/tourney/templates.inc';
		
		if ($submitted) {
			echo '<p class=content>';
			$f_start = FormDateTime($f_start);

			if ($f_preset) {
				foreach($f_preset as $key => $value) {
					$keys = explode('-', $key);
					
					$opt = TO_LOSER_SUBMIT | TO_STRICTTIMES;
					if ($TourneyPreSet[$keys[0]]['data'][$keys[1]]['-wwcl'])
						$opt |= TO_WWCL | TO_SKIP_DOUBLEFINAL;
					
					$fields = array(
						'MaxTeams' => 64,
						'DELimit' => 2048,
						'name' => _("Tournament"),
						'rules' => '',
						'icon' => '',
						'TeamSize' => 1,
						'grp' => $f_group,
						'MatchPause' => 10,
						'Games' => 2,
						'GameLength' => 10,
						'ScoreName' => _("Points"),
						'DrawHandling' => 0,
						'MatchSettings' => 0,
						'MapList' => '',
						'TeamType' => '',
						'ScoreType' => 0,
						'options' => $opt
					);
					
					foreach ($TourneyPreSet[$keys[0]]['data'][$keys[1]] as $fname => $fvalue) {
						if ($fname[0] != '-')
							$fields[$fname] = $fvalue;
					}
					//echo SQL_QueryFields($fields).'<br>';
					$sqlfields = SQL_QueryFields($fields);					
					SQL_Query("INSERT INTO Tourney SET StartTime=FROM_UNIXTIME($f_start),$sqlfields");
					printf(_("Tournament %s created...").'<br>', $TourneyPreSet[$keys[0]]['data'][$keys[1]]['name']);
				}
			}
			echo '</p>';
		} else {
  		$d = getdate();
  		$days = (5 - $day['wday']);
  		if ($days <= 0)
  			$days = 7 + $days;
  		
  		$f_start = mktime(18, 0, 0, $d['mon'], $d['mday'] + $days, $d['year']);
  		
			FormStart();
				FormValue('action', $action);
				FormValue('submitted', 1);

				FormElement('f_start', _("Tournament Start"), $f_start, 'datetime');
				FormSelectStart('f_group', _("Group"), $f_group, 'size=1');
					$cnt = 0;
					$res = SQL_Query("SELECT id,name FROM TourneyGroup ORDER BY name");
					while ($row = mysql_fetch_array($res)) {
						FormSelectItem($row['name'], $row['id']);
						$cnt++;
					}
				FormSelectEnd();
				
				foreach ($TourneyPreSet as $gkey => $grp)  {
					FormGroup(($grp['desc'] == '*default') ? _("Default Templates") : $grp['desc']);
					foreach ($grp['data'] as $key => $value)  {
						$s = $value['name'];
						if ($value['icon'])
							$s = '<img width=16 height=16 src="'.$LS_BASEPATH.'images/tourney/icons/'.$value['icon'].'"> '.$s;
						
						FormElement('f_preset['.$gkey.'-'.$key.']', $s, 1, 'checkbox');
					}
				}
				
				FormElement('', '', _("Create"), 'submit');
			FormEnd();
		}
	/*} elseif ($action != 'new' && !isset($id)) {
		FormStart();
			FormValue('action', $action);
			
			$res = SQL_Query("SELECT id,name FROM Tourney ORDER BY grp,name");
			FormSelectStart('id', _("Tournament"), 0, 'size=20');
				while ($row = mysql_fetch_array($res)) {
					FormSelectItem($row['name'], $row['id']);
				}
			FormSelectEnd();
			
			FormElement('', '', _("OK"), 'submit');
		FormEnd();*/
	} elseif ($action == $acEdit || $action == $acNew) {
		if ($submitted) {
			if (!$f_name)
				FormErrorEx('f_name', _("Name must not be empty"));
			if ($f_teamsize <= 0)
				FormErrorEx('f_teamsize', _("Team Size must be greater or equal to 1"));
			$f_start = FormDateTime($f_start);
			if ($f_start == -1)
				FormErrorEx('f_start_time', _("Start time is invalid"));
			if (!$f_scorename)
				FormErrorEx('f_scorename', _("Score name must not be empty"));
			
			$f_options = 0;
			if ($f_stricttime)
				$f_options |= TO_STRICTTIMES;
			if ($f_ignoregroup)
				$f_options |= TO_IGNOREGROUP;
			if ($f_score_games)
				$f_options |= TO_SCORE_COUNT_GAMES;
			if ($f_loser_submit)
				$f_options |= TO_LOSER_SUBMIT;
			if ($f_wwcltype)
				$f_options |= TO_WWCL;
			if (!$f_doublefinal)
				$f_options |= TO_SKIP_DOUBLEFINAL;
			
			if (!$FormErrorCount) {
				$fields = SQL_QueryFields(array(
					'MaxTeams' => $f_teams,
					'DELimit' => $f_de_limit,
					'name' => $f_name,
					'rules' => $f_rulefile,
					'icon' => $f_icon,
					'TeamSize' => $f_teamsize,
					'grp' => $f_group,
					'MatchPause' => $f_match_pause,
					'Games' => $f_game_count,
					'GameLength' => $f_game_length,
					'ScoreName' => $f_scorename,
					'DrawHandling' => $f_drawhandling,
					'MatchSettings' => $f_mapselection,
					'MapList' => $f_maplist,
					'TeamType' => $f_teamtypes,
					'ScoreType' => $f_score_type,
					'options' => $f_options,
					'WWCLType' => $f_wwcltype
					)).", StartTime=FROM_UNIXTIME($f_start)";
				if ($action == $acNew)
					SQL_Query("INSERT INTO Tourney SET $fields");
				else
					SQL_Query("UPDATE Tourney SET $fields WHERE id=$id");
				echo '<p class=content>';
				echo _("Settings saved.");
				echo '</p>';
			}
		} else {
			if ($action == $acEdit) {
				$res = SQL_Query("SELECT *, UNIX_TIMESTAMP(StartTime) as 'stime' FROM Tourney WHERE id=$id");
				$row = mysql_fetch_array($res);
				$f_teams = $row['MaxTeams'];
				$f_de_limit = $row['DELimit'];
				$f_name = $row['name'];

				$f_rulefile = $row['rules'];
				$f_icon = $row['icon'];
				$f_teamsize = $row['TeamSize'];
				$f_group = $row['grp'];
				$f_match_pause = $row['MatchPause'];
				$f_game_count = $row['Games'];
				$f_game_length = $row['GameLength'];
				$f_scorename = $row['ScoreName'];
				$f_drawhandling = $row['DrawHandling'];
				$f_mapselection = $row['MatchSettings'];
				$f_maplist = $row['MapList'];
				$f_teamtypes = $row['TeamType'];
				$f_score_type = $row['ScoreType'];
				$f_start = $row['stime'];
				$f_stricttime = $row['options'] & TO_STRICTTIMES;
				$f_ignoregroup = $row['options'] & TO_IGNOREGROUP;
				$f_score_games = $row['options'] & TO_SCORE_COUNT_GAMES;
				$f_loser_submit = $row['options'] &  TO_LOSER_SUBMIT;
				$f_doublefinal = !($row['options'] & TO_SKIP_DOUBLEFINAL);
				$f_wwcltype = ($row['options'] &  TO_WWCL) ? $row['WWCLType'] : 0;
			} else {
				$f_teams = 64;
				$f_de_limit = 2048;
				$f_teamsize = 1;
				$f_group = 1;
				$f_start = time();
				$f_match_pause = 10;
				$f_game_count = 2;
				$f_game_length = 10;
				$f_stricttime = 1;
				$f_score_type = SCORE_DEFAULT;
				$f_loser_submit = true;
				$f_wwcltype = 0;
				$f_doublefinal = true;
			}
		}
		$f_start_date = DateToStr($f_start);
		$f_start_time = TimeToStr($f_start);
		
		if (!$submitted || $FormErrorCount) {
			FormStart();
				FormValue('submitted', 1);
				FormValue('action', $action);
				if ('action' != $acNew)
					FormValue('id', $id);
				
				FormGroup(_("General Settings"));
			
				FormElement('f_name', _("Name"), $f_name);
				FormSelectStart('f_teams', _("Maximum Teams/Player"), $f_teams);
					FormSelectItem(8);
					FormSelectItem(16);
					FormSelectItem(32);
					FormSelectItem(64);
					FormSelectItem(128);
					FormSelectItem(256);
				FormSelectEnd();
				FormSelectStart('f_de_limit', _("Double Elimination Limit"), $f_de_limit, '', true);
					FormSelectItem(_("Complete Single Elimination"), 0);
/*					for ($i = 16; $i < 256; $i*=2)
						FormSelectItem(sprintf(_("Double Elimination in rounds with %d matches or less"), $i), $i);*/
					FormSelectItem(_("Complete Double Elimination"), 2048);
				FormSelectEnd();
				FormElement('f_doublefinal', _("Loser bracket winner must beat winner bracket winner two times"), 1, 'checkbox', ($f_doublefinal) ? 'checked' : '');
				
				FormElement('f_teamsize', _("Players per Team"), $f_teamsize, 'text', 'size=4');
				FormSelectStart("f_rulefile", _("Rules"), $f_rulefile);
					FormSelectItem(_("(No Rules)"), '');
					$dir = opendir('../rules/');
					while (($file = readdir($dir))!=false) {
						if ($file != "." && $file != "..")
							$rFiles[] = $file;
					}
					closedir($dir); 
					sort ($rFiles);
					foreach ($rFiles as $file)
 						FormSelectItem($file);
				FormSelectEnd();
				
				FormSelectStart('f_wwcltype', _("WWCL Tournament Type"), $f_wwcltype);
					FormSelectItem(_("No WWCL Tournament"), 0);
					FormSelectItem("Quake 3 Arena (1on1)", 1);
					FormSelectItem("Quake 3 Arena (TDM 4on4)", 2);
					FormSelectItem("Unreal Tournament (CTF 5on5)", 3);
					FormSelectItem("Unreal Tournament (TDMPro 4on4)", 4);
					FormSelectItem("Unreal Tournament (1on1)", 5);
					FormSelectItem("HalfLife (4on4)", 6);
					FormSelectItem("Counter-Strike (5on5)", 7);
					FormSelectItem("Team Fortress Classic (8on8)", 8);
					FormSelectItem("Broodwar (2on2)", 9);
					FormSelectItem("Broodwar (1on1)", 10);
					FormSelectItem("Need for Speed (1on1)", 11);
					FormSelectItem("Serious Sam (1on1)", 12);
					FormSelectItem("Fifa 2001 (1on1)", 13);
					FormSelectItem("Formel1 GP3 (1on1)", 14);
					FormSelectItem("Age of Empires 2 (1on1)", 15);
					FormSelectItem("Tribes 2 (12on12)", 16);
				FormSelectEnd();
				
				FormSelectStart("f_icon", _("Icon"), $f_icon);
					FormSelectItem(_("(Select icon)"), "");
					$dir = opendir("../../images/tourney/icons/");
					while (($file = readdir($dir))!=false) {
						if ($file != "." && $file != "..")
    					FormSelectItem($file, $file);
					}
					closedir($dir); 
				FormSelectEnd();

				FormSelectStart('f_group', _("Group"), $f_group, 'size=1');
					$cnt = 0;
					$res = SQL_Query("SELECT id,name FROM TourneyGroup ORDER BY name");
					while ($row = mysql_fetch_array($res)) {
						FormSelectItem($row['name'], $row['id']);
						$cnt++;
					}
				FormSelectEnd();
//				FormElement('f_group', _("Group"), $f_group, 'text', 'size=4');
				FormElement('f_ignoregroup', _("Ignore Group"), 1, 'checkbox', ($f_ignoregroup) ? 'checked' : '');
				
				FormGroup(_("Times (all Times in Minutes)"));
				FormElement('f_start', _("Tournament Start"), $f_start, 'datetime');
				FormElement("f_match_pause", _("Pause per Match"), $f_match_pause, "text", "size=2 maxlength=4");
				FormElement("f_game_count", _("Games"), $f_game_count, "text", "size=2 maxlength=4");
				FormElement("f_game_length", _("Game Length"), $f_game_length, "text", "size=2 maxlength=4");
				
				FormElement('f_stricttime', _("Draw winner automaticaly when no result is entered"), 1, 'checkbox', ($f_stricttime) ? 'checked' : '');
				
				FormGroup(_("Options and Settings"));
				FormElement('f_scorename', _("Score name (Frags, Wins, ...)"), $f_scorename);
				
				FormSelectStart("f_score_type", _("Score Type"), $f_score_type, '', true);
					FormSelectItem(_("Count result relative"), SCORE_RELATIVE);
					FormSelectItem(_("Count result absolute"), SCORE_ABSOLUTE);
					//FormSelectItem(_("CounterStrike CNC Scoring"), SCORE_CS_CNC);
				FormSelectEnd();
				FormElement('f_score_games', _("Determine win by won games"), 1, 'checkbox', ($f_score_games) ? 'checked' : '');
				FormElement('f_loser_submit', _("Loser has to submit the result"), 1, 'checkbox', ($f_loser_submit) ? 'checked' : '');
				
				FormSelectStart("f_drawhandling", _("Draw game handling"), $f_drawhandling, '', true);
					FormSelectItem(_("Single round with full time"), DRAW_SINGLE);
					FormSelectItem(_("Single round with half time"), DRAW_SINGLE_HALF_TIME);
					FormSelectItem(_("Two rounds with half time"), DRAW_DOUBLE_HALF_TIME);
				FormSelectEnd();
				FormValue('f_drawhandling', DRAW_SINGLE);

				/*FormSelectStart("f_mapselection", _("Match Settings"), $f_mapselection);
					FormSelectItem(_("Choosen by partipants"), TS_USER);
					FormSelectItem(_("Choosen by admin"), TS_ADMIN);
					FormSelectItem(_("Randomly choosen"), TS_RANDOM);
				FormSelectEnd();*/
				FormValue('f_mapselection', TS_USER);

				FormElement("f_maplist", _("Map list"), $f_maplist, "textarea", "rows=6 cols=20");
				FormElement("f_teamtypes", _("Team types<br>(e.g. Red and Blue or<br>Terrorist and Counter)<br>On entry per line"), $f_teamtypes, "textarea", "rows=3 cols=20");
				
				FormElement('', '', _("Save"), 'submit');
			
			FormEnd();
		}
		
	} elseif ($action == $acRemove) {
		
		if ($submitted) {
			$res = SQL_Query("SELECT id FROM TourneyTeam WHERE tourney=".$id);
			while ($row = mysql_fetch_array($res))
				SQL_Query("DELETE FROM TourneyTeamMember WHERE team=".$row['id']);
			SQL_Query("DELETE FROM TourneyTeam WHERE tourney=".$id);
			SQL_Query("DELETE FROM TourneyMatchResult WHERE tourney=".$id);
			SQL_Query("DELETE FROM TourneyMatchComment WHERE tourney=".$id);
			SQL_Query("DELETE FROM TourneyMatchFile WHERE tourney=".$id);
			SQL_Query("DELETE FROM TourneyMatch WHERE tourney=".$id);
			SQL_Query("DELETE FROM TourneyBracket WHERE tourney=".$id);
			SQL_Query("DELETE FROM Tourney WHERE id=".$id);
			
			echo '<p class=content>';
			echo _("Tournament has been removed.");
			echo '</p>';
		} else {
			echo '<p class=content>';
			echo _("Are you sure you want to remove this tournament?");
			echo '</p>';
			
			FormStart();
				FormValue('submitted', 1);
				FormValue('id', $id);
				FormValue('action', $action);
			
				FormElement('', '', _("Remove"), 'submit');
			FormEnd();
		}
	}
	
	NavPrintBack();
	EndPage();
?>