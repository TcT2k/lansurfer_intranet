<?
// LANSURFER Basis script

// Scripte
	require $LS_BASEPATH."../includes/ls_const.inc";
	require $LS_BASEPATH."../includes/ls_conf.inc";
  require $LS_BASEPATH."../includes/sql_util.inc";
	require $LS_BASEPATH."../includes/ls_printing.inc";
	require $LS_BASEPATH."../includes/user_util.inc";

// Funktionen

	function LS_Error($msg, $die = TRUE) {
		echo $msg;
		echo "</table>";
		EndPage();
		flush();
		if ($die)
			die;
	}

	function StartPage($title = "", $innerstyle = 1) {
		global $LS_BASEPATH, $LS_TITLEPAGE, $LS_Design, $footerhtml, $HTTP_COOKIE_VARS, $LS_POPUPPAGE;


		user_auth();
?>
<html>
<head>
	<title><? echo $title; ?></title>
<!--

-->	
	
<style type="text/css">
<!--
A{
	color : #99ccff;
	font : 11px Verdana, Helvetica;
	text-decoration : none;
}

A:visited {
	color : #99ccff;
}

A:hover {
	color : #FFFFFF;
}

A:active {
	color : #99ccff;
}

body {
	scrollbar-face-color : #666666;
	scrollbar-highlight-color : #333333;
	scrollbar-shadow-color : #222222;
	scrollbar-arrow-color : #9cCEFF;
	scrollbar-3dlight-color : #888888;
	scrollbar-track-color : #222222;
	scrollbar-darkshadow-color : #111111;
}

H3.content {
	FONT-FAMILY: Verdana,Helvetica;
	FONT-SIZE: 17px;
	FONT-WEIGHT: bold;
  COLOR: #99ccff;
}
.content {
	FONT-FAMILY: Verdana,Helvetica;
	FONT-SIZE: 12px;
	FONT-WEIGHT: normal;
}

.form {
	FONT-FAMILY: Verdana,Helvetica;
	FONT-SIZE: 12px;
	FONT-WEIGHT: normal;
}

TH.liste {
	FONT-FAMILY: Verdana,Helvetica;
	FONT-SIZE: 12px;
	FONT-WEIGHT: normal;
         BACKGROUND-COLOR: #333333;
}

TD.liste {
	FONT-FAMILY: Verdana,Helvetica;
	FONT-SIZE: 12px;
	FONT-WEIGHT: normal;
  BACKGROUND-COLOR: #555555;
}

TD.openmatch {
	FONT-FAMILY: Verdana,Helvetica;
	FONT-SIZE: 12px;
	FONT-WEIGHT: normal;
	COLOR: #909090;
  BACKGROUND-COLOR: #555555;
}

.form_field {
  font:10pt 'Verdana';
  border-color: black;
  border-style:solid;
  border-width:2;
}

.form_btn {
background-color: black; 
color: white;
font: 12px 'Arial';
font-weight:bold;
border-color:#99ccff;
}


.formerror {
	FONT-FAMILY: Verdana,Helvetica;
	FONT-SIZE: 12px;
	FONT-WEIGHT: bold;
         COLOR: #900000;
}

.formerrorsign {
	FONT-SIZE: 20px;
         COLOR: #900000;
}

#signupsign {
	FONT-SIZE: 30px;
}

#signupsign_ready {
	FONT-SIZE: 30px;
         COLOR: #E7B500;
}

A.tlink:hover {
	TEXT-DECORATION: none;
}

A.nav_link{
	FONT-FAMILY: Verdana,Helvetica;
	FONT-SIZE: 11px;
	FONT-WEIGHT: normal;
	TEXT-DECORATION: none;
}
A.nav_link:hover {
	TEXT-DECORATION: underline;
}

#nav_level1 {
	COLOR: #999999;
}

#nav_level2 {
	COLOR: #CCCCCC;
}

#nav_level3 {
	COLOR: #999999;
}

#nav_level4 {
	COLOR: #666666;
}

-->
</style>
<? if ($innerstyle == STYLE_NAVBAR): ?>
<base target="main">
<? endif; ?>
</head>

<body text=white bgcolor=<? echo ($innerstyle != STYLE_NAVBAR) ? /*"#333333"*/ "black" : "black"; ?> class=content>
<? if (!$LS_POPUPPAGE): ?>
<h3 class=content><? echo $title; ?></h3>
<?
if ($title)
	NavPrint($title);
?>
<? endif; ?>
<p class="content">
<?		
		
	}
	
	function EndPage() {
		global $LS_BASEPATH, $LS_TITLEPAGE, $LS_Design, $footerhtml;
		
		echo "</p>";
		echo "<!-- End Content -->\n";
		echo $footerhtml;
		
		SQL_Done();
	}
	
	function LS_GetParty($id) {
		$res = SQL_Query("SELECT * FROM party WHERE id=$id");
		$row = mysql_fetch_array($res);
		return $row;
	}
	
	function LS_GetTeam($id) {
		$res = SQL_Query("SELECT * FROM orga_team WHERE id=$id");
		$row = mysql_fetch_array($res);
		return $row;
	}

?>
