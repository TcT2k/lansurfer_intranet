<?
	$LS_BASEPATH = '../../';
	include $LS_BASEPATH.'../includes/ls_base.inc';
	StartPage("Konten Verwaltung");
	if (user_auth_ex(AUTH_TEAM, 0, TEAM_CATERING, FALSE)) {
	
	if ($mode=="submit_edit") {
		$kontostand+=$alter_kontostand;
		SQL_Query("UPDATE user SET kontostand=$kontostand WHERE id='$id'");
		?>
		<br>Der neue Kontostand beträgt <?echo"$kontostand";echo LS_CATERING_CURRENCY;?>
		<?
	}
	if ($mode=="submit_overall") {
		SQL_Query("UPDATE user SET kontostand=99999");
		?>
		<br>Alle Konten wurden auf 99.999 <?echo LS_CATERING_CURRENCY;?> gesetzt!
		<?
	}
	if ($mode=="edit") {
		$res=SQL_Query("SELECT id,name,realname1,realname2,kontostand FROM user WHERE id='$id'");
		$row=mysql_fetch_array($res);
		?>
		<br><br><br>
		Konto von <?echo "$row[realname1], $row[realname2] ($row[name]) :<br>"?>
		Derzeitiger Kontostand : <?echo "$row[kontostand]"?><br><br>
		<form action="verwaltung_konten.php" method=POST>
		<input type="hidden" name="mode" value="submit_edit">
		<input type="hidden" name="id" value="<?echo"$row[id]";?>">
		<input type="hidden" name="alter_kontostand" value="<?echo"$row[kontostand]";?>">
		Folgenden Betrag <b>hinzufügen</b> :  <input type="text" name="kontostand"  class="form_field" size="5">
		<input type="submit" name="Submit" value="Buchen" class="form_btn">
		</form>
		<br>
		<br>
		<br>
		<a href="verwaltung_konten.php">Zurück</a> zur Verwaltung ...<br>
		<?
	}
	else {
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
		<a class="content" href="verwaltung_konten.php?mode=submit_overall">Alle Konten auf 99.999 <?echo LS_CATERING_CURRENCY;?> setzen</a>
		<form action="verwaltung_konten.php" method=POST>
		<input type="hidden" name="order" value="<?echo"$order";?>">
		<input type="text" name="suche" class="form_field"><input type="submit" name="Submit" value="Suchen" class="form_btn">
		</form>
		<?
	}
	else {
		?>
		<a href="verwaltung_konten.php?order=<?echo"$order";?>&direction=<?echo"$direction";?>">Alle User</a> zeigen<br>
		<?
	}
	$directionold=$direction;
	if ($direction=="ASC") $direction="DESC";
	else $direction="ASC";
	if ($direction=="") $direction="ASC";
	if ($order=="") $order="name";
	?>
	<br>
	<br>
	<table width="95%" class="liste">
		<tr>
			<td>
				<table width="100%" border="0">
					<tr>
						<th class="liste"><a href="verwaltung_konten.php?order=name&suche=<?echo"$suche";?>&direction=<?echo"$direction";?>">Nick</a></th>
						<th class="liste"><a href="verwaltung_konten.php?order=realname1&suche=<?echo"$suche";?>&direction=<?echo"$direction";?>">Vorname</a></th>
						<th class="liste"><a href="verwaltung_konten.php?order=realname2&suche=<?echo"$suche";?>&direction=<?echo"$direction";?>">Nachname</a></th>
						<th class="liste" width="20"><a href="verwaltung_konten.php?order=kontostand&suche=<?echo"$suche";?>&direction=<?echo"$direction";?>">Konto</a></th>
						<? 
							if ($suche!="") { 
							?>
						<th class="liste">$</th>
							<? } ?>
					</tr>
					<?
					if (!$suche) 
						$result=SQL_Query("SELECT id,name,realname1,realname2,kontostand FROM user ORDER BY $order $direction");
					else
						$result=SQL_Query("SELECT id,name,realname1,realname2,kontostand FROM user WHERE name LIKE '%$suche%' OR realname1 LIKE '%$suche%' OR realname2 LIKE '%$suche%' ORDER BY $order $direction");
						
					if ($row=mysql_fetch_array($result)) {
						do {
							$row[kontostand]=number_format($row[kontostand],2,",",".");
							?>
							<tr>
								<td class="liste"><a href="verwaltung_konten.php?mode=edit&id=<?echo"$row[id]";?>"><?echo HTMLStr($row[name]); ?></a></td>
								<td class="liste"><?echo"$row[realname1]";?></td>
								<td class="liste"><?echo"$row[realname2]";?></td>
								<?if (($row[kontostand]=="")||($row[kontostand]=="0")) {
										?>
										<td class="liste"><div align="center"><img src="red.gif">
										<?
									}
									else {
										?>
										<td class="liste"><div align="right"><?echo "$row[kontostand]";?>
										<?
									}
								?>								
								</div></td>
								<?if ($suche!="") {
								?>
								<td class="form"><div align="center">
								<form action="abbuchen.php" method=POST>
								<input type="hidden" name=id value="<?echo "$row[id]";?>">
								<input type="hidden" name=mode value="bezahlung_intern">
								<select name="angebot_id" class="form_field">
								<?
								$res=SQL_Query("SELECT id,name FROM CatProduct WHERE vorhanden='1'");
								if ($row2=mysql_fetch_array($res)) {
									do {
										echo "<option value=\"$row2[id]\">$row2[name]</option>";
									}
									while ($row2=mysql_fetch_array($res));
								}
								?>
								</select>
								<input type="text" name="anzahl" class="form_field" size="3" value="1">
								<input type="submit" name="Submit" value="$" class="form_btn">
								</form>
								</div>
								</td>
								<?
								}
								?>
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
