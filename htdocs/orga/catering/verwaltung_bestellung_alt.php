<?
	$LS_BASEPATH = '../../';
	include $LS_BASEPATH.'../includes/ls_base.inc';
	StartPage("Bestellungen Verwalten");

	if (user_auth_ex(AUTH_TEAM, 0, TEAM_CATERING, FALSE)) {
	$fp=fopen($LS_BASEPATH."../includes/logs/catering_orga.txt","a");

	$orga=$user_current[name];	

	if ($mode=="change_bearbeitet") {
		if ($old==1) $new=0;
		else $new=1;
		SQL_Query("UPDATE CatOrder SET bearbeitet=$new WHERE id='$id'");
		$day=date(d);
		$month=date(m);
		$hour=date(H);
		$minute=date(i);
		if ($new==1) fputs($fp, date('Y-m-d H:i:s')." Bestellung von $user auf Bearbeitet gestellt von $orga\n");
		else fputs($fp, date('Y-m-d H:i:s')." Bestellung von $user auf nicht Bearbeitet gestellt von $orga\n");	
	}	
	if ($mode=="change_eingetroffen") {
		if ($old==1) $new=0;
		else $new=1;
		SQL_Query("UPDATE CatOrder SET eingetroffen=$new WHERE id='$id'");
		$day=date(d);
		$month=date(m);
		$hour=date(H);
		$minute=date(i);
		if ($new==1) fputs($fp, date('Y-m-d H:i:s')." Bestellung von $user auf eingetroffen gestellt von $orga\n");
		else  fputs($fp, date('Y-m-d H:i:s')." Bestellung von $user auf nicht eingetroffen gestellt von $orga\n");	
	}	
	if ($mode=="del") {
		mysql_query ("DELETE FROM CatOrder WHERE id='$id'");
		$day=date(d);
		$month=date(m);
		$hour=date(H);
		$minute=date(i);
		fputs($fp, date('Y-m-d H:i:s')." Bestellung von $user wurde von $orga gelöscht\n");
	}
	if ($mode=="do_ausgeliefert") {
		if ($old==1) {
			SQL_Query("UPDATE CatOrder SET ausgeliefert=0 WHERE id='$id'");
		}
		else {
			$day=date(d);
			$month=date(m);
			$hour=date(H);
			$minute=date(i);
			echo "ID $id";
			SQL_Query("UPDATE CatOrder SET ausgeliefert=1 WHERE id='$id'");
			fputs($fp, date('Y-m-d H:i:s')." Bestellung von $user wurde von $orga ausgeliefert. Der Kontostand wurde auf $new zurückgesetzt. Die Bestellung wurde gelöscht\n");			
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
	if ($suche=="") {
		?>
		<form action="verwaltung_bestellung_alt.php" method=POST>
		<input type="hidden" name="order" value="<?echo"$order";?>">
		<input type="hidden" name="filter" value="<?echo"$filter";?>">
		<input type="text" name="suche" class="form_field"><input type="submit" name="Submit" value="Suchen" class="form_btn">
		</form>
		<?
	}
	else {
		?>
		<a href="verwaltung_bestellung_alt.php?order=<?echo"$order";?>&direction=<?echo"$direction";?>&filter=<?echo"$filter";?>">Alle User</a> zeigen<br>
		<?
	}
	if ($filter=="") {
		$res=SQL_Query("SELECT * FROM CatSupplier");
		?>
		<form action="verwaltung_bestellung_alt.php" method=POST>
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
		<p class="content"><a href="verwaltung_bestellung_alt.php?order=<?echo"$order";?>&direction=<?echo"$direction";?>&suche=<?echo"$suche";?>">Alle Lieferanten</a> zeigen</p>
		<?
	}
	if ($direction=="ASC") $direction="DESC";
	else $direction="ASC";
	if ($direction=="") $direction="ASC";
	if ($order=="") $order="bestellung.user_id";
	?>
	<br>
	<br>
	<p class="content">Alte Bestellungen (Sortierrichtung durch klick auf Überschrifft):
	<table width="95%" class="liste">
		<tr>
			<td>
				<table width="100%" border="0">
					<tr>
						<th class="liste"><a href="verwaltung_bestellung_alt.php?order=user_id&suche=<?echo"$suche";?>&direction=<?echo"$direction";?>">Nick</a></th>
						<th class="liste"><font color="#0000FF">Zeit</font></th>
						<th class="liste"><a href="verwaltung_bestellung_alt.php?order=angebot.nummer&suche=<?echo"$suche";?>&direction=<?echo"$direction";?>">No</a></th>
						<th class="liste"><a href="verwaltung_bestellung_alt.php?order=angebot.name,angebot.size&suche=<?echo"$suche";?>&direction=<?echo"$direction";?>">Bestellung</a></th>
						<th class="liste"><a href="verwaltung_bestellung_alt.php?order=preis&suche=<?echo"$suche";?>&direction=<?echo"$direction";?>">Preis</a></th>
						<th class="liste"><a href="verwaltung_bestellung_alt.php?order=anzahl&suche=<?echo"$suche";?>&direction=<?echo"$direction";?>">Anzahl</a></th>
						<th class="liste"><a href="verwaltung_bestellung_alt.php?order=bearbeitet&suche=<?echo"$suche";?>&direction=<?echo"$direction";?>">Bearbeitet</a></th>
						<th class="liste"><a href="verwaltung_bestellung_alt.php?order=eingetroffen&suche=<?echo"$suche";?>&direction=<?echo"$direction";?>">Eingetroffen</a></th>
						<th class="liste" width="20"><a href="verwaltung_bestellung_alt.php?order=ausgeliefert&suche=<?echo"$suche";?>&direction=<?echo"$direction";?>">Ausgeliefert</a></th>
						<th class="liste"></th>
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
																		AND bestellung.ausgeliefert=1
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
																	FROM CatOrder bestellung,user, CatProduct angebot, CatSupplier lieferant
																	WHERE bestellung.user_id=user.id
																		AND bestellung.ausgeliefert=1
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
																		AND bestellung.ausgeliefert=1
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
																	FROM CatOrder bestellung,user, CatProduct angebot, CatSupplier lieferant
																	WHERE bestellung.user_id=user.id
																		AND bestellung.ausgeliefert=1
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
								<td class="liste"><?echo"$row[nummer]";?></td>
								<td class="liste"><?echo"$row[angebot] - $size";?></td>
								<td class="liste"><div align="right"><?echo"$row[preis]";?></div></td>
								<td class="liste"><div align="right"><?echo"$row[anzahl]";?></div></td>
								<td><div align="center">
								<?
								if ($row[bearbeitet]=="1") {
									?>
									<a href="verwaltung_bestellung_alt.php?mode=change_bearbeitet&id=<?echo"$row[id]";?>&old=<?echo"$row[bearbeitet]";?>&user=<?echo"$row[user]";?>"><img src="green.gif" border="0" alt="Status auf nicht bearbeitet ändern"></a>
									<?
								}
								else {
									$row[bearbeitet]=0;
									?>
									<a href="verwaltung_bestellung_alt.php?mode=change_bearbeitet&id=<?echo"$row[id]";?>&old=<?echo"$row[bearbeitet]";?>&user=<?echo"$row[user]";?>"><img src="red.gif" border="0" alt="Status auf bearbeitet ändern"></a>
									<?
								}
								?>
								</div></td>
								<td><div align="center">
								<?
								if ($row[eingetroffen]=="1") {
									?>
									<a href="verwaltung_bestellung_alt.php?mode=change_eingetroffen&id=<?echo"$row[id]";?>&old=<?echo"$row[eingetroffen]";?>&user=<?echo"$row[user]";?>"><img src="green.gif" border="0" alt="Status auf nicht eingetroffen ändern"></a>
									<?
								}
								else {
									$row[eingetroffen]=0;
									?>
									<a href="verwaltung_bestellung_alt.php?mode=change_eingetroffen&id=<?echo"$row[id]";?>&old=<?echo"$row[eingetroffen]";?>&user=<?echo"$row[user]";?>"><img src="red.gif" border="0" alt="Status auf eingetroffen ändern"></a>
									<?
								}
								?>
								</div></td>
								<td><div align="center">
								<?
								if ($row[ausgeliefert]=="1") {
									?>
									<a href="verwaltung_bestellung_alt.php?mode=do_ausgeliefert&id=<?echo"$row[id]";?>&old=<?echo"$row[ausgeliefert]";?>&user=<?echo"$row[user]";?>"><img src="green.gif" border="0" alt="Status auf nicht ausgeliefert setzen"></a>
									<?
								}
								else {
									$row[ausgeliefert]=0;
									?>
									<a href="verwaltung_bestellung_alt.php?mode=do_ausgeliefert&id=<?echo"$row[id]";?>&old=<?echo"$row[ausgeliefert]";?>&user=<?echo"$row[user]";?>"><img src="red.gif" border="0" alt="Status auf ausgeliefert setzen"></a>
									<?
								}
								?>
								</div></td>
								<td><div align="center"><a href="verwaltung_bestellung_alt.php?mode=del&id=<?echo"$row[id]";?>&user=<?echo"$row[user]";?>""><img src="delete_button.gif" border="0" alt="Auftrag Löschen"></a></div></td>
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
	<br>
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
