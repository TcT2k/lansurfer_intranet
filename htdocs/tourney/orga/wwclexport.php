<?php

$LS_BASEPATH = '../../';
require $LS_BASEPATH.'../includes/ls_base.inc';
require $LS_BASEPATH."../includes/tourney/base.inc";

$nl="
";
	
user_auth();
	
user_auth_ex(AUTH_TOURNEY, -1, 0, TRUE);

if (!$pvdid)
{
	StartPage("XML "._("WWCL Export"));

	echo _("To generate the XML WWCL Export, we need some data.");

	FormStart();
	FormElement('pvdid', _("PlanetLAN PVDID"), '', 'text');
	FormElement('pid', _("WWCL PID"), '', 'text');
	FormElement('town', _("Location of this LAN (Town)"), '', 'text');
	FormElement('name', _("Name of this LAN party"), '', 'text');
	FormElement('', '', _("Initiate WWCL Export"), 'submit');
	FormEnd();

	echo _("Export schedule");
	$res=SQL_Query("SELECT name, status, options FROM tourney order by 1");

	echo '<table>';
	echo '<tr>';
	echo '<th class=liste width=200>'._("Name").'</th>';
	echo '<th class=liste width=80>'._("WWCL").'</th>';
	echo '<th class=liste width=80>'._("Finished").'</th>';
	echo '<th class=liste width=200>'._("Status").'</th>';
	echo '</tr>';

	while ($data=mysql_fetch_array($res))
	{
		echo '<tr>';
		echo '<td class=liste>'.$data[0].'</td>';
		echo ($data[2]&TO_WWCL) ? '<td class=liste>'._("Yes").'</td>' : '<td class=liste>'._("No").'</td>';
		echo ($data[1]==TS_FINISHED) ? '<td class=liste>'._("Yes").'</td>' : '<td class=liste>'._("No").'</td>';
		echo (($data[2]&TO_WWCL) and ($data[1]==TS_FINISHED)) ? '<td class=liste>'._("OK").'</td>' : '<td class=liste>'._("Not included in WWCL export").'</td>';
		echo '</tr>';
	}

	echo '</table>';

	NavPrintBack();
	EndPage();
}
else
{
	header("Content-disposition: attachment; filename=wwcl.xml");

	echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>".$nl.$nl;
	echo "<wwcl>".$nl.$nl;
	echo "<submit>".$nl;
	{
		echo "<tool>LANsurfer Intranet ".LS_INTRANET_VERSION." http://www.lansurfer.com/</tool>".$nl;
		$res = SQL_Query("SELECT unix_timestamp(sysdate())");
		$data = mysql_fetch_array ($res);
		echo "<timestamp>".$data[0]."</timestamp>".$nl;
		echo "<party_name>".$name."</party_name>".$nl;
		echo "<pid>".$pid."</pid>".$nl;
		echo "<pvdid>".$pvdid."</pvdid>".$nl;
		echo "<stadt>".$town."</stadt>".$nl;
	}
	echo "</submit>".$nl.$nl;
	echo "<tmpplayer>".$nl;
	{
		$res=SQL_Query("SELECT tt.id, tt.wwclid, u.wwclid, t.teamsize, u.email, u.name, tt.name FROM tourney t, tourneyteam tt left join user u on tt.leader=u.id where t.id=tt.tourney and t.status=".TS_FINISHED." and t.options&".TO_WWCL." order by 1");
		$teams=0;
		$tmpclans=0;
		$tmpplayers=0;
		$teamid=array();
		$wwclid=array();
		$isteam=array();
		$emails=array();
		while ($data=mysql_fetch_array($res))
		{
			$teamid[$teams]=$data[0];
			$isteam[$teams]=($data[3] > 1);
			$emails[$teams]=$data[4];

			if ($data[3] > 1) $wwclnr=$data[1];
			else $wwclnr=$data[2];

			if ($wwclnr)
			{
				$wwclid[$teams] =($isteam[$teams])? 'C' : 'P';
				$wwclid[$teams].=$wwclnr;
			}
			else
			{
				$found=FALSE;
				$i=0;
				while ($i<$teams and !($found))
				{
					if (($isteam[$i]==$isteam[$teams]) and ($emails[$i]==$emails[$teams])) $found=TRUE;
					else $i++;
				}
				if ($found) $wwclid[$teams]=$wwclid[$i];
				else
				{
					if ($isteam[$teams])
					{
						$tmpclans++;
						$wwclid[$teams].='CT'.$tmpclans;
						$tmpname=$data[6];
					}
					else
					{
						$tmpplayers++;
						$wwclid[$teams].='PT'.$tmpplayers;
						$tmpname=$data[5];
					}
					echo "<data>";
					echo "<tmpid>".$wwclid[$teams]."</tmpid>";
					echo "<name>".htmlspecialchars($tmpname)."</name>";
					echo "<email>".htmlspecialchars($data[4])."</email>";
//					echo "<name>".HTMLStr($tmpname)."</name>";
//					echo "<email>".HTMLStr($data[4])."</email>";
					echo "</data>".$nl;
				}
			}
			$teams++;
		}
	}
	echo "</tmpplayer>".$nl.$nl;
	{
		$res=SQL_Query("SELECT id, wwcltype, name, maxteams FROM tourney where status=".TS_FINISHED." and options&".TO_WWCL." order by 1");
		while ($data=mysql_fetch_array($res))
		{
			echo "<tourney>".$nl;
			echo "<name>".HTMLStr($data[2])."</name>".$nl;
			echo "<gid>".$data[1]."</gid>".$nl;
			echo "<maxplayer>".$data[3]."</maxplayer>".$nl;
			echo "<mode>M</mode>".$nl;
			echo "<ranking>".$nl;
	
			$tourney=new Tourney($data[0]);
	
			$Rankings = $tourney->GetRankings();
		
			foreach ($Rankings as $rank => $teams)
			{
				foreach ($teams as $team)
				{
					echo "<data>";
					echo "<rank>".$rank."</rank>";
					echo "<id>";
					$i=0;
					$found=FALSE;
	
					while ($i<$teams and !($found))
					{
						if ($team['id']==$teamid[$i])
						{
							$found=TRUE;
							echo $wwclid[$i];
						}
						$i++;
					}
					echo "</id>";
					echo "</data>".$nl;
				}
			}
			echo "</ranking>".$nl;
			echo "</tourney>".$nl.$nl;
		}
	}
	echo "</wwcl>".$nl;
}
?>
