<?php
// (C) 2003-01-31 by Berndl Gregor (berndl_gregor@gmx.at)
// Benutzung ausschliesslich für die lansurfer.net Intranetsoftware gestattet - Keine Veränderungen erlaubt
// Usage only permitted for the lansurfer.net intranet software - No modifications permitted

function seeding(&$ary)
{
	$depth=ceil(log(sizeof($ary))/log(2));

	$copy=$ary;
	$ary=array();
	$slot=array();

	for ($i=0;$i<=$depth;$i++)
		{
		$partnersum[$i] = pow(2,$i+1) - pow(2,$i) + ($i ? 1 : 0);
		$min= $i ? pow(2,$i-1) : 0;
		$max=pow(2,$i)-1;
		$elem=1+$max-$min;
		$delta=pow(2,$depth)/$elem;

		$pos=$delta/2;
		for ($j=0;$j<$elem;$j++)
		{
			if ($i)
			{
				$slot[$pos]=$partnersum[$i]-$slot[$pos-$delta/2];
				$pos=$pos+$delta;
			}
			else $slot[0]=1;
		}
	}

	for ($i=0;$i<pow(2,$depth);$i++)
		{
		$ary[$i]= $copy[$slot[$i]-1] ? $copy[$slot[$i]-1] : -1;
		}

}

function mktteams($id,&$teams)
{
				$res = SQL_Query("SELECT distinct seed FROM TourneyTeam WHERE tourney=$id ORDER BY seed IS NULL, seed");

				$seedarray=array();
				$sacount=0;
				$teamarray=array();
				$teams=array();

				while ($row=mysql_fetch_array($res)) $seedarray[$sacount++]=$row['seed'];

				$i=0;
				while ($i<$sacount)
				{
					$query="SELECT id FROM TourneyTeam WHERE tourney=$id AND seed";
					if ($seedarray[$i]==NULL) $query.=" IS NULL";
					else $query.="=".$seedarray[$i];

					$res = SQL_Query($query);

					$teamarray[$sacount]=array();
					$tacount=0;

					while ($row=mysql_fetch_array($res)) $teamarray[$sacount][$tacount++]=$row['id'];

					srand ((double)microtime()*1000000);
					shuffle($teamarray[$sacount]);

					for ($j=0;$j<$tacount;$j++) $teams[]=$teamarray[$sacount][$j];

					$i++;
				}
}

?>