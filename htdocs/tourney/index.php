<?
	$LS_BASEPATH = '../';
	require $LS_BASEPATH.'../includes/ls_base.inc';
	require $LS_BASEPATH.'../includes/tourney/base.inc';
	
	StartPage(_("Tournament Overview"));
	
	if (user_auth_ex(AUTH_TOURNEY, -1, 0, FALSE)) {
		echo '<p class=content>';
		NavPrintAction("orga/", _("Tournament Administration"));
		echo '</p>';
	}
	
	$tourneyAdmin = user_auth_ex(ADMIN_TOURNEY, -1, 0, FALSE);
	
	echo '<p class=content>';
	$res = SQL_Query("SELECT 
			t.id,
			t.name,
			t.icon,
			t.status,
			t.MaxTeams,
			t.TeamSize,
			t.options,
			t.eflags,
			g.name as 'GrpName',
			g.note as 'GrpNote',
			g.type as 'GrpType',
			g.id as 'GrpID',
			COUNT(tt.id) as 'TeamCount'
		FROM  
			Tourney t
			LEFT JOIN TourneyGroup g ON t.grp=g.id
			LEFT JOIN TourneyTeam tt ON t.id=tt.tourney
		GROUP BY t.id
		ORDER BY g.name,name");
	
	$grpcount = 0;
	$prevgrp = -1;
	
	while ($row = mysql_fetch_array($res)) {
		$page = 'rules.php?id=%d';
		switch ($row['status']) {
			case TS_CLOSED:
				$s = _("Registration closed");
				break;
			case TS_REGISTRATION:
				$s = _("Registration open").':<br>'.sprintf(
					($row['eflags'] & TEF_BLINDDRAW) ? _("%d players for %d teams registered") :
					(($row['TeamSize'] == 1) ? _("%d of %d players registered") : _("%d of %d teams registered"))
					, $row['TeamCount'], $row['MaxTeams']);
				$page = 'teams.php?id=%d';
				break;
			case TS_POSTDRAW:
				$s = _("Post Draw");
				$page = 'teams.php?id=%d';
				break;
			case TS_PREMATCH:
				$s = _("Pre Match");
				$page = 'teams.php?id=%d';
				break;
			case TS_MATCH:
				$s = _("Matches are beeing played");
				$page = 'matches.php?id=%d';
				break;
			case TS_FINISHED:
				$s = _("Tournament finished");
				$page = 'ranking.php?id=%d';
				break;
			case TS_CANCELED:
				$s = _("Tournament canceled");
				break;
			default:
				$s = _("Unknown");
				break;
		}
		if ($row['GrpID'] != $prevgrp) {
			if ($grpcount)
				echo '</table></p>';
			
			echo '<h3 class=content>'.$row['GrpName'].'</h3>';
			echo '<p class=content>';
			if ($row['GrpNote']) {
				echo $row['GrpNote'].'<br>';
			}
			switch ($row['GrpType']) {
				case GRP_EXCLUSIVE:
					echo _("A player can only register for one tournament in this group.").'<br>';
					break;
				case GRP_ALLEXCLUSIVE:
					echo _("A player registered for a tournament in this group may not register for <b>any</b> other tournament.").'<br>';
					break;
			}
			echo '<br>';
			
			echo '<table>';
			echo '<tr>';
			echo '<th class=liste width=280>'._("Name").'</th>';
			echo '<th class=liste width=220>'._("Status").'</th>';
			echo '</tr>';
		}
		
		$page = sprintf($page, $row['id']);
		echo '<tr>';
		echo '<td class=Tourney>';
		echo '<a href="'.$page.'">';
		if ($row['icon'])
		  echo '<img width=32 height=32 border=0 src='.$LS_BASEPATH.'images/tourney/icons/'.$row['icon'].'> ';
		if ($row['options'] & TO_WWCL)
		  echo '<img width=43 height=14 border=0 src="'.$LS_BASEPATH.'images/tourney/wwcl.gif"> ';
		
		echo $row['name'];
		echo '</a>';
		echo '</td>';
		echo '<td class=Tourney>';
		echo $s;
		echo '</td>';
		echo '</tr>';
		
		$prevgrp = $row['GrpID'];
		$grpcount++;
	}
	if ($grpcount);
		echo '</table>';
	
	echo '</p>';
	
	EndPage();

?>