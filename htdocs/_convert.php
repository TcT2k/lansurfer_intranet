<html>
<head>
	<title>Setup</title>
	<style>
	<!--
	BODY {
		FONT-FAMILY: Verdana, Helvetica;
		FONT-SIZE: 12px;
	}
	
	TH.liste {
		FONT-FAMILY: Verdana,Helvetica;
		FONT-SIZE: 12px;
		FONT-WEIGHT: normal;
    BACKGROUND-COLOR: #333333;
    COLOR: WHITE;
	}
	
	TD.liste {
		FONT-FAMILY: Verdana,Helvetica;
		FONT-SIZE: 12px;
		FONT-WEIGHT: normal;
	  BACKGROUND-COLOR: #555555;
	  COLOR: WHITE;
	}
	
	H2 {
		FONT-FAMILY: Verdana,Helvetica;
		FONT-SIZE: 24px;
		FONT-WEIGHT: bold;
	}
	-->
	</style>
</head>
<body>
<h2>LANsurfer Intranet Konverter</h2>

<p class=content>
<?
	$LS_BASEPATH = "";
	include $LS_BASEPATH.'../includes/ls_base.inc';
	
	if (!$submitted) {
		echo 'Dieses Script konvertiert eine alte bestehende Datenbank in das neue format mit verschlüsselten Passwörtern.';
		echo '<form action="'.$PHP_SELF.'" method="post"><input type=hidden name=submitted value=1><input type=submit value="Kovertieren"></form>';
	} else	{
		SQL_Query("ALTER TABLE user CHANGE password pwd varchar(255) binary;");
		SQL_Query("UPDATE user SET pwd=PASSWORD(pwd)");
		echo 'User Datenbank erfolgreich konvertiert.';
	}
?>
</p>

</body>
</html>
