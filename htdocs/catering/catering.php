<?
	// Copyright (c) 2001 Henrik 'Hotkey' Brinkmann  Email: hotkey@cncev.de

	$LS_BASEPATH = '../';
	include $LS_BASEPATH.'../includes/lsi_base.inc';
	StartPage("Catering");

//Checken ob user eingelogged ist.
if ($user_valid=="true") {
	$id=$user_current[id];
	?>
	<br><br>
	Willkommen zum Catering System. Allgemeine Infos zu zu diesem System findest du <a href="info.php">hier</a><br>
	<br>
	<br>
	<?
	if ($ansicht=="") $ansicht="short";
	if ($ansicht=="short") {
		?>
		Dein Status - [Kurzübersicht] | [<a href="catering.php?ansicht=detailed">Genaue Übersicht</a>]<br><br>
		<?
		//User ID auslesen $id=$user_array[id];
		$res=SQL_Query("SELECT kontostand FROM user WHERE id='$id'");
		$row=mysql_fetch_array($res);
		$kontostand=$row[kontostand];
		if ($kontostand=="") $kontostand=0;
		$res=SQL_Query("SELECT 
												o.anzahl AS anzahl,
												o.bearbeitet AS bearbeitet,
												o.eingetroffen AS eingetroffen,
												o.preis AS preis
												FROM CatOrder o
													LEFT JOIN CatProduct p ON p.id=o.angebot_id
												WHERE user_id='$id'");
		if ($row=mysql_fetch_array($res)) {
			do {
				$bestellungen++;
				$anzahl+=$row[anzahl];
				$kosten+=($row[anzahl]*$row[preis]);
				$bearbeitet+=$row[bearbeitet];
				$eingetroffen+=$row[eingetroffen];
			}
			while ($row=mysql_fetch_array($res));
		}
		?>
		<table class="liste" width="85%">
			<tr>
				<th class="liste">Kontostand</th>
				<th class="liste">Anz. Bestellungen</th>
				<th class="liste">Anz. bearbeitet</th>
				<th class="liste">Anz. eingetroffen</th>
				<th class="liste">Anz. Prod. Ges.</th>
				<th class="liste">Anz. Kosten Ges.</th>
			</tr>
			<tr>
				<td class="liste"><?echo"$kontostand";?></td>
				<td class="liste"><?echo"$bestellungen";?></td>
				<td class="liste"><?echo"$bearbeitet";?></td>
				<td class="liste"><?echo"$eingetroffen";?></td>
				<td class="liste"><?echo"$anzahl";?></td>
				<td class="liste"><?echo"$kosten";?></td>
			</tr>
		</table>
		<?
	}
	if ($ansicht=="detailed") {
		?>
		Dein Status - [<a href="catering.php?ansicht=short">Kurze Übersicht</a>] | [Genaue Übersicht]<br><br>
		<?
		//User ID auslesen $id=$user_array[id];
		if ($direction=="ASC") $direction="DESC";
		else $direction="ASC";
		if ($direction=="") $direction="ASC";
		if ($order=="") $order="bestellung.hour";
		$result=SQL_Query("SELECT 
											bestellung.id AS id,
											bestellung.user_id AS user_id,
											bestellung.hour AS hour,
											bestellung.minute AS minute,
											bestellung.angebot_id AS angebot_id,
											bestellung.bearbeitet AS bearbeitet,
											bestellung.eingetroffen AS eingetroffen,
											bestellung.ausgeliefert AS ausgeliefert,
											bestellung.anzahl AS anzahl,
											user.name AS user,
											angebot.name AS angebot,
											angebot.preis AS preis
											FROM bestellung,user,angebot
											WHERE bestellung.user_id=user.id AND user_id='$id'
											AND bestellung.angebot_id=angebot.id
											ORDER BY $order $direction");
?>
		<br>Sortierung durch klicken auf Überschriften.
		<table class="liste">
			<tr>
				<th class="liste"><a href="catering.php?ansicht="detailed"&direction=<?echo"$direction";?>&order=hour">Uhrzeit</a></th>
				<th class="liste"><a href="catering.php?ansicht="detailed"&direction=<?echo"$direction";?>&order=angebot">Name</a></th>
				<th class="liste"><a href="catering.php?ansicht="detailed"&direction=<?echo"$direction";?>&order=anzahl">Anzahl</a></th>
				<th class="liste"><a href="catering.php?ansicht="detailed"&direction=<?echo"$direction";?>&order=preis">Preis</a></th>
				<th class="liste">Gesamt</th>
				<th class="liste"><a href="catering.php?ansicht="detailed"&direction=<?echo"$direction";?>&order=bearbeitet">Bearbeitet</a></th>
				<th class="liste"><a href="catering.php?ansicht="detailed"&direction=<?echo"$direction";?>&order=eingetroffen">Eingetroffen</a></th>
			</tr>
			<?
				if ($row=mysql_fetch_array($result)) {
					do {
						$gesamt=($row[anzahl]*$row[preis]);
						?>
						<tr>
							<td class="liste"><?echo"$row[hour]:$row[minute]";?></td>
							<td class="liste"><?echo"$row[angebot]";?></td>
							<td class="liste"><div align="right"><?echo"$row[anzahl]";?></div></td>
							<td class="liste"><div align="right"><?echo"$row[preis]";?></div></td>
							<td class="liste"><div align="right"><?echo"$gesamt";?></div></td>
							<td><div align="center">
							<?
							if ($row[bearbeitet]=="1") {
								$draw_loeschen="no";
								?>
								<img src="green.gif" border="0" alt="Diese Bestellung wurde bereits bearbeitet">
								<?
							}
							else {
								?>
								<img src="red.gif" border="0" alt="Diese Bestellung wurde noch nicht bearbeitet">
								<?
							}
							?>
							</div></td>
							<td><div align="center">
							<?
							if ($row[eingetroffen]=="1") {
								?>
								<img src="green.gif" border="0" alt="Die Bestellung ist eingetroffen. Bitte abholen !">
								<?
							}
							else {
								$row[eingetroffen]=0;
								?>
								<img src="red.gif" border="0" alt="Bestellung ist noch nicht eingetroffen"></a>
								<?
							}
							?>
							</div></td>
							<?
							if ($row[bearbeitet]!="1") {
							?>
							<td><div align="center"><a href="bestellung.php?mode=del&id=<?echo"$row[id]";?>&user=<?echo"$row[user]";?>""><img src="delete_button.gif" border="0" alt="Auftrag Löschen"></a></div></td>
							<?
							}
							else echo "<td></td>";
							?>
						</tr>
						<?
						
					}
					while ($row=mysql_fetch_array($result));
				}
			?>	
		</table>
		<?
	}	
	?>
	<br>
	<br>
	<br>
	Das Angebot :<br>
	<?
	$result=SQL_Query("SELECT * FROM angebot WHERE vorhanden='0'");
	?>
	<table class="liste">
		<tr>
			<th class="liste">Name</th>
			<th class="liste">Beschreibung</th>
			<th class="liste">Preis</th>
			<th class="liste"><div align="center">$</div></th>
		<tr>
		<?
		if ($row=mysql_fetch_array($result)) {
			do {
		?>
		<tr>
			<td class="liste"><?echo "$row[name]";?></td>
			<td class="liste"><?echo "$row[beschreibung]";?></td>
			<td class="liste"><div align="right"><?echo "$row[preis]";?></div></td>
			<td class="liste">
			<form action="bestellung.php" method=POST>
			<input type="hidden" name="angebot_id" value="<?echo"$row[id]";?>">
			<input type="hidden" name="preis" value="<?echo"$row[preis]";?>">
			<input type="hidden" name="mode" value="add">
			<input type="text" class="form_field" name="anzahl" size="3" value="1">
			<input type="submit" class="form_btn" name="Submit" value="Order">
			</form>
			</td>
		</tr>
		<?
			}
			while ($row=mysql_fetch_array($result));
		}
		?>
	</table>			
	<?
}
else {
	echo "Du must eingelogged sein um das Catering System nutzen zu können!";
}
EndPage();
	
	
