<?
	// Copyright (c) 2001 Henrik 'Hotkey' Brinkmann  Email: hotkey@cncev.de

	$LS_BASEPATH = '../../';
	include $LS_BASEPATH.'../includes/lsi_base.inc';

	StartPage("Angebots Verwaltung");

	user_auth_ex(AUTH_TEAM, 0, TEAM_CATERING, TRUE);

?>
<div align="center">
<?
if ($mode=="new_currency") {
	SQL_Query("INSERT INTO CatCurrency (name) VALUES ('$name')");
}
if ($mode=="change_currency") {
	SQL_Query("UPDATE CatCurrency SET name='$name' WHERE id='1'");
}

if ($mode=="submit_edit") {
	$locale_info = localeconv();
	$preis = str_replace($locale_info['decimal_point'], '.', $preis);
	
	if (($name=="")||($beschreibung=="")||($preis=="")) {
		LS_Error("Nicht alle Felder ausgefüllt");
	}
	else {
		SQL_Query("UPDATE CatProduct SET name='$name', nummer='$nummer',beschreibung='$beschreibung', 
		  preis='$preis', lieferant='$lieferant', vorhanden='$vorhanden', size='$size' WHERE id='$id'");
	}
}
elseif ($mode=="submit_edit_lieferant") {
	if ($name=="") {
		LS_Error("Nicht alle Felder ausgefüllt");
	}
	else {
		SQL_Query("UPDATE CatSupplier SET name='$name' WHERE id='$id'");
		SQL_Query("UPDATE CatSupplier SET telefon='$telefon' WHERE id='$id'");
		SQL_Query("UPDATE CatSupplier SET knr='$knr' WHERE id='$id'");
	}
}
elseif ($mode=="del") {
	SQL_Query("DELETE FROM CatProduct WHERE id='$id'");
	echo "Das Angebot mit der ID = $id wurde gelöscht";
}
elseif ($mode=="del_lieferant") {
	SQL_Query("DELETE FROM CatSupplier WHERE id='$id'");
	echo "Der Lieferant mit der ID = $id wurde gelöscht";
}
elseif ($mode=="add_angebot") {
	if (($name=="")||($beschreibung=="")||($preis=="")) {
		LS_Error("Nicht alle Felder ausgefüllt");
	}
	else {
		SQL_Query("INSERT INTO CatProduct (name,beschreibung,preis,vorhanden,lieferant,nummer,size) VALUES ('$name','$beschreibung','$preis','$vorhanden','$lieferant','$nummer','$size')");
	}
}
elseif ($mode=="add_lieferant") {
	if ($name=="") {
		LS_Error("Nicht alle Felder ausgefüllt");
	}
	else {
		SQL_Query ("INSERT INTO CatSupplier (name,telefon,knr) VALUES ('$name','$telefon','$knr')");
	}
}
elseif ($mode=="edit_lieferant") {
	$result=SQL_Query("SELECT * FROM CatSupplier WHERE id='$id'");
	$row=mysql_fetch_array($result);
	?>
		<form action="verwaltung_angebot.php" method=POST>
		<input type="hidden" name="mode" value="submit_edit_lieferant">
		<input type="hidden" name="id" value="<?echo"$row[id]";?>">
		<table class="form" width="95%" border="1" cellspacing="0" cellpadding="0" bordercolor="#000000">
			<tr>
				<td class=form>
					<table width="100%" border="0">
						<tr>
						 <td class=form>Name</td>
						 <td class=form><input type"text" name="name" value="<?echo"$row[name]";?>"></td>
						</tr>
						<tr>
						 <td class=form>Telefon</td>
						 <td class=form><input type"text" name="telefon" value="<?echo"$row[telefon]";?>"></td>
						</tr>
						<tr>
						 <td class=form>Zugewiesene Kunden Nr.</td>
						 <td class=form><input type"text" name="knr" value="<?echo"$row[knr]";?>"></td>
						</tr>
					</table>
				<input type="submit" name="Submit" class="form_btn" value="Änderungen übernehmen">
				</td>
			</tr>
		</table>
		</form>
	<?
}
elseif ($mode=="edit") {

	$result=SQL_Query("SELECT * FROM CatProduct WHERE id='$id'");
	$row=mysql_fetch_array($result);
	$row[preis]= FloatToCurrency($row[preis], true);
	?>
		<form action="verwaltung_angebot.php" method=POST>
		<input type="hidden" name="mode" value="submit_edit">
		<input type="hidden" name="id" value="<?echo"$row[id]";?>">
		<table class=form width="95%" border="1" cellspacing="0" cellpadding="0" bordercolor="#000000">
			<tr>
				<td class=form>
					<table width="100%" border="0">
						<tr>
						 <td class=form>Nummer</td>
						 <td class=form><input type"text" name="nummer" value="<?echo"$row[nummer]";?>"></td>
						</tr>
						<tr>
						 <td class=form>Name</td>
						 <td class=form><input type"text" name="name" value="<?echo"$row[name]";?>"></td>
						</tr>
						<tr>
							<td class=form>Beschreibung</td>
							<td class=form><input type="text" name="beschreibung" value="<?echo"$row[beschreibung]";?>"></td>
						</tr>
						<tr>
							<td class=form>Grösse</td>
							<td class=form>
								<select name="size" class="form_field">
									<option value="<?echo $row[size];?>">keine Änderung</option>
									<option value="0">klein</option>
									<option value="1">mittel</option>
									<option value="2">gross</option>
								</select>
							</td>
						</tr>
						<tr>
							<td class=form>Preis (Pfennige durch '.' trennnen)</td>
							<td class=form><input type="text" name="preis" size="5" value="<?echo $row[preis];?>"></td>
						</tr>
						<tr>
							<td class=form>Lieferant</td>
							<td class=form>
								<select name="lieferant" class="form_field">
									<option value="<?echo"$row[lieferant]";?>">keine Änderung</option>
									<?
										$res=SQL_Query("SELECT * FROM CatSupplier");
										while ($row=mysql_fetch_array($res)) {
											?>						
									<option value="<?echo "$row[id]";?>"><?echo"$row[name]";?></option>
											<?
										}
									?>
								</select>
							</td>
						</tr>
						<tr>
							<td class=form>Intern/Extern</td>
							<td class=form>
								<select name="vorhanden">
									<option value="<?echo"$row[vorhanden]";?>">keine Änderung</option>
									<option value="0">extern</option>
									<option value="1">intern</option>
								</select>
							</td>
						</tr>
					</table>
					<input type="submit" name="Submit" class="form_btn" value="&Auml;nderungen übernehmen">
				</td>
			</tr>
		</table>
		</form>
		<br>
		<br>
		<a href="verwaltung_angebot.php">Zur&uuml;ck</a> zur Angebots Verwaltung ...
	<?
}
if (($mode!="edit")&&($mode!="edit_lieferant")) {
?>

<br>
<br>
<table width="95%" class="liste">
	<tr>
		<td class=liste>
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
Angebote :
			<table width="100%">
				<tr>
					<th class=liste>Lieferant</th>
					<th class=liste>No.</th>
					<th class=liste>Grösse</th>
					<th class=liste>Name</th>
					<th class=liste>Beschreibung</th>
					<th class=liste>Preis</th>
					<th class=liste>Typ</th>
				</tr>
				<?
				$result=SQL_Query("SELECT angebot.id AS id,
																		angebot.nummer AS nummer,
																		angebot.name AS name,
																		angebot.beschreibung AS beschreibung,
																		angebot.size AS size,
																		angebot.preis AS preis,
																		angebot.vorhanden AS vorhanden,
																		lieferant.name AS lieferant
																		FROM 
																			CatProduct angebot,
																			CatSupplier lieferant
																		WHERE angebot.lieferant=lieferant.id
																		ORDER BY lieferant,nummer,name");
				if ($row=mysql_fetch_array($result)) {
					do {
						$row[preis]= FloatToCurrency($row[preis]);
						if ($row[size]==0)$size="klein";
						if ($row[size]==1)$size="mittel";
						if ($row[size]==2)$size="gross";
						?>
						<td class=liste><?echo"$row[lieferant]";?></td>
						<td class=liste align=right><?echo $row[nummer];?></td>
						<td class=liste><?echo $size;?></td>
						<td class=liste><a href="verwaltung_angebot.php?mode=edit&id=<?echo"$row[id]";?>"><?echo"$row[name]";?></a></td>
						<td class=liste><?echo HTMLStr($row[beschreibung], 30);?></td>
						<td class=liste><div align="right"><?echo"$row[preis]";?></div></td>
						<td class=liste>
						<?
							if ($row[vorhanden]==1){
								 echo "intern";
							}
							else {
								echo "ausserhalb";
							}						
						?>
						</td>
						<td><? NavPrintDel($PHP_SELF.'?mode=del&id='.$row['id']);	?></td>
					</tr>
					<?
					}
					while ($row=mysql_fetch_array($result));
				}
				?>
			</table>

<br>
<br>
<br>
</div>
<div align="left">
Neues Angebot eintragen :<br>
<form action="verwaltung_angebot.php" method=POST>
<input type="hidden" name="mode" value="add_angebot">
<table>
	<tr>
		<td class=form>
			<table width="100%" border="0">
				<tr>
				 <td class=form>Nummer</td>
				 <td class=form><input type"text" name="nummer" class="form_field"></td>
				</tr>
				<tr>
				 <td class=form>Name</td>
				 <td class=form><input type"text" name="name" class="form_field"></td>
				</tr>
				<tr>
					<td class=form>Beschreibung</td>
					<td class=form><input type="text" name="beschreibung" class="form_field"></td>
				</tr>
				<tr>
					<td class=form>Grösse</td>
					<td class=form>
						<select name="size" class="form_field">
							<option value="0">klein</option>
							<option value="1">mittel</option>
							<option value="2">gross</option>
						</select>
					</td>
				</tr>
				<tr>
					<td class=form>Preis (Pfennige durch '.' trennen)</td>
					<td class=form><input type="text" name="preis" size="5" class="form_field"></td>
				</tr>
				<tr>
					<td class=form>Lieferant</td>
					<td class=form>
						<select name="lieferant" class="form_field">
							<?
								$res=SQL_Query("SELECT * FROM CatSupplier");
								while ($row=mysql_fetch_array($res)) {
									?>						
							<option value="<?echo "$row[id]";?>"><?echo"$row[name]";?></option>
									<?
								}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<td class=form>Intern/Extern</td>
					<td class=form>
						<select name="vorhanden" class="form_field">
							<option value="0">extern</option>
							<option value="1">intern</option>
						</select>
					</td>
				</tr>
			</table>
			<input type="submit" name="Submit" value="Eintragen"  class="form_btn">
		</td>
	</tr>
</table>
</form>
</div>
<br>
<br>
</div>
<div align="left">
Vorhandene Lieferanten bearbeiten :
<br>
	<table width="25%">
		<tr>
			<th class=liste>Name</th>
			<th class=liste>Telefon</th>
			<th class=liste>Kundennummer</th>
		</tr>
		<?
		$result=SQL_Query("SELECT * FROM CatSupplier ORDER BY name");
		while ($row=mysql_fetch_array($result)) {
				?>
				<td class=liste><a href="verwaltung_angebot.php?mode=edit_lieferant&id=<?echo"$row[id]";?>"><?echo"$row[name]";?></a></td>
				<td class=liste><?echo"$row[telefon]";?></td>
				<td class=liste><?echo"$row[knr]";?></td>
				<td><? NavPrintDel('verwaltung_angebot.php?mode=del_lieferant&id='.$row[id]); ?></td>
			</tr>
			<?
		}
		?>
	</table>

Neuen Lieferanten eintragen :<br>
<form action="verwaltung_angebot.php" method=POST>
<input type="hidden" name="mode" value="add_lieferant">
<table>
	<tr>
		<td class=form>
			<table width="100%" border="0">
				<tr>
				 <td class=form>Name</td>
				 <td class=form><input type"text" name="name" class="form_field"></td>
				</tr>
				<tr>
				 <td class=form>Telefon</td>
				 <td class=form><input type"text" name="telefon" class="form_field"></td>
				</tr>
				<tr>
				 <td class=form>Zugewiesene Kunden Nr.</td>
				 <td class=form><input type"text" name="knr" class="form_field"></td>
				</tr>
			</table>
			<input type="submit" name="Submit" value="Eintragen"  class="form_btn">
		</td>
	</tr>
</table>
</form>
</div>
<br>
<br>
<br>
<br>
<?
}

EndPage();
?>