<?
	// Copyright (c) 2001 Henrik 'Hotkey' Brinkmann  Email: hotkey@cncev.de

	$LS_BASEPATH = '../';
	include $LS_BASEPATH.'../includes/lsi_base.inc';
	StartPage("Catering");

	//Checken ob user eingelogged ist.

	//User id ermitteln 
	$user_id=$user_current[id];
	
	if ($mode=="del") {
		$res=SQL_Query("SELECT bearbeitet FROM CatOrder WHERE id='$id'");
		$row=mysql_fetch_array($res);
		if ($row[bearbeitet]=="1") LS_ERROR ("Die Bestellung kann nicht mehr gelöscht werden da sie bereits bearbeitet wurde !");
		else {
			mysql_query ("DELETE FROM CatOrder WHERE id='$id'");
			echo "Die Bestellung wurde gelöscht !<br><br>";
			?>
			<a href="catering.php">Zurück</a> zum Angebot<br>
			<?
		}		
	}
	
	if ($mode=="add") {
		if ($anzahl<1) LS_ERROR("Bitte einen gültigen Wert für die Anzahl eingeben!");
		else {
			$hour=date(H);
			$minute=date(i);
			if ($minute<10) $minute=0.$minute;
			$result=SQL_Query("SELECT 
														CatOrder.anzahl AS anzahl,
														CatProduct.preis AS preis
														FROM CatOrder,CatProduct
														WHERE CatOrder.angebot_id=CatProduct.id AND CatOrder.user_id='$user_id'");
			if ($row=mysql_fetch_array($result)) {
				do {
					$vorhandene_kosten+=($row[anzahl]*$row[preis]);
				}
				while ($row=mysql_fetch_array($result));
			}																
			$result=SQL_Query("SELECT kontostand FROM user WHERE id='$user_id'");
			$row=mysql_fetch_array($result);
			$kosten=($anzahl*$preis);
			$kosten+=$vorhandene_kosten;
			if ((($anzahl*$preis)>$row[kontostand])||($kosten>$row[kontostand])) LS_ERROR("Dein Kontostand reicht für die von Dir gewählte Bestellung nicht aus !");
			else {
				mysql_query ("INSERT INTO CatOrder (user_id,angebot_id,anzahl,bearbeitet,eingetroffen,hour,minute) VALUES ('$user_id','$angebot_id','$anzahl','0','0','$hour','$minute')");
				echo "Vielen Dank für deine Bestellung !<br><br>";
				?>
				<a href="catering.php">Zurück</a> zum Angebot<br>
				<?
			}
		}
	}
	
	?>
	
	
<?
	EndPage();
?>
