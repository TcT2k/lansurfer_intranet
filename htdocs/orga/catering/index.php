<?
	// Copyright (c) 2001 Henrik 'Hotkey' Brinkmann  Email: hotkey@cncev.de

	$LS_BASEPATH = '../../';
	include $LS_BASEPATH.'../includes/lsi_base.inc';

	StartPage("Catering Administration");

if (user_auth_ex(AUTH_TEAM, 0, TEAM_CATERING, FALSE)) {
	
	?>
	<div align="center">
	<br>
	<br>
	<table width="95%" class="liste">
		<tr>
			<td class="liste">
				<table width="100%" border="0">
					<tr>
							<th class="liste"><div align="center"><a href="index.php">Home</a></div></td>
							<th class="liste"><div align="center"><a href="verwaltung_angebot.php">Angebote</a></div></td>
							<th class="liste"><div align="center"><a href="verwaltung_konten.php">User Konten</a></div></td>
							<th class="liste"><div align="center"><a href="verwaltung_bestellung.php">Neue Bestellungen</a></div></td>
							<th class="liste"><div align="center"><a href="verwaltung_bestellung_alt.php">Alte Bestellungen</a></div></td>
							<th class="liste"><div align="center"><a href="verwaltung_bestellung_history.php">History</a></div></td>
							<th class="liste"><div align="center"><a href="verwaltung_stats.php">Stats</a></div></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<br>
	<br>
	<br>
	<?
	if ($action=="insert_intern") {
		//Die Gewählten Produkte zur Statistik hinzufügen
		for($i=0;$i<=$idcount;$i++) {
			if ($anzahl[$i]>0) {
				$gesamtpreis+=($preis[$i]*$anzahl[$i]);
				$angebot_id=$id_nummer[$i];
				$anz=$anzahl[$i];
				$res=SQL_Query("SELECT anzahl FROM CatStats WHERE angebot_id='$angebot_id'");
				if ($row=mysql_fetch_array($res)) {
					//Produkt schon vorhanden : aufadieren der neuen anzahl
					$newanzahl=$anz+$row[anzahl];
					SQL_Query("UPDATE CatStats SET anzahl='$newanzahl' WHERE angebot_id='$angebot_id'");
				}
				else {
					//Produkt noch nicht in Statistik vorhanden : neu anlegen
					SQL_Query("INSERT INTO CatStats (angebot_id,anzahl) VALUES ('$angebot_id','$anz')");
				}
			}
		}
		echo "Die Produkte wurden eingetragen. Zu Bezahlen : <b>$gesamtpreis</b><br><br>";
	}
	?>
		
	<table border=0 width="50%">
		<tr class="liste">
	Eigene Umsätze eintragen : <br>
	Mit diesem Formular werden die Verkäufe zur Statistik hinzugefügt die nicht über <br>
	das Catering Konto abgewickelt werden. Somit ist am Ende der Party eine Überprüfung<br>
	des Internen Umsatzes möglich.	</tr>
	<table>
	<br><br>
	<form action="index.php" method=POST>
	<input type="hidden" name="action" value="insert_intern">
	<table border=0 width="50%">
		<tr class="liste">
			<th class="liste">Name</th>
			<th class="liste">Menge</th>
		</tr>
	<?
	$res=SQL_Query ("SELECT * FROM CatProduct WHERE vorhanden=1");
	if ($row=mysql_fetch_array($res)) {
		$idcount=0;
		do {
			?>
			<tr class="liste">
				<td class="liste"><?echo"$row[name]";?></td>
				<td class="liste"><div align="center">
					<input type="hidden" name="preis[<?echo $idcount;?>]" value="<?echo"$row[preis]";?>">
					<input type="hidden" name="id_nummer[<?echo $idcount;?>]" value="<?echo"$row[id]";?>">
					<input type="text" class="form_field" size="4" value="0" name="anzahl[<?echo"$idcount";?>]">
				</div>
				</td>
			</tr>
			<?
			$idcount++;
		}
		while ($row=mysql_fetch_array($res));
	}
	$idcount--;
	?>
	</table>
	<input type="hidden" name="idcount" value="<?echo"$idcount";?>">
	<input class="form_btn" type="submit" name="Submit" value="Eintragen">
	</form>
	</div>
	<?
}
else {
	LS_ERROR("Du must als Administrator eingelogged sein");
	?>
	<br><br>
	<a href="../party/details.php">Login</a>
	<br>
	<?
	//Log Datei schliessen
	fclose($fp);
}
EndPage();
?>
