<?
	$res = SQL_Query("SELECT COUNT(*) AS 'cnt' FROM guest WHERE id>0 $GL_WhereAdd");
	$rowcount = mysql_result($res, 0, 0);


	function PrintDel($url, $caption = "L&ouml;schen") {
		echo "<a href=\"$url\" class=\"action_del\" title=\"$caption\">&nbsp;X&nbsp;</a>";
	}

	function ListLink($newpage, $neworder = "") {
		global $sort, $desc, $page, $PHP_SELF, $sess;


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

		if (isset($sess))
			$txt = $sess->url($txt);

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
		user.name, user.clan, user.ip_address, guest.id, guest.flags, user.realname1, user.realname2
		FROM guest
			LEFT JOIN user ON user.id=guest.user
		WHERE guest.id>0 $whereadd
		ORDER BY $sort $SQLSort
		$SQLlimit");


	if ($rowcount > $DisplayRowCount && $action != "search") {
		echo "<p class=\"content\">";
		$PageCount = ceil($rowcount / $DisplayRowCount);

		for ($i=0; $i <= $PageCount; $i++) {
			$startindex = ($i) * $DisplayRowCount;
			if (!$startindex)
				$startindex = 1;
			$text = sprintf("%03d-%03d", $startindex, ($startindex + $DisplayRowCount));

			echo " ";

			if 	($i == $PageCount) {
				if ($page == -1)
					echo "Alle";
				else
					echo "<a href=\"".ListLink(-1)."\">Alle</a>";
			} elseif ($i == $page)
				echo $text;
			else
 				echo "<a href=\"".ListLink($i)."\">".$text."</a>";
 			if (($i + 1) % 6 == 0)
 				echo "<br>";
		}
		echo "</p>";
	}

	if ($GL_DisplaySearch) {
		FormStart("name=\"GuestSearch\" action=\"$PHP_SELF\" method=\"post\"");
			FormValue("action", "search");
			FormValue("id", $id);

			FormElement("f_search", "Suchen", $f_search);
			FormElement("", "", "Suchen", "submit");
		FormEnd();
	}

	echo "<table class=\"liste\">";

	echo "<tr class=\"liste\">";
	?>
			<th class="liste" width=180><a class=liste href="<? echo ListLink($page, "name"); ?>">Name</a></th>
			<th class="liste" width=80><a class=liste href="<? echo ListLink($page, "clan,name"); ?>">Clan</a></th>
			<th class="liste" width=80><a class=liste href="<? echo ListLink($page, "realname1"); ?>">Vorname</a></th>
			<th class="liste" width=80><a class=liste href="<? echo ListLink($page, "realname2"); ?>">Nachname</a></th>
			<th class="liste" width=80><a class=liste href="<? echo ListLink($page, "flags"); ?>">Status</a></th>
			<th class="liste" width=80>Anwesend</th>
			<th class="liste" width=80>IP-Adresse</th>
	<?
	echo "</tr>";


	while ($row = mysql_fetch_array($res)) {
		echo "<tr class=\"liste\">";

			echo "<td class=\"liste\">";
			if ($GL_DetailLink)
				echo "<a href=\"".sprintf($GL_DetailLink, $row[id], $page)."\">";
			echo HTMLStr($row[name]);
			if ($GL_DetailLink)
				echo "</a>";

			echo "</td>";

			echo "<td class=\"liste\">".HTMLStr($row[clan])."</td>";
			echo "<td class=liste>".$row[realname1]."</td>";
			echo "<td class=liste>".$row[realname2]."</td>";

			echo "<td class=\"liste\">";
			if ($row[flags] & GUEST_VIP)
				echo "VIP";
			elseif ($row[flags] & GUEST_EVENING)
				echo "Abendkasse";
			elseif ($row[flags] & GUEST_PREPAID)
				echo "Bezahlt (Vorgemerkt)";
			elseif ($row[flags] & GUEST_PAID)
				echo "Bezahlt";
			else
				echo "Nicht Bezahlt";
			echo "</td>";
			echo "<td class=\"liste\">";
			echo ($row[flags] & GUEST_ONLOCATION) ? "Anwesend" : "Nicht Anwesend";
			echo "</td>";

/*			echo "<td class=liste>";
			if ($row[sid])
					echo "<a href=\"seat.phtml?id=".$row[block]."&row=".$row[row]."&col=".$row[col]."\">".$row[block_name].", Platz: ".chr($row[row] + 65)." ".($row[col] + 1)."</a>";
			else
				echo "(Nicht Reserviert)";
			echo "</td>";*/
			echo "<td class=liste>".$row[ip_address]."</td>";

			if ($GL_DelLink) {
				echo "<td>";
				PrintDel(sprintf($GL_DelLink, $row[id], $page));
				echo "</td>";
			}

		echo "</tr>\n";
	}
	echo "<tr>";
		echo "<td colspan=2>";
		if ($page > 0)
			NavPrintPrevPage(ListLink($page - 1));
		else
			echo "&nbsp;";
		echo "</td>";

		echo "<td align=right>";
		if ($page < $PageCount - 1 && $page >= 0 && $PageCount)
			NavPrintNextPage(ListLink($page + 1));
		else
			echo "&nbsp;";
		echo "</td>";
	echo "</tr>";

	echo "</table>"

?>