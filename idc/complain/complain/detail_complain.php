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
$_code = $_GET["_code"];

//PROCESS FORM
require_once "tpl_process_form.php";

//========================================================================================== DEFAULT PROCESS
$sql = "SELECT * FROM ".ZKP_SQL."_tb_customer_complain WHERE cp_idx = $_code";

if (isZkError($result =& query($sql)))
	$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/index.php");

$column =& fetchRowAssoc($result);
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

function initPage() {
	setSelect(window.document.frmUpdate.cboCategory, "<?php echo $column['cp_category'] ?>");
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
<strong class="title">[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] NEW COMPLAIN</strong><br /><br />
<form name='frmUpdate' method='POST'>
<input type='hidden' name='p_mode' value='insert_complain'>
<input type='hidden' name='_code' value='<?php echo $_code ?>'>
<table width="100%" class="table_a">
	<tr>
    	<th width="12%">TANGGAL</th>
        <td><input name="_tanggal" type="text" class="fmt" size="13" value="<?php echo date('d-M-Y', strtotime($column['cp_date'])) ?>"></td>
        <th width="12%">CUSTOMER</th>
        <td width="30%"><input name="_customer" type="text" class="req" style="width:100%" value="<?php echo $column['cp_customer'] ?>"></td>
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
        <td colspan="5"><textarea rows="4" class="req" style="width:100%" name="_desc_complain"><?php echo $column['cp_complain_desc'] ?></textarea></td>
    </tr>
    <tr>
    	<th>ACTION/<br />ANSWER</th>
        <td colspan="5"><textarea rows="4" class="req" style="width:100%" name="_action"><?php echo $column['cp_complain_completion'] ?></textarea></td>
    </tr>
    <tr>
    	<th>REMARKS</th>
        <td colspan="5"><textarea rows="2" style="width:100%" name="_remark"><?php echo $column['cp_remark'] ?></textarea></td>
    </tr>
</table>
</form>
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td><button name='btnDelete' class='input_btn' style='width:150px;'><img src="../../_images/icon/trash.gif" width="15px" align="middle"> &nbsp; Delete Customer</button></td>
        <td align="center"><i>
        Created by <?php echo $column["cp_created_by_account"] .", ". date('d-M-Y H:i:s', strtotime($column["cp_created_timestamp"]))?>
        <?php if($column["cp_updated_timestamp"] != "") { ?>
        <br />Updated by <?php echo $column["cp_updated_by_account"] .", ". date('d-M-Y H:i:s', strtotime($column["cp_updated_timestamp"]))?>
        <?php } ?>
        </i></td>
		<td align="right">
			<button name='btnUpdate' class='input_btn' style='width:80px;'><img src="../../_images/icon/update.gif" width="20px" align="middle"> &nbsp; Update</button>&nbsp;
			<button name='btnList' class='input_btn' style='width:130px;'><img src="../../_images/icon/list.gif" width="20px" align="middle"> &nbsp; Go to summary</button>
		</td>
</table>
<script language="javascript" type="text/javascript">
	//Define the form that you want to handle
	var oForm = window.document.frmUpdate;
	var oForm2 = window.document.frmBlock;
	
	window.document.all.btnDelete.onclick = function() {
		if(confirm("Are you sure to delete?")) {
			oForm.p_mode.value = 'delete_complain';
			oForm.submit();
		}
	}
	
	window.document.all.btnUpdate.onclick = function() {
		if(confirm("Are you sure to update?")) {
			if(verify(oForm)){
				oForm.p_mode.value = 'update_complain';
				oForm.submit();
			}
		}
	}

	window.document.all.btnList.onclick = function() {
		window.location.href = 'list_complain.php';
	}
</script>
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