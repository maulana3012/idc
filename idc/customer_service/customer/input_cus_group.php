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
$left_loc = "input_cus_group.php";

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
[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] NEW CUSTOMER GROUP
</strong>
<hr><br /><br />
<form name='frmAdd' method='POST'>
<input type='hidden' name='p_mode' value='insert_cus_group'>
	<table width="100%" class="table_a">
		<tr>
			<th>CODE</th>
			<td>
				<input name="_code" type="text" class="req" size="3" maxlength="2">
				<span class="comment">* 2 character only</span>
			</td>
			<th>REG/ TIME </th>
			<td><input type="text" name="_regtime" class="fmtd" value="<?php echo date("Y-m-d")?>"></td>
		</tr>
		<tr>
			<th>NAME</th>
			<td><input type="text" name="_name" class="req" size="35"></td>
			<th>BASIC DISC %</th>
			<td><input type="text" name="_basic_disc_pct" class="reqn" value="30" size="2"></td>
		</tr>
		<tr>
			<th>REMARK</th>
			<td colspan="3"><textarea name="_remark" cols="60" rows="8"></textarea></td>
		</tr>
	</table>
</form>
<p align='center'>
	<button name='btnSave' class='input_btn' style='width:150px;'><img src="../../_images/icon/btnSave-blue.gif" width="15px" align="middle" alt="Save group"> &nbsp; Save Customer</button>&nbsp;
	<button name='btnList' class='input_btn' style='width:150px;'><img src="../../_images/icon/list.gif" width="20px" align="middle" alt="List customer group"> &nbsp; List Group</button>

</p>
<script language="javascript" type="text/javascript">
	//Define the form that you want to handle
	var oForm = window.document.frmAdd;

	window.document.all.btnSave.onclick = function() {
		if(verify(oForm)){
			if(confirm("Are you sure to save the customer group?")) {
				oForm.submit();
			}
		}
	}
	
	window.document.all.btnList.onclick = function() {
		window.location.href = 'list_cus_group.php';
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