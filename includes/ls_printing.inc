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
			echo "<tr><td class=formerror colspan=2>"._("There were errors when processing the form:<br>Please check your input.")."</td></tr>";
	
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
		echo "<tr><th class=\"form\" colspan=2 align=left><br>".$text."<hr width=400></th></tr>\n";
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

	function FormSelectStart($name, $caption, $value, $extratags = "", $radio = false) {
		global $FormCurrentSelectValue, $FormCurrentSelectRadio, $FormCurrentSelectName;
		$FormCurrentSelectValue = $value;
		$FormCurrentSelectRadio = $radio;
		$FormCurrentSelectName = $name;
		
		echo "<tr><td class=\"form\" valign=top>".$caption.":</td><td class=\"form\">\n";
		if (!$radio) {
			echo "<select name=\"".$name."\"";
			if ($extratags)
				echo $extratags." ";
			echo ">\n";
		}
	}
	
	function FormSelectItem($caption, $value = -1, $extratags = "") {
		global $FormCurrentSelectValue, $FormCurrentSelectRadio, $FormCurrentSelectName;
	
		if (!$FormCurrentSelectRadio) {
			echo "<option";
			if ($value != -1) {
				echo " value=\"$value\"";
				
				if ($value == $FormCurrentSelectValue)
					echo " selected";
			} else {
				if ($caption == $FormCurrentSelectValue)
					echo " selected";
			}
			if ($extratags)
				echo " $extratags";
			echo ">$caption</option>";
		} else {
			echo '<input type=radio name="'.$FormCurrentSelectName.'" value="'.$value.'"';
			if ($value == $FormCurrentSelectValue)
				echo " checked";
			echo '>'.$caption.'<br>';
		}
	}
	
	function FormSelectEnd() {
		global $FormCurrentSelectValue, $FormCurrentSelectRadio;

		echo "</select></td></tr>\n";
	}

	function FormDateTime($datetime) {
		return StrToDate($datetime[date], $datetime[time]);
	}
	
	function FormElement($name, $caption, $value = "", $type = "text", $extratags = "", $linepos = 0) {
		global $FormErrors, $FormErrorCount;

		for ($i=0; $i < $FormErrorCount && !($ElementError = ($FormErrors[$i] == $name)); $i++) ;
	
		
		$showcaption = ($type == "text" || $type == "password" || $type == "textarea" || $type == "file" || $type == "datetime") ? 1 : 0;
		if ($type == "checkbox" || $type == "radio")
			$showcaption = 2;

		if ($linepos > 1)
			$showcaption = 0;
	
		if ($linepos == 0 || $linepos == 1) {
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
		} elseif ($type == 'datetime') {
			echo ' <input type="text" value="'.DateToStr($value).'" name="'.$name.'[date]" size=10 maxlength=10 class="form_field">';
			echo ' <input type="text" value="'.TimeToStr($value).'" name="'.$name.'[time]" size=5 maxlength=5 class="form_field">';
		} else {
			echo '<input type="'.$type.'" value="'.$value.'" ';
			if ($name)
				echo 'name="'.$name.'"';
			if ($extratags)
				echo " $extratags ";
			if ($type == "submit")
				echo "class=\"form_btn\"";
			if ($type == 'text' || $type == 'password' || $type == 'datetime')
				echo 'class="form_field"';
				
			echo ">";
		}
		if ($showcaption == 2)
			echo "&nbsp;".$caption;
		
		if ($ElementError)
			echo "</td><td class=\"formerrorsign\">&lt;</font>";

		if ($linepos == 0 || $linepos == 3)
			echo "\n</td></tr>\n";
	}

	
	function NavStruct($path, $param = FALSE) {
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

		$NavStructParam[team] = $team;

		$max = count($NavList);
		
		if (!$max) return;
		
		echo '<table><tr>';

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
		
			echo '<td class="content" width="10"><img src="'.$LS_BASEPATH.'images/navigation/arrow_left.gif" width="5" height="9"></td>';
			echo '<td class="content"><img src="'.$LS_BASEPATH.'images/pixel.gif" width="5" height="1"></td>';
			echo '<td class="content" valign="top" nowrap><a href="'.$url.'" class="NavLink" id="NavLevel'.$level.'">'.$NavList[$i][0].'</a></td>';
      echo '<td class="content"><img src="'.$LS_BASEPATH.'images/pixel.gif" width="5" height="1"></td>';
		}

		$level = 1;
		echo '<td class="content" width="10"><img src="'.$LS_BASEPATH.'images/navigation/quadrat.gif" width="9" height="9"></td>';
		echo '<td class="content"><img src="'.$LS_BASEPATH.'images/pixel.gif" width="5" height="1"></td>';
		echo '<td valign="top" width="100%" nowrap><span class=content id=NavLevel1>'.$title.'</span></td>';
		
		echo '</tr></table>';
	}
	
	function NavPrintBack($caption = "") {
		global $LS_BASEPATH, $NavList;
		
		echo "<p class=content>";
		NavPrintAction($NavList[count($NavList) - 1][1], ($caption) ? $caption : _("Back"));
		echo "</p>";
	}
	
	function NavPrintNextPage($url, $caption = "") {
		global $LS_BASEPATH, $LS_Design;
	
		echo "<a href=\"$url\">";
		echo ($caption) ? $caption : _("Next Page");
		echo " <img src=\"".$LS_Design[nav_next]."\" border=0 width=\"10\" height=\"9\"></a>";
	}

	function NavPrintPrevPage($url, $caption = "") {
		global $LS_BASEPATH, $LS_Design;
	
		echo "<a href=\"$url\"><img src=\"".$LS_Design[nav_prev]."\" border=0 width=\"10\" height=\"9\"> ";
		echo ($caption) ? $caption : _("Previous Page");
		echo "</a>";
	}
	
	function NavPrintAction($url, $caption, $htmltags = "") {
		global $LS_BASEPATH, $LS_Design;
		
		if ($url == "")
			$url = $LS_BASEPATH."notopen.php";
		
		echo '<a href="'.$url.'"';
		if ($htmltags)
			echo ' '.$htmltags;
		echo "><img border=0 width=5 height=9 src=\"".$LS_Design[nav_action]."\"> $caption</a>";
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

	define("TH_NOUBB", 		1);			// Kein UBB Code
	define("TH_NOBR", 		2);			// nl nicht in <br> umwandeln

	function Text2HTML($text, $options = 0) {
		$text = HTMLSpecialChars($text);
		
		if (!($options & TH_NOBR))
			$text = nl2br($text);
		
		$search = array(
			"'\\[i\\](.*?)\\[\\/i\\]'is",
			"'\\[b\\](.*?)\\[\\/b\\]'is",
			"'\\[u\\](.*?)\\[\\/u\\]'is",
			"'([^=])(http|ftp|https)://([A-Za-z0-9#?/.&=_-]*)'i",
			"'\\[url\\=(.*?)\\](.*?)\\[\\/url\\]'is",
			);
		$replace = array(
			"<i>\\1</i>",
			"<b>\\1</b>",
			"<u>\\1</u>",
			"\\1<a target=_blank href=\"\\2://\\3\">\\2://\\3</a>",
			"<a target=_blank href=\"\\1\">\\2</a>",
			);

    return preg_replace($search, $replace, $text);
	}
	
	function PrintText2HTML($text, $options = 0) {
		echo Text2HTML($text, $options);
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
			$LS_Design[nav_action] = $LS_BASEPATH."images/navigation/arrow_right.gif";
			$LS_Design[nav_next] = $LS_BASEPATH."images/navigation/page_next.gif";
			$LS_Design[nav_prev] = $LS_BASEPATH."images/navigation/page_prev.gif";
			
			include $LS_BASEPATH."../includes/design/header.php";		
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
			include $LS_BASEPATH."../includes/design/footer.php";		
		}
	}
	
	function ExternalURL($url) {
		return $url;
	}
	
	function PrintExternalURL($url) {
		echo ExternalURL($url);
	}

	define('DD_TIME',			1);
	define('DD_DATE',			2);
	define('DD_NORELATIVE',		4);
	
	function DisplayDate($timestamp, $options = 3) {
		$d = getdate($timestamp);
		$n = getdate(time());
		
		if ($options & DD_NORELATIVE) {
			$day = strftime('%x', $timestamp);
		} else {
			$daydiff = $n[yday] - $d[yday];
			switch ($daydiff) {
				case 1:
					$day = "<b>"._("Yesterday")."</b>";
					break;
				case 0:
					$day = "<b>"._("Today")."</b>";
					break;
				case -1:
					$day = "<b>"._("Tomorrow")."</b>";
					break;
				default:
					$day = strftime('%x', $timestamp);
			}
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

	function NavPrintDel($url, $caption = '', $htmltags = '') {
		global $LS_BASEPATH;
		if ($caption == '')
			$caption = _("Remove");
	
		echo '<a href="'.$url.'"';
		if ($htmltags)
			echo ' '.$htmltags;
		echo '><img alt="'.$caption.'" border=0 width=12 height=12 src="'.$LS_BASEPATH.'images/navigation/delete_button.gif"></a>';
	}

	function PrintSeat($userid) {
		$res = SQL_Query("SELECT * FROM guest WHERE user=".$userid);
		if ($Guest = mysql_fetch_array($res)) {
			$res = SQL_Query("SELECT
					sb.id as 'blockid',
					s.id,
					sb.name,
					s.row,
					s.col 
				FROM guest AS g
					LEFT JOIN seats AS s ON s.guest=g.id
					LEFT JOIN seat_block AS sb ON s.block=sb.id
				WHERE g.id=".$Guest[id]." AND s.status=".SEAT_OCCUPIED."
				");
			$Seat = mysql_fetch_array($res);
		}
		if ($Seat && $Seat[id]) {
			echo '<a href="../party/seat.php?id='.$Seat[blockid].'&row='.$Seat[row].'&col='.$Seat[col].'" target="_new">'.$Seat[name].', '.chr($Seat[row] + 65).' '.($Seat[col] + 1).'</a>';
		} else {
			echo "("._("Unknown").")";
		}
	}
	
	function FloatToCurrency($value, $edit = false) {
		global $LS_locale_info;

		if (!$LS_locale_info)
			$LS_locale_info = localeconv();

		
		if ($edit)
			return sprintf('%s', str_replace('.', $LS_locale_info['decimal_point'], $value));
		else
			return str_replace('.', $LS_locale_info['decimal_point'], sprintf('%.2f %s', str_replace('.', $LS_locale_info['decimal_point'], $value), LS_CATERING_CURRENCY));
	}

	$LS_locale_info = false;

	
	$LS_Design[nav_action] = $LS_BASEPATH."images/navigation/arrow_right.gif";
	$LS_Design[nav_next] = $LS_BASEPATH."images/navigation/page_next.gif";
	$LS_Design[nav_prev] = $LS_BASEPATH."images/navigation/page_prev.gif";

?>