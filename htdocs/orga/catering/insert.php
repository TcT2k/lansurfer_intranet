<?
// Copyright (c) 2001 Henrik 'Hotkey' Brinkmann  Email: hotkey@cncev.de

	$LS_BASEPATH = '../../';
	include $LS_BASEPATH.'../includes/lsi_base.inc';
	StartPage("Angebote importieren ...");
	
	if (user_auth_ex(AUTH_TEAM, 0, TEAM_CATERING, FALSE)) {	
		//Dateiauswahlfeld
		if ($action=="") {
			?>
			Die einzelnen Datenelemente müssen in der folgenden Reihenfolge stehen :<br>
			Das Trennzeichen kann selbst gewählt werden. (Default ist ";" - Semikolon)<br>
			<b><br>Name-preis-size-beschreibung-lieferant-nummer-vorhanden<br></b><br>
			Gültige Werte :<br><br>
			<b>Size :<br></b>
			- 0 <-> klein<br>
			- 1 <-> mittel<br>
			- 2 <-> gross<br>
			<br><br>
			<b>Lieferant :<br></b>
			abhängig von der Datenbank. Der als 1. eingetragene Lieferant hat die 1. danach 2,3 usw.<br><br>
			<b>Vorhanden :<br></b>
			- 0 extern - wird nicht auf der Session selbst verkauft (z.B. Pizza)<br>
			- 1 intern - wird direkt auf der Session angeboten (z.B. Getränke)<br><br>
			<form method="post" action="insert.php" enctype="multipart/form-data">
			<input type="hidden" name="action" value="do_insert">
			CSV Datei:<br>
			<input type="file" name="csvdatei" class="form_field"><br>
			Trennzeichen :<input type="text" name="separator" class="form_field" value=";" size="2">
			<input type="submit" class="form_btn"name="submit" value="Importieren">
			</form>
			<?
		}	
		//Importieren starten
		if ($action=="do_insert") {
			//Debug :
			echo "DEBUG : csvdatei : $csvdatei<br>";
			echo "DEBUT : Trennzeichen : $separator<br>";
			
			$fp=fopen($csvdatei,"r");
			while(!feof($fp)) {
				$zeile=fgets($fp,200);
				$elemente=explode($separator,$zeile);
				echo "Name : $elemente[0]. Preis : $elemente[1]. Size : $elemente[2]. Beschr. : $elemente[3]. Lieferant : $elemente[4]. Nummer $elemente[5]. Vorhanden : $elemente[6]<br>";
				SQL_Query ("INSERT INTO CatProduct (name,preis,size,beschreibung,lieferant,nummer,vorhanden)
										VALUES ('$elemente[0]','$elemente[1]','$elemente[2]','$elemente[3]','$elemente[4]','$elemente[5]','$elemente[6]')");
			}	
		}	
	}
	else {
		LS_ERROR("Du must als Administrator eingelogged sein");
		?>
		<br><br>
		<a href="../party/details.php">Login</a>
		<br>
		<?
	}
	EndPage();
?>