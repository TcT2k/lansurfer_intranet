<?
	$LS_BASEPATH = '../';
	require $LS_BASEPATH.'../includes/ls_base.inc';
  require $LS_BASEPATH."../includes/tourney/base.inc";
	require $LS_BASEPATH."../includes/tourney/tabs.inc";
	
  NavStruct("tournaments");

	StartPage($tourney->info['name'], 1, $LS_BASEPATH.'images/tourney/icons/'.$tourney->info['icon']);

	PrintTabs();

	$tourney->RenderTree();

	EndPage();

?>