<?
	/*
  IP-Adder V 1.0
  Geschrieben [MTCL]Matz 03/2001
  Kommentare, Anregungen, Geld an matz@mtcl-clan.de
  Die neueste Version findet sich unter http://www.impworld.de/lansurfer/

  Die Veränderung der Codes ist ausdrücklich erlaubt - das ist der Sinn von Lansurfer!
  Wenn Ihr eine Verbesserung schreibt, mailt sie mir doch, damit mehr Leute etwas davon haben!
	*/

  $LS_BASEPATH = "../";
  include $LS_BASEPATH."../includes/ls_base.inc";

  NavStruct("orga/");

  StartPage("IP-Adressenvergabe");

  user_auth_ex(AUTH_TEAM);

// lets check if there's already a column "ip_address" in table "user"
$res = SQL_Query("describe user");
$found = false;
while ($row=mysql_fetch_array($res))
{
  if ($row["Field"] == "ip_address")
  {
    $found = true;
  }
}

if (!$found)
{
  // if not - add column to table
  $res = SQL_Query("ALTER TABLE user ADD ip_address VARCHAR (15)");
}

// get all the blocks and their number of users
$select = "SELECT
             seat_block.id as 'block_id',
             seat_block.name as 'block_name',
             seats.block, COUNT(seats.block)
           FROM seat_block, seats
           WHERE seat_block.id = seats.block AND
                 seats.status = 2
           GROUP by seats.block
           ORDER by seat_block.name";
//echo $select;
$res = SQL_Query($select);
$netmask = 0;

?>

<form name="ipform" action="ip_adder.php">
<INPUT TYPE="HIDDEN" NAME="ACTION" VALUE="<? echo $ACTION; ?>">
<table>
  <TR>
    <TD> Startnetz </TD>
    <TD><INPUT NAME="Netz1" SIZE="3" MAXLENGTH="3" VALUE="<? echo $Netz1; ?>">.
        <INPUT NAME="Netz2" SIZE="3" MAXLENGTH="3" VALUE="<? echo $Netz2; ?>">.
        <INPUT NAME="Netz3" SIZE="3" MAXLENGTH="3" VALUE="<? echo $Netz3; ?>">.*
        <i>&nbsp;z.B. 10.1.1</i></TD>
  </TR>
<!--  <tr>
    <TD> OrgaClan: </TD>
    <TD><INPUT NAME="OrgaClan" VALUE="<? echo $OrgaClan; ?>"><i>&nbsp;z.B. MTCL oder leerlassen</i></TD>
  </TR>
  <TR>
    <TD> Orga-Startadresse </TD>
    <TD><INPUT NAME="OrgaStart" VALUE="<? echo $OrgaStart; ?>"><i>&nbsp;z.B. 10</i></TD>
  </TR>-->
</TABLE>

<p class=content><input type="BUTTON" class=form_btn NAME="CALC" VALUE="Berechnen" onClick="javascript:calc();">
<P class=content>
<table>
    <tr class="liste">
      <th class="liste" width=180>Blockname</th>
      <th class="liste" width=180>Anzahl Gäste</th>
      <th class="liste" width=300>IP-Bereich</th>
<?
      if ($ACTION == "go")
      {
        echo "<th class=\"liste\" width=300>Vvergebene IPs</th>";
      }
?>

    </tr>
