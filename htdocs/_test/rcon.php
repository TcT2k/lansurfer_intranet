<?
	$LS_BASEPATH = '../';
	require $LS_BASEPATH.'../includes/ls_base.inc';
	
	
	StartPage(_("RCON"));
	
		if ($submitted) {
			$fp = fsockopen ('udp://'.$f_ip, $f_port, $errno, $errstr, 1);

			if (!$fp) {
			    echo "$errstr ($errno)<br>\n";
			} else {
			    $s = "\xff\xff\xff\xffrcon\x20".$f_password.' "'.$f_command."\"\x20\x00";
			    echo 'StrLen '.strlen($s)."<br>";
			    flush();
			    printf('%d bytes sent<br>', fputs($fp, $s, strlen($s)));
			    $s = '';
		    	socket_set_blocking ($fp, false);
		    	
		    	sleep(1);
		    	while ($tmp = fread($fp, 4096)) {
		    		$s .= $tmp;
		    		sleep(1);
		    	}
	    	
		    	echo '<br>';
		    	
	    		echo nl2br(str_replace("\xff\xff\xff\xff", "", $s));
			    fclose ($fp);
			}

			
		} else {
			$f_ip = '10.10.0.84';
			$f_port = 27960;
			$f_password = 'test';
			$f_command = 'echo test';
		}
		
		if (!$submitted || $FormErrorCount) {
			FormStart();
				FormValue('submitted', 1);
				FormElement('f_ip', _("Adress"), $f_ip);
				FormElement('f_port', _("Port"), $f_port);
				FormElement('f_password', _("Password"), $f_password, 'password');
				FormElement('f_command', _("Command"), $f_command);
				FormElement('', '', _("Execute"), 'submit');
			FormEnd();
		}
	
	EndPage();
	
?>