<?
	$LS_BASEPATH = '../';
	require $LS_BASEPATH.'../includes/ls_base.inc';
  require $LS_BASEPATH."../includes/tourney/base.inc";

	
  NavStruct('tournaments/teams/', array('tid' => $tourney));

	$tourney = new Tourney($tourney);

	StartPage($tourney->info['name'].': '._("Registration"), 1, $LS_BASEPATH.'images/tourney/icons/'.$tourney->info['icon']);

	if ($action == 'add') {
		if (isset($f_user)) {
			$adminuser = $f_user;
			$action = 'register';
		} else {
			echo '<form action='.$PHP_SELF.' method="post">';
			FormValue('action', $action);
			FormValue('tourney', $tourney->id);
		
			$userSearchSingle = TRUE;
			require $LS_BASEPATH.'../includes/user.search.inc';
				
			echo '<input type="submit" value="';
			echo ($ucount) ? _("Add") : _("Search");
			echo '">';
			echo '</form>';
		}
	}
	
	if ($action == 'register') {
		$title = (($tourney->TeamSize == 1) or ($tourney->eflags & TEF_BLINDDRAW)) ? _("Register") : _("Create Team");

		if ($adminuser) {
			$founder = $adminuser;

			if (count($tourney->Maps) <= 1 && count($tourney->TeamTypes) <= 1 && $tourne->info['options'] & TO_WWCL) {
				$f_rules = 1;
				$submitted=1;
			}
			$res = SQL_Query("SELECT clan, wwclclanid FROM user WHERE id=".$adminuser);
			$res = SQL_Query("SELECT clan, wwclclanid, name FROM user WHERE id=".$adminuser);
			if ($u = mysql_fetch_array($res)) {
				$nick = $u['name'];
				$clan = $u['clan'];
				$wwclclan = ($u['wwclclanid']) ? ('C'.$u['wwclclanid']) : '';
				if ($tourney->eflags & TEF_BLINDDRAW) {
					$submitted=1;
					$f_rules=1;
					$f_name=$nick;
				}

			} else {
				$nick = '';
				$clan = '';
				$wwclclan = '';
			}
		} else {
			$founder = $user_current['id'];
			$nick = $user_current['name'];
			$clan = $user_current['clan'];
			$wwclclan = ($user_current['wwclclanid']) ? ('C'.$user_current['wwclclanid']) : '';
		}

		if (!$tourney->UserCanRegister($founder)) {
			LS_Error(_("You cannot register for this tourney."));
		}

		if ($submitted) {
			if ($tourney->TeamSize > 1) {
				if (!$f_name)
					FormErrorEx('f_name', _("You have to specify a team name"));
				else {
					$res = SQL_Query("SELECT * FROM TourneyTeam WHERE tourney=".$tourney->id." AND name=".SQL_Quot($f_name));
					if ($row=mysql_fetch_array($res)) {
						FormErrorEx('f_name', sprintf(_("The specified team name %s is allready in use."), $f_name));
						$f_name .= ' (2)';
					}
				}
				if ($tourney->info['options'] & TO_WWCL && $f_wwclid && !ereg("^C([0-9]{4,})$", strtoupper($f_wwclid)))
					FormErrorEx('f_wwclid', _("You have to specify a valid WWCL Clan ID"));
			}
			if (!$f_rules)
				FormErrorEx('f_rules', _("You have to read and accept the rules"));
				
			if (!$FormErrorCount) {
				echo '<p class=content>';
				$fields = SQL_QueryFields(array(
					'tourney' => $tourney->id,
					'name' => $f_name,
					'DefMap' => $f_def_map,
					'DefTeam' => $f_def_ttype,
					'leader' => $founder,
					'wwclid' => substr($f_wwclid, 1)
					));
				SQL_Query("INSERT INTO TourneyTeam SET $fields");
				$newteamid = mysql_insert_id();
				SQL_Query("INSERT INTO TourneyTeamMember SET team=$newteamid,user=".$founder);
				
				if (($tourney->TeamSize > 1) and !($tourney->eflags & TEF_BLINDDRAW))
					printf(_("The team %s has been created."), $f_name);
				else
					printf(_("You have been registered."), $f_name);
				echo '</p>';
			}
		} else {
			if ($tourney->eflags & TEF_BLINDDRAW)
				{
				$f_name = $nick;
				$f_wwclid = '';
			}
			else {
				$f_name = $clan;
				$f_wwclid= $wwclclan;
			}
			if (count($tourney->Maps))
				$f_def_map = $tourney->Maps[0];
			if (count($tourney->TeamTypes))
				$f_def_ttype = $tourney->TeamTypes[0];
			
		}
		
		if (!$submitted || $FormErrorCount) {
			FormStart();
				FormValue('submitted', 1);
				FormValue('tourney', $tourney->id);
				FormValue('action', $action);
				if (isset($adminuser)) {
					FormValue('adminuser', $adminuser);
				}
				
				if ($tourney->TeamSize > 1) {
					if ($tourney->eflags & TEF_BLINDDRAW)
						FormValue('f_name', $f_name);
					else
						FormElement('f_name', _("Team Name"), $f_name);
					if ($tourney->info['options'] & TO_WWCL)
						FormElement('f_wwclid', _("WWCL Clan ID"), $f_wwclid, 'text', 'size=7 maxlength=8');
				}
				if (($size = count($tourney->Maps)) > 1) {
					if ($size > 10)
						$size = 10;
					FormSelectStart('f_def_map', _("Default Map"), $f_def_map, 'size='.$size);
					foreach ($tourney->Maps as $m)
						FormSelectItem($m);
					FormSelectEnd();
				}
				if (($size = count($tourney->TeamTypes)) > 1) {
					if ($size > 10)
						$size = 10;
					FormSelectStart('f_def_ttype', _("Default Team Type"), $f_def_ttype, 'size='.$size);
					foreach ($tourney->TeamTypes as $t)
						FormSelectItem($t);
					FormSelectEnd();
				}
				
				if ($adminuser)
					FormValue('f_rules', 1);
				else	
					FormElement('f_rules', _("I have read the rules and accepted them"), 1, 'checkbox', ($f_rules) ? 'checked' : '');
				
				FormElement('', '', $title, 'submit');
			FormEnd();
		}

	} elseif ($action == 'seed') {

		if ($submitted)
			{
				if (user_auth_ex(AUTH_TOURNEY, -1, 0, FALSE)) {
					$res = SQL_Query("SELECT id FROM TourneyTeam WHERE tourney=".$tourney->id);
					while ($row=mysql_fetch_array($res)) {
						$seedv="seed_".$row['id'];
						$seed=$$seedv;
						if (!$seed) $seed='NULL';
						SQL_Query("UPDATE TourneyTeam SET seed=$seed WHERE tourney=".$tourney->id." AND id=".$row['id']);
					}
					$submitted=false;
				}
				else
					LS_Error(_("You have insufficient rights."));
			}

		if (!$submitted || $FormErrorCount) {
			FormStart();
				FormValue('submitted', 1);
				FormValue('tourney', $tourney->id);
				FormValue('action', $action);

				$res = SQL_Query("SELECT id, name, wwclid, seed FROM TourneyTeam WHERE tourney=".$tourney->id." order by seed is null, seed");
				while ($row=mysql_fetch_array($res)) {
					FormElement('seed_'.$row['id'], $row['name'], $row['seed']);
				}


				FormElement('', '', _("Submit"), 'submit');
			FormEnd();
		}

	} elseif ($action == 'hand') {

		if ($submitted)
			{
				if (user_auth_ex(AUTH_TOURNEY, -1, 0, FALSE)) {
					$res = SQL_Query("SELECT tm.id, tm.team FROM TourneyTeam tt, TourneyTeamMember tm WHERE tt.id=tm.team and tt.tourney=".$tourney->id);
					while ($row=mysql_fetch_array($res)) {
						$handv="hand_".$row['id'];
						$hand=$$handv;
						if (!$hand) $hand='NULL';
						SQL_Query("UPDATE TourneyTeamMember SET handicap=$hand WHERE team=".$row['team']." AND id=".$row['id']);
					}
					$submitted=false;
				}
				else
					LS_Error(_("You have insufficient rights."));
			}

		if (!$submitted || $FormErrorCount) {
			FormStart();
				FormValue('submitted', 1);
				FormValue('tourney', $tourney->id);
				FormValue('action', $action);

				$res = SQL_Query("SELECT tm.id, u.name, tm.handicap FROM TourneyTeamMember tm, TourneyTeam tt, User u WHERE tt.id=tm.team AND tm.user=u.id AND tt.tourney=".$tourney->id." ORDER BY tm.handicap IS NULL, tm.handicap");
				while ($row=mysql_fetch_array($res)) {
					FormElement('hand_'.$row['id'], $row['name'], $row['handicap']);
				}


				FormElement('', '', _("Submit"), 'submit');
			FormEnd();
		}
		
	} elseif ($action == 'unregister') {
	
	}

	NavPrintBack();
?>