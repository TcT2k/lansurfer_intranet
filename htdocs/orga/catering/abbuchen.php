<?
$LS_BASEPATH = '../../';
	include $LS_BASEPATH.'../includes/lsi_base.inc';
		StartPage("Abbuchungen");

if (user_auth_ex(AUTH_TEAM, 0, TEAM_CATERING, FALSE)) {
	//Log Datei Festlegen :
	$fp=fopen($LS_BASEPATH."../includes/logs/catering_orga.log","a");
	 
	if ($mode=="bezahlung_intern") {
		$res=SQL_Query("SELECT preis FROM CatProduct WHERE id='$angebot_id'");
		$row=mysql_fetch_array($res);
		$preis=$row[preis]*$anzahl;
		$res=SQL_Query("SELECT name,kontostand FROM user WHERE id='$id'");
		$row=mysql_fetch_array($res);
		$kontostand=$row[kontostand];
		$diff=$kontostand-$preis;
		if ($diff<0) {
		?>
			Kontostand nicht ausreichend !
			<br>
			<br>
			Das Konto von <?echo"$row[name]";?> <a href="verwaltung_konten.php?mode=edit&id=<?echo"$id";?>">Editieren</a> ?
		<?
		}
		else {
			//Das Produkt zur Statistik hinzufügen ...
			$res=SQL_Query("SELECT anzahl FROM CatStats WHERE angebot_id='$angebot_id'");
			if ($row=mysql_fetch_array($res)) {
				//Produkt schon vorhanden : aufadieren der neuen anzahl
				$newanzahl=$row[anzahl]+$anzahl;
				SQL_Query("UPDATE CatStats SET anzahl='$newanzahl' WHERE angebot_id='$angebot_id'");
			}
			else {
				//Produkt noch nicht in Statistik vorhanden : neu anlegen
				SQL_Query("INSERT INTO CatStats (angebot_id,anzahl) VALUES ('$angebot_id','$anzahl')");
			}

			$newguthaben=($kontostand-$preis);
			SQL_Query ("UPDATE user SET kontostand=$newguthaben WHERE id='$id'");
			echo "$preis DM wurde vom Konto abgebucht.<br>";
			echo "Neuer Kontostand : <b>$newguthaben</b>";
			//Logdatei schreiben:
			fputs($fp,"Der Kontostand von user $row[name] wurde durch abbuchung von angebots_id $angebot_id um DM $preis auf $newguthaben geändert.\n");
			
			// Testen ob der neue Kontostand ausreicht um die aktuellen Bestellkosten zu decken:
			// MODYFIED 26.05.2001 : nicht mehr nötig !
			/*
			$res=SQL_Query("SELECT CatOrder.anzahl AS anzahl,
															 CatProduct.preis AS preis
															 FROM CatOrder,CatProduct WHERE CatOrder.angebot_id=CatProduct.id 
															 AND CatOrder.user_id='$id'");
			if ($row=mysql_fetch_array($res)) {
				do {
					$kosten+=($row[anzahl]*$row[preis]);
				}
				while ($row=mysql_fetch_array($res));
			}
			if ($newguthaben<$kosten) {
			?>
			<br>
			<br>
			<font color="#FF0000"><b>Der neue Kontostand reicht nicht mehr aus um die bestehenden Bestellungen zu bezahlen !</b></font>
			<?
			}
			*/
		}
	}
	?>
	<br>
	<br>
	<a href="verwaltung_konten.php">Zurück</a> zur Kontenverwaltung<br>
	<?
}
else {
	LS_ERROR("Du must als Administrator eingelogged sein");
	?>
	<br><br>
	<a href="../party/details.php">Login</a>
	<br>
	<?
	fclose($fp);
}
EndPage();
?>