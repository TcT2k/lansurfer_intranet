<?
	$res = SQL_Query("SELECT COUNT(*) AS 'cnt' FROM guest WHERE id>0 $GL_WhereAdd");
	$rowcount = mysql_result($res, 0, 0);
	
	if ($toggleonlocation) {
		
		$res = SQL_Query("SELECT * FROM guest WHERE id=".SQL_Quot($toggleonlocation));
		if ($row = mysql_fetch_array($res)) {
			if ($row['flags'] & GUEST_ONLOCATION)
				$row['flags'] &= ~GUEST_ONLOCATION;
			else
				$row['flags'] |= GUEST_ONLOCATION;
			SQL_Query("UPDATE guest SET flags=".$row['flags']." WHERE id=".SQL_Quot($toggleonlocation));
		}
	}
	
	function PrintDel($url, $caption = "") {
		if ($caption == "")
			$caption = _("Remove");
		NavPrintDel($url, $caption);
	}

	function ListLink($newpage = -2, $neworder = "") {
		global $sort, $desc, $page, $PHP_SELF;
	
		if ($newpage == -2)
			$newpage = $page;
		
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

		$txt = $PHP_SELF."?page=$newpage&sort=".$txt;
		
		return $txt;
	}

	$DisplayRowCount = 50;

	if (!$sort)
		$sort = "name";
	if (!isset($desc))
		$desc = 0;

	if (!isset($page))
		$page = 0;
	if ($action == "search")
		$page = -1;

	if ($page >= 0) {
		$startindex= $DisplayRowCount * $page;
		$SQLlimit = "LIMIT $startindex, $DisplayRowCount";
	} else {
		$SQLlimit = "";
	}

	if ($desc) {
		$SQLSort = "DESC";
	}
	else
		$SQLSort = "";

	flush();

	if ($action == "search") {
		$whereadd = "AND (name like '%$f_search%' OR clan like '%$f_search%' OR 
			realname1 like '%$f_search%' OR realname2 like '%$f_search%')";
	} else 
		$whereadd = "";

	$res = SQL_Query("SELECT 
		user.name, user.clan, guest.id, guest.flags, user.realname1, user.realname2
		FROM guest 
			LEFT JOIN user ON user.id=guest.user
		WHERE guest.id>0 $whereadd
		ORDER BY $sort $SQLSort
		$SQLlimit");


	if ($rowcount > $DisplayRowCount && $action != "search") {
		echo "<p class=\"content\">";
		
		echo '<table><tr>';
		
		$PageCount = ceil($rowcount / $DisplayRowCount);
	
		for ($i=0; $i <= $PageCount; $i++) {
			$startindex = ($i) * $DisplayRowCount;
			if (!$startindex)
				$startindex = 1;
			$text = sprintf("%03d-%03d", $startindex, ($startindex + $DisplayRowCount));
			
			echo '<td width="100" class=TourneyTab';
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
 			if (($i + 1) % 6 == 0)
 				echo "</tr><tr>";
		}
		echo '</tr></table>';
		echo "</p>";
	}
	
	if ($GL_DisplaySearch) {
		FormStart("name=\"GuestSearch\" action=\"$PHP_SELF\" method=\"post\"");
			FormValue("action", "search");
			FormValue("id", $id);
			
			FormElement("f_search", _("Search"), $f_search);
			FormElement("", "", _("Search"), "submit");
		FormEnd();
	}
	
	echo "<table class=\"liste\">";

	echo "<tr>";
		echo "<td class=content colspan=2>";
		if ($page > 0) 
			NavPrintPrevPage(ListLink($page - 1)); 
		else
			echo "&nbsp;";
		echo "</td>";
		
		echo "<td class=content align=right colspan=4>";
		if ($page < $PageCount - 1 && $page >= 0 && $PageCount)
			NavPrintNextPage(ListLink($page + 1)); 
		else
			echo "&nbsp;";
		echo "</td>";
	echo "</tr>";
	
	echo "<tr class=\"liste\">";
	?>
			<th class="liste" width=180><a class=liste href="<? echo ListLink($page, "name"); ?>"><? echo _("Name"); ?></a></th>
			<th class="liste" width=80><a class=liste href="<? echo ListLink($page, "clan,name"); ?>"><? echo _("Clan"); ?></a></th>
			<th class="liste" width=80><a class=liste href="<? echo ListLink($page, "realname1"); ?>"><? echo _("First Name"); ?></a></th>
			<th class="liste" width=80><a class=liste href="<? echo ListLink($page, "realname2"); ?>"><? echo _("Last Name"); ?></a></th>
			<th class="liste" width=80><a class=liste href="<? echo ListLink($page, "flags"); ?>"><? echo _("Status"); ?></a></th>
			<th class="liste" width=80><? echo _("On Location"); ?></th>
	<?			
	echo "</tr>";

	while ($row = mysql_fetch_array($res)) {
		echo "<tr class=\"liste\">";
			
			echo "<td class=\"liste\">";
			if ($GL_DetailLink)
				echo "<a href=\"".sprintf($GL_DetailLink, $row[id], $page)."\">";
			echo HTMLStr($row[name], 25);
			if ($GL_DetailLink)
				echo "</a>";
			
			echo "</td>";

			echo "<td class=\"liste\">".HTMLStr($row[clan], 20)."</td>";
			echo "<td class=liste>".$row[realname1]."</td>";
			echo "<td class=liste>".$row[realname2]."</td>";
			
			echo "<td class=\"liste\">";
			if ($row[flags] & GUEST_VIP)
				echo _("VIP");
			elseif ($row[flags] & GUEST_EVENING)
				echo _("Evening Checkout");
			elseif ($row[flags] & GUEST_PREPAID)
				echo _("Payment Noted");
			elseif ($row[flags] & GUEST_PAID)
				echo _("Paid");
			else
				echo "Nicht Bezahlt";
			echo "</td>";
			echo "<td class=\"liste\">";
			echo '<a href="'.ListLink().'&toggleonlocation='.$row['id'].'">';
			echo ($row[flags] & GUEST_ONLOCATION) ? _("Yes") : _("No");
			echo '</a>';
			echo "</td>";
			
			if ($GL_DelLink) {
				echo "<td>";
				PrintDel(sprintf($GL_DelLink, $row[id], $page));
				echo "</td>";
			}
		
		echo "</tr>\n";
	}
	echo "<tr>";
		echo "<td class=content colspan=2>";
		if ($page > 0) 
			NavPrintPrevPage(ListLink($page - 1)); 
		else
			echo "&nbsp;";
		echo "</td>";
		
		echo "<td class=content align=right colspan=4>";
		if ($page < $PageCount - 1 && $page >= 0 && $PageCount)
			NavPrintNextPage(ListLink($page + 1)); 
		else
			echo "&nbsp;";
		echo "</td>";
	echo "</tr>";
	
	echo "</table>"

?>