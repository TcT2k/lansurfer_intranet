<?
	// Copyright (c) 2001 Henrik 'Hotkey' Brinkmann  Email: hotkey@cncev.de

	$LS_BASEPATH = '../';
	include $LS_BASEPATH.'../includes/lsi_base.inc';
	StartPage("Catering - KONTO");
	
	//Log Datei Festlegen :
	$fp=fopen($LS_BASEPATH."../includes/logs/catering_user.txt", "a");

	//Checken ob user eingelogged ist.
if ($user_valid=="true") {
	user_auth(TRUE);
	
	// user id ermitteln ...
	$user_id=$user_current[id];
	$user_current[kontostand]=number_format ($user_current[kontostand],2,",",".");
	
	
	//Bestellung löschen
	if ($action=="delete") {
		// Loescht eine Bestellung aus der Datenbank wenn sie noch nicht bearbeitet,ausgeliefert oder eingetroffen ist.
		// Zusätzlich wird ein Vermerk in der Log Datei gemacht.
		$res=SQL_Query("SELECT bearbeitet,eingetroffen,ausgeliefert,anzahl,angebot_id,user_id FROM CatOrder WHERE id='$id'");
		if ($row=mysql_fetch_array($res)) {
			$error=0;
			if (($row[bearbeitet]==1)||($row[eingetroffen])||($row[ausgeliefert]==1)) {
				$error=1;
				echo "Die Bestellung konnte nicht gelöscht werden !<br>Mögliche Gründe :<br>";
				echo "<br>- Die Bestellung wurde bereits bearbeitet<br>- Die Bestellung ist bereits eingetroffen<br>- Die Bestellung wurde schon ausgeliefert<br>";
			}
		}
		else $error=2;
		if ($error==0) {
			SQL_Query ("DELETE FROM CatOrder WHERE id='$id'");
			echo "Die Bestellung wurde gelöscht !<br>";
			$anzahl=$row[anzahl];
			$angebot_id=$row[angebot_id];
			$user=$row[user_id];
			$day=date(d);
			$month=date(m);
			$hour=date(H);
			$minute=date(i);
			//In Log Datei schreiben:
			fputs($fp,"[$day.$month] um [$hour:$minute] Bestellung von $user[name] gelöscht\n");
			//Zurücksetzen des Kontos...
			$res=SQL_Query("SELECT preis FROM CatProduct WHERE id='$angebot_id'");
			$row=mysql_fetch_array($res);
			$gutschrifft=$anzahl*$row[preis];
			$res=SQL_Query("SELECT kontostand FROM user WHERE id='$user'");
			$row=mysql_fetch_array($res);
			$neu=$row[kontostand]+$gutschrifft;
			echo "Kontostand alt : $row[kontostand]<br>";
			echo "Gutschrifft    : <font color=\"#00FF00\">$gutschrifft</font><br>";
			echo "Kontostand neu : $neu<br>";
			SQL_Query("UPDATE user SET kontostand=$neu WHERE id='$user'");			
		}
		if ($error==2) {
			echo "Datenbank fehler - Bitte melden Sie sich bei dem Orga Team!";
		}
	}
	
	
	//Details der Bestellung anzeigen :
	
	if ($action=="detailed") {
	require $LS_BASEPATH.'../includes/catering/header.inc';
?>
<table width="95%" border="0" cellpadding="0" cellspacing="0" valign="top" align="center">
  <tr> 
 		<?
 			$result=SQL_Query("SELECT s.id AS id,
 																s.name AS name
 															FROM CatSupplier s,
 																	 CatProduct a
 															WHERE s.id=a.lieferant AND a.vorhanden=0
 															GROUP BY s.name");
 	$firstrun=true;
 			if ($row=mysql_fetch_array($result)) {
 				do {
 				  	if (!$firstrun) {
    		?>    
    		<td class="liste"><img src="../images/spacer.gif" width="10" height="1">|<img src="../images/spacer.gif" width="10" height="1"></td>
    		<?		
    	}
    	else ($firstrun=false);
    		?>
    <td class="liste" width="15"><img src="../images/spacer.gif" width="15" height="1"></td>
    <td class="liste" align="left"><a href="bestellen.php?filter=<?echo"$row[id]";?>&order=<?echo"$order";?>&suche=<?echo"$suche";?>"><b><font size="2"><?echo"$row[name]";?></font></b></td>
    		<?
    		}
				while ($row=mysql_fetch_array($result));
}		
     		
   	
    
    if ($suche=="") {
?>
<td class="liste" width="100%">&nbsp;</td>
</tr>
</table>
<table width="95%" border="0" cellpadding="0" cellspacing="0" valign="top" align="center">
<tr>
<td>&nbsp;</td>
	<td width="230" class="liste" align="right" valign="bottom">
    <form name="form1" method="post" action="bestellen.php">
    <input type="hidden" name="order" value="<?echo"$order";?>">
    <input type="hidden" name="filter" value="<?echo"$filter";?>">
    <input type="text" name="suche">
    <input type="submit" name="Submit" class="form_btn" value="Suchen">
    <img src="../images/spacer.gif" width="1" height="8"></form></td>
    </form>
    <?
    }
    else {
    ?>
    <a href="bestellen.php?suche=&order=<?echo"$order";?>&filter=<?echo"$filter";?>">Alle Angebote zeigen</a>
    <?
    }
    ?>
    </font></td> 
  </tr>
</table>
<br>
<?
			$res=SQL_Query("SELECT o.bearbeitet,
			o.eingetroffen,
			o.ausgeliefert,
			UNIX_TIMESTAMP(o.time) as 'time',
			o.id,
			o.anzahl,
			p.name,
			p.preis,
			p.beschreibung
		FROM
			CatOrder o,
			CatProduct p
		WHERE 
			o.angebot_id = p.id	AND o.id = '$id'	AND o.user_id = '$user_id'");
		$row=mysql_fetch_array($res);
		if ($row[ausgeliefert]==1) $text="ausgeliefert";
		else if ($row[eingetroffen]==1) $text="Eingetroffen - bitte abholen !";
		else if ($row[bearbeitet]==1) $text="bearbeitet";
		else $text="Noch nicht bearbeitet";
		//$row[preis]=number_format($row[preis],2,",",".");
?>
	<table width="95%" border="0" cellpadding="0" cellspacing="0" align="center">
	  <tr> 
	    <td> 
	      <p><font size="4">Details</font></p>
	    </td>
	  </tr>
	  <tr> 
	    <td> 
	      <p><font size="2">der Bestellung Nr. <?echo $row[id];?> vom <?echo DisplayDate($row[time]);?></font></p>
	    </td>
	  </tr>
	  <tr> 
	    <td><font size="2">Status : <b><?echo"$text";?></font>
		</td>
	  </tr>
	</table> 
	<br>
	<table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
  	<tr> 
  	<td class="liste" width="10"><img src="../images/spacer.gif" width="10" height="1"></td>
    <td class="liste">Name</td>
    <td class="liste" align="center" width="100">Anzahl</td>
    <td class="liste" align="center" width="150">Preis</td>
   </tr>
  <tr> 
    <td><img src="../images/spacer.gif" width="1" height="15"></td>
  </tr>
</table>


				<table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
				  <tr> 
				    <td><?echo"$row[name]";?></td>
				    <td width="25"><img src="../images/spacer.gif" width="10" height="1">|<img src="../images/spacer.gif" width="10" height="1"></td>
				    <td width="100" align="center"><font size="2"><?echo "$row[anzahl]";?></font></td>
				    <td width="25"><img src="../images/spacer.gif" width="10" height="1">|<img src="../images/spacer.gif" width="10" height="1"></td>
				    <td align="right" width="100"><font color="#FF0000"><?echo FloatToCurrency($row[preis]); ?></font> </td>
				  </tr>
				  <tr>
				    <td><font size="1"><?echo"$row[beschreibung]";?></font></td>
				  </tr>
				</table>
<br>
		<?
		$summe=$row[anzahl]*$row[preis];
		?>
<table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr> 
    <td valign="top">&nbsp;</td>
    <td width="190" align="right"><img src="../images/spacer.gif" width="10" height="1">Gesamtpreis:</td>
    <td width="135" align="right" valign="top"><font color="#FF0000"><?$gsumme=sprintf("%.2f", $summe);echo"$gsumme";echo LS_CATERING_CURRENCY;?></font></td>
  </tr>	
</table>
		<?
		if (($row[bearbeitet]==0)&&($row[eingetroffen]==0)&&($row[ausgeliefert]==0)) {
			?>	
			<blockquote>
			<a href="konto.php?action=delete&id=<?echo "$row[id]";?>"><img src="../images/navigation/arrow_right.gif" border="0"> Bestellung l&ouml;schen</a><br>
			</blockquote>
			<?
		}
		?>
		<blockquote>
		<a href="konto.php"><img src="../images/navigation/arrow_right.gif" border="0"> zurück zur Übersicht</a></a> 
		</blockquote>
		<?
	}
	
	
	// BESTELLUNG ORDERN UND NOCHMALS DEN KONTOSTAND ÜBERPRÜFEN ...
	
	if ($action=="order") {
		$res=SQL_Query("SELECT  s.name AS lieferant,
															p.name AS angebot,
															p.preis,
															p.beschreibung,
															p.size,
															o.id,
															o.anzahl
															FROM 
																CatOrder o
																LEFT JOIN CatProduct p ON p.id=o.angebot_id
																LEFT JOIN CatSupplier s ON s.id=p.lieferant
															WHERE 
																o.user_id='$user_id'
																AND o.wagen=1
															ORDER by p.id");
		if ($row=mysql_fetch_array($res)) {
			do {
				$gesamtpreis+=(FloatToCurrency($row[preis], true)*$row[anzahl]);
				$row[preis]= FloatToCurrency($row[preis]);
			}
			while ($row=mysql_fetch_array($res));
		}
		$res=SQL_Query("SELECT kontostand FROM user WHERE id='$user_id'");
		$row=mysql_fetch_array($res);
		$diff=$row[kontostand]-$gesamtpreis;
		if ($row[kontostand]<$gesamtpreis) {
			?>
			Ihr Kontostand reicht nicht aus ! Es fehlen <?echo "$diff";echo LS_CATERING_CURRENCY;?>. Bitte erst nachzahlen.
			<?
		}
		else {
			$date=date("Y-m-d");
			$hour=date(H);
			$minute=date(i);
			$res=SQL_Query("SELECT  lieferant.name AS lieferant,
															angebot.name AS angebot,
															angebot.preis AS preis,
															angebot.beschreibung AS beschreibung,
															angebot.size AS size,
															bestellung.anzahl AS anzahl,
															bestellung.id AS id
															FROM 
																CatSupplier lieferant,
																CatProduct angebot,
																CatOrder bestellung
															WHERE lieferant.id=angebot.lieferant 
																AND bestellung.angebot_id=angebot.id
																AND bestellung.user_id='$user_id'
																AND bestellung.wagen=1
															ORDER by lieferant.id");
			while ($row=mysql_fetch_array($res)) {
				SQL_Query("UPDATE CatOrder SET time=NOW(),wagen=0,bearbeitet=0,eingetroffen=0,ausgeliefert=0 WHERE id='$row[id]'");
			}	
			//neuen Kontostand speichern :
			SQL_Query("UPDATE user SET kontostand='$diff' WHERE id='$user_id'");
			?>
			<blockquote>
			<div class="content">
			Die Produkte wurden erfolgreich bestellt!<br>	
			Ihr neuer Kontostand ist <font size="3"><?$kdiff=sprintf("%.2f", $diff);echo"$kdiff";echo LS_CATERING_CURRENCY;?></font><br>
			</div>
			</blockquote>
<br>
<table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr> 
    <td valign="top" align="left"><a class="content" href="bestellen.php"><img src="../images/navigation/arrow_right.gif" border="0"> weiter shoppen</a></td>
  </tr>
</table>			
			<?
		}
	}
	
	
	// DIE BESTELLUNG ÜBERPRÜFEN ...
	
	if ($action=="validate") {
		
	//Status der letzten bestellung ermitteln :
	$text="noch nicht bearbeitet";
	$res=SQL_Query("SELECT * FROM CatOrder WHERE user_id='$user_id'AND wagen=0 AND ausgeliefert=0 ORDER by time ASC");
	if ($row=mysql_fetch_array($res)) {
		$id=0;
		do {
			if (($id==0)||($status=="be")) {
				if ($row[eingetroffen]==1) {
					$id=$row[id];
					$status="ein";
					$text="Bitte Abholen !";
				}
				else if (($row[bearbeitet]==1)&&($status=="")) {
					$id=$row[id];
					$status="be";
					$text="bearbeitet";
				}
			}
		}
		while ($row=mysql_fetch_array($res));
	}
	else {
		//noch keine bestellung
		$text="Keine Bestellung bisher";
	}
	//Ende Status der ...
	
	require $LS_BASEPATH.'../includes/catering/header.inc';
?>
<table width="95%" border="0" cellpadding="0" cellspacing="0" valign="top" align="center">
  <tr> 
 		<?
 			$result=SQL_Query("SELECT s.id AS id,
 																s.name AS name
 															FROM CatSupplier s,
 																	 CatProduct a
 															WHERE s.id=a.lieferant AND a.vorhanden=0
 															GROUP BY s.name");
 	$firstrun=true;
 			if ($row=mysql_fetch_array($result)) {
 				do {
 				  	if (!$firstrun) {
    		?>    
    		<td class="liste"><img src="../images/spacer.gif" width="10" height="1">|<img src="../images/spacer.gif" width="10" height="1"></td>
    		<?		
    	}
    	else ($firstrun=false);
    		?>
    <td class="liste" width="15"><img src="../images/spacer.gif" width="15" height="1"></td>
    <td class="liste" align="left"><a href="bestellen.php?filter=<?echo"$row[id]";?>&order=<?echo"$order";?>&suche=<?echo"$suche";?>"><b><font size="2"><?echo"$row[name]";?></font></b></td>
    		<?
    		}
				while ($row=mysql_fetch_array($result));
}		
     		
   	
    
    if ($suche=="") {
?>
<td class="liste" width="100%">&nbsp;</td>
</tr>
</table>
<table width="95%" border="0" cellpadding="0" cellspacing="0" valign="top" align="center">
<tr>
<td>&nbsp;</td>
	<td width="230" class="liste" align="right" valign="bottom">
    <form name="form1" method="post" action="bestellen.php">
    <input type="hidden" name="order" value="<?echo"$order";?>">
    <input type="hidden" name="filter" value="<?echo"$filter";?>">
    <input type="text" name="suche">
    <input type="submit" name="Submit" class="form_btn" value="Suchen">
    <img src="../images/spacer.gif" width="1" height="8"></form></td>
    </form>
    <?
    }
    else {
    ?>
    <a href="bestellen.php?suche=&order=<?echo"$order";?>&filter=<?echo"$filter";?>">Alle Angebote zeigen</a>
    <?
    }
    ?>
    </font></td> 
  </tr>
</table>
<br>

	<table width="95%" border="0" cellpadding="0" cellspacing="0" align="center">
	  <tr> 
	    <td> 
	      <font size="4">Kasse</font><br>
	      <font size="2">Bitte bestätigen Sie Ihre Bestellung</font>
	    </td>
	  </tr>
	</table>
	<br>
	<br>
	<? //Überschrifften tabelle ... ?>
<table width="95%" border="0" cellspacing="0" cellpadding="0" align="center">
  <th class="liste"> 
<td class="liste" width="5"><img src="../images/spacer.gif" width="5" height="1"></td>
    <td class="liste"><b><a href="einkaufswagen.php?filter=<?echo"$filter";?>&suche=<?echo"$suche";?>&order=angebot.name&direction=<?echo"$direction";?>">Name</a></b></td>
    <td class="liste" width="25"><img src="../images/spacer.gif" width="10" height="1">|<img src="../images/spacer.gif" width="10" height="1"></td>
    <td class="liste" width="100" align="center"><b>Grösse</b></td>
    <td class="liste" width="25"><img src="../images/spacer.gif" width="10" height="1">|<img src="../images/spacer.gif" width="10" height="1"></td>
    <td class="liste" align="center" width="80"><b><a href="einkaufswagen.php?filter=<?echo"$filter";?>&suche=<?echo"$suche";?>&order=bestellung.anzahl&direction=<?echo"$direction";?>">Anzahl</a></b></td>
    <td class="liste" width="25"><img src="../images/spacer.gif" width="10" height="1">|<img src="../images/spacer.gif" width="10" height="1"></td>
    <td class="liste" width="100" align="center"> <b><a href="einkaufswagen.php?filter=<?echo"$filter";?>&suche=<?echo"$suche";?>&order=angebot.preis&direction=<?echo"$direction";?>">Einzelpreis</a></b></td>
    <td class="liste" width="25"><img src="../images/spacer.gif" width="10" height="1">|<img src="../images/spacer.gif" width="10" height="1"></td>  
    <td class="liste" width="100" align="center"> <b><a href="einkaufswagen.php?filter=<?echo"$filter";?>&suche=<?echo"$suche";?>&order=angebot.preis&direction=<?echo"$direction";?>">Gesamtpreis</a></b></td>
    <td class="liste" width="25"><img src="../images/spacer.gif" width="10" height="1"></td>  
</tr>
  <tr> 
    <td colspan="11"><img src="../images/spacer.gif" width="1" height="15"></td>
  </tr>
</table>
		<?
		$res=SQL_Query("SELECT  lieferant.name AS lieferant,
															angebot.name AS angebot,
															angebot.preis AS preis,
															angebot.beschreibung AS beschreibung,
															angebot.size AS size,
															bestellung.anzahl AS anzahl
															FROM 
																CatSupplier lieferant,
																CatProduct angebot,
																CatOrder bestellung
															WHERE lieferant.id=angebot.lieferant 
																AND bestellung.angebot_id=angebot.id
																AND bestellung.user_id='$user_id'
																AND bestellung.wagen=1
															ORDER by lieferant.id");
		if ($row=mysql_fetch_array($res)) {
			do {
				$preis= FloatToCurrency($row[preis], true) *$row[anzahl];
				$gesamtpreis+=$preis;
				//$row[preis]=number_format($row[preis],2,",",".");
				if ($row[size]==0)$size="klein";
				if ($row[size]==1)$size="mittel";
				if ($row[size]==2)$size="gross";

?>
<table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr> 
    
    <td><?echo $row[angebot];?></td>
    <td width="25"><img src="../images/spacer.gif" width="10" height="1">|<img src="../images/spacer.gif" width="10" height="1"></td>
    <td width="100" align="center"><font size="2"><?echo "$size";?></font></td>
    <td width="25"><img src="../images/spacer.gif" width="10" height="1">|<img src="../images/spacer.gif" width="10" height="1"></td>
    <td align="center" width="80">
      <input type="text" name="anzahl_nummer[<?echo $idcount;?>]" size=4 maxlength=4 value="<?echo"$row[anzahl]";?>">
    </td>
    <td width="25"><img src="../images/spacer.gif" width="10" height="1">|<img src="../images/spacer.gif" width="10" height="1"></td>
    <td width="106" align="right"><?echo FloatToCurrency($row[preis]); ?></td>
    <td width="145" align="right"><font color="#FF0000"><? echo FloatToCurrency($preis); ?></font></td>
</tr>
  <tr> 
    <td><font size="1"><?echo "$row[beschreibung]";?></font></td>
    <td colspan="7">&nbsp;</td>
  </tr>
</table>
<table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr> 
    <td><img src="../images/spacer.gif" width="1" height="15"></td>
  </tr>
  <tr> 
    <td bgcolor="#99ccff"><img src="../images/spacer.gif" width="1" height="1"></td>
  </tr>
  <tr> 
    <td><img src="../images/spacer.gif" width="1" height="15"></td>
  </tr>
</table>
			<?
			}
			while ($row=mysql_fetch_array($res));
		}
		else {
			$noentry=1;
		}
		$res=SQL_Query("SELECT kontostand FROM user WHERE id='$user_id'");
		$row=mysql_fetch_array($res);
		$kontostand=$row[kontostand];
	?>
<table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr> 
    <td valign="top"><a href="bestellen.php"><img src="../images/navigation/arrow_right.gif" border="0"> weiter shoppen</a></td>
    <td width="190" align="right"><img src="../images/spacer.gif" width="10" height="1">Gesamtpreis:</td>
    <td width="135" align="right" valign="top"><font color="#FF0000"><? echo FloatToCurrency($gesamtpreis); ?></font></td>
  </tr>
    <tr> 
    <td valign="top">&nbsp;</td>
    <td width="190" align="right"><img src="../images/spacer.gif" width="10" height="1">Alter Kontostand:</td>
    <td width="135" align="right" valign="top"><? echo FloatToCurrency($kontostand); ?></td>
  </tr>

	<?
	if ($noentry==1) {
		echo "Keine Bestellung vorhanden !";
	}
	else {
		$diff= $kontostand-$gesamtpreis;
		if ($kontostand<$gesamtpreis) {
			?>
			Ihr Kontostand reicht nicht aus ! Es fehlen <?$kdiff=sprintf("%.2f", $diff);echo"$kdiff";echo LS_CATERING_CURRENCY;?> . Bitte erst nachzahlen.
			<?
		}
		else {
			?>
	</tr>
    <tr> 
    <td valign="top">&nbsp;</td>
    <td width="190" align="right"><img src="../images/spacer.gif" width="10" height="1"><font color="#FF0000">Neuer Kontostand:</font></td>
    <td width="135" align="right" valign="top"><font color="#FF0000"><? echo FloatToCurrency($diff); ?></font></td>
	</tr>
  </table>
  <br>
<table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
	<tr>
	<td colspan="2">&nbsp;</td> 
    <td align="right" valign="top"><img src="../images/shopper2.gif" width="19" height="15"><a href="konto.php?action=order"> Bestätigen</a></td>
  	</tr>	
  </table>
	
	<?
		}
	}

}
if ($action=="") {											
	//Status der letzten bestellung ermitteln :
	$text="noch nicht bearbeitet";
	$res=SQL_Query("SELECT * FROM CatOrder WHERE user_id='$user_id'AND wagen=0 AND ausgeliefert=0 ORDER by time ASC");
	if ($row=mysql_fetch_array($res)) {
		$id=0;
		do {
			if (($id==0)||($status=="be")) {
				if ($row[eingetroffen]==1) {
					$id=$row[id];
					$status="ein";
					$text="Bitte Abholen !";
				}
				else if (($row[bearbeitet]==1)&&($status=="")) {
					$id=$row[id];
					$status="be";
					$text="bearbeitet";
				}
			}
		}
		while ($row=mysql_fetch_array($res));
	}
	else {
		//noch keine bestellung
		$text="Keine Bestellung bisher";
	}
	//Ende Status der ...
	require $LS_BASEPATH.'../includes/catering/header.inc';
?>
<table width="95%" border="0" cellpadding="0" cellspacing="0" valign="top" align="center">
  <tr> 
 		<?
 			$result=SQL_Query("SELECT s.id AS id,
 																s.name AS name
 															FROM CatSupplier s,
 																	 CatProduct a
 															WHERE s.id=a.lieferant AND a.vorhanden=0
 															GROUP BY s.name");
 	$firstrun=true;
 			if ($row=mysql_fetch_array($result)) {
 				do {
 				  	if (!$firstrun) {
    		?>    
    		<td class="liste"><img src="../images/spacer.gif" width="10" height="1">|<img src="../images/spacer.gif" width="10" height="1"></td>
    		<?		
    	}
    	else ($firstrun=false);
    		?>
    <td class="liste" width="15"><img src="../images/spacer.gif" width="15" height="1"></td>
    <td class="liste" align="left"><a href="bestellen.php?filter=<?echo"$row[id]";?>&order=<?echo"$order";?>&suche=<?echo"$suche";?>"><b><font size="2"><?echo"$row[name]";?></font></b></td>
    		<?
    		}
				while ($row=mysql_fetch_array($result));
}		
     		
   	
    
    if ($suche=="") {
?>
<td class="liste" width="100%">&nbsp;</td>
</tr>
</table>
<table width="95%" border="0" cellpadding="0" cellspacing="0" valign="top" align="center">
<tr>
<td>&nbsp;</td>
	<td width="230" class="liste" align="right" valign="bottom">
    <form name="form1" method="post" action="bestellen.php">
    <input type="hidden" name="order" value="<?echo"$order";?>">
    <input type="hidden" name="filter" value="<?echo"$filter";?>">
    <input type="text" name="suche">
    <input type="submit" name="Submit" class="form_btn" value="Suchen">
    <img src="../images/spacer.gif" width="1" height="8"></form></td>
    </form>
    <?
    }
    else {
    ?>
    <a href="bestellen.php?suche=&order=<?echo"$order";?>&filter=<?echo"$filter";?>">Alle Angebote zeigen</a>
    <?
    }
    ?>
    </font></td> 
  </tr>
</table>
<br>

	<table width="95%" border="0" cellpadding="0" cellspacing="0" align="center">
	  <tr> 
	    <td> 
	      <p><font size="4">Konto</font></p>
	      </td>
	  </tr>
	</table>
	&nbsp; 
	<table width="95%" border="0" cellspacing="0" cellpadding="0" align="center">
	  <tr>
	    <td><font size="2">Offene Bestellungen:</font></td>
	  </tr>
	</table>
	<table width="95%" border="0" cellspacing="0" cellpadding="0" align="center">
	<tr> 
	    <td colspan="9"><img src="../images/spacer.gif" width="1" height="15"></td>
	  </tr>  
	<tr bgcolor="#99ccff"> 
		<td  class="liste"width="5"><img src="../images/spacer.gif" width="5" height="1"></td>
	    <td class="liste"><b>Bestellung #</b></td>
	    <td class="liste" width="25"><img src="../images/spacer.gif" width="10" height="1">|<img src="../images/spacer.gif" width="10" height="1"></td>
	    <td class="liste" width="300" align="center"><b>Bestelldatum</b></td>
	    <td class="liste" width="25"><img src="../images/spacer.gif" width="10" height="1">|<img src="../images/spacer.gif" width="10" height="1"></td>
	    <td class="liste" align="center" width="150"><b>Status</b></td>
	    <td class="liste" width="100"><img src="../images/spacer.gif" width="100" height="1"></td>  
	</tr>
	<tr> 
	    <td colspan="9"><img src="../images/spacer.gif" width="1" height="15"></td>
	  </tr>  
	</table>
	
	<?
	//Spalten für offene Bestellungen zeichnen ...
	$res= SQL_Query("SELECT bestellung.id AS id,
														UNIX_TIMESTAMP(bestellung.time) AS date,
														bestellung.bearbeitet AS bearbeitet,
														bestellung.eingetroffen AS eingetroffen,
														lieferant.name AS name
														FROM 
															CatOrder bestellung,
															CatSupplier lieferant,
															CatProduct angebot
														WHERE bestellung.user_id = '$user_id'
															AND bestellung.angebot_id = angebot.id
															AND angebot.lieferant = lieferant.id
															AND bestellung.wagen=0
															AND bestellung.ausgeliefert=0
															ORDER BY time");
	if ($row=mysql_fetch_array($res)) {
		do {
		if ($row[eingetroffen]==1) {
			$status="abholen !";
			$color="FF0000";
		}
		else if ($row[bearbeitet]==1) {
			$status="bearbeitet";
			$color="00FF00";
		}
		else {
			$status="keine Info";
			$color="FFFFFF";
		}		?>
	<table width="95%" border="0" cellspacing="0" cellpadding="0" align="center">
	  <tr>
	    <td><a href="konto.php?action=detailed&id=<?echo"$row[id]";?>"><font size="2"><?echo"$row[name]-000-$row[id]";?></font></a></td>
	    <td width="25"><img src="../images/spacer.gif" width="10" height="1"><font size="2">|</font><img src="../images/spacer.gif" width="10" height="1"></td>
	    <td width="300" align="center"><font size="2"><?echo DisplayDate($row[date]); ?></font></td>
	    <td width="25"><img src="../images/spacer.gif" width="10" height="1"><font size="2">|</font><img src="../images/spacer.gif" width="10" height="1"></td>
	    <td align="center" width="150"><font size="2" color="#<?echo"$color";?>"><?echo "$status";?></font></td>
	    <td width="100"><img src="../images/spacer.gif" width="100" height="1"></td>  
	  </tr>
	</table>
		<?
		}
		while ($row=mysql_fetch_array($res));
	}
	// Ende der Spalten für offene Bestellungen
	?>
	<table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
	  <tr> 
	    <td><img src="../images/spacer.gif" width="1" height="15"></td>
	  </tr>
	  <tr> 
	    <td bgcolor="#99ccff"><img src="../images/spacer.gif" width="1" height="1"></td>
	  </tr>
	  <tr> 
	    <td><img src="../images/spacer.gif" width="1" height="15"></td>
	  </tr>
	</table>
	<table width="95%" border="0" cellspacing="0" cellpadding="0" align="center">
	  <tr> 
	    <td><font size="2">Abgeholte Bestellungen:</font></td>
	  </tr>
	</table>
	<table width="95%" border="0" cellspacing="0" cellpadding="0" align="center">
	  <tr> 
	    <td colspan="9"><img src="../images/spacer.gif" width="1" height="15"></td>
	  </tr>
	  <tr bgcolor="#99ccff"> 
	    <td class="liste" width="5"><img src="../images/spacer.gif" width="5" height="1"></td>
	    <td class="liste"><b>Bestellung #</b></td>
	    <td class="liste" width="25"><img src="../images/spacer.gif" width="10" height="1">|<img src="../images/spacer.gif" width="10" height="1"></td>
	    <td class="liste" width="300" align="center"><b>Bestelldatum</b></td>
	    <td class="liste" width="25"><img src="../images/spacer.gif" width="10" height="1">|<img src="../images/spacer.gif" width="10" height="1"></td>
	    <td class="liste" align="center" width="150"><b>Status</b></td>
	    <td class="liste" width="100"><img src="../images/spacer.gif" width="100" height="1"></td>
	  </tr>
	  <tr> 
	    <td colspan="9"><img src="../images/spacer.gif" width="1" height="15"></td>
	  </tr>
  </table>
  <?
  //Spalten für alte Bestellungen zeichnen ...
	$res= SQL_Query("SELECT bestellung.id AS id,
														UNIX_TIMESTAMP(bestellung.time) AS date,
														bestellung.bearbeitet AS bearbeitet,
														bestellung.eingetroffen AS eingetroffen,
														lieferant.name AS name
														FROM 
															CatOrder bestellung,
															CatSupplier lieferant,
															CatProduct angebot
														WHERE bestellung.user_id = '$user_id'
															AND bestellung.angebot_id = angebot.id
															AND angebot.lieferant = lieferant.id
															AND bestellung.wagen=0
															AND bestellung.ausgeliefert=1
															ORDER BY time");
	$status="Ausgeliefert";
	$color="CCCCCC";
	while($row=mysql_fetch_array($res)) {
		?>
		<table width="95%" border="0" cellspacing="0" cellpadding="0" align="center">
		  <tr>
		    <td><a href="konto.php?action=detailed&id=<?echo"$row[id]";?>"><font size="2"><?echo"$row[name]-000-$row[id]";?></font></a></td>
		    <td width="25"><img src="../images/spacer.gif" width="10" height="1"><font size="2">|</font><img src="../images/spacer.gif" width="10" height="1"></td>
		    <td width="300" align="center"><font size="2"><?echo DisplayDate($row[date]); ?></font></td>
		    <td width="25"><img src="../images/spacer.gif" width="10" height="1"><font size="2">|</font><img src="../images/spacer.gif" width="10" height="1"></td>
		    <td align="center" width="150"><font size="2" color="#<?echo"$color";?>"><?echo "$status";?></font></td>
		    <td width="100"><img src="../images/spacer.gif" width="100" height="1"></td>  
		  </tr>
		</table>
		<?
	}
?>
<br>
<table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr> 
    <td valign="top" align="left"><a href="bestellen.php"><img src="../images/navigation/arrow_right.gif" border="0"> weiter shoppen</a></td>
  </tr>
</table>
<?
	// Ende der Spalten für alte Bestellungen
}
}
else {
	echo "Du must eingelogged sein um das Catering System nutzen zu können!";
}
EndPage();
?>
