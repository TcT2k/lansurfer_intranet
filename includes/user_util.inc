<?
// User authentifizierung
	$user_valid = FALSE;
	$user_current = FALSE;
	

	function user_auth() {
		global $user_current, $user_valid, $HTTP_COOKIE_VARS, $ls_id, $ls_passwd;

		$user_valid = FALSE;

		if ($ls_id && $ls_passwd) {
			$res = SQL_Query("SELECT * FROM user 
				WHERE (id=".$ls_id.") AND (pwd='".$ls_passwd."')");
			$user_current = mysql_fetch_array($res);
			if ($user_current)
				$user_valid = TRUE;
		} else {
			$user_valid = FALSE;
		}
		
/*		if ($HTTP_COOKIE_VARS['ls_id'] && $HTTP_COOKIE_VARS['ls_pw']) {
			$res = SQL_Query("SELECT * FROM user 
				WHERE (id=".$HTTP_COOKIE_VARS['ls_id'].") AND 
					(password='".addslashes($HTTP_COOKIE_VARS['ls_pw'])."')");
			$user_current = mysql_fetch_array($res);
			if ($user_current)
				$user_valid = TRUE;
		} else {
			$user_valid = FALSE;
		}
*/		
	}
	
	function user_auth_ex($type, $param = 0, $rights = 0, $die = TRUE) {
		global $user_valid, $user_current;
	
		switch ($type) {
			case AUTH_TEAM: {
			
				if ($user_valid) {
				
					$res = SQL_Query("SELECT * FROM orga WHERE (user=".$user_current['id'].")");
					$row = mysql_fetch_array($res);

					if ($row) {
						if ($rights) {
							if (!($row[rights] & $rights)) {
								$right_checked = FALSE;
							} else 
								$right_checked = TRUE;
						} else {
							$right_checked = TRUE;
						}
					}
				}
				
				if (!$user_valid || !$row || !$right_checked) {
					if ($die)
						LS_Error("Du musst Mitglied dieses OrgaTeams sein und die entsprechenden Rechte besitzen.");
					else
						return FALSE;
				}
				return TRUE;
				
			}
			case AUTH_TOURNEY: {
				return user_auth_ex(AUTH_TEAM, 0, TEAM_TOURNEY, $die);
				// TODO: Noch richtiger check
				// return TRUE;
			}
			case AUTH_LS_INTERN: {
				if ($user_valid && $user_current[id] == 1)
					return TRUE;
				else
				  return FALSE;
			}
			case AUTH_GUEST: {
				if ($user_valid) {
					$res = SQL_Query("SELECT * FROM guest WHERE party=$param AND user=".$user_current[id]);
					$Guest = mysql_fetch_array($res);
				}
				if ($Guest)
					return $Guest;
				else
				  return FALSE;
			}
		}
		
		return FALSE;
	}
	
  function user_login ($new_ls_id, $pw, $encpw = false) {
		global $HTTP_COOKIE_VARS, $LS_BASEPATH, $ls_id, $ls_passwd;

		$res = SQL_Query("SELECT *, PASSWORD(".SQL_Quot($pw).") as 'lpwd' FROM user WHERE (id='".addslashes($new_ls_id)."') OR
            (email='".addslashes($new_ls_id)."')");
          if ($row=mysql_fetch_array($res)) {
			if ($row['pwd'] == $row['lpwd'] || $encpw) {
                          $HTTP_COOKIE_VARS['ls_id'] = $row[id];
				$HTTP_COOKIE_VARS['ls_passwd'] = $row['pwd'];
                          $ls_id = $row[id];
				$ls_passwd = $row['pwd'];

                          $cookietime = mktime(0, 0, 0, 31, 12, 2010);
                          SetCookie("ls_id", $row[id], $cookietime, "/");
				SetCookie("ls_passwd", $ls_passwd, $cookietime, "/");

                          user_auth();
                  } else
                          $login_error = 2;
          } else
                  $login_error = 1;

          return $login_error;
  }
	
	function user_logout () {
		global $HTTP_COOKIE_VARS, $LS_BASEPATH;

		SetCookie("ls_id", "", 0, "/");
		SetCookie("ls_passwd", "", 0, "/");
		
		$HTTP_COOKIE_VARS['ls_id'] = 0;
		$HTTP_COOKIE_VARS['ls_passwd'] = "";
		user_auth();
	}

?>
