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
ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/list_complain.php");

//GLOBAL
$left_loc = "input_complain.php";

//PROCESS FORM
require_once "tpl_process_form.php";
?>
<html>
<head>
<title><?php echo $cboFilter[0][ZKP_FUNCTION][0][1] ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
<script language="text/javascript" type="text/javascript" src="../../_script/js_category.php"></script>
<script language='text/javascript' type='text/javascript'>
function checkform(o) {
	if (verify(o)) {
		o.submit();
	}
}
</script>
</head>
<body topmargin="0" leftmargin="0">
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
<strong class="title">[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] NEW COMPLAIN</strong><br /><br />
<form name='frmInsert' method='POST'>
<input type='hidden' name='p_mode' value='insert_complain'>
<table width="100%" class="table_a">
	<tr>
    	<th width="12%">TANGGAL</th>
        <td><input name="_tanggal" type="text" class="fmt" size="13" value="<?php echo date('d-M-Y') ?>"></td>
        <th width="12%">CUSTOMER</th>
        <td width="30%"><input name="_customer" type="text" class="req" style="width:100%"></td>
    	<th width="12%">CATEGORY</th>
        <td>
			<select name="cboCategory">
				<option value="product">PRODUCT</option>
				<option value="delivery">DELIVERY</option>
				<option value="others">OTHERS</option>
			</select>
        </td>
    </tr>
    <tr>
    	<th>DESC COMPLAIN</th>
        <td colspan="5"><textarea rows="4" class="req" style="width:100%" name="_desc_complain"></textarea></td>
    </tr>
    <tr>
    	<th>ACTION/<br />ANSWER</th>
        <td colspan="5"><textarea rows="4" class="req" style="width:100%" name="_action"></textarea></td>
    </tr>
    <tr>
    	<th>REMARKS</th>
        <td colspan="5"><textarea rows="2" style="width:100%" name="_remark"></textarea></td>
    </tr>
</table>
</form>
<p align='center'>
	<button name='btnSave' class='input_btn' style='width:120px;' onclick='checkform(window.document.frmInsert)'><img src="../../_images/icon/btnSave-blue.gif" width="15px" align="middle" alt="Save item"> &nbsp; Save item</button>&nbsp;
	<button name='btnCancel' class='input_btn' style='width:120px;' onclick='window.location.href="input_item.php"'><img src="../../_images/icon/delete.gif" width="15px" align="middle" alt="Cancel item"> &nbsp; Cancel item</button>
</p>
            <!--END: BODY-->
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