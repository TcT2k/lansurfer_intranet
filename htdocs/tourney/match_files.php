<?
	$LS_BASEPATH = '../';
	require $LS_BASEPATH.'../includes/ls_base.inc';
  require $LS_BASEPATH."../includes/tourney/base.inc";
  require $LS_BASEPATH."../includes/tourney/tabutil.inc";


	if ($download) {
		$res = SQL_Query("SELECT * FROM TourneyMatchFile WHERE id=".SQL_Quot($download));
		if ($row = mysql_fetch_array($res)) {
			
			if ($fp = fopen($LS_BASEPATH.'../includes/tourney/upload/'.$row['id'], 'r')) {
				Header("Content-Type: application/octetstream");
				Header("Content-disposition: filename=".$row['filename']);
				Header("Content-Length: ".$row[size]);
				
				fpassthru($fp);
				fclose($fp);
				die();
			}
		}
	}

	$LS_POPUPPAGE = TRUE;

	$match = new Match($id);
	$tourney = new Tourney($match->tourneyID);
	$match->tourney = $tourney;

	user_auth();
	
	$tourneyAdmin = user_auth_ex(AUTH_TOURNEY, $tourney->id, 0, false);

	StartPage(_("Match Details"));

	PrintMatchTabs();

	if (LS_TOURNEY_UPLOAD && $action == 'add') {
		if (!$f_desc)
			FormErrorEx('f_desc', _("Description may not be empty."));
		if (!$f_file_name)
			FormErrorEx('f_file', _("A file must be specified"));
		if (!$FormErrorCount) {
			SQL_Query("INSERT INTO TourneyMatchFile SET date=NOW(),".SQL_QueryFields(array(
				'tourney' => $tourney->id,
				'mtch' => $id,
				'user' => $user_current['id'],
				'dsc' => $f_desc,
				'filename' => $f_file_name,
				'size' => $f_file_size
				)));
			move_uploaded_file($f_file, $LS_BASEPATH.'../includes/tourney/upload/'.mysql_insert_id());
			echo '<p class=content>';
			echo _("File Uploaded.");
			echo '</p>';
		}
	}
	
	$match->Load($tourney->TeamSize);
	$teamAdmin = $user_valid && ($match->team1->leader == $user_current['id'] || $match->team2->leader == $user_current['id'] || $tourneyAdmin);

	if ($tourneyAdmin && $remove) {
		SQL_Query("DELETE FROM TourneyMatchFile WHERE id=".SQL_Quot($remove));
		@unlink($LS_BASEPATH.'../includes/tourney/upload/'.$remove);
	}
	
	$res = SQL_Query("SELECT
			mf.id,
			mf.filename,
			mf.dsc,
			mf.size,
			u.name
		FROM
			TourneyMatchFile mf
			LEFT JOIN user u ON mf.user=u.id
		WHERE
			mtch=".SQL_Quot($id));
	
	echo '<table width="100%">';
	echo '<tr>';
	echo '<th class=liste width="90%">'._("Description").'</th>';
	echo '<th class=liste width="10%">'._("Size").'</th>';
	echo '</tr>';
	
	while ($row = mysql_fetch_array($res)) {
		echo '<tr>';
		echo '<td class=liste><a href="'.$PHP_SELF.'?download='.$row['id'].'">'.HTMLStr($row['dsc'], 40).'</td>';
		echo '<td class=liste align=right>'.sprintf(_("%d KB"), $row['size'] / 1024).'</td>';
		if ($tourneyAdmin) {
			echo '<td>';
			NavPrintDel($PHP_SELF.'?id='.$id.'&remove='.$row['id'], _("Remove"), "onclick=\"return window.confirm('"._("Do you really wish to remove this file?")."');\"");
			echo '</td>';
		}
		echo '</tr>';
	}
	
	echo '</table>';
	
	if ($teamAdmin && LS_TOURNEY_UPLOAD) {
		FormStart('enctype="multipart/form-data" action="'.$PHP_SELF.'" method="post"');
			FormValue('id', $id);
			FormValue('action', 'add');
			
			FormElement('f_desc', _("Description"), $f_desc, 'text', 'size=40 maxlength=250');
			FormElement('f_file', _("File"), '', 'file', 'size=40');
			
			FormElement('', '', _("Upload"), 'submit');
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