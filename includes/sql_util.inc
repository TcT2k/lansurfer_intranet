<?
// SQL Utils
	$SQL_Initialized = FALSE;
	
	$SQL_Link = 0;
	
	function SQL_Init() {
		global $SQL_Initialized, $SQL_Link, $SQL_DBLink;

		if (!$SQL_Initialized) {
			if (!($SQL_Link = mysql_connect(SQL_HOST, SQL_USER, SQL_PASSWORD)))
				SQL_Error();
			mysql_select_db(SQL_DB, $SQL_Link);
			$SQL_Initialized = TRUE;
		}
		return $SQL_Link;
	}
	
	function SQL_Done() {
		global $SQL_Initialized, $SQL_Link;
		
		if ($SQL_Initialized) {
			mysql_close($SQL_Link);
			
			$SQL_Initialized = FALSE;
		}
	}
	
	function SQL_Query($query, $die = TRUE) {
		global $SQL_Initialized, $SQL_Link, $SQL_DBLink;
	
		if (!$SQL_Initialized)
			SQL_Init();
	
		if ($res = mysql_query($query, $SQL_Link))
			return $res;
		else
			SQL_Error($query, $die);
	}
	
	// Funktioniert wie AddSlashes aber berücksichtigt nur '
	
	function SQL_Str($str) {
		if (get_cfg_var("magic_quotes_gpc"))
			return $str;
		else {
		  return str_replace('\\', "\\\\", $str);
		  return str_replace("'", "\\'", $str);
		}
			//return addslashes($str);
		//return $str;
	}
	
	function SQL_Quot($str) {
		return "'".SQL_Str($str)."'";
	}

	function SQL_QueryField($name, $value, $last = FALSE) {
		$s = $name."=".SQL_Quot($value);
		if (!$last)
			$s.= ", ";
		return $s;
	}
	
	function SQL_QueryFields($fields) {
		$i = 0;
		$s = "";
		reset ($fields);
  	while (list($fieldname, $fieldvalue) = each ($fields)) {
  		if ($i)
  			$s .= ", ";
  		$s.= $fieldname."=".SQL_Quot($fieldvalue);
  		$i++;
  	}
  	return $s;
	}
	
	function SQL_Error($query, $die = TRUE) {
		global $LS_BASEPATH, $user_valid, $user_current, $PHP_SELF, $QUERY_STRING, $HTTP_REFERER;

		$err = mysql_error();
		$errNo = mysql_errno();
		$msg = _("SQL Error").': '.$errNo.'<br>';
		
		$mon = date('m');
		$year = date('Y');
		
		// Error Log
		$fp = @fopen($LS_BASEPATH.'../includes/logs/sql_errors_'.$year.'_'.$mon.'.log', 'a');
		if ($fp) {
			$s = '['.date('Y-m-d H:i:s').'] ';
			if ($user_valid)
				$s .= $user_current['id'];
			else
				$s .= '0';
			$ref = ($HTTP_REFERER) ? $HTTP_REFERER : '(None)';
			
			$s .= str_replace("\n", "", " ".$PHP_SELF."?".$QUERY_STRING." ".$ref." ".$errNo.": ".$err."; ".$query);
			fputs($fp, $s."\n");
			fclose($fp);
		}
		
		if ($die)
			LS_Error($msg);
		else
			echo $msg;
	}

?>