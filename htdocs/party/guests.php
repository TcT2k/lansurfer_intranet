<?
	$LS_BASEPATH = "../";
	include $LS_BASEPATH."../includes/lsi_base.inc";
	
	/*
			format:
				1: Teilnehmer Liste
				2: Gästeliste
				3: Warteliste
	*/
	$DisplayRowCount = 50;
	
	if (!isset($format) || $format > 3)
		$format = 1;

	switch ($format) {
		case 1:
			$title = _("Subscriber list");
			break;
		case 2:
			$title = _("Guests");
			break;
		case 3:
			$title = _("Waiting list");
			break;
	}

	function ListLink($newpage, $neworder = "") {
		global $party, $sort, $desc, $page, $PHP_SELF;

		$txt = "";
	
		if ($neworder) {
			$txt .= $neworder;
			if ($page >= 0)
				$newpage = 0;
		} else
			$txt .= $sort;
		
		if ($neworder == $sort)
			$txt .= "&desc=".(!$desc);
		else
			$txt .= "&desc=".($desc);

		$txt = $PHP_SELF."?party=$party&page=$newpage&sort=".$txt;
		
		return $txt;
	}

	if (!isset($sort))
		$sort = "clan, name";
	if (!isset($desc))
		$desc = 0;
	
	StartPage($title);
	
	if ($format == 2)
		$whereadd = "AND (guest.flags & ".GUEST_PAID.")";
	elseif ($format == 3)
		$whereadd = "AND NOT (guest.flags & ".GUEST_PAID.")";
	else
		$whereadd = "";
	$res= SQL_Query("SELECT COUNT(*) as 'cnt' FROM guest");
	$row = mysql_fetch_array($res);
	$rowcount = $row[cnt];
	
	if (!$f_search) {
		echo '<p class="content">';
		
		if ($rowcount > $DisplayRowCount) {
			echo '<table><tr>';
			$PageCount = ceil($rowcount / $DisplayRowCount);

			for ($i=0; $i <= $PageCount; $i++) {
				$startindex = $i * $DisplayRowCount;
/*				if (!$startindex)
					$startindex = 1;*/
				$text = sprintf("%03d-%03d", $startindex + 1, ($startindex + $DisplayRowCount));
				
				echo '<td width="100" class=TourneyTab ';
				if ($i == $page || ($i == $PageCount && $page == -1))
					echo " id=TourneyTabSelected";
				echo '>';
				if 	($i == $PageCount) {
					if ($page == -1)
						echo _("All");
					else
						echo "<a href=\"".ListLink(-1)."\">"._("All")."</a>";
				} elseif ($i == $page)
					echo $text;
				else
	 				echo "<a href=\"".ListLink($i)."\">".$text."</a>";
	 			echo '</td>';
	 			if (($i + 1) % 8 == 0)
	 				echo "</tr><tr>";
			}
			echo '</tr></table>';
		}
		printf(_("%d players on the list."), $rowcount);
		echo '</p>';
	}

	echo '<form action="'.$PHP_SELF.'" method="post" name="Search"><input type=text name=f_search value="'.$f_search.'"> <input type=submit class=form_btn value="'._("Search").'"></form>';
	
	if ($page >= 0) {
		$startindex= $DisplayRowCount * $page;
		$SQLlimit = "LIMIT $startindex, $DisplayRowCount";
	} else {
		$SQLlimit = "";
	}
	
	if ($desc)
		$SQLSort = "DESC";
	else
		$SQLSort = "";
	
	flush();
	
	if ($f_search)
		$whereadd = " user.name like '%".SQL_Str($f_search)."%' OR  user.clan like '%".SQL_Str($f_search)."%'";
	
	if ($whereadd)
		$whereadd = "WHERE ".$whereadd;

	$sres = SQL_Query("SELECT 
			s.guest,
			sb.name,
			s.row,
			s.col,
			s.block
		FROM 
		seats s
		LEFT JOIN seat_block sb ON s.block=sb.id
		WHERE s.status=".SEAT_OCCUPIED."
		ORDER BY s.guest");
	while ($row = mysql_fetch_array($sres))
		$seats[$row['guest']] = $row;
	mysql_free_result($sres);
	
	function GetSeat($gid) {
		global $seats;
		
		return $seats[$gid];
	}
	
	$res = SQL_Query("SELECT 
		user.name,
		user.clan,
		user.id,
		user.ip_address,
		guest.flags,
		guest.id as 'gid'
		FROM guest 
			LEFT JOIN user ON (user.id=guest.user) 
		$whereadd
		ORDER BY $sort $SQLSort
		$SQLlimit");

	?>
	<table class="liste" align="center" width="98%">
		<tr>
			<td class="content"><?  
			if ($page > 0) 
				NavPrintPrevPage(ListLink($page - 1)); 
			else
				echo "&nbsp;";
			?></td>
			<td class="content" align="right" colspan=<? echo (LS_IP_PUBLIC) ? 3: 2; ?>><? 
			if ($page < $PageCount - 1 && $page >= 0 && $PageCount)
				NavPrintNextPage(ListLink($page + 1)); 
			else
				echo "&nbsp;";
			?></td>
		</tr>
		<tr class="liste">
			<th class="liste" width="35%"><? echo '<a href="'.ListLink($page, "name").'">'._("Name").'</a>'; ?></th>
			<th class="liste" width="20%"><? echo '<a href="'.ListLink($page, "clan,name").'">'._("Clan").'</a>'; ?></th>
			<? if ($format == 1): ?>
			<th class="liste" width="35%"><? echo '<a href="'.ListLink($page, "flags").'">'._("Status").'</a>'; ?></th>
			<? endif; ?>
			<? if (LS_IP_PUBLIC): ?> 
			<th class="liste" width="35%"><? echo '<a href="'.ListLink($page, "ip_address").'">'._("IP address").'</a>'; ?></th>
			<? endif; ?>
		</tr>
	<?
		while ($row=mysql_fetch_array($res)) {
	?>
		<tr class="liste">
			<td class="liste"><? PrintIMSContactLink($row['id'], HTMLStr($row[name])); ?></td>
			<td class="liste"><? echo ($row[clan] == "") ? "&nbsp;" : HTMLStr($row[clan]); ?></td>
			<? if ($format==1): ?>
			<td class="liste"><? 
				$seat = GetSeat($row['gid']);
				if ($seat) {
					echo '<a href="seat.php?id='.$seat[block].'&row='.$seat[row].'&col='.$seat[col].'">'.$seat[name].', '._("Seat").": ".chr($seat[row] + 65)." ".($seat[col] + 1)."</a>";
				} else
					echo ($row[flags] & GUEST_PAID) ? _("Paid") : _("Not Paid"); ?></td>
			<? endif; ?>
			<? if (LS_IP_PUBLIC): ?> 
			<td class="liste"><? echo ($row['ip_address']) ? $row['ip_address'] : '&nbsp;'; ?></td>
			<? endif; ?>
		</tr>
	<?
		}
	?>
		<tr>
			<td class="content"><?  
			if ($page > 0) 
				NavPrintPrevPage(ListLink($page - 1)); 
			else
				echo "&nbsp;";
			?></td>
			<td class="content" align="right" colspan=<? echo (LS_IP_PUBLIC) ? 3: 2; ?>><? 
			if ($page < $PageCount - 1 && $page >= 0 && $PageCount)
				NavPrintNextPage(ListLink($page + 1)); 
			else
				echo "&nbsp;";
			?></td>
		</tr>
	</table>
<SCRIPT LANGUAGE="JavaScript">
<!--
document.Search.f_search.focus();
//  -->
</SCRIPT>
<?

	EndPage("");
?>
