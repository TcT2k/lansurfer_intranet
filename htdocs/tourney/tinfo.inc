<p>
<font face=Verdana size=2>
<b>Infos:</b>
  <ul type=square>
		<li>Maximale <? echo ($tourney->info[teamsize] > 1) ? "Teams" : "Spieler"; ?>: <b><? echo $tourney->info[maxteams]; ?></b></li>
		<li><? if (!($tourney->info[flags] & $TF_STARTED)) echo "Vorausichtlicher " ?>Start: <b><? echo shortdate($tourney->starttime); ?></b></li>
		<li>Ende: <b><? echo shortdate($tourney->endtime); ?></b></li>
		<? if ($tourney->info[teamsize] > 1): ?>
		<li>Spieler pro Team: <b><? echo $tourney->info[teamsize]; ?></b><? if (!($tourney->info[flags] & $TF_STARTED)) echo " <br>Teams mit <b>weniger</b> Spielern werden beim Start des Turniers <b>entfernt</b>."; ?></li>
		<? endif; ?>
	</ul>
</font>	
</p>
