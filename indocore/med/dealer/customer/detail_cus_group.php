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
ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//GLOBAL
$left_loc = "list_cus_group.php";
$_code	= $_GET['_code'];

//PROCESS FORM
require_once "tpl_process_form.php";

//========================================================================================== DEFAULT PROCESS
$sql = "SELECT * FROM ".ZKP_SQL."_tb_customer_group WHERE cug_code = '$_code'";

if (isZkError($result = & query($sql)))
	$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/list_cus_group.php");

$column = fetchRowAssoc($result);
?>
<html>
<head>
<title><?php echo $cboFilter[0][ZKP_FUNCTION][0][1] ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
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
<strong style="font-size:18px;font-weight:bold">
[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] DETAIL CUSTOMER GROUP
</strong>
<hr><br /><br />
<form name='frmUpdate' method='POST'>
<input type='hidden' name='p_mode'>
<input type="hidden" name="_code" value="<?php echo $_code;?>">
<table width="100%" class="table_a">
	<tr>
		<th>CODE</th>
		<td><input name="_code" type="text" class="fmt" size="3" maxlength="2" value="<?php echo $column['cug_code'];?>" readonly></td>
		<th>REG/ TIME </th>
		<td><input type="text" name="_regtime" class="fmtd" value="<?php echo $column['cug_regtime'];?>" readonly></td>
	</tr>
	<tr>
		<th>NAME</th>
		<td><input type="text" name="_name" class="req" size="45" value="<?php echo $column['cug_name'];?>"></td>
		<th>BASIC DISC %</th>
		<td><input type="text" name="_basic_disc_pct" class="reqn" value="<?php echo $column['cug_basic_disc_pct'];?>" size="2"></td>
	</tr>
	<tr>
		<th>REMARK</th>
		<td colspan="3"><textarea name="_remark" cols="60" rows="8"><?php echo $column['cug_remark'];?></textarea></td>
	</tr>
</table>
</form>
<!--START Button-->
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td>
			<button name='btnDelete' class='input_btn' style='width:150px;'><img src="../../_images/icon/trash.gif" width="15px" align="middle"> &nbsp; Delete Group</button>
		</td>
		<td align="right">
			<button name='btnUpdate' class='input_btn' style='width:80px;'><img src="../../_images/icon/update.gif" width="20px" align="middle"> &nbsp; Update</button>&nbsp;
			<button name='btnList' class='input_btn' style='width:130px;'><img src="../../_images/icon/list.gif" width="20px" align="middle"> &nbsp; Go to summary</button>
		</td>
</table>
<script language="javascript" type="text/javascript">
	//Define the form that you want to handle
	var oForm = window.document.frmUpdate;
	
	window.document.all.btnDelete.onclick = function() {
		if(confirm("Are you sure to delete?")) {
			oForm.p_mode.value = 'delete_cus_group';
			oForm.submit();
		}
	}
	
	window.document.all.btnUpdate.onclick = function() {
		if(confirm("Are you sure to update?")) {
			if(verify(oForm)){
				oForm.p_mode.value = 'update_cus_group';
				oForm.submit();
			}
		}
	}
	
	window.document.all.btnList.onclick = function() {
		window.location.href = 'list_cus_group.php';
	}
</script>
<!--END Button-->
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