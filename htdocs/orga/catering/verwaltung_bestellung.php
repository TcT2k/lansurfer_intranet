<?
	// Copyright (c) 2001 Henrik 'Hotkey' Brinkmann  Email: hotkey@cncev.de

	$LS_BASEPATH = '../../';
	include $LS_BASEPATH.'../includes/lsi_base.inc';
	StartPage("Bestellungen Verwalten");

if (user_auth_ex(AUTH_TEAM, 0, TEAM_CATERING, FALSE)) {
	//Log Datei Festlegen :
	$fp=fopen($LS_BASEPATH."../includes/logs/catering_orga.log","a");
	

	$orga=$user_current[name];	

	if ($mode=="change_bearbeitet") {
		// Setzt die entsprechende Bestellung auf den Status Bearbeitet oder "nicht bearbeitet"
		// Zusätzlich wird ein Vermerk in der Log Datei gemacht.
		if ($old==1) $new=0;
		else $new=1;
		SQL_Query("UPDATE CatOrder SET bearbeitet=$new WHERE id='$id'");
		$day=date(d);
		$month=date(m);
		$hour=date(H);
		$minute=date(i);
		//$orga=
		// In Log Datei schreiben 
		if ($new==1) fputs($fp,"[$day.$month] um [$hour:$minute] Bestellung von $user auf Bearbeitet gestellt von $orga\n");
		else fputs($fp,"[$day.$month] um [$hour:$minute] Bestellung von $user auf nicht Bearbeitet gestellt von $orga\n");	
	}	
	if ($mode=="change_eingetroffen") {
		// Setzt die entsprechende Bestellung auf den Status Eingetroffen oder "nicht eingetroffen"
		// Zusätzlich wird ein Vermerk in der Log Datei gemacht.
		if ($old==1) $new=0;
		else $new=1;
		SQL_Query("UPDATE CatOrder SET eingetroffen=$new WHERE id='$id'");
		$day=date(d);
		$month=date(m);
		$hour=date(H);
		$minute=date(i);
		//$orga=
		// In Log Datei schreiben :
		if ($new==1) fputs($fp, date('Y-m-d H:i:s')." Bestellung von $user auf eingetroffen gestellt von $orga\n");
		else  fputs($fp, date('Y-m-d H:i:s')." Bestellung von $user auf nicht eingetroffen gestellt von $orga\n");	
	}	
	if ($mode=="del") {
		// Loescht eine Bestellung aus der Datenbank unabhängig davon ob die Bestellung Eingetroffen ist oder nicht.
		// Zusätzlich wird ein Vermerk in der Log Datei gemacht.
		$res=mysql_query ("SELECT user_id,angebot_id,anzahl FROM CatOrder WHERE id='$id'");
		if ($row=mysql_fetch_array($res)) {	
			//Den Wert der gelöschten Bestellung wieder dem Konto gutschreiben
			$anzahl=$row[anzahl];
			$user_id=$row[user_id];
			$angebot=$row[angebot_id];
			$res=SQL_Query("SELECT preis FROM CatProduct WHERE id='$angebot'");
			$row=mysql_fetch_array($res);
			$preis=$row[preis];
			$res=SQL_Query("SELECT kontostand FROM user WHERE id='$user_id'");
			$row=mysql_fetch_array($res);
			$kontostand=$row[kontostand];
			$gutschrifft=$anzahl*$preis;
			$kontostand+=$gutschrifft;
			SQL_Query ("UPDATE user SET kontostand='$kontostand' WHERE id='$user_id'");
			SQL_Query ("DELETE FROM CatOrder WHERE id='$id'");
			$day=date(d);
			$month=date(m);
			$hour=date(H);
			$minute=date(i);
			//In Log Datei schreiben:
			fputs($fp,"[$day.$month] um [$hour:$minute] Bestellung von $user wurde von $orga gelöscht.\n");
			fputs($fp,"		Der Kontostand von $user (id : $user_id) um $anzahl x $preis = $gutschrifft auf $kontostand erhöht.\n");	
		}
		else {
			//Datenbankfehler : in Logdatei schreiben
			fputs($fp,"[$day.$month] um [$hour:$minute] Bestellung der id $id sollte gelöscht werden. DATENBANK FEHLER!!\n");
		}
	}
	if ($mode=="do_ausgeliefert") {
		//Setzt den Status auf Ausgeliefert.
		//Es wird zusätzlich ein Eintrag in der Log Datei gemacht.
		if ($old==1) {
			SQL_Query("UPDATE CatOrder SET ausgeliefert=0 WHERE id='$id'");
			
			//Das Produkt wieder aus der Statistik löschen
			$res=SQL_Query("SELECT angebot_id,anzahl FROM CatOrder WHERE id='$id'");
			$row=mysql_fetch_array($res);
			$angebot_id=$row[angebot_id];
			$anzahl=$row[anzahl];
			$res=SQL_Query("SELECT anzahl FROM CatStats WHERE angebot_id='$angebot_id'");
			$row=mysql_fetch_array($res);
			$newanzahl=$row[anzahl]-$anzahl;
			if (($newanzahl==0)||($newanzahl=="")) SQL_Query("DELETE FROM CatStats WHERE angebot_id='$angebot_id'");
			else SQL_Query("UPDATE CatStats SET anzahl='$newanzahl' WHERE angebot_id='$angebot_id'");
		}
		else {
			$day=date(d);
			$month=date(m);
			$hour=date(H);
			$minute=date(i);
			echo "ID $id";
			SQL_Query("UPDATE CatOrder SET ausgeliefert=1 WHERE id='$id'");
			fputs($fp,"[$day.$month] um [$hour:$minute] Bestellung von $user wurde von $orga ausgeliefert.\n");			
			
			//Das Produkt zur Statistik hinzufügen ...
			$res=SQL_Query("SELECT angebot_id,anzahl FROM CatOrder WHERE id='$id'");
			$row=mysql_fetch_array($res);
			$angebot_id=$row[angebot_id];
			$anzahl=$row[anzahl];
			$res=SQL_Query("SELECT anzahl FROM CatStats WHERE angebot_id='$angebot_id'");
			if ($row=mysql_fetch_array($res)) {
				//Produkt schon vorhanden : aufadieren der neuen anzahl
				$newanzahl=$anzahl+$row[anzahl];
				SQL_Query("UPDATE CatStats SET anzahl='$newanzahl' WHERE angebot_id='$angebot_id'");
			}
			else {
				//Produkt noch nicht in Statistik vorhanden : neu anlegen
				SQL_Query("INSERT INTO CatStats (angebot_id,anzahl) VALUES ('$angebot_id','$anzahl')");
			}
		}
	}
	
	if ($mode=="group_bestellung_status_setzen") {
		// Es werden alle Bestellung eines Lieferanten gruppiert nach Art und Grösse auf bearbeitet gesetzt.
		// Zudem werden die Bestellten Produkte in einer neuen Tabelle abgelegt. (History Funktion)
		$res=SQL_Query("SELECT angebot.name AS name,
														 angebot.nummer AS nummer,
														 angebot.size AS size,
														 bestellung.anzahl AS anzahl
														 FROM 
														 	CatProduct angebot,
														 	CatOrder bestellung
														 WHERE bestellung.angebot_id=angebot.id
														 	AND angebot.lieferant=$lieferant
														 	AND bestellung.bearbeitet=0
														 	AND bestellung.ausgeliefert=0
														 	AND bestellung.eingetroffen=0
														 	AND bestellung.wagen=0
														 GROUP BY angebot.nummer,angebot.size");
		if ($row=mysql_fetch_array($res)) {
		//Alle Artikel sollen in einer neuen Tabelle gespeichert werden um
		//nachher die einzelnen Bestellungen nach telefonischer bestellung gruppiert
		//wieder anzeigen oder rückgängig machen zu können.
			$res2=SQL_Query ("SELECT group_id FROM CatHistory");
			if ($row2=mysql_fetch_array($res2)) {
				$group_id=0;
				do {
					if ($group_id<=$row2[group_id]) $group_id=$row2[group_id];
				}
				while ($row2=mysql_fetch_array($res2));
				$group_id++;
			}
			$month=date(m);
			$day=date(d);
			$hour=date(H);
			$minute=date(i);
			$zeit="catering_".$day."_".$month."_".$hour."_".$minute;
			SQL_Query("INSERT INTO CatHistory (zeit,group_id) VALUES ('$zeit','$group_id')");
			
			
			$i=0;
			do {
				$i++;
				$size[$i]=$row[size];
				$nummer[$i]=$row[nummer];
			}
			while ($row=mysql_fetch_array($res));
		}
		for ($j=1;$j<=$i;$j++) {
			$res=SQL_Query("SELECT angebot.name AS name,
															 angebot.nummer AS nummer,
															 angebot.size AS size,
															 bestellung.anzahl AS anzahl,
															 bestellung.id AS id
															 FROM 
															 	CatProduct angebot,
															 	CatOrder bestellung
															 WHERE bestellung.angebot_id=angebot.id
															 	AND angebot.lieferant=$lieferant
															 	AND bestellung.bearbeitet=0
															 	AND bestellung.ausgeliefert=0
															 	AND bestellung.eingetroffen=0
															 	AND bestellung.wagen=0");
			while ($row=mysql_fetch_array($res)) {
				if (($size[$j]==$row[size])&&($nummer[$j]==$row[nummer])) {
					$anzahl[$j]+=$row[anzahl];
					$name[$j]=$row[name];
					SQL_Query("UPDATE CatOrder SET bearbeitet=1 WHERE id='$row[id]'");
					SQL_Query("INSERT INTO CatHistoryItems (bestellung_id,group_id,name,size,anzahl) VALUES ('$row[id]','$group_id','$row[name]','$row[size]','$row[anzahl]')");
					$usersql=SQL_Query ("SELECT user.name FROM user,CatOrder WHERE user.id = CatOrder.user_id AND CatOrder.id='$row[id]'");
					$user=mysql_fetch_array($usersql);
					$day=date(d);
					$month=date(m);
					$hour=date(H);
					$minute=date(i);
					//$orga=
					// In Log Datei schreiben 
					fputs($fp,"[$day.$month] um [$hour:$minute] Gruppenbestellung - Bestellung mit der id $row[id] von $user[0] auf Bearbeitet gestellt von $orga\n");
				}
			}	
		}
	}
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
	if ($mode=="group_bestellung") {
		// Es werden alle Bestellung eines Lieferanten gruppiert nach Art und Grösse mit der jeweiligen 
		// Anzahl aufgelistet und alle Bestellungen können gleichzeitig auf "bearbeitet" gesetzt werden...
		// Gleichzzeitig wird eine Druckbare Datei erzeugt die alle nötigen infos enthält.
		
		//Druckbare Datei anlegen:
		unlink($LS_BASEPATH."../includes/logs/bestellung.txt");
		$best=fopen($LS_BASEPATH."../includes/logs/bestellung.txt","a");
		
		$res=mysql_query("SELECT * FROM CatSupplier WHERE id='$lieferant'");
		$row=mysql_fetch_array($res);
		fputs($best,"Lieferant : $row[name] - Telefon : $row[telefon] - Kunden Nr. : $row[knr]\n\n");
		echo "<p class=\"content\">Lieferant: $row[name] - Telefon: $row[telefon] - Kunden Nr.: $row[knr]</p>";
		$res=SQL_Query("SELECT angebot.name AS name,
														 angebot.nummer AS nummer,
														 angebot.size AS size,
														 bestellung.anzahl AS anzahl
														 FROM 
														 	CatProduct angebot,
														 	CatOrder bestellung
														 WHERE bestellung.angebot_id=angebot.id
														 	AND angebot.lieferant=$lieferant
														 	AND bestellung.bearbeitet=0
														 	AND bestellung.ausgeliefert=0
														 	AND bestellung.eingetroffen=0
														 	AND bestellung.wagen=0
														 GROUP BY angebot.nummer,angebot.size");
		if ($row=mysql_fetch_array($res)) {
			$i=0;
			do {
				$i++;
				$size[$i]=$row[size];
				$nummer[$i]=$row[nummer];
			}
			while ($row=mysql_fetch_array($res));
		}
		?>
		<br><br>
		<form action="verwaltung_bestellung.php" method=POST>
		<?
		for ($j=1;$j<=$i;$j++) {
			$res=SQL_Query("SELECT angebot.name AS name,
															 angebot.nummer AS nummer,
															 angebot.size AS size,
															 bestellung.anzahl AS anzahl,
															 bestellung.id AS id
															 FROM 
															 	CatProduct angebot,
															 	CatOrder bestellung
															 WHERE bestellung.angebot_id=angebot.id
															 	AND angebot.lieferant=$lieferant
															 	AND bestellung.bearbeitet=0
															 	AND bestellung.ausgeliefert=0
															 	AND bestellung.eingetroffen=0
															 	AND bestellung.wagen=0");
			if ($row=mysql_fetch_array($res)) {
				do {
					if (($size[$j]==$row[size])&&($nummer[$j]==$row[nummer])) {
						$anzahl[$j]+=$row[anzahl];
						$name[$j]=$row[name];
					}
				}
				while ($row=mysql_fetch_array($res));
			}	
		}
		for ($j=1;$j<=$i;$j++) {
			if ($size[$j]==0)$sizetext="klein";
			if ($size[$j]==1)$sizetext="mittel";
			if ($size[$j]==2)$sizetext="gross";
			echo "Name : $name[$j] - Size : $sizetext - Anzahl : $anzahl[$j]<br>";
			fputs($best,"Name : $name[$j] - Size : $sizetext - Anzahl : $anzahl[$j]\n");
		}
		
		//Die einzelnen Bestellungen in die Druckbare Datei schreiben ...
		$res=SQL_Query("SELECT angebot.name AS name,
													angebot.nummer AS nummer,
													angebot.size AS size,
													angebot.preis AS preis,
													bestellung.anzahl AS anzahl,
													bestellung.id AS id,
													user.name AS username
													FROM
														user,
														CatProduct angebot,
														CatOrder bestellung
													WHERE bestellung.angebot_id=angebot.id
													 	AND bestellung.user_id=user.id
													 	AND angebot.lieferant=$lieferant
													 	AND bestellung.bearbeitet=0
													 	AND bestellung.ausgeliefert=0
													 	AND bestellung.eingetroffen=0
													 	AND bestellung.wagen=0");
		if ($row=mysql_fetch_array($res)) {
			do {
				if ($row[size]==0)$sizetext="klein";
				if ($row[size]==1)$sizetext="mittel";
				if ($row[size]==2)$sizetext="gross";
				fputs($best,"Gast : $row[username] -> Name : $row[name] - Size : $sizetext - Anzahl : $row[anzahl] für je $row[preis]\n");
			}
			while ($row=mysql_fetch_array($res));
		}														
		
		fclose($best);
		?>
		<input type="hidden" name="lieferant" value="<?echo"$lieferant";?>">
		<input type="hidden" name="mode" value="group_bestellung_status_setzen">
		<?
		if ($j<=1) echo "<p class=\"content\">Keine neuen Bestellungen</p>";
		else {
		?>
			<input type="submit" name="Submit" value="Diese Bestellungen auf bearbeitet setzen" class="form_btn">
			<br><a href="<?echo "$LS_BASEPATH";echo"../includes/logs/bestellung.txt";?>">Druckbare Datei</a><br>
		<?
		}
		?>
		</form>
		<?	
	}
	
	//Komplette Liste nicht immer anzeigen ...
	if ($mode!="group_bestellung") {
	// Suchen ...
	if ($suche=="") {
		?>
		<form action="verwaltung_bestellung.php" method=POST>
		<input type="hidden" name="order" value="<?echo"$order";?>">
		<input type="hidden" name="filter" value="<?echo"$filter";?>">
		<input type="text" name="suche" class="form_field"><input type="submit" name="Submit" value="Suchen" class="form_btn">
		</form>
		<?
	}
	else {
		?>
		<a href="verwaltung_bestellung.php?order=<?echo"$order";?>&direction=<?echo"$direction";?>&filter=<?echo"$filter";?>">Alle User</a> zeigen<br>
		<?
	}
	// Ende suchen !
	
	// Filtern :
	if ($filter=="") {
		$res=SQL_Query("SELECT * FROM CatSupplier");
		?>
		<form action="verwaltung_bestellung.php" method=POST>
		<input type="hidden" name="order" value="<?echo"$order";?>">
		<input type="hidden" name="suche" value="<?echo"$suche";?>">
		<select name="filter">
		<?
			if ($row=mysql_fetch_array($res)) {
				do {
					?>
			<option value="<?echo"$row[id]";?>"><?echo"$row[name]";?></option>
					<?
				}
				while ($row=mysql_fetch_array($res));
			}
		?>
		</select>
		<input type="submit" name="Submit" class="form_btn" value="Nach Lieferant Filtern">
		</form>
		<?
	}
	else {
		?>
		<p class="content"><a href="verwaltung_bestellung.php?order=<?echo"$order";?>&direction=<?echo"$direction";?>&suche=<?echo"$suche";?>">Alle Lieferanten</a> zeigen</p>
		<?
	}
	// ENDE Filtern!
	
	// Suchrichtung:
	if ($direction=="ASC") $direction="DESC";
	else $direction="ASC";
	if ($direction=="") $direction="ASC";
	if ($order=="") $order="bestellung.user_id";
	?>
	<br>
	<br>
	<p class="content">Aktuelle Bestellungen (Sortierrichtung durch klick auf Überschrifft):
	<table width="95%" class="liste">
		<tr>
			<td>
				<table width="100%" border="0">
					<tr>
						<th class="liste"><a href="verwaltung_bestellung.php?order=user_id&suche=<?echo"$suche";?>&filter=<?echo"$filter";?>&direction=<?echo"$direction";?>">Nick</a></th>
						<th class="liste"><a href="verwaltung_bestellung.php?order=date,hour,minute&suche=<?echo"$suche";?>&filter=<?echo"$filter";?>&direction=<?echo"$direction";?>">Zeit</a></th>
						<th class="liste"><a href="verwaltung_bestellung.php?order=lieferant.name&suche=<?echo"$suche";?>&filter=<?echo"$filter";?>&direction=<?echo"$direction";?>">Lieferant</a></th>
						<th class="liste"><a href="verwaltung_bestellung.php?order=angebot.nummer&suche=<?echo"$suche";?>&filter=<?echo"$filter";?>&direction=<?echo"$direction";?>">No</a></th>
						<th class="liste"><a href="verwaltung_bestellung.php?order=angebot.name,angebot.size&suche=<?echo"$suche";?>&filter=<?echo"$filter";?>&direction=<?echo"$direction";?>">Bestellung</a></th>
						<th class="liste"><a href="verwaltung_bestellung.php?order=preis&suche=<?echo"$suche";?>&filter=<?echo"$filter";?>&direction=<?echo"$direction";?>">Preis</a></th>
						<th class="liste"><a href="verwaltung_bestellung.php?order=anzahl&suche=<?echo"$suche";?>&filter=<?echo"$filter";?>&direction=<?echo"$direction";?>">Anzahl</a></th>
						<th class="liste"><a href="verwaltung_bestellung.php?order=bearbeitet&suche=<?echo"$suche";?>&filter=<?echo"$filter";?>&direction=<?echo"$direction";?>">Bearbeitet</a></th>
						<th class="liste"><a href="verwaltung_bestellung.php?order=eingetroffen&suche=<?echo"$suche";?>&filter=<?echo"$filter";?>&direction=<?echo"$direction";?>">Eingetroffen</a></th>
						<th class="liste" width="20"><a href="verwaltung_bestellung.php?order=ausgeliefert&suche=<?echo"$suche";?>&filter=<?echo"$filter";?>&direction=<?echo"$direction";?>">Ausgeliefert</a></th>
					</tr>
					<?
					if ($suche=="") {
						if ($filter=="") {
						$result=SQL_Query("SELECT 
																	bestellung.id AS id,
																	bestellung.user_id AS user_id,
																	UNIX_TIMESTAMP(bestellung.time) AS date,
																	bestellung.angebot_id AS angebot_id,
																	bestellung.bearbeitet AS bearbeitet,
																	bestellung.eingetroffen AS eingetroffen,
																	bestellung.ausgeliefert AS ausgeliefert,
																	bestellung.anzahl AS anzahl,
																	user.name AS user,
																	angebot.nummer AS nummer,
																	angebot.name AS angebot,
																	angebot.preis AS preis,
																	angebot.size AS size,
																	lieferant.name AS lieferant
																	FROM 
																		CatOrder bestellung,
																		user,
																		CatProduct angebot,
																		CatSupplier lieferant
																	WHERE bestellung.user_id=user.id
																		AND bestellung.ausgeliefert=0
																		AND bestellung.angebot_id=angebot.id
																		AND angebot.lieferant=lieferant.id
																		AND bestellung.wagen=0
																	ORDER BY $order $direction");
						}
						else {
						$result=SQL_Query("SELECT 
																	bestellung.id AS id,
																	bestellung.user_id AS user_id,
																	UNIX_TIMESTAMP(bestellung.time) AS date,
																	bestellung.angebot_id AS angebot_id,
																	bestellung.bearbeitet AS bearbeitet,
																	bestellung.eingetroffen AS eingetroffen,
																	bestellung.ausgeliefert AS ausgeliefert,
																	bestellung.anzahl AS anzahl,
																	user.name AS user,
																	angebot.nummer AS nummer,
																	angebot.name AS angebot,
																	angebot.preis AS preis,
																	angebot.size AS size,
																	lieferant.name AS lieferant
																	FROM 
																		CatOrder bestellung,
																		user,
																		CatProduct angebot,
																		CatSupplier lieferant
																	WHERE bestellung.user_id=user.id
																		AND bestellung.ausgeliefert=0
																		AND bestellung.angebot_id=angebot.id
																		AND angebot.lieferant=lieferant.id
																		AND bestellung.wagen=0
																		AND angebot.lieferant='$filter'
																	ORDER BY $order $direction");
						}
					}
					else {
						if ($filter=="") {
							$result=SQL_Query("SELECT 
																	bestellung.id AS id,
																	bestellung.user_id AS user_id,
																	UNIX_TIMESTAMP(bestellung.time) AS date,
																	bestellung.angebot_id AS angebot_id,
																	bestellung.bearbeitet AS bearbeitet,
																	bestellung.eingetroffen AS eingetroffen,
																	bestellung.ausgeliefert AS ausgeliefert,
																	bestellung.anzahl AS anzahl,
																	user.name AS user,
																	angebot.nummer AS nummer,
																	angebot.name AS angebot,
																	angebot.preis AS preis,
																	angebot.size AS size,
																	lieferant.name AS lieferant
																	FROM 
																		CatOrder bestellung,
																		user,
																		CatProduct angebot,
																		CatSupplier lieferant
																	WHERE bestellung.user_id=user.id
																		AND bestellung.ausgeliefert=0
																		AND bestellung.angebot_id=angebot.id
																		AND angebot.lieferant=lieferant.id
																		AND bestellung.wagen=0
																		AND (angebot.name LIKE '%$suche%'
																		 OR user.name LIKE '%$suche%')
																	ORDER BY $order $direction");
						}
						else {
							$result=SQL_Query("SELECT 
																	bestellung.id AS id,
																	bestellung.user_id AS user_id,
																	UNIX_TIMESTAMP(bestellung.date) AS date,
																	bestellung.angebot_id AS angebot_id,
																	bestellung.bearbeitet AS bearbeitet,
																	bestellung.eingetroffen AS eingetroffen,
																	bestellung.ausgeliefert AS ausgeliefert,
																	bestellung.anzahl AS anzahl,
																	user.name AS user,
																	angebot.nummer AS nummer,
																	angebot.name AS angebot,
																	angebot.preis AS preis,
																	angebot.size AS size,
																	lieferant.name AS lieferant
																	FROM 
																		CatOrder bestellung,
																		user,
																		CatProduct angebot,
																		CatSupplier lieferant
																	WHERE bestellung.user_id=user.id
																		AND bestellung.ausgeliefert=0
																		AND bestellung.angebot_id=angebot.id
																		AND angebot.lieferant=lieferant.id
																		AND bestellung.wagen=0
																		AND angebot.lieferant='$filter'
																		AND angebot.name LIKE '%$suche%'
																		 OR user.name LIKE '%$suche%'
																	ORDER BY $order $direction");
						}
					}	
					if ($row=mysql_fetch_array($result)) {
						do {
							if ($row[size]==0)$size="klein";
							if ($row[size]==1)$size="mittel";
							if ($row[size]==2)$size="gross";
							?>
							<tr>
								<td class="liste"><?echo"$row[user]";?></td>
								<td class="liste"><?echo DisplayDate($row[date]);?></td>
								<td class="liste"><?echo"$row[lieferant]";?></td>
								<td class="liste"><?echo"$row[nummer]";?></td>
								<td class="liste"><?echo"$row[angebot] - $size";?></td>
								<td class="liste"><div align="right"><?echo"$row[preis]";?></div></td>
								<td class="liste"><div align="right"><?echo"$row[anzahl]";?></div></td>
								<td><div align="center">
								<?
								if ($row[bearbeitet]=="1") {
									?>
									<a href="verwaltung_bestellung.php?mode=change_bearbeitet&id=<?echo"$row[id]";?>&old=<?echo"$row[bearbeitet]";?>&user=<?echo"$row[user]";?>&filter=<?echo"$filter";?>"><img src="green.gif" border="0" alt="Status auf nicht bearbeitet ändern"></a>
									<?
								}
								else {
									$row[bearbeitet]=0;
									?>
									<a href="verwaltung_bestellung.php?mode=change_bearbeitet&id=<?echo"$row[id]";?>&old=<?echo"$row[bearbeitet]";?>&user=<?echo"$row[user]";?>&filter=<?echo"$filter";?>"><img src="red.gif" border="0" alt="Status auf bearbeitet ändern"></a>
									<?
								}
								?>
								</div></td>
								<td><div align="center">
								<?
								if ($row[eingetroffen]=="1") {
									?>
									<a href="verwaltung_bestellung.php?mode=change_eingetroffen&id=<?echo"$row[id]";?>&old=<?echo"$row[eingetroffen]";?>&user=<?echo"$row[user]";?>&filter=<?echo"$filter";?>"><img src="green.gif" border="0" alt="Status auf nicht eingetroffen ändern"></a>
									<?
								}
								else {
									$row[eingetroffen]=0;
									?>
									<a href="verwaltung_bestellung.php?mode=change_eingetroffen&id=<?echo"$row[id]";?>&old=<?echo"$row[eingetroffen]";?>&user=<?echo"$row[user]";?>&filter=<?echo"$filter";?>"><img src="red.gif" border="0" alt="Status auf eingetroffen ändern"></a>
									<?
								}
								?>
								</div></td>
								<td><div align="center">
								<?
								if ($row[ausgeliefert]=="1") {
									?>
									<a href="verwaltung_bestellung.php?mode=do_ausgeliefert&id=<?echo"$row[id]";?>&old=<?echo"$row[ausgeliefert]";?>&user=<?echo"$row[user]";?>&filter=<?echo"$filter";?>"><img src="green.gif" border="0" alt="Status auf nicht ausgeliefert setzen"></a>
									<?
								}
								else {
									$row[ausgeliefert]=0;
									?>
									<a href="verwaltung_bestellung.php?mode=do_ausgeliefert&id=<?echo"$row[id]";?>&old=<?echo"$row[ausgeliefert]";?>&user=<?echo"$row[user]";?>&filter=<?echo"$filter";?>"><img src="red.gif" border="0" alt="Status auf ausgeliefert setzen"></a>
									<?
								}
								?>
								</div></td>
								<td><? NavPrintDel('verwaltung_bestellung.php?mode=del&id='.$row[id].'&user='.$row[user]); ?></td>
							</tr>
						<?
						}
						while ($row=mysql_fetch_array($result));
					}
					?>
				</table>
			</td>
		</tr>
	</table>
	<br>
	<?
	$res=SQL_Query("SELECT * FROM CatSupplier");
	?>
	<form action="verwaltung_bestellung.php" method=POST>
	<input type="hidden" name="mode" value="group_bestellung">
	<input type="hidden" name="order" value="<?echo"$order";?>">
	<input type="hidden" name="suche" value="<?echo"$suche";?>">
	<p class="content">Bestellungen gruppiert nach 
	<select name="lieferant" class="form_field">
	<?
		if ($row=mysql_fetch_array($res)) {
			do {
				?>
		<option value="<?echo"$row[id]";?>"><?echo"$row[name]";?></option>
				<?
			}
			while ($row=mysql_fetch_array($res));
		}
	?>
	</select> bearbeiten.
	<input type="submit" name="Submit" class="form_btn" value="Go">
	</form>
	<?	
}
//letzte Klammer für die Anzeige der Liste (je nach mode nicht alles auflisten)
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
