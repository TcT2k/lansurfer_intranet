<?
	$LS_BASEPATH = '../../';
	require $LS_BASEPATH.'../includes/ls_base.inc';
  require $LS_BASEPATH."../includes/tourney/base.inc";

	
  NavStruct('tournaments/teams/', array('tid' => $tourney));

	$tourney = new Tourney($tourney);

	StartPage($tourney->info['name'].': '._("Registration"), 1, $LS_BASEPATH.'images/tourney/icons/'.$tourney->info['icon']);

	if ($action == 'add') {
		
		if ($submitted) {
			echo _("Adding Teams");
			$res = SQL_Query("SELECT * FROM user WHERE name like 'player%' ORDER BY name");
			for ($i = 0; $i < $f_count; $i++) {
				echo '.';
				flush();
				$user = mysql_fetch_array($res);
				
				SQL_Query("INSERT INTO TourneyTeam SET ".SQL_QueryFields(array(
					'tourney' => $tourney->id,
					'leader' => $user['id'],
					'name' => ($tourney->TeamSize > 1) ? sprintf('Team %d', $i + 1): ''
				)));
				SQL_Query("INSERT INTO TourneyTeamMember SET ".SQL_QueryFields(array(
					'team' => mysql_insert_id(),
					'user' => $user['id']
				)));
				
			}
				
		} else {
			FormStart();
				FormValue('tourney', $tourney->id);
				FormValue('action', $action);
				FormValue('submitted', 1);
				
				FormSelectStart('f_count', _("Players"), 16);
					FormSelectItem(8);
					FormSelectItem(16);
					FormSelectItem(32);
					FormSelectItem(64);
					FormSelectItem(128);
					FormSelectItem(256);
				FormSelectEnd();
				
				FormElement('', '', _("Add"), 'submit');
				
			FormEnd();
		}
		
	} elseif ($action == 'create') {
		
		for ($i = 1; $i <= 256; $i++)
		{
			SQL_Query("INSERT INTO user SET ".SQL_QueryFields(array(
				'name' => sprintf("Player %03d", $i),
				'email' => sprintf('player%03d@debug.com', $i)
				)).", pwd=PASSWORD('test')");
		}	
	}

	NavPrintBack();
?>