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
ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//GLOBAL
$left_loc	= "input_customer.php";

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
		addOption(document.frmInsert._marketing_staff,mkt[i][1], mkt[i][0]);
}

function initPage() {
	setSelect(window.document.frmInsert._channel, "<?php echo $cus_channel[$department] ?>");
	initOption();
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
[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] NEW CUSTOMER
</strong>
<hr><br /><br />
<form name="frmInsert" method="POST">
<input type='hidden' name='p_mode'>
<strong>BASIC INFORMATION</strong>
<table width="100%" class="table_box">
	<tr>
		<th width="15%">CODE</th>
		<td width="45%" colspan="3">
			<select name="_channel" class="req">
				<option value="">==SELECT==</option>
				<option value="000">Medical Dealer</option>
				<option value="001">Medicine Dist</option>
				<option value="002">Pharmacy Chain</option>
				<option value="003">Gen/ Specialty</option>
				<option value="004">Pharmaceutical</option>
				<option value="005">Hospital</option>
				<option value="6.1">M/L Marketing</option>
				<option value="6.2">Mail Order</option>
				<option value="6.3">Internet Business</option>
				<option value="007">Promotion &amp; Other</option>
				<option value="008">Individual</option>
				<option value="009">Private use</option>
				<option value="00S">Service</option>
			</select>&nbsp;&nbsp;
			<input name="_code" type="text" class="req" size="5" maxlength="7">
			<span class="comment">* Check Code: </span>
			<input name="_check_code" type="text" class="fmt" size="2" maxlength="7">
			&nbsp;
			<a href="javascript:viewCurrentCus()"><img src="../../_images/icon/search_mini.gif" alt="View current customer . . ."></a>
			<script language="javascript" type="text/javascript">
				function viewCurrentCus() {
					var o = window.document.frmInsert._check_code.value;
					if (o.length > 0) {
						openWindow('p_list_cus_code.php?_check_code='+ o, 420,600);
					} else {
						alert("Please, Enter the code");
					}
				}

				window.document.frmInsert._channel.onchange = function() {
					var o = window.document.frmInsert;
					switch (this.value) {
						case "000" : o._code.value = o._check_code.value = "0"; break;
						case "001" : o._code.value = o._check_code.value = "1"; break;
						case "002" : o._code.value = o._check_code.value = "2"; break;
						case "003" : o._code.value = o._check_code.value = "3"; break;
						case "004" : o._code.value = o._check_code.value = "4"; break;
						case "005" : o._code.value = o._check_code.value = "5"; break;
						case "6.1" :
						case "6.2" :
						case "6.3" : o._code.value = o._check_code.value = "6"; break;
						case "007" : o._code.value = o._check_code.value = "7"; break;
						case "008" : o._code.value = o._check_code.value = "8"; break;
						case "009" : o._code.value = o._check_code.value = "9"; break;
						case "00S" : o._code.value = o._check_code.value = "S"; break;
						default : o._code.value = o._check_code.value = "";
					}
				}
			</script>
			</td>
		<th width="15%">SINCE</th>
		<td><input type="text" name="_since" class="fmtd" size="15" value="<?php echo date("j-M-Y")?>"></td>
	</tr>
	<tr>
		<th>FULL NAME</th>
		<td colspan="3">
			<span class="comment">Title:</span>&nbsp;
			<input type="text" name="_company_title" class="fmt" size="10">
			<input type="text" name="_full_name" class="req" size="40">
		</td>
		<th>MANAGEMENT GROUP</th>
		<td><?php printHtmlSelect("customer_group", "fmt")?></td>
	</tr>
	<tr>
		<th>NAME</th>
		<td colspan="3"><input type="text" name="_name" class="req" size="52"></td>
		<th>REPRESENTATIVE</th>
		<td><input type="text" name="_representative" class="req"></td>
	</tr>
	<tr>
		<th>INTRODUCED BY</th>
		<td colspan="3"><input type="text" name="_introduced_by" class="fmt" size="52"></td>
		<th>NPWP</th>
		<td>
			<input type="text" name="_type_of_biz" class="fmt"> <br />CODE 
			<input type="radio" name="_tax_code_status" value="1" checked>1 &nbsp;
			<input type="radio" name="_tax_code_status" value="2">2 &nbsp;
			<input type="radio" name="_tax_code_status" value="3">3 &nbsp;
			<input type="radio" name="_tax_code_status" value="7">7
		</td>
	</tr>
</table><br />
<strong>CONTACT INFORMATION</strong>
<table width="100%" class="table_box">
	<tr>
		<th width="15%">CONTACT</th>
		<td width="45%"><input type="text" name="_contact" class="fmt" size="52"></td>
		<th width="15%">CONTACT POSITION</th>
		<td><input type="text" name="_contact_position" class="fmt"></td>
	</tr>
	<tr>
		<th>CONTACT NO </th>
		<td colspan="5">
			<span class="comment">PHONE : </span> <input type="text" name="_contact_phone" class="fmt" size="25"> &nbsp;  
			<span class="comment">HAND PHONE : </span><input type="text" name="_contact_hphone" class="fmt" size="25">
		</td>
	</tr>
	<tr>
		<th>CONTACT EMAIL</th>
		<td><input type="text" name="_contact_email" class="fmt" size="25"></td>
		<th>MARKETING</th>
		<td>
            <select name="_marketing_staff" id="_marketing_staff" class="req">
                <option value="">==SELECT==</option>
            </select>
		</td>
	</tr>
	<tr>
		<th>FP EMAIL</th>
		<td><input type="text" name="_fp_email" class="fmt" size="100"> <br /> <small>*) Please separate email using comma (,).</small> </td>
	</tr>
</table><br />
<strong>ADDRESS INFORMATION</strong>
<table width="100%" class="table_box">
	<tr>
		<th width="15%" rowspan="3">ADDRESS</th>
		<td width="45%" rowspan="3"><textarea rows="4" style="width:100%" name="_address"></textarea></td>
		<th width="15%">PHONE</th>
		<td><input type="text" name="_phone" class="req"></td>
	</tr>
	<tr>
		<th>FAX</th>
		<td colspan="3"><input type="text" name="_fax" class="req"></td>
	</tr>
	<tr>
		<th>CITY</th>
		<td colspan="3"><input type="text" name="_city" class="req"></td>
	</tr>
    <tr>
		<th>REMARKS</th>
		<td colspan="3"><textarea rows="5" style="width:100%" name="_remark"></textarea></td>
	</tr>
</table><br />
</form>
<p align="center">
	<button name='btnSave' class='input_btn' style='width:150px;'><img src="../../_images/icon/btnSave-blue.gif" width="15px" align="middle" alt="Save customer"> &nbsp; Save Customer</button>&nbsp;
	<button name='btnList' class='input_btn' style='width:150px;'><img src="../../_images/icon/list.gif" width="20px" align="middle" alt="Go to list customer"> &nbsp; List Customer</button>
</p>
<script language="javascript" type="text/javascript">
	//Define the form that you want to handle
	var oForm = window.document.frmInsert;

	window.document.all.btnSave.onclick = function() {
		if(verify(oForm)){
			if(confirm("Are you sure to save the customer?")) {
				oForm.p_mode.value = 'insert_cus';
				oForm.submit();
			}
		}
	}
	
	window.document.all.btnList.onclick = function() {
		window.location.href = '<?php echo HTTP_DIR . "$currentDept/$moduleDept/" ?>list_customer.php?_channel=002';
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