<?
	$LS_BASEPATH = '../../';
	include $LS_BASEPATH.'../includes/ls_base.inc';
	StartPage("Bestellungen Verwalten");
	
	user_auth_ex(AUTH_TEAM, 0, TEAM_CATERING, True);
	

	$fp=fopen($LS_BASEPATH."../includes/logs/catering_orga.txt","a");

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
	if ($mode=="del") {
		SQL_Query("DELETE FROM CatHistory WHERE group_id='$id'");
		SQL_Query("DELETE FROM CatHistoryItems WHERE group_id='$id'");
	}
	if ($mode=="undo_single") {
		SQL_Query("UPDATE CatOrder SET bearbeitet=0 WHERE id='$id'");
		SQL_Query("DELETE FROM CatHistoryItems WHERE bestellung_id='$id'");
	}
	if ($mode=="undo") {
		$res=SQL_Query("SELECT bestellung_id FROM CatHistoryItems WHERE group_id='$id'");
		if ($row=mysql_fetch_array($res)) {
			do {
				SQL_Query("UPDATE CatOrder SET bearbeitet=0 WHERE id='$row[bestellung_id]'");
			}
			while ($row=mysql_fetch_array($res));
		}
		SQL_Query("DELETE FROM CatHistoryItems WHERE group_id='$id'");
		SQL_Query("DELETE FROM CatHistory WHERE group_id='$id'");
	}
	if (($mode=="info")||($mode=="undo_single")) {
		?>
		<br>
		<br>
		Alle Bestellten Produkte von <?echo"$zeit";?>:
		<table width="95%" class="liste">
			<tr>
				<td>
					<table width="100%" border="0">
						<tr>
							<th class="liste">Name</th>
							<th class="liste">Groesse</th>
							<th class="liste">Anzahl</th>
							<th class="liste"></th>
						</tr>
						<?
						$res=SQL_Query("SELECT * FROM CatHistoryItems WHERE group_id='$id'");
						if ($row=mysql_fetch_array($res)) {
							do {
							if ($row[size]==0) $size="klein";
							if ($row[size]==1) $size="mittel";
							if ($row[size]==2) $size="gross";
							?>
						<tr>
							<td class="liste"><?echo"$row[name]";?></td>
							<td class="liste"><?echo"$size";?></td>
							<td class="liste"><?echo"$row[anzahl]";?></td>
							<td class="liste"><a href="verwaltung_bestellung_history.php?mode=undo_single&id=<?echo"$row[bestellung_id]";?>&zeit=<?echo"$zeit";?>">Undo</a></td>						
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
	
	if (($mode!="info")&&($mode!="undo_single")) {
	?>
	<br>
	<p class="content">Alle durchgeführten bestellungen (gruppiert nach ihrer zeitlichen Aufgabe beim Lieferanten):<br>
	<table width="95%" class="liste">
		<tr>
			<td>
				<table width="100%" border="0">
					<tr>
						<th class="liste">Bestellung</th>
						<th class="liste">Info</th>
						<th class="liste"></th>
						<th class="liste"></th>
					</tr>
					<?
					$res=SQL_Query("SELECT * FROM CatHistory");
					while($row=mysql_fetch_array($res)) {
						?>
					<tr>
						<td class="liste"><?echo"$row[zeit]";?></td>
						<td class="liste"><a href="verwaltung_bestellung_history.php?mode=info&id=<?echo"$row[group_id]";?>&zeit=<?echo"$row[zeit]";?>">Info</a></td>
						<td class="liste"><a href="verwaltung_bestellung_history.php?mode=undo&id=<?echo"$row[group_id]";?>">Undo</a></td>
						<td class="liste"><a href="verwaltung_bestellung_history.php?mode=del&id=<?echo"$row[group_id]";?>">DEL</a></td>						
					</tr>
						<?
					}
					?>
				</table>
			</td>
		</tr>
	</table>
	<?
	}

	fclose($fp);

EndPage();
?>
