<?
	// Copyright (c) 2001 Henrik 'Hotkey' Brinkmann  Email: hotkey@cncev.de

	$LS_BASEPATH = '../../';
	include $LS_BASEPATH.'../includes/lsi_base.inc';
	StartPage("Bestellung Statistiken");

if (user_auth_ex(AUTH_TEAM, 0, TEAM_CATERING, FALSE)) {
	//Log Datei Festlegen :
	$fp=fopen($LS_BASEPATH."../includes/logs/catering_orga.log","a");

	$orga=$user_current[name];	
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
	$res=SQL_Query("SELECT CatProduct.name AS name,
												 CatProduct.preis AS preis,
												 CatProduct.vorhanden AS vorhanden,
												 CatProduct.lieferant AS lieferant,
												 CatStats.anzahl AS anzahl
												 FROM CatProduct,CatStats
												 WHERE CatProduct.id=CatStats.angebot_id
												 ORDER BY CatProduct.lieferant");
	if ($row=mysql_fetch_array($res)) {
		do {
			//Umsätze ermitteln ...
			$umsatz_gesamt+=$row[preis]*$row[anzahl];
			if ($row[vorhanden]==1) $umsatz_intern+=$row[preis]*$row[anzahl];
			else $umsatz_extern+=$row[preis]*$row[anzahl];
		}
		while ($row=mysql_fetch_array($res));
	}
	?>
	<div align="left">
	<?
	echo "Umsatz intern : $umsatz_intern <br>";
	echo "Umsatz extern : $umsatz_extern <br>";	
	echo "Umsatz gesamt : $umsatz_gesamt <br>";
	?>
	</div><div align="center">
	<br>
	<?
	//Die Umsätze nach den Lieferanten sortiert anzeigen
	$res=SQL_Query("SELECT id,name FROM CatSupplier");
	if ($row=mysql_fetch_array($res)) {
		$i=0;
		do {
			$lieferant[$i]=$row[id];
			$lieferant_name[$i]=$row[name];
			$i++;
		}
		while ($row=mysql_fetch_array($res));
	}
	$i--;
	
	for ($j=0;$j<=$i;$j++) {
		$id=$lieferant[$j];
		?>
		<br><br></div><div align="left">
		<?
		echo "$lieferant_name[$j]<br><br>";
		
		// Suchrichtung:
		if ($direction[$j]=="ASC") $direction[$j]="DESC";
		else $direction[$j]="ASC";
		if ($direction[$j]=="") $direction[$j]="ASC";
		if ($order[$j]=="") $order[$j]="s.anzahl";
		$currentorder=$order[$j];
		$currentdirection=$direction[$j];
		?>
		</div><div align="center">
		<table width="88%" class="liste">
			<tr>
				<td class="liste">
					<table width="100%" border="0">
						<tr>
							<th width="22%" class="liste"><div align="center"><a href="verwaltung_stats.php?order[<?echo"$j";?>]=p.name&direction[<?echo"$j";?>]=<?echo"$direction[$j]";?>">Name</a></div></th>
							<th width="22%" class="liste"><div align="center"><a href="verwaltung_stats.php?order[<?echo"$j";?>]=p.preis&direction[<?echo"$j";?>]=<?echo"$direction[$j]";?>">Preis</a></div></th>
							<th width="22%" class="liste"><div align="center"><a href="verwaltung_stats.php?order[<?echo"$j";?>]=s.anzahl&direction[<?echo"$j";?>]=<?echo"$direction[$j]";?>">Anzahl</a></div></th>
							<th width="22%" class="liste"><div align="center">Umsatz</th>
						</tr>
					<?
		$res=SQL_Query("SELECT p.preis AS preis,
											p.name AS name,
											s.anzahl AS anzahl,
											l.name AS lieferant
							 				FROM
							 				CatProduct p,
							 				CatStats s,
							 				CatSupplier l
							 				WHERE
							 				s.angebot_id = p.id AND p.lieferant = l.id AND l.id = '$id' 
							 				ORDER BY $currentorder $currentdirection");
		if ($row=mysql_fetch_array($res)) {
			do {
			$umsatz=$row[anzahl]*$row[preis];
			?>
						<tr>
							<td class="liste"><?echo"$row[name]";?></td>
							<td class="liste"><div align="right"><?echo"$row[preis]";?></div></td>
							<td class="liste"><div align="right"><?echo"$row[anzahl]";?></div></td>
							<td class="liste"><div align="right"><?echo"$umsatz";?></div></td>
						</tr>
			<?
			}
			while ($row=mysql_fetch_array($res));
		}
		?>
					</table>
				</td>
			</tr>
		</table>		
		<?
	}			
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