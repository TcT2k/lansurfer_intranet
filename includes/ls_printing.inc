<?

	function FormStart($tags = "") {
		global $FormErrors, $FormErrorCount, $FormError, $sess, $PHP_SELF;

		if (!$tags) {
			$action = $PHP_SELF;
			if ($sess)
				$action = $sess->url($action);
			$tags = "action=\"$action\" method=\"post\"";
		}

		echo "<form $tags>\n<table>\n";

		unset($FormErrors);
		$FormErrorCount = 0;
		
		if (count($FormError)) {
			reset ($FormError);
  		while (list($field, $text) = each ($FormError)) {
				FormError($field, $text);
			}
		}
	}

	function FormEnd() {
		echo "</table>\n</form>\n";
	}

	function FormError($name, $text) {
		global $FormErrors, $FormErrorCount;
			
		if (!$FormErrorCount)
			echo "<tr><td class=formerror colspan=2>Es gab Fehler beim bearbeiten des Formulars:<br>Bitte &uuml;berpr&uuml;fe deine Angaben.</td></tr>";
	
		$FormErrors[$FormErrorCount] = $name;
		
		$FormErrorCount++;
		echo "<tr><td class=formerror colspan=2>&nbsp;&nbsp;$text</td></tr>";
	}
	
	function FormErrorEx($name, $text) {
		global $FormError, $FormErrorCount;
		
		$FormError[$name] = $text;
		$FormErrorCount++;
	}

	function FormGroup($text) {
		echo "<tr><th class=\"form\" colspan=2><br>".$text."</th></tr>\n";
	}

	function FormValue($name, $value) {
		echo "<input type=\"hidden\" name=\"$name\" value=\"$value\">";
	}
	
	function FormVisibleValue($caption, $value = "") {
		echo "<tr><td class=\"form\"";
		if ($caption == "")
			echo "colspan=2";
		echo ">";
		if ($caption) {
			echo $caption;
			if ($value)
				echo ": ";
			echo "</td><td class=\"form\">";
		}
		echo $value."</td></tr>\n";
	}

	function FormSelectStart($name, $caption, $value, $extratags = "") {
		global $FormCurrentSelectValue;
		$FormCurrentSelectValue = $value;
		
		echo "<tr><td class=\"form\">".$caption.":</td><td class=\"form\">\n";
		echo "<select name=\"".$name."\"";
		if ($extratags)
			echo $extratags." ";
		echo ">\n";
	}
	
	function FormSelectItem($caption, $value = -1, $extratags = "") {
		global $FormCurrentSelectValue;
	
		echo "<option";
		if ($value != -1) {
			echo " value=\"$value\"";
			
			if ($value == $FormCurrentSelectValue)
				echo " selected";
		}
		if ($extratags)
			echo " $extratags";
		echo ">$caption</option>";
	}
	
	function FormSelectEnd() {
		echo "</select></td></tr>\n";
	}

	function FormElement($name, $caption, $value = "", $type = "text", $extratags = "", $linepos = 0) {
		global $FormErrors, $FormErrorCount;

		for ($i=0; $i < $FormErrorCount && !($ElementError = ($FormErrors[$i] == $name)); $i++) ;
	
		
		$showcaption = ($type == "text" || $type == "password" || $type == "textarea");
		if ($type == "checkbox" || $type == "radio")
			$showcaption = 2;

		if ($linepos > 1)
			$showcaption = 0;
	
		if ($linepos >= 0) {
			echo "     <tr><td class=\"form\"";
			if ($showcaption == 2 || $type == "submit")
				echo " colspan=2";
			echo ">";
	
			// Elemente mit vorangestellter Caption
				
			if ($showcaption == 1)
				echo $caption.":</td>\n<td class=\"form\">\n";
		} else {
			echo "&nbsp;";
		}
			
		if ($type == "textarea") {
			echo "<textarea name=\"$name\" class=\"form_textarea\" $extratags>$value</textarea>";
		} else {
			echo "<input type=\"$type\" value=\"$value\" ";
			if ($name) {
				echo "name=\"".$name;
				if ($datetime)
					echo "_date";
				echo "\"";
			}
			if ($extratags)
				echo " $extratags ";
			if ($type == "submit")
				echo "class=\"form_btn\"";
			if ($type == "text" || $type == "password")
				echo "class=\"form_field\"";
				
			echo ">";
			
		}
		if ($showcaption == 2)
			echo "&nbsp;".$caption;
		
		if ($ElementError)
			echo "</td><td class=\"formerrorsign\">&lt;</font>";

		if ($linepos == 0)
			echo "\n</td></tr>\n";
	}

	
	function NavStruct($path, $param = NULL) {
		global $LS_BASEPATH, $NavStructParam;
		
		include $LS_BASEPATH."../includes/navbase.inc";
		
		$pages = explode("/", $path);
		for ($i = 0; $i < count($pages); $i++) {
		
			for ($p = 0; $p < count($NavStruct) && $NavStruct[$p][name] != $pages[$i]; $p++) ;
			if ($p < count($NavStruct)) {
				$url = $NavStruct[$p][url];
				
				NavAdd($NavStruct[$p][title], $LS_BASEPATH.$url);
			}
			
		}
		
		$NavStructParam = $param;
	
	}

	function NavAdd($title, $url) {
		global $NavList;
		
		$i = count($NavList);
		$NavList[$i][0] = $title;
		$NavList[$i][1] = $url;
		
	}
	
	function NavPrint($title) {
		global $NavList, $LS_BASEPATH, $sess, $NavStructParam, $team;

		$NavStructParam['team'] = $team;

		$max = count($NavList);
		
		if (!$max) return;

		for ($i=0;$i<$max;$i++) {
			switch ($max - $i) {
				case 1:
					$level = 2;
					break;
				case 2:
					$level = 3;
					break;
				default:
					$level = 4;
					break;
			}
			$url = $NavList[$i][1];

			if (is_array($NavStructParam) && count($NavStructParam)) {
				reset ($NavStructParam);
  			while (list($key, $value) = each ($NavStructParam)) {
  				$url = str_replace("%".$key."%", $value, $url);
   			}
   		}
			$NavList[$i][1] = $url;

			if (isset($sess))
				$url = $sess->url($url);
		
			echo "<td class=\"contentpanel\" width=\"10\"><img src=\"".$LS_BASEPATH."images/navigation/arrow_left.gif\" width=\"5\" height=\"9\"></td>\n";
			echo "<td class=\"contentpanel\"><img src=\"".$LS_BASEPATH."images/pixel.gif\" width=\"5\" height=\"1\"></td>";
			echo "<td class=\"contentpanel\" valign=\"top\" nowrap><a href=\"".$url."\" class=\"nav_link\" id=\"nav_level".$level."\">".$NavList[$i][0]."</a></td>\n";
      echo "<td class=\"contentpanel\"><img src=\"".$LS_BASEPATH."images/pixel.gif\" width=\"5\" height=\"1\"></td>\n";
		}

		$level = 1;
		echo "<td class=\"contentpanel\" width=\"10\"><img src=\"".$LS_BASEPATH."images/navigation/quadrat.gif\" width=\"9\" height=\"9\"></td>\n";
		echo "<td class=\"contentpanel\"><img src=\"".$LS_BASEPATH."images/pixel.gif\" width=\"5\" height=\"1\"></td>";
		echo "<td class=\"contentpanel\" valign=\"top\" width=\"100%\" navwrap id=\"#nav_level1\">$title</td>\n";
	}
	
	function NavPrintBack($caption = "") {
		global $LS_BASEPATH, $NavList;
		
		echo "<p>";
		NavPrintAction($NavList[count($NavList) - 1][1], ($caption) ? $caption : "Zur&uuml;ck");
		echo "</p>";
	}
	
	function NavPrintNextPage($url, $caption = "") {
		global $LS_BASEPATH, $LS_Design;
	
		echo "<a href=\"$url\">";
		echo ($caption) ? $caption : "N&auml;chste Seite";
		echo " <img src=\"".$LS_Design['nav_next']."\" border=0 width=\"10\" height=\"9\"></a>";
	}

	function NavPrintPrevPage($url, $caption = "") {
		global $LS_BASEPATH, $LS_Design;
	
		echo "<a href=\"$url\"><img src=\"".$LS_Design['nav_prev']."\" border=0 width=\"10\" height=\"9\"> ";
		echo ($caption) ? $caption : "Vorherige Seite";
		echo "</a>";
	}
	
	function NavPrintAction($url, $caption) {
		global $LS_BASEPATH, $LS_Design, $sess;
		
		if ($url == "")
			$url = $LS_BASEPATH."notopen.phtml";
		if (isset($sess))
			$url = $sess->url($url);
		
		echo "<a href=\"".$url."\"><img border=0 width=5 height=9 src=\"".$LS_Design['nav_action']."\"> $caption</a>";
	}
	
	function HTMLStr($str, $limit = 0) {
		if ($limit && strlen($str) > $limit) {
			$str = substr($str, 0, $limit)."...";
		}
		return htmlentities($str);
	}
	
	function PrintHTMLStr ($str, $limit = 0) {
		echo HTMLStr($str, $limit);
	}
	
	function PrintPageHeader($innerstyle = 1) {
		global $LS_Design_id, $LS_PageTitle, $LS_BASEPATH, $login, $LS_TITLEPAGE, 
			$LS_Design, $footerhtml, $HTTP_COOKIE_VARS, $sess, $auth, $LS_OPENPAGE,
			$LS_StatusRefreshNeeded;

		$title = $LS_PageTitle;
		
		if ($LS_Design_id) {
			$res = SQL_Query("SELECT * FROM design WHERE (id=$LS_Design_id)");
			$LS_Design = mysql_fetch_array($res);
			
			if ($LS_Design) {
				$html = $LS_Design[html];

				if ($innerstyle == STYLE_NORMAL)
					$html = str_replace("%CONTENT%", $LS_Design[innerhtml], $html);
				
				$html = str_replace("%TITLE%", $title, $html);
				
				$i = strpos($html, "%CONTENT%");
				$headerhtml = substr($html, 0, $i);
				$footerhtml = str_replace("%CONTENT%", "", stristr($html, "%CONTENT%"));
				
				echo $headerhtml;
				echo "<!-- Content  -->\n"; 
			}
		
		} else {
			$LS_Design[ls_intern] = TRUE;
			$LS_Design['nav_action'] = $LS_BASEPATH."images/navigation/arrow_right.gif";
			$LS_Design['nav_next'] = $LS_BASEPATH."images/navigation/page_next.gif";
			$LS_Design['nav_prev'] = $LS_BASEPATH."images/navigation/page_prev.gif";
			
			include $LS_BASEPATH."../includes/design/header.phtml";		
		}
		if ($LS_StatusRefreshNeeded) {
			LS_RefreshStatusFrame();
			$LS_StatusRefreshNeeded = FALSE;
		}
	}
	
	function PrintPageFooter() {
		global $LS_BASEPATH, $LS_TITLEPAGE, $LS_Design, $footerhtml, $sess;
	
		if ($LS_Design[ls_intern] != TRUE) {
			echo "<!-- End Content -->\n";
			echo $footerhtml;
		
		} else {
			include $LS_BASEPATH."../includes/design/footer.phtml";		
		}
	}
	
	function ExternalURL($url) {
		return $url;
	}
	
	function PrintExternalURL($url) {
		echo ExternalURL($url);
	}

	define("DD_TIME",			1);
	define("DD_DATE",			2);
	
	function DisplayDate($timestamp, $options = 3) {
		$d = getdate($timestamp);
		$n = getdate(time());
		
		$daydiff = $n[yday] - $d[yday];
		switch ($daydiff) {
			case 1:
				$day = "<b>Gestern</b>";
				break;
			case 0:
				$day = "<b>Heute</b>";
				break;
			case -1:
				$day = "<b>Morgen</b>";
				break;
			default:
				$day = date("d.m.Y", $timestamp);
		}
		$s = "";
		if ($options & DD_DATE)
			$s .= $day;
		if ($options & DD_DATE && $options & DD_TIME)
			$s .= ", ";
		if ($options & DD_TIME)
			$s .= date("H:i", $timestamp);
		return $s;
	}
	
	function GetGuestStatusString($party = 0) {
		global $user_valid, $user_current, $sess;
		
		$text = "";
		if ($user_valid) {
			$text = "<b>".HTMLStr($user_current[name],30)."</b><br>";
			
			
			$res = SQL_Query("SELECT * FROM guest WHERE (user=".$user_current[id].") AND (party=$party)");
			$guest = mysql_fetch_array($res);
			
			$res = SQL_Query("SELECT * FROM party WHERE id=$party");
			$Party = mysql_fetch_array($res);
			
			if ($guest) {
				$text .= "1. Angemeldet<br>";
				if ($guest[flags] & GUEST_PAID) {
					$seaturl = $Party[seat_url];
					if (!$seaturl || $seaturl == "http://")
						$seaturl = "http://".LS_EXT_SCRIPT_PATH."signup.phtml?party=".$party;
					
					$seaturl = $sess->url($seaturl);
				
					$text.= "2. Bezahlt<br>3. <a href=\"".$seaturl."\">Sitzplatz</a><br>";
				} else {
					$text.= "2. <a href=\"".$sess->url("http://".LS_EXT_SCRIPT_PATH."signup.phtml?party=$party")."\">Bezahlen</a><br>3. Sitzplatz<br>";
				}
			} else {
				$text .= "1. <a href=\"".$sess->url("http://".LS_EXT_SCRIPT_PATH."signup.phtml?party=$party")."\">Anmelden</a><br>2. Bezahlen<br>3. Sitzplatz<br>";
			}
	
			$text .= "<a href=\"".$sess->url("http://".LS_EXT_SCRIPT_PATH."details.phtml?party=$party")."\">Details</a>";
			
		} else {
			$text = "<a href=\"".$sess->url("http://".LS_EXT_SCRIPT_PATH."details.phtml?party=$party")."\">Login</a>";
		}
		return $text;
	}
/*	
	$NavList[0][0] = "Home";
	$NavList[0][1] = $LS_BASEPATH."index.phtml";
*/
	$LS_Design['nav_action'] = $LS_BASEPATH."images/navigation/arrow_right.gif";
	$LS_Design['nav_next'] = $LS_BASEPATH."images/navigation/page_next.gif";
	$LS_Design['nav_prev'] = $LS_BASEPATH."images/navigation/page_prev.gif";

?>