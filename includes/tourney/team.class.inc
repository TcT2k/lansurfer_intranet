<?
	class Team {
		var $tourneyID = 0;
		var $id;
		var $name;
		var $clan;
		var $member;
		var $memberCount;
		var	$leader;
	
		function Team($id, $teamsize = 2) {
			$this->member = array();
			if ($id == -1) {
				$this->name = _("Die");
			} elseif ($id == 0) {
				$this->name = '???';
			} else {
				$res = SQL_Query("SELECT * FROM TourneyTeam WHERE id=$id");
				if ($team=mysql_fetch_array($res)) {
					$this->tourneyID = $team['tourney'];
					$this->name = $team['name'];
					$this->leader = $team['leader'];
					$this->id = $team['id'];
					
					$res = SQL_Query("SELECT
							u.id as 'uid',
							tm.id as 'mid',
							u.name,
							u.clan
						FROM TourneyTeamMember tm
							LEFT JOIN user u ON u.id=tm.user
						WHERE tm.team=$id
						ORDER BY u.name, u.clan");
					while ($row = mysql_fetch_array($res)) {
						if ($teamsize == 1) {
							$this->name = $row['name'];
							$this->clan = $row['clan'];
						}
						$this->member[] = $row;
					}
					$this->memberCount = count($this->member);
					if (!$this->name && $this->memberCount == 1) {
						$this->name = $this->member[0]['name'];
						$this->clan = $this->member[0]['clan'];
					}
				}
			}
		}
		
		function UserIsMember($uid) {
			foreach($this->member as $member)
				if ($member['uid'] == $uid)
					return true;
			return false;
		}
		
		function UserCanLeaveTeam($uid) {
			return $this->UserIsMember($uid) && $user_current['id'] != $this->leader;
		}
		
	}
?>