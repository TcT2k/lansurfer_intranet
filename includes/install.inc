<html>

<head>
<style>
a:link			{font:8pt/11pt verdana; color:red}
a:visited		{font:8pt/11pt verdana; color:#4e4e4e}
.form { font:8pt/11pt verdana;  }
.formerror { font:8pt/11pt verdana; color:red}
</style>
<meta HTTP-EQUIV="Content-Type" Content="text-html; charset=Windows-1252">
<title>Intranet Installtion</title>
</head>

<?
	if (!isset($step))
		$step = 1;

	if ($submitted) {
		switch ($step) {
			case 1:
				
				if ($link = @mysql_connect ($f_host, $f_user, $f_password))
					$step = 2;
        else
        	$stepError = "SQL Daten Fehlerhaft.";
				
				break;
			case 2:
				if ($link = @mysql_connect ($f_host, $f_user, $f_password)) {
					if (mysql_select_db($f_database))
						$step = 3;
					else
						$stepError = "Die Datenbank konnte nicht benutzt werden";
				} else
        	$stepError = "SQL Daten Fehlerhaft.";
			
		}
	}

?>

<body bgColor="white">

<table width="400" cellpadding="3" cellspacing="5">
  <tr>
    <td id="tableProps" valign="top" align="left"><img SRC="images/info.gif" width="25" height="33">
    </td>
    <td id="tableProps2" align="left" valign="middle" width="360">
    <h1 id="term1"
    style="COLOR: black; FONT: 13pt/15pt verdana"><span id="pageNotFound">Intranet Setup</span></h1>
    </td>
  </tr>
  <tr>
    <td id="tablePropsWidth" width="400" colspan="2"><font id="LID1"
    style="COLOR: black; FONT: 8pt/11pt verdana">
    Ok der Web Server scheint nun schon zu funktieren wenn man diese Seite sieht.</font></td>
  </tr>
  <tr>
    <td id="tablePropsWidth" width="400" colspan="2">
  	<hr color="#C0C0C0" noshade>
    
    <h1 id="term1"
    style="COLOR: black; FONT: 13pt/15pt verdana"><span id="pageNotFound">Schritt <? echo $step; ?></span></h1>
    
    <?
    if ($stepError) 
    	FormErrorEx("-", $stepError);
    else
    	$submitted = FALSE;
    
    switch ($step) {
    	case 1:
    		if (!$submitted) {
    			$f_host = SQL_HOST;
    			$f_user = SQL_USER;
    			$f_password = SQL_PASSWORD;
    		}
    	
    		echo "<p class=form><b>mySQL Server Einrichtung:</b><br>Nun bitte die Richtigen Login daten für den mySQL Server eingeben</p>";
    		FormStart();
    			FormValue("step", 1);
    			FormValue("submitted", 1);
    			
    			FormElement("f_host", "Host", $f_host);
    			FormElement("f_user", "Account", $f_user);
    			FormElement("f_password", "Passwort", $f_password, "password");
    		
    			FormElement("", "", "Weiter >", "submit", -1);
    		FormEnd();
    	
    		break;
    	case 2:
    		if (!$submitted)
    			$f_database = SQL_DB;
    	
    		echo "<p class=form><b>Datenbank Einrichtung:</b><br>Nun bitte den namen der Datenbank angeben. Entweder ein <i>neuer</i> Name oder der Name einer <i>leeren</i> Datenbank.</p>";
    		FormStart();
    			FormValue("f_host", $f_host);
    			FormValue("f_user", $f_user);
    			FormValue("f_password", $f_password);
    		
    			FormValue("step", 2);
    			FormValue("submitted", 1);
    			
    			FormElement("f_database", "Datenbank", $f_database);
    		
    			FormElement("", "", "Weiter >", "submit");
    		FormEnd();
    	
    		break;
    		
    }
    
    ?>
    
    </td>
  </tr>
</TBODY>
</table>
</body>
</html>
