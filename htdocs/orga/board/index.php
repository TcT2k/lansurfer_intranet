<?
	$LS_BASEPATH = '../../';
	require $LS_BASEPATH."../includes/ls_base.inc";

	NavStruct("orga/");
	StartPage(_("Board Configuration"));

	//$auth->RequirePermission(PERM_TEAM, $team);
	user_auth_ex(AUTH_TEAM, 0, TEAM_FORUM, true);

?>
	<p class="content" id="group">
		<? NavPrintAction("edit.php?action=new", _("New Board")); ?><br>
		<br>
		<?
			$header = FALSE;
			$res = SQL_Query("SELECT id,name FROM forum ORDER BY name");
			while ($row = SQL_fetch_array($res)) {
				if (!$header) {
					$header = TRUE;
				?>
				<table class="liste">
				<tr class="liste">
					<th class="liste" width=260><? echo _("Name"); ?></th>
				</tr>
				<?
				}	
				?>
				<tr class="liste">
					<td class="liste"><a href="<? echo ("edit.php?id=".$row[id]); ?>"><? echo $row[name]; ?></a></td>
					<td><? NavPrintDel("edit.php?action=remove&team=$team&id=".$row[id]); ?></td>
				</tr>					
				<?
			}
			if ($header)
				echo "</table>";
		?>
	</p>

	<p class="content">
	<?
		NavPrintBack();
	?>
	</p>
	
<? 
	EndPage();
?>