<?
	$LS_BASEPATH = "../";
	include $LS_BASEPATH."../includes/ls_base.inc";

	NavStruct("orga/");
	
	StartPage(_("Current Stats"));
	
	user_auth_ex(AUTH_TEAM);
	
	$res = SQL_Query("SELECT COUNT(*) FROM guest");
	$GuestCount = mysql_result($res, 0, 0);

	$res = SQL_Query("SELECT COUNT(*) FROM guest WHERE flags & ".GUEST_PAID);
	$PaidCount = mysql_result($res, 0, 0);

	$res = SQL_Query("SELECT COUNT(*) FROM guest WHERE flags & ".GUEST_PREPAID);
	$PrePaidCount = mysql_result($res, 0, 0);

	$res = SQL_Query("SELECT COUNT(*) FROM guest WHERE flags & ".GUEST_EVENING);
	$EveningCount = mysql_result($res, 0, 0);

	$res = SQL_Query("SELECT COUNT(*) FROM guest WHERE flags & ".GUEST_ONLOCATION);
	$OnLocationCount = mysql_result($res, 0, 0);

?>
	<table>
		<tr>
			<th class=liste><? echo _("Paid"); ?></th>
			<td class=liste align=right><? echo $PaidCount; ?></td>
		</tr>
		<tr>
			<td class=liste><? echo _("Payment Noted"); ?></td>
			<td class=liste align=right><? echo $PrePaidCount; ?></td>
		</tr>
		<tr>
			<td class=liste><? echo _("Evening Checkout"); ?></td>
			<td class=liste align=right><? echo $EveningCount; ?></td>
		</tr>
		<tr>
			<td colspan=2></td>
		</tr>
		<tr>
			<th class=liste><? echo _("On Location"); ?></th>
			<td class=liste align=right><? echo $OnLocationCount; ?></td>
		</tr>
		<tr>
			<th class=liste><? echo _("Overall"); ?></th>
			<td class=liste align=right><? echo $GuestCount; ?></td>
		</tr>
		
	</table>
<?	
	NavPrintBack();
	
	EndPage();
	
?>