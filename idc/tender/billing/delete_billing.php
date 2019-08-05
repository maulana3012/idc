<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at indosat.net.id
*
* @author : daesung kim
*/
//REQUIRE
require_once "../../zk_config.php";
require_once LIB_DIR . "zk_dbconn.php";

//PAGE ACCESS PERMISSION
require_once "./_access_local.php";
ckperm(ZKP_SELECT, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//GLOBAL
$left_loc = 'summary_billing_by_group.php';
if (!isset($_GET['_code']) || $_GET['_code'] == '') {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/index.php");
} else {
	$_code = urldecode($_GET['_code']);
}

//PROCESS FORM
require_once APP_DIR . "_include/billing/tpl_process_form.php";

//========================================================================================= DEFAULT PROCESS
if($S->getValue("ma_authority") & 4)	{ $page_permission = false;}
else 									{ $page_permission = true;}

if ($page_permission) {
	$result = new ZKError(
		"NOT_ENOUGH_AUTHORITY",
		"NOT_ENOUGH_AUTHORITY",
		"You don't have authority to do this action. Please contact the Administrator.");
	$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/revise_billing.php?_code=".$_code);
}

$sql = "SELECT bill_code, bill_inv_date, bill_revesion_time, (SELECT book_idx FROM ".ZKP_SQL."_tb_booking WHERE book_doc_ref='$_code' AND book_doc_type=1) AS book_idx FROM ".ZKP_SQL."_tb_billing WHERE bill_code = '$_code'";
if(isZKError($result =& query($sql)))
	$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/index.php");

$column =& fetchRowAssoc($result);
if (numQueryRows($result) <= 0)
	goPage(HTTP_DIR . "$currentDept/summary/daily_billing_by_group.php");
?>
<html>
<head>
<title><?php echo $cboFilter[0][ZKP_FUNCTION][0][1] ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
<script language="javascript" type="text/javascript">
function initPage() {
	setSelect(window.document.frmDeleteBilling._account, "<?php echo $S->getValue("ma_idx") ?>");
	window.document.frmDeleteBilling._password.focus();
	
}
</script>
</head>
<body topmargin="0" leftmargin="0" onLoad="initPage()">
<table border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#9CBECC">
  <tr>
    <td>
			<?php require_once APP_DIR . "_include/tpl_header.php"?>
    </td>
  </tr>
  <tr>
    <td style="padding:5 10 0 10" valign="bottom">
			<?php require_once APP_DIR . "_include/tpl_topMenu.php";?>
    </td>
  </tr>
  <tr>
    <td style="padding:0 3 3 3">
    	<table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF">
        <tr>
						<?php require_once "_left_menu.php";?>
						<td style="padding:10" height="480" valign="top">
          	<!--START: BODY-->
<!---------------------------------------- start process delete invoice IO ---------------------------------------->
<h4>[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] DELETE BILLING</h4>
<h5 class="info"> for Invoice <?php echo $_code ?></h5>
<form name="frmDeleteBilling" method="POST">
<input type="hidden" name="p_mode" value="delete_billing_pajak">
<input type="hidden" name="_code" value="<?php echo $column['bill_code'] ?>">
<input type="hidden" name="_inv_date" value="<?php echo date('Ym', strtotime($column['bill_inv_date'])) ?>">
<input type='hidden' name="_revision_time" value="<?php echo $column['bill_revesion_time']?>">
<input type="hidden" name="_book_idx" value="<?php echo $column['book_idx'] ?>">
<table width="100%" cellpadding="0">
 	<tr>
		<td width="2%"><img src="../../_images/properties/p_leftmenu_icon02.gif"></td>
		<td><strong>Account Information</strong></td>
    </tr>
    <tr>
		<td></td>
    	<td>
			<table width="100%" class="table_box">
				<tr>
					<td width="15%">Account Name</td>
					<td width="2%">:</td>
					<td>
<select name="_account" class="req">
	<option value="" selected>==SELECT==</option>
<?php
$acc = array();
$res = query("SELECT ma_idx, ma_account FROM ".ZKP_SQL."_tb_mbracc WHERE ma_isvalidacc=true ORDER BY ma_account");
while($col = fetchRow($res)) {
	echo "\t<option value=\"{$col[0]}\">".ucfirst($col[1])."</option>\n";
	$acc[$col[0]] = $col[1];
}
?>
</select>
					</td>
					<td width="15%">Account Password</td>
					<td width="2%">:</td>
					<td width="15%"><input type="password" name="_password" class="reqd" size="15" value=""></td>
					<td align="right">
						<button name="btnDelete2" class="input_red"><img src="../../_images/icon/check.jpg"> &nbsp; Delete Billing</button>&nbsp;
						<button name="btnCancel" class="input_sky"><img src="../../_images/icon/delete_2.gif"> &nbsp; Cancel</button>
					</td>
				</tr>
			</table>
    	</td>
    </tr>
</table><br /><br />
</form>
<script language="javascript" type="text/javascript">
	window.document.frmDeleteBilling.btnDelete2.onclick = function() {
		var f = window.document.frmDeleteBilling;

		if(f._account.value == '') {
			alert('ACCOUNT NAME must be entered');
			return;
		}
		if(f._password.value.length <= 0) {
			alert('PASSWORD must be entered');
			return;
		}

		if(confirm("Are you sure to delete billing?\nIf you delete this billing, you will miss the vat number")) {
			f.submit();
		}
	}

	window.document.frmDeleteBilling.btnCancel.onclick = function() {
		window.location.href = 'revise_billing.php?_code=<?php echo $_code ?>';
	}
</script>
<!---------------------------------------- end process delete invoice IO ---------------------------------------->
          </td>
        </tr>
      </table>
      </td>
  </tr>
  <tr>
    <td style="padding:5 10 5 10" bgcolor="#FFFFFF">
			<?php require_once APP_DIR . "_include/tpl_footer.php"?>
    </td>
  </tr>
</table>
</body>
</html>