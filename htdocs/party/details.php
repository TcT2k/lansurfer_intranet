<?
	$LS_BASEPATH = "../";
	include $LS_BASEPATH."../includes/ls_base.inc";

	if ($action == "login") {
		$login_error = user_login($form_id, $form_password);
	} elseif ($action == "logout") {
		user_logout();
	}

	StartPage(_("My Details"), $party);
	
	if ($action == "logout" || $action == "login") {
?>
  <script language="JavaScript">
  <!--
  var status_frame = parent.frames["navbar"];
  if (status_frame.location) {
  	var url = status_frame.location.href;
  	status_frame.location.replace(url);
  }
  //-->
  </script>
<?		
	}
	
	user_auth();
	
	if (!($user_valid) || $login_error || $action == "logout") {
	
		FormStart("action=\"".$PHP_SELF."\" method=\"post\"");
			if ($login_error == 1)
				FormError("form_id", _("Unknown Email or LANsurfer ID"));
			elseif ($login_error == 2)
				FormError("form_password", _("The password is incorrect"));
				
			FormGroup(_("Login"));
			FormValue("action", "login");
			FormValue("party", $party);
			FormElement("form_id", _("Email or LANsurfer ID"), $form_id);
			FormElement("form_password", _("Password"), "", "password");
			FormElement("", "", _("Login"), "submit");
		FormEnd();
		
	} else {
	
		$res = SQL_Query("SELECT * FROM guest WHERE user=".$user_current[id]);
		$Guest = mysql_fetch_array($res);
		
		if ($Guest) {
			$res = SQL_Query("SELECT * FROM seats WHERE guest=".$Guest[id]." AND status=".SEAT_OCCUPIED);
			$Seat = mysql_fetch_array($res);
		}
			
?>
	<p class="content">
	<table class="content">
	<? NavPrintAction("details_edit.php", _("Change Profile")) ?><br>
	<br>
	<?
		function PrintValue($caption, $name) {
			global $user_current;
			
			echo "<tr class=\"content\"><td align=\"right\" class=\"content\"><b>$caption</b>:</td><td class=\"content\">";
			if ($name == "email")
				echo "<a href=\"mailtto:".$user_current[$name]."\">".HTMLStr($user_current[$name])."</a>";
			else
				echo HTMLStr($user_current[$name]);
			echo "</td></tr>\n";
		}
		PrintValue(_("Name"), "name");
		PrintValue(_("Clan"), "clan");
		PrintValue(_("First Name"), "realname1");
		PrintValue(_("Email"), "email");
		if (LS_IP_WARN) {
			if ($user_current["ip_address"] == "")
			  $addition = "<font color='red'>Dir wurde keine IP-Adresse zugewiesen - bitte melde dich bei einem Orga!</font>";
	   	PrintValue("Zugewiesene<br>IP-Adresse", "ip_address", $addition);
	?>
	<tr class="content"><td align="right" class="content"><b>Derzeitige<br>IP-Adresse</b>:</td><td class="content">
  <?
    if (getenv(HTTP_X_FORWARDED_FOR))
    {
      $ip=getenv(HTTP_X_FORWARDED_FOR);
    }
    else
    {
      $ip=getenv(REMOTE_ADDR);
    }
    echo $ip;
    if ($user_current["ip_address"] == $ip)
      echo " -> alles klar";
    else
      if ($user_current["ip_address"] != "")
        echo " -> <font color='red'>Fehlkonfiguration! Bitte ändere Deine IP-Adresse auf ".$user_current["ip_address"]."!</font>";
  ?>
</td></tR>
<?
	  }
?>

	</table>
	</p>
	<p class="content">
  <font color="red">Bitte beachten:</font> Ihr müßt Cookies aktiviert haben um korrekt eingeloggt zu sein!
  </p>
	<p class=content>&nbsp;</p>
	<p class="content">
	<? NavPrintAction($PHP_SELF."?action=logout&party=$party", _("Logout")); ?><br>
	</p>

<?	
	
	}
	
	EndPage();
	
?>
