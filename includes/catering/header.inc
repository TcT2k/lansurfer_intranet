<table width="95%" border="0" cellpadding="0" cellspacing="0" align="center">
  <tr> 
    <td class="liste"><img src="../images/spacer.gif" width="10" height="1">Letzte Bestellung:<img src="../images/spacer.gif" width="10" height="1"><?echo"$text";?><?if ($id>0) {?> (<a href="konto.php?action=detailed&id=<?echo"$id";?>">Info</a>)<?}?></td>
    <td class="liste" width="100" align="right"><a href="einkaufswagen.php">Einkaufswagen <a></td>
    <td class="liste" width="25" align="right"><a href="einkaufswagen.php"><img src="../images/shopper.gif" width="19" height="15" border=0"><a></td>
    <td class="liste" width="25" align="center"><img src="../images/spacer.gif" width="10" height="1">|<img src="../images/spacer.gif" width="10" height="1"></td>
    <td class="liste" width="140" align="center"><a href="konto.php">Konto</a>: <?echo $user_current['kontostand']; echo LS_CATERING_CURRENCY;?></td>
    <td class="liste" width="15" align="center"><img src="../images/spacer.gif" width="10" height="1">|</td>
    <td class="liste" align="center"><a href="hilfe.php">Hilfe</font></a></td>
  </tr>
 </table>
