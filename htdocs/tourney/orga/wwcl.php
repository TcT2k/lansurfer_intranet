<?
	$LS_BASEPATH = '../../';
	require $LS_BASEPATH.'../includes/ls_base.inc';
  require $LS_BASEPATH."../includes/tourney/base.inc";
	
  //NavStruct("tournaments");
  
 	//NavAdd(_("Tournament Administration"), 'edit.php');
  
	//StartPage(_("WWCL Export"));
	user_auth();
	
	user_auth_ex(AUTH_TOURNEY, -1, 0, TRUE);
	
	//Header("Content-Type: text/plain");
	
	
	function GetWWCLId($id, $clan, $name, $email = '') {
		$prefix = ($clan) ? 'C' : 'P';
		if (!$id) {
			$where = ($clan) ? "name=".SQL_Quot($name) : "email=".SQL_Quot($email);
			$type = ($clan) ? 1 : 0;
			$res = SQL_Query("SELECT * FROM TourneyTempPlayer WHERE type=$type AND $where");
			$prefix = $prefix . 'T';
			if (($row = mysql_fetch_array($res)))
				$id = $row['id'];
			else
				SQL_Query("INSERT INTO TourneyTempPlayer SET ".SQL_QueryFields(array(
				'name' => $name,
				'email' => $email,
				'type' => $type
				)));
		}
		return $prefix.$id;
	}
	
	function ExportTournament($id) {
		
		
		$tourney = new Tourney($id);
		
		$idPrefix = ($tourney->TeamSize == 1) ? 'P' : 'C';
		
		$ini['INFO']['ExporterVersion'] = 'LANsurfer Intranet '.LS_INTRANET_VERSION.' http://www.lansurfer.com/';
		$ini['INFO']['LastEdit'] = date('m.d.Y G:i:s');
		$ini['INFO']['MaxPlayer'] = $tourney->StartTeams;
		$ini['INFO']['Modus'] = ($tourney->DoubleLimit) ? 1 : 0;
		$ini['INFO']['TID'] = $id;
		$ini['INFO']['GameID'] = ($tourney->info['WWCLType']) ? ($tourney->info['WWCLType'] - 1) : 0;
		$ini['INFO']['TurnierName'] = $tourney->info['name'];
		$ini['INFO']['PT'] = $idPrefix;
		$ini['INFO']['Closed'] = 1;
		$ini['INFO']['Uebergabe'] = 2;
		$ini['INFO']['AktRound'] = 0;
		$ini['INFO']['AktLRound'] = 0;

/*AktRound=3
AktLRound=5
LGrenze=0*/
		
		$res = SQL_Query("SELECT 
				tm.status,
				tm.row,
				tm.col,
				tm.op1,
				tm.op2,
				tm.flags,
				tt1.name as 'Team1Name',
				tt1.wwclid as 'Team1WWCLid',
				tt2.name as 'Team2Name',
				tt2.wwclid as 'Team2WWCLid',
				L1.name as 'Leader1Name',
				L1.wwclid as 'Leader1WWCLid',
				L1.email as 'Leader1Email',
				L2.name as 'Leader2Name',
				L2.wwclid as 'Leader2WWCLid',
				L2.email as 'Leader2Email'
			FROM 
				TourneyMatch tm
				LEFT JOIN TourneyTeam tt1 ON tt1.id=tm.op1
				LEFT JOIN user L1 ON tt1.leader=L1.id
				LEFT JOIN TourneyTeam tt2 ON tt2.id=tm.op2
				LEFT JOIN user L2 ON tt2.leader=L2.id
			WHERE tm.tourney=$id AND tm.status>=".MS_PLAYED."
			ORDER BY col");
		
		$players = array();
		
		while ($row = mysql_fetch_array($res)) {
			if ($row['col'] >= $tourney->WinnerRounds && $tourney->DoubleLimit)
				$colName = 'Looser'.($tourney->LoserRounds +1);
			else
				$colName = ($row['col'] < 0) ? 'Looser'.abs($row['col']) : 'Winner'.$row['col'];
			
			$ini[$colName]['closed'] = 1;
			if ($row['op1'] == -1) {
				$name = 'Freilos';
				$wid = 'F1';
			} else {
				$name = ($tourney->TeamSize == 1) ? $row['Leader1Name'] : $row['Team1Name'];
				//$wid = $idPrefix . (($tourney->TeamSize == 1) ? $row['Leader1WWCLid'] : $row['Team1WWCLid']);
				$wid = ($tourney->TeamSize == 1) ? 
					GetWWCLid($row['Leader1WWCLid'], false, $row['Leader1Name'], $row['Leader1Email']) : 
					GetWWCLid($row['Team1WWCLid'], true, $row['Team1Name'], $row['Leader1Email']);
				if ($row['col'] != 0 && !($row['flags'] & MF_OP2WON))
					$players[$row['op1']] += ($row['col'] > 0) ? 2 : 1;
			}
			$ini[$colName][$row['row'].'_m_P1Name'] = $name;
			$ini[$colName][$row['row'].'_m_P1ID'] = $wid;
			$ini[$colName][$row['row'].'_m_P1Ergebnis'] = ($row['flags'] & MF_OP2WON || $row['op1'] == -1) ? 0 : 1;
			$ini[$colName][$row['row'].'_m_P1HasLost'] = ($row['flags'] & MF_OP2WON || $row['op1'] == -1) ? 1 : 0;

			if ($row['op2'] == -1) {
				$name = 'Freilos';
				$wid = 'F1';
			} else {
				$name = ($tourney->TeamSize == 1) ? $row['Leader2Name'] : $row['Team2Name'];
//				$wid = $idPrefix . (($tourney->TeamSize == 1) ? $row['Leader2WWCLid'] : $row['Team2WWCLid']);
				$wid = ($tourney->TeamSize == 1) ? 
					GetWWCLid($row['Leader2WWCLid'], false, $row['Leader2Name'], $row['Leader2Email']) : 
					GetWWCLid($row['Team2WWCLid'], true, $row['Team2Name'], $row['Leader2Email']);
				if ($row['col'] != 0 && $row['flags'] & MF_OP2WON)
					$players[$row['op2']] += ($row['col'] > 0) ? 2 : 1;
			}
			$ini[$colName][$row['row'].'_m_P2Name'] = $name;
			$ini[$colName][$row['row'].'_m_P2ID'] = $wid;
			$ini[$colName][$row['row'].'_m_P2Ergebnis'] = ($row['flags'] & MF_OP2WON || $row['op1'] == -1) ? 1 : 0;
			$ini[$colName][$row['row'].'_m_P2HasLost'] = ($row['flags'] & MF_OP2WON || $row['op1'] == -1) ? 0 : 1;
			
			$ini[$colName][$row['row'].'_m_closed'] = 1;
			$ini[$colName][$row['row'].'_m_IsFinished'] = 1;

			if ($row['col'] >= $tourney->WinnerRounds && $tourney->DoubleLimit)
				$ini['Winner'.($tourney->WinnerRounds)] = $ini[$colName];
		}

		$res = SQL_Query("SELECT
				tb.team,
				tt.id as 'TeamID',
				tt.name as 'TeamName',
				tt.wwclid as 'TeamWWCLid',
				u.name as 'UserName',
				u.wwclid as 'UserWWCLid',
				u.email as 'UserEmail'
			FROM
			  TourneyBracket tb
				LEFT JOIN TourneyTeam tt ON tb.team=tt.id
				LEFT JOIN user u ON tt.leader=u.id
			WHERE tb.tourney=$id");

		$ini['Player']['count'] = mysql_num_rows($res);
		
		$dieCount = 0;
		
		global $Rankings;
		$Rankings = $tourney->GetRankings();
		//echo 'GetRankings() = '.count($Rankings)."\r\n";
		
		function GetRanking($tid) {
			global $Rankings;
			
			//echo 'RC: '.count($Rankings)."\r\n";
			
			foreach ($Rankings as $rank => $teams) {
				foreach ($teams as $team) {
					if ($team['id'] == $tid)
						return $rank;
				}
			}
			return 2048;
		}
		
		for ($i = 0; $row = mysql_fetch_array($res); $i++) {
			if ($row['team'] == -1) {
				$ini['Player'][$i.'_name'] = sprintf("Freilos %d", ++$dieCount);
				$ini['Player'][$i.'_id'] = sprintf("F%d", $dieCount);
				$ini['Player'][$i.'_punkte'] = 0;
			} else {
				$ini['Player'][$i.'_name'] = ($tourney->TeamSize == 1) ? $row['UserName'] : $row['TeamName'];
				//$ini['Player'][$i.'_id'] = $idPrefix . (($tourney->TeamSize == 1) ? $row['UserWWCLid'] : $row['TeamWWCLid']);
				$ini['Player'][$i.'_id'] = ($tourney->TeamSize == 1) ? 
						GetWWCLid($row['UserWWCLid'], false, $row['UserName'], $row['UserEmail']) : 
						GetWWCLid($row['TeamWWCLid'], true, $row['TeamName'], $row['TeamEmail']);
				
				$ini['Player'][$i.'_punkte'] = 2049 - GetRanking($row['TeamID']); //(isset($players[$row['TeamID']])) ? $players[$row['TeamID']] : 0;
			}
		}

		$s = '';
		foreach ($ini as $section => $values) {
			$s .= "[".$section."]\r\n";
			foreach ($values as $key => $value) {
				$s .= $key."=".$value."\r\n";
			}
			$s .= "\r\n";
		}
		echo $s;
	}

	$filename = ($action == 'unknowns') ? 'tmp_player.db' : $id.'.tdf';

	Header("Content-Type: application/octetstream");
	Header("Content-disposition: filename=$filename");

	if ($action != 'unknowns')
		ExportTournament($id);
	else {
		$res = SQL_Query("SELECT * FROM TourneyTempPlayer");
		while ($row = mysql_fetch_array($res)) {
			if ($row['type'] == 0)
				$prefix = 'P';
			else
				$prefix = 'C';
			echo $prefix.'T'.$row['id'].';';
			echo $row['name'].';';
			echo $row['email'].';';
			echo "\r\n";
		}
	}
	
	

	/*NavPrintBack();

	EndPage();*/
?>