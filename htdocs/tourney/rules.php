<?
	$LS_BASEPATH = '../';
	require $LS_BASEPATH.'../includes/ls_base.inc';
  require $LS_BASEPATH."../includes/tourney/base.inc";
	require $LS_BASEPATH."../includes/tourney/tabs.inc";
	
  NavStruct("tournaments");
	StartPage($tourney->info['name'], 1, $LS_BASEPATH.'images/tourney/icons/'.$tourney->info['icon']);

	PrintTabs();

	if ($tourney->info['rules']) {
		
		if (strpos($tourney->info['rules'], '.pdf')) {
			echo '<p class=content>';
			echo '<a href="rules/'.$tourney->info['rules'].'" target=_blank><img border=0 src="'.$LS_BASEPATH.'images/acrobat.gif" width=32 height=32> '._("View rules (Adobe Acrobat Reader required)").'</a>';
			echo '</p>';
		} else
  		include 'rules/'.$tourney->info['rules'];
	} else {
		echo '<p class=content>';
		echo _("No rules are defined for this tournament.");
		echo '</p>';
	}

	EndPage();
?>