<?
	// Copyright (c) 2001 Henrik 'Hotkey' Brinkmann  Email: hotkey@cncev.de

	$LS_BASEPATH = '../';
	include $LS_BASEPATH.'../includes/lsi_base.inc';
	StartPage("Catering - Hilfe");

user_auth(TRUE);

//ToDo: Checken ob user eingelogged ist.
if ($user_valid=="true") {
	
	//Default werte belegen :
	//Standart Sortierung nach nummer:
	if ($order=="") $order=nummer;
	//Standart Filtern nach lieferant id = 1:
	if ($filter=="") $filter=1;
	
	//Sortierung realisieren :
	if ($direction=="ASC") $direction="DESC";
	else $direction="ASC";
	if ($direction=="") $direction="ASC";
	
	// user id ermitteln ...
	$user_id = $user_current[id];
	$user_current['kontostand'] = number_format ($user_current['kontostand'],2,",",".");
	
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
	      <p><font size="4">Hilfe</font></p>
	      </td>
	  </tr>
	</table>
	&nbsp; 
	<blockquote>
			Catering System - so gehts :<br><br><br>
			Das Catering System ermöglicht es Euch alle verfügbaren Produkte direkt von Eurem Sitzplatz aus zu bestellen.
			Die Bezahlung erfolgt dabei durch ein virtuelles Konto welches Ihr an der Catering Theke
			auffüllen könnt.<br>
			Am Ende der Veranstaltung wird Euch der Kontostand wieder ausgezahlt.
			Auch am Catering Stand selbst könnt Ihr dort gekaufte Produkte direkt von Eurem Konto
			abbuchen lassen.<br>
			Im Prinzip also wie Wertmarken, nur das Sie hier virtuell sind, und der Rest nachher wieder
			ausgezahlt wird.<br>
			Solltet ihr dieses System nicht nutzen wollen könnt ihr selbstverständlich weiterhin Bar
			bezahlen.<br>
			<br>
			Der Ablauf sieht wie folgt aus :<br>
			Ihr zahlt einen bestimmten Betrag zu Beginn der Party ein. Nun könnt ihr "online" eure Pizza 
			bestellen. <br>
			Auf den Catering Seiten wird immer oben der Status der letzten Bestellung angezeigt. 
			Also entweder "keine Info" - "bestellung bearbeitet" - oder "bestellung abholbereit".
			<br><br>
			Zu den Schritten im Einzelnen :<br>
			<br>			
			1) Unter "Catering" könnt Ihr Eure Bestellungen ordern - d.h. in den Warenkorb legen. Ihr könnt dabei
			zwischen den verschiedenen Angeboten wälen, die vom Orgateam ausserhalb der Halle bestellt werden.
			Eine Suchfunktion steht ebenfalls zur Verfügung.<br>
			<br>
			2) Die Waren die Ihr dort auswählt landen alle im "Einkaufswagen". Dort könnt Ihr die Menge
			der Produkte ändern und Euch den Gesamtpreis anzeigen lassen.
			(einfach nach der Änderung REFRESH drücken).<br>
			<br>
			3) Dort könnt ihr dann auch nach klick auf "zur Kasse" Eure Wahl entgültig bestellen. Erst jetzt
			wird die Bestellung vom Orgateam bearbeitet und der Betrag von Eurem Konto abgebucht.
			<br><br>
			Alle Eure Bestellungen könnt ihr unter "My Konto" verwalten. Dort werden alle Bestellungen (alt und neu) aufgeführt.
			Wenn Ihr eine Bestellung löschen wollt könnt ihr das dort erledigen. 
			<br><b>WICHTIG : "EINE BESTELLUNG KANN NUR GELÖSCHT WERDEN WENN SIE NOCH NICHT VOM ORGA TEAM BEARBEITET WURDE -
			ALSO Z.B. BEI DER PIZZERIA BESTELLT WURDE".</b><br>
			Das Löschen einer bearbeiteten  Bestellung ist also NICHT möglich.
			<br><br>
			Das sollte es an nötigen Infos gewesen sein. Solltet ihr Fragen haben dann fragt ;-)
			<br><br>
			Viel Spass auf der Party !<br><br>
			CNC|Hotkey
	</blockquote>
	<?
}
else {
	echo "Du must eingelogged sein um das Catering System nutzen zu können!";
}

EndPage();
?>