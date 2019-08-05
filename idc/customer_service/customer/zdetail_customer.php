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
require_once "../../_system/util_html.php";

//PAGE ACCESS PERMISSION
require_once "./_access_local.php";
ckperm(ZKP_SELECT, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//GLOBAL
$_channel	= $_GET['_channel'];
$_code		= $_GET['_code'];
$left_loc	= "list_customer.php?_channel=$_channel";

$page_title["000"] = "Medical Dealer";
$page_title["001"] = "Medicine Dist";
$page_title["002"] = "Pharmacy Chain";
$page_title["003"] = "Gen/ Specialty";
$page_title["004"] = "Pharmaceutical";
$page_title["005"] = "Hospital";
$page_title["6.1"] = "M/L Marketing";
$page_title["6.2"] = "Mail Order";
$page_title["6.3"] = "Internet Business";
$page_title["007"] = "Promotion&Other";
$page_title["008"] = "Individual";
$page_title["009"] = "Private use";
$page_title["00S"] = "Service";

//PROCESS FORM
require_once "tpl_process_form.php";

//========================================================================================== DEFAULT PROCESS
$sql = "SELECT * FROM ".ZKP_SQL."_tb_customer WHERE cus_code = '$_code'";

if (isZkError($result =& query($sql)))
	$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/index.php");

$column =& fetchRowAssoc($result);
$column['cus_since'] = empty($column['cus_since']) ? "" : date("j-M-Y", strtotime($column['cus_since']));
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
<?php
$sql = "SELECT ma_idx, ma_account, ma_display_as FROM tb_mbracc WHERE ma_display_as > 0 ORDER BY ma_account";
$result = & query($sql);
echo "var mkt = new Array();\n";
$i = 0;
while ($row =& fetchRow($result,0)) {
	if($row[2] & 1) $j='IDC';
	if($row[2] & 2) $j='MED';
	if($row[2] & 1 && $row[2] & 2) $j='ALL';
	if($row[2] == 4) $j=false;
	if($j != false) {
		if(ZKP_SQL == $j || $j == 'ALL') echo "mkt['".$i++."'] = ['".$row[0]."','".strtoupper($row[1])."',".$row[2]."];\n";
	}
}
?>

function initOption() {
	for (i=0; i<mkt.length; i++) 
		addOption(document.frmUpdate._marketing_staff,mkt[i][1], mkt[i][0]);
}

function initPage() {
	initOption();
	setSelect(window.document.frmUpdate._marketing_staff, "<?php echo $column['cus_responsibility_to'] ?>");
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
<strong style="font-size:18px;font-weight:bold">
[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] CUSTOMER DETAIL: <span style="color:#6633FF"><?php echo $page_title[$_channel]?></span>
</strong>
<hr>
<form name='frmUpdate' method='POST'>
<input type='hidden' name='p_mode'>
<input type='hidden' name='_code' value='<?php echo $_code?>'>
<input type='hidden' name='_channel' value='<?php echo $_channel?>'>
<strong>BASIC INFORMATION</strong>
<table width="100%" class="table_box">
	<tr>
		<th width="15%">CODE</th>
		<td width="45%" colspan="3"><b><?php echo $column['cus_code'] ?></b></td>
		<th width="15%">SINCE</th>
		<td><input type="text" name="_since" value="<?php echo $column['cus_since'];?>" class="fmtd" size="15"></td>
	</tr>
	<tr>
		<th>FULL NAME</th>
		<td colspan="3">
			<span class="comment">Title:</span>&nbsp;
			<input type="text" name="_company_title" value="<?php echo $column['cus_company_title'];?>" class="fmt" size="10">
			<input type="text" name="_full_name" value="<?php echo $column['cus_full_name'];?>" class="req" size="40"></td>
		<th>MANAGEMENT GROUP</th>
		<td><?php printHtmlSelect("customer_group", "fmt", $column['cug_code']);?></td>
	</tr>
	<tr>
		<th>NAME</th>
		<td colspan="3"><input type="text" name="_name" value="<?php echo $column['cus_name'];?>" class="req" size="52"></td>
		<th>REPRESENTATIVE</th>
		<td><input type="text" name="_representative" value="<?php echo $column['cus_representative'];?>" class="fmt"></td>
	</tr>
	<tr>
		<th>INTRODUCED BY</th>
		<td colspan="3"><input type="text" name="_introduced_by" value="<?php echo $column['cus_introduced_by'];?>" class="fmt" size="52"></td>
		<th>NPWP</th>
		<td><input type="text" name="_type_of_biz" value="<?php echo $column['cus_type_of_biz'];?>" class="fmt">			</td>
	</tr>
</table><br />
<strong>CONTACT INFORMATION</strong>
<table width="100%" class="table_box">
	<tr>
		<th width="15%">CONTACT</th>
		<td width="45%" colspan="3"><input type="text" name="_contact" value="<?php echo $column['cus_contact'];?>" class="fmt" size="52">			</td>
		<th width="15%">CONTACT POSITION</th>
		<td><input type="text" name="_contact_position" value="<?php echo $column['cus_contact_position'];?>" class="fmt">			</td>
	</tr>
	<tr>
		<th>CONTACT NO</th>
		<td colspan="5">
			<span class="comment">PHONE : </span>
			<input type="text" name="_contact_phone" value="<?php echo $column['cus_contact_phone'];?>" class="fmt" size="25"> &nbsp; 
			<span class="comment">HAND PHONE : </span><input type="text" name="_contact_hphone" value="<?php echo $column['cus_contact_hphone'];?>" class="fmt" size="25">
		</td>
	</tr>
	<tr>
		<th>CONTACT EMAIL</th>
		<td colspan="3"><input type="text" name="_contact_email" value="<?php echo $column['cus_contact_email'];?>" class="fmt" size="25"></td>
		<th>MARKETING</th>
		<td>
            <select name="_marketing_staff" id="_marketing_staff" class="fmt">
                <option value="">==SELECT==</option>
            </select>
		</td>
	</tr>
</table><br />
<strong>ADDRESS INFORMATION</strong>
<table width="100%" class="table_box">
	<tr>
		<th width="15%" rowspan="3">ADDRESS</th>
		<td width="45%" rowspan="3"><textarea rows="4" style="width:100%" name="_address"><?php echo $column['cus_address']?></textarea></td>
		<th width="15%">PHONE</th>
		<td><input type="text" name="_phone" class="fmt" value="<?php echo $column['cus_phone'];?>"></td>
	</tr>
	<tr>
		<th>FAX</th>
		<td><input type="text" name="_fax" value="<?php echo $column['cus_fax'];?>" class="fmt"></td>
	</tr>
	<tr>
		<th>CITY</th>
		<td colspan="3"><input type="text" name="_city" class="fmt" value="<?php echo $column['cus_city'];?>"></td>
	</tr>
    <tr>
		<th>REMARKS</th>
		<td colspan="3"><textarea rows="5" style="width:100%" name="_remark"><?php echo $column['cus_remark'];?></textarea></td>
	</tr>
</table>
</form>
<!--START Button-->
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td>
			<button name='btnDelete' class='input_btn' style='width:150px;'><img src="../../_images/icon/trash.gif" width="15px" align="middle"> &nbsp; Delete Customer</button>
		</td>
		<td align="right">
			<button name='btnUpdate' class='input_btn' style='width:80px;'><img src="../../_images/icon/update.gif" width="20px" align="middle"> &nbsp; Update</button>&nbsp;
			<button name='btnList' class='input_btn' style='width:130px;'><img src="../../_images/icon/list.gif" width="20px" align="middle"> &nbsp; Go to summary</button>
		</td>
</table><br />
<form name='frmBlock' method='POST'>
<input type='hidden' name='p_mode'>
<input type='hidden' name='_code' value='<?php echo $_code?>'>
<?php if ($column["cus_is_blocked_timestamp"] == '') { ?>
<strong style="color:red">DEACTIVE CUSTOMER STORE</strong>
<table width="100%" class="table_box">
	<tr>
		<th width="15%" rowspan="2">CUSTOMER</th>
		<td><?php echo "[". trim($_code) . "] " . $column['cus_full_name'] ?></td>
		<td align="right"><button name='btnBlock' class='input_btn' style='width:100px;'><img src="../../_images/icon/trash.gif" width="15px" align="middle"> &nbsp; BLOCK</button></td>
	</tr>
	<tr>
		<td colspan="2"><small>By submitting this form, there's always a hint when you make new Order using this customer</small></td>
	</tr>
</table>
<?php } else { ?>
<strong style="color:red">ACTIVATE CUSTOMER STORE</strong>
<table width="100%" class="table_box">
	<tr>
		<th width="15%">CUSTOMER</th>
		<td><?php echo "[". trim($_code) . "] " . $column['cus_full_name'] ?></td>
		<td align="right"><button name='btnUnBlock' class='input_btn' style='width:100px;'>UNBLOCK</button></td>
	</tr>
</table>
<?php } ?>
</form>
<script language="javascript" type="text/javascript">
	//Define the form that you want to handle
	var oForm = window.document.frmUpdate;
	var oForm2 = window.document.frmBlock;
	
	window.document.all.btnDelete.onclick = function() {
		if(confirm("Are you sure to delete?")) {
			oForm.p_mode.value = 'delete_cus';
			oForm.submit();
		}
	}
	
	window.document.all.btnUpdate.onclick = function() {
		if(confirm("Are you sure to update?")) {
			if(verify(oForm)){
				oForm.p_mode.value = 'update_cus';
				oForm.submit();
			}
		}
	}

	if(window.document.frmBlock.btnBlock == '[object]') {
		window.document.frmBlock.btnBlock.onclick = function() {
			if(confirm("Are you sure to deactive the customer?")) {
				if(verify(oForm2)){
					oForm2.p_mode.value = 'block_cus';
					oForm2.submit();
				}
			}
		}
	} else {
		window.document.frmBlock.btnUnBlock.onclick = function() {
			if(confirm("Are you sure to activate the customer?")) {
				if(verify(oForm2)){
					oForm2.p_mode.value = 'unblock_cus';
					oForm2.submit();
				}
			}
		}
	}

	window.document.all.btnList.onclick = function() {
		window.location.href = '<?php echo HTTP_DIR . "$currentDept/$moduleDept/" ?>list_customer.php?_channel=<?php echo $_channel?>';
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