<?
	$LS_BASEPATH = '../';
	require $LS_BASEPATH.'../includes/ls_base.inc';
  require $LS_BASEPATH."../includes/tourney/base.inc";
  require $LS_BASEPATH."../includes/tourney/tabutil.inc";


	$LS_POPUPPAGE = TRUE;

	$match = new Match($id);
	$tourney = new Tourney($match->tourneyID);
	$match->tourney = $tourney;

	user_auth();
	
	$tourneyAdmin = user_auth_ex(AUTH_TOURNEY, $tourney->id, 0, false);

	StartPage(_("Match Details"));
	
	PrintMatchTabs();
	
	if ($action == 'add') {
		if ($f_cmt) {
			SQL_Query("INSERT INTO TourneyMatchComment SET date=NOW(),".SQL_QueryFields(array(
				'user' => $user_current['id'],
				'text' => $f_cmt,
				'mtch' => $id,
				'tourney' => $tourney->id
				)));
			$f_cmt = '';
		} else {
			FormErrorEx('f_cmt', _("Comment text may not be empty."));
		}
	} elseif ($action == 'remove' && $tourneyAdmin) {
		echo '<p class=content>';
		SQL_Query("DELETE FROM TourneyMatchComment WHERE id=".SQL_Quot($cmt));
		echo _("Comment removed.");
		echo '</p>';
	}
	
	
	$res = SQL_Query("SELECT
			mc.id,
			u.name,
			mc.text,
			UNIX_TIMESTAMP(mc.date) date
		FROM
			TourneyMatchComment mc
			LEFT JOIN user u ON u.id=mc.user
		WHERE
			mc.mtch =".SQL_Quot($id));
			
	echo '<table width=100%>';
	for ($i = 1; $row = mysql_fetch_array($res); $i++) {
		echo '<tr>';
		echo '<th class=liste width="5% align=left">#'.$i.'</th>';
		echo '<th class=liste width="55% align=left">'.HTMLStr($row['name'], 30).'</th>';
		echo '<th class=liste width="40% align=right">'.DisplayDate($row['date']).'</th>';
		echo '</tr>';
		echo '<tr>';
		echo '<td class=liste colspan=3>'.Text2HTML($row['text']);
		if ($tourneyAdmin) {
			echo '<br>';
			echo "<a onclick=\"return window.confirm('"._("Do you really wish to remove this comment?")."');\" href=\"".$PHP_SELF."?id=".$id."&action=remove&cmt=".$row['id']."\">"._("Remove").'</a>';
		}
		echo '</td>';
		echo '</tr>';
	}
	echo '</table>';


	if ($user_valid) {
		FormStart();
			FormValue('action', 'add');
			FormValue('id', $id);
			FormElement('f_cmt', _("Comment"), $f_cmt, 'textarea', 'rows=8 cols=46');
			FormElement('', '', _("Add Comment"), 'submit');
		FormEnd();
	}

	echo '<p class=content>';
	//if (!$action)
		NavPrintAction("javascript:window.close()", _("Close Window"));
	/*else
		NavPrintAction($PHP_SELF."?id=".$id, _("Back"));*/
	echo '</p>';

	EndPage();
?>