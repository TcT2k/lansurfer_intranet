<?
	$LS_BASEPATH = '../';
	include $LS_BASEPATH.'../includes/ls_base.inc';
	StartPage("Catering - Einkaufswagen");
	
	if ($user_valid=="true") {
	user_auth(TRUE);
	
	if ($order=="") $order=name;
	
	if ($direction=="ASC") $direction="DESC";
	else $direction="ASC";
	if ($direction=="") $direction="ASC";
	
	$user_id=$user_current[id];
	$user_current[kontostand]=number_format ($user_current[kontostand],2,",",".");
	
	if ($action=="add_wagen") {
		$res=SQL_Query("SELECT id,angebot_id,anzahl FROM CatOrder WHERE user_id='$user_id' AND wagen=1");
		$vorhanden=0;
		if ($row=mysql_fetch_array($res)) {
			do {
				if ($id==$row[angebot_id]){
				 	$vorhanden=$row[anzahl];
					$id_vorhanden=$row[angebot_id];
				}
			}
			while ($row=mysql_fetch_array($res));
		}
		if ($vorhanden==0) {
			SQL_Query("INSERT INTO CatOrder (user_id,angebot_id,wagen,anzahl,bearbeitet,eingetroffen,ausgeliefert) 
																	VALUES ('$user_id','$id','1','1','0','0','0')");
		}
		else {
			$vorhanden++;
			SQL_Query("UPDATE CatOrder SET anzahl=$vorhanden WHERE angebot_id='$id_vorhanden' AND user_id='$user_id' AND wagen=1");
		}
	}
	
	elseif ($action=="refresh") {
		for ($i=0;$i<$idcount;$i++) {
			if ($anzahl_nummer[$i]==0) 
				SQL_Query("DELETE FROM CatOrder WHERE id='$id_nummer[$i]'");
			else 
				SQL_Query("UPDATE CatOrder SET anzahl='$anzahl_nummer[$i]' WHERE id='$id_nummer[$i]'");
		}
	}
	elseif ($action=="del_from_wagen") {
		SQL_Query("DELETE FROM CatOrder WHERE id='$id' AND user_id='$user_id' AND wagen=1");
	}
	
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
		$text="Keine Bestellung bisher";
	}
	
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
<td>
</td>
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
<table width="95%" border="0" cellpadding="0" cellspacing="0" align="center">
  <tr> 
    <td> 
      <font size="4">Einkaufswagen</font><br>
      <font size="2">Hier werden alle Ihre gewünschten Waren gesammelt, bis Sie zur Kasse gehen</font>
    </td>
  </tr>
</table>
<table width="95%" border="0" cellspacing="0" cellpadding="0" align="center">
<form action="einkaufswagen.php" method="post">
<input type="hidden" name="action" value="refresh">
<input type="hidden" name="suche" value="<?echo"$suche";?>">
<input type="hidden" name="order" value="<?echo"$order";?>">
<input type="hidden" name="filter" value="<?echo"$filter";?>">
<tr>
<td><img src="../images/spacer.gif" width="1" height="10"></td>
</tr>  
<tr>
    <td align="right" valign="top"><img src="../images/shopper2.gif" width="19" height="15"> 
      <a href="konto.php?action=validate&filter=<?echo"$filter";?>">Zur Kasse gehen</a></td>
  </tr>
<tr>
<td><img src="../images/spacer.gif" width="1" height="10"></td>
</tr>
</table>
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
    <td class="liste" width="100"><img src="../images/spacer.gif" width="100" height="1"></td>  
</tr>
  <tr> 
    <td colspan="9"><img src="../images/spacer.gif" width="1" height="15"></td>
  </tr>
</table>
<? 
	
	$res=SQL_Query("SELECT o.id,
						o.anzahl,
						p.name,
						p.beschreibung,
						p.preis,
						p.size
					FROM 
						CatOrder o
						LEFT JOIN CatProduct p ON p.id=o.angebot_id
					WHERE
						o.user_id='$user_id'
						AND o.wagen=1
					ORDER BY $order $direction");
		$idcount=0;
		while ($row=mysql_fetch_array($res)) {
			$zwischensumme += $row[anzahl] * FloatToCurrency($row[preis], true);
			$row[preis]= FloatToCurrency($row[preis]);
			if ($row[size]==0)$size="klein";
			elseif ($row[size]==1)$size="mittel";
			elseif ($row[size]==2)$size="gross";
			?>
			<input type="hidden" name="id_nummer[<?echo $idcount;?>]" value="<?echo"$row[id]";?>">
<table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr> 
    
    <td><?echo $row[name];?></td>
    <td width="25"><img src="../images/spacer.gif" width="10" height="1">|<img src="../images/spacer.gif" width="10" height="1"></td>
    <td width="100" align="center"><font size="2"><?echo "$size";?></font></td>
    <td width="25"><img src="../images/spacer.gif" width="10" height="1">|<img src="../images/spacer.gif" width="10" height="1"></td>
    <td align="center" width="80">
      <input type="text" name="anzahl_nummer[<?echo $idcount;?>]" size=4 maxlength=4 value="<?echo"$row[anzahl]";?>">
    </td>
    <td width="25"><img src="../images/spacer.gif" width="10" height="1">|<img src="../images/spacer.gif" width="10" height="1"></td>
    <td width="100" align="center"><font color="#FF0000"><?echo "$row[preis]";?></font></td>
    <td width="100" align="right"><a href="einkaufswagen.php?action=del_from_wagen&id=<?echo"$row[id]";?>&order=<?echo"$order";?>">loeschen</a></td>  
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
			$idcount++;
	}
	$zwischensumme=FloatToCurrency($zwischensumme);
?>
													 
														
<table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr> 
    <td valign="top"><a href="bestellen.php"><img src="../images/navigation/arrow_right.gif" border="0"> weiter shoppen</a></td>
    <td width="160" align="right"><img src="../images/spacer.gif" width="10" height="1">Zwischensumme:</td>
    <td width="100" align="center" valign="top"><font color="#FF0000"><?echo"$zwischensumme";?></font></td>
    <td width="100" align="right"><input type = "submit" class="form_btn" name="Submit" value="Refresh"></td>
  </tr>
</table>
<table width="95%" border="0" cellspacing="0" cellpadding="0" align="center">
  <tr> 
    <td><img src="../images/spacer.gif" width="1" height="10"></td>
  </tr>
  <tr> 
    <td align="right" valign="top"><img src="../images/shopper2.gif" width="19" height="15"> 
      <a href="konto.php?action=validate">Zur Kasse gehen</a></td>
  </tr>
  <tr> 
    <td><img src="../images/spacer.gif" width="1" height="10"></td>
  </tr>
</table>
<?
?>
	<input type="hidden" name="idcount" value="<?echo"$idcount";?>">
	</form>
<?
}
else {
	echo "Du must eingelogged sein um das Catering System nutzen zu können!";
}
EndPage();
?>
