<?
	$LS_BASEPATH = '../';
	include $LS_BASEPATH.'../includes/ls_base.inc';

	$LS_POPUPPAGE = TRUE;
	StartPage(_("Send Message"));
	
	$res = SQL_Query("SELECT name,id FROM user WHERE id=".SQL_Quot($to));
	if (!($dstUser = mysql_fetch_array($res)))
	  LS_Error(_("Invalid destination."));
	
	if ($submitted) {
		/*if (!$f_subject)
			FormErrorEx('f_subject', _("The subject may not be empty"));*/
		
		if (!$FormErrorCount) {
			SQL_Query("INSERT INTO ims SET date=NOW(),".SQL_QueryFields(array(
				'dst' => $to,
				'src' => $user_current['id'],
				'msg' => $f_msg
				)));
				$f_subject = '';
				$f_msg = '';
?>
<script language="JavaScript">
<!--
	window.parent.frames[0].location.reload();
// -->
</script>
<?			
		}
	}
	
?>
<form name="SendIMS" action="<? echo $PHP_SELF; ?>" method="post">
<table>
<input type="hidden" name="submitted" value="1">
<input type="hidden" name="to" value="<? echo $to; ?>">
<?

			$res = SQL_Query("SELECT * FROM imsUsers WHERE owner=".SQL_Quot($user_current['id'])." AND user=".SQL_Quot($dstUser['id']));
			if (!($row = mysql_fetch_array($res))) {
				echo '<tr><td class=form>';
				echo ' ';
				NavPrintAction("javascript:IMSBuddyList('?add=".$dstUser['id']."')", _("Add to buddy list"));
				echo '</td>';
				echo '</tr>';
			}
	
?>
<tr>
	<td class="form">
		<textarea name="f_msg" class="form_textarea" cols=44 rows=8></textarea>
	</td>
	</tr>
  <tr>
  	<td class="form" align=right><input type="submit" value="Senden"   accesskey="s"  class="form_btn">
  </td>
</tr>
</table>
</form>
<script language="JavaScript">
<!--
document.SendIMS.f_msg.focus();
//document.SendIMS.f_subject.select();
// -->
</script>

<?
	
	EndPage();
?>