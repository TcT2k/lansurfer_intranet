<?
	// Copyright (c) 2001 Henrik 'Hotkey' Brinkmann  Email: hotkey@cncev.de
	
	$LS_BASEPATH = '../';
	include $LS_BASEPATH.'../includes/lsi_base.inc';

	
	StartPage("Catering - Sortiment");
	
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
<td class="liste" width="100%">&nbsp;</td>
</tr>
</table>
<table width="95%" border="0" cellpadding="0" cellspacing="0" valign="top" align="center">
<tr>
<td>    <a href="bestellen.php?suche=&order=<?echo"$order";?>&filter=<?echo"$filter";?>">Alle Angebote zeigen</a>
</td>
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
    ?>
    </font></td> 
  </tr>
</table>
  
<table width="95%" border="0" cellpadding="0" cellspacing="0" align="center">
  <tr>
      <td><font size="4">
      <?
      	//Den Namen des aktuell ausgewählten Lieferanten gross ausgeben
      	$res=SQL_Query("SELECT name FROM CatSupplier WHERE id='$filter'");
      	$row=mysql_fetch_array($res);
      	echo $row[name]." :";
      ?>
      </font></td>
    </tr>
  </table>
<table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr> 
    <td class="liste" width="50">
    <img src="../images/spacer.gif" width="10" height="1"><a href="bestellen.php?filter=<?echo"$filter";?>&suche=<?echo"$suche";?>&order=nummer&direction=<?echo"$direction";?>">Nr.</a>
    </td>
    <td class="liste">
    <a href="bestellen.php?filter=<?echo"$filter";?>&suche=<?echo"$suche";?>&order=name&direction=<?echo"$direction";?>">Name</a>
    </td>
    <td class="liste" width="130">
    <a href="bestellen.php?filter=<?echo"$filter";?>&suche=<?echo"$suche";?>&order=size&direction=<?echo"$direction";?>">Grösse</a>
    </td>
    <td class="liste" width="210">
    <a href="bestellen.php?filter=<?echo"$filter";?>&suche=<?echo"$suche";?>&order=preis&direction=<?echo"$direction";?>">Preis</a>
    </td>
   </tr>
  <tr> 
    <td><img src="../images/spacer.gif" width="1" height="15"></td>
  </tr>
</table>
<?
	$i=0;
	//Alle Produkte des jeweiligen Lieferanten anzeigen ...
	if ($suche=="")	$res=SQL_Query("SELECT * FROM CatProduct WHERE lieferant='$filter' ORDER BY $order $direction");
	else $res=SQL_Query("SELECT * FROM CatProduct WHERE lieferant='$filter' AND name LIKE '%$suche%' OR beschreibung LIKE '%$suche%' ORDER BY $order $direction");
	if ($suche=="")	$res2=SQL_Query("SELECT * FROM CatProduct WHERE lieferant='$filter' ORDER BY $order $direction");
	else $res2=SQL_Query("SELECT * FROM CatProduct WHERE lieferant='$filter' AND name LIKE '%$suche%' OR beschreibung LIKE '%$suche%' ORDER BY $order $direction");
	$row2=mysql_fetch_array($res2);
	
	if ($row=mysql_fetch_array($res)) {
		do {
			$row2=mysql_fetch_array($res2);
			$row[preis]= FloatToCurrency($row[preis]);
			if ($row[size]==0)$size="klein";
			if ($row[size]==1)$size="mittel";
			if ($row[size]==2)$size="gross";
			if (($i>0)&&($row[nummer]!=$old_nummer)) {
			// Die Reihen-trennlinien nicht beim 1. mal zeichnen...	
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
				<?
			}
			$i++;			
			// Gleiche Pizza Nummer nur andere Grösse ?
			if ($row[nummer]==$old_nummer) {
				// Dann nur Grösse und Preis ausgeben ...
			?>
				<table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
				  <tr> 
				    <td rowspan="2" width="50" valign="top"><font size="4"></font></td>
				    <?
					  if ($row[nummer]==$row2[nummer]) {
					  	?>
					  	<td><img src="../images/spacer.gif" width="10" height="1"></td>
					  	<?
				    }
					  else {	
					  ?>	
					  	<td><font size="1"><?echo"$row[beschreibung]";?></font></td>
				    <?
				    }
				    ?>
				    <td width="25"><img src="../images/spacer.gif" width="10" height="1">|<img src="../images/spacer.gif" width="10" height="1"></td>
				    <td width="100" align="center"><font size="2"><?echo "$size";?></font></td>
				    <td width="25"><img src="../images/spacer.gif" width="10" height="1">|<img src="../images/spacer.gif" width="10" height="1"></td>
				    <td align="right" width="100"><font color="#FF0000"><?echo $row[preis];?></font> </td>
				    <td width="25"><img src="../images/spacer.gif" width="10" height="1">|<img src="../images/spacer.gif" width="10" height="1"></td>
				    <td width="80"><img src="../images/shopper3.gif" width="19" height="15"> <a href="einkaufswagen.php?action=add_wagen&id=<?echo"$row[id]";?>&filter=<?echo"$filter";?>">Bestellen</a></td>
				  </tr>
				</table>
			<?
			}
			else {
				?>
				<table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
				  <tr> 
				    <td rowspan="2" width="50" valign="top"><font size="4"><?echo"$row[nummer]";?></font></td>
				    <td><?echo"$row[name]";?></td>
				    <td width="25"><img src="../images/spacer.gif" width="10" height="1">|<img src="../images/spacer.gif" width="10" height="1"></td>
				    <td width="100" align="center"><font size="2"><?echo "$size";?></font></td>
				    <td width="25"><img src="../images/spacer.gif" width="10" height="1">|<img src="../images/spacer.gif" width="10" height="1"></td>
				    <td align="right" width="100"><font color="#FF0000"><?echo $row[preis];?></font> </td>
				    <td width="25"><img src="../images/spacer.gif" width="10" height="1">|<img src="../images/spacer.gif" width="10" height="1"></td>
				    <td width="80"><img src="../images/shopper3.gif" width="19" height="15"> <a href="einkaufswagen.php?action=add_wagen&id=<?echo"$row[id]";?>&filter=<?echo"$filter";?>">Bestellen</a></td>
				  </tr>
				  <?
				  if ($row[nummer]==$row2[nummer]) {
				  }
				  else {
				  ?>
				  <tr>
				    <td><font size="1"><?echo"$row[beschreibung]";?></font></td>
				  </tr>
				  <?
				  }
				  ?>
				</table>
				<?			
			}
			$old_nummer=$row[nummer];
		}
		while ($row=mysql_fetch_array($res));
	}
}
else {
	echo "Du must eingelogged sein um das Catering System nutzen zu können!";
}

EndPage();
?>