<?
    // checks if given net-numbers are valid and changes them to a valid address
    function checkNet(&$Netz1, &$Netz2, &$Netz3, &$netmask)
    {
      if ($Netz3 > 255)
      {
        $Netz2++;
        if ($netmask < 2)
          $netmask++;
        if ($Netz2 > 255)
        {
          if ($netmask < 3)
            $netmask++;
          $Netz1++;
          if ($Netz1>255)
            echo "first number is too big! ";
          $Netz2=0;
        }
        $Netz3=0;
      }
    }

    // increases network address and checks if new number is valid and changes it to a valid address
    function incNet(&$Netz1, &$Netz2, &$Netz3, &$Netz4)
    {
      $Netz4++;
      if ($Netz4 > 254)
      {
        $Netz4 = 1;
        $Netz3++;
        if ($Netz3 > 255)
        {
          $Netz3=0;
          $Netz2++;
          if ($Netz2 > 255)
          {
            $Netz2=0;
            $Netz1++;
            if ($Netz1>255)
              echo "erste Zahl zu groß!";
            $Netz2=0;
          }
          $Netz3=0;
        }
      }
    }

    $round=0; // counter for netmask to check if only one block was available
    while ($row=mysql_fetch_array($res))
    {
?>
    <tr class="liste">
      <td class="liste" width=180 valign=top>
        <?
          PrintHTMLStr($row['block_name'])
        ?>
      </td>
      <td class="liste" width=180 valign=top>
        <? PrintHTMLStr($row['COUNT(seats.block)']); ?>
      </td>
      <td class="liste" width=300  valign=top>
        <?
         // local vars 'cos the function changes them
         // we need them untouched later for the update
         $lNetz1 = $Netz1;
         $lNetz2 = $Netz2;
         $lNetz3 = $Netz3;

         $round++;
         $max = $row['COUNT(seats.block)'];
         checkNet($Netz1, $Netz2, $Netz3, $netmask);
         // start address
         echo $Netz1.".".$Netz2.".".$Netz3.".1 - ";
         while ($max > 254)
         {
           $Netz3++;
           if ($netmask < 2)
             $netmask++;
           $max = $max -254;
           if ($Netz3 > 255)
           {
             $Netz2++;
             $netmask++;
             if ($Netz2 > 255)
             {
               if ($netmask < 3)
                 $netmask++;
               $Netz1++;
               if ($Netz1>255)
                 echo ("first number is too big! ");
               $Netz2=0;
             }
             $Netz3=0;
           }
         }
         // end address
         echo $Netz1.'.'.$Netz2.'.'.$Netz3.'.'.$max;

         if ($ACTION == "go")
         {
           echo "<td class=\"liste\" width=300>";
           // 1. get every user of this block
           $query = "SELECT DISTINCT user.id as user_id, user.name as user_name, guest.id, seats.id, seat_block.id
                       FROM user, guest, seats, seat_block
                       WHERE seat_block.name=\"".$row["block_name"]."\" AND
                             seats.block = seat_block.id AND
                             seats.status = 2 AND
                             guest.id = seats.guest AND
                             user.id = guest.user
                       ORDER BY user.clan DESC, user.name DESC";
           //echo $query;
           $guests = SQL_Query($query);
           $users = array();
           // since MySQL can't do subquerys we have to make an array of the resulting guest-ids
           while ($urow=mysql_fetch_array($guests))
           {
             array_push($users, $urow["user_id"]);
           }
           echo "<br>".count($users)." found";
           // 2. give an IP-Adress to every user
           $start = 1;
           while (count($users) > 0)
           {
             $userid = array_pop ($users);
             echo "<br>".$userid." -> ".$lNetz1.'.'.$lNetz2.'.'.$lNetz3.'.'.$start;
             $update = "UPDATE user set ip_address = \"".$Netz1.'.'.$Netz2.'.'.$Netz3.'.'.$start."\"
                          WHERE id = ".$userid;
             SQL_Query($update);
             incNet(&$lNetz1, &$lNetz2, &$lNetz3, &$start);
           }
           echo "</td>";
         }

         $Netz3++;
         if ($netmask < 1)
           $netmask++;
         echo "\n";
        ?>
      </td>
    </tr>
    <?
    } // while

    ?>
</table>
<p class=content> Errechnete Netmask:
<?
if ($round ==1)
  $netmask--;
  switch ($netmask)
  {
    case 0: echo "255.255.255.0";
    break;
    case 1: echo "255.255.0.0";
    break;
    case 2: echo "255.0.0.0";
    break;
    case 3: echo "0.0.0.0";
    break;
  }
?>
<SCRIPT>
  function submitQuestion()
  {
    Check = confirm("Die IP-Adressen wirklich neu vergeben??");
    if(Check)
    {
      ipform.ACTION.value="go";
      ipform.submit();
    }
  }

</SCRIPT>
<p class=content>
<? if ($ACTION=="Calc")
{
  echo "<INPUT TYPE=\"BUTTON\" class=form_btn onClick=\"javascript:submitQuestion();\" VALUE=\"Neu vergeben\">";
}
?>

<script>
  function calc()
  {
    ipform.ACTION.value="Calc";
    ipform.submit();
  }
</script>

</form>
<?
  EndPage();
?>


