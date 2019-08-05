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
ckperm(ZKP_SELECT, "index.php");

//GLOBAL
$left_loc	= "list_letter.php";
$_code 		= $_GET["_code"];

//PROCESS FORM
require_once "tpl_process_form.php"; 

//========================================================================================== DEFAULT PROCESS
$sql = "SELECT * FROM ".ZKP_SQL."_tb_letter WHERE lt_reg_no = '$_code'";
$result =& query($sql);
$column =& fetchRowAssoc($result);
$file_sql = "SELECT * FROM ".ZKP_SQL."_tb_letter_file WHERE lt_reg_no = '$_code' ORDER BY ltf_file_name";
$file_res =& query($file_sql);

if(numQueryRows($result) <= 0) {
	goPage("list_letter.php");
} else if($column['lt_status_of_letter'] == "1"){
	goPage("revise_letter.php?_code=$_code");
}
?>
<html>
<head>
<title><?php echo $cboFilter[0][ZKP_FUNCTION][0][1] ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
<script type="text/javascript">
function initPage() {
	setSelect(window.document.all.cboRegType, "<?php echo $column['lt_type_of_letter'] ?>");	
}
</script>
</head>
<body topmargin="0" leftmargin="0" onload="initPage()">
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
<div class="head-line">[ <font color="#446fbe"><?php echo strtoupper($currentDept) ?></font> ] Detail Official Letter</div>
<table width="100%" class="table_box">
	<tr>
		<td colspan="2"><div class="i_line">Letter Info</div></td>
		<td colspan="4" align="right" valign="bottom"><span class="comment"><i><?php echo "Lastupdated by ". $column["lt_lastupdated_by_account"] . ", " . date("d-M-Y H:i:s", strtotime($column["lt_lastupdated_timestamp"])) ?></i></span></td>
	</tr>
	<tr>
		<th width="15%">REG. NO</th>
		<td width="20%"><b><?php echo $_code ?><b></td>
		<th width="15%">REG. DATE</th>
		<td width="20%"><?php echo date('d-M-Y', strtotime($column["lt_reg_date"])) ?></td>
		<th width="15%">ISSUED BY</th>
		<td><?php echo $column["lt_issued_by"] ?></td>
	</tr>
	<tr>
		<th>TYPE OF LETTER</th>
		<td>
			<select name="cboRegType" disabled>
				<option value="T">TENDER</option>
				<option value="Q">QUOTATION</option>
				<option value="B">BUSINESS</option>
				<option value="O">OTHERS</option>
			</select>
		</td>
		<th>SEND TO</th>
		<td><?php echo $column["lt_send_to"] ?></td>
	</tr>
	<tr>
		<th>CONFIRM DATE</th>
		<td><?php echo ($column["lt_confirm_date"]!="") ? date('d-M-Y', strtotime($column["lt_confirm_date"])) : ""; ?></td>

	</tr>
	<tr>
		<th>REMARK</th>
		<td colspan="3"><?php echo $column["lt_remark"] ?></td>
	</tr>
	<tr>
		<th>BRIEF SUMMARY</th>
		<td colspan="5"><textarea name="_reg_brief_summary" rows="4" class="req" style="width:100%" readonly><?php echo $column["lt_brief_summary"] ?></textarea></td>
	</tr>
</table><br /><br />
<div class="i_line">Download Letter &amp; Attachment</div>
<?php if(numQueryRows($file_res) <= 0) { ?>
<span class="comment"><i>( No uploaded file )</i></span>
<?php } else { ?>
<table width="100%" class="table_f">
<?php
pg_result_seek($file_res,0);
while($rows =& fetchRowAssoc($file_res)) {
if($rows["ltf_type"]=='A') {
	$file_name = HTTP_DIR.'letter/letter'.$rows["ltf_file_path"];

	echo "<tr height='30px'>\n";
	cell_link('<span class="'.$rows["ltf_file_type"].'">'.$rows["ltf_file_name"].'</span>', ' colspan="2"', 
			  ' href="'.$file_name.'"');
	echo "</tr>\n";
}}

pg_result_seek($file_res,0);
while($rows =& fetchRowAssoc($file_res)) {
if($rows["ltf_type"]=='B') {
	$file_name = HTTP_DIR.'letter/letter'.$rows["ltf_file_path"];

	echo "<tr height='30px'>\n";
	cell($rows["ltf_file_desc"], ' width="20%"');
	cell_link('<span class="'.$rows["ltf_file_type"].'">'.$rows["ltf_file_name"].'</span>', '', 
			  ' href="'.$file_name.'"');
	echo "</tr>\n";
}}
?>
</table>
<div align="right"><small><i>*To download related file, please right click to the link &amp; choose Save Target As</i></small></div>
<?php } ?><br /><br />
<?php if($column['lt_status_of_letter'] == "2") { ?>
<form name="frmCancelled" method="POST">
<input type='hidden' name='p_mode' value="cancel">
<input type='hidden' name='_code' value="<?php echo $_code ?>">
<input type='hidden' name='_by_account' value="<?php echo ucfirst($S->getValue("ma_account")) ?>">
<div class="i_line">Cancelled Letter</div>
<table width="100%" class="table_box">
	<tr>
		<th width="15%">REASON</th>
		<td><input type="text" name="_reason" class="fmt" style="width:100%"></td>
		<td width="10%"><button name='btnSubmit' class='input_btn' style='width:100%;'> Submit</button></td>
	</tr>
</table>
</form>
<script language="javascript" type="text/javascript">
window.document.frmCancelled.btnSubmit.onclick = function() {
	if(confirm("Are you sure to cancel this Letter?")) {
		window.document.frmCancelled.submit();
	}
}
</script>
<?php } else if($column['lt_status_of_letter'] == "3") { ?>
<table width="100%" class="table_box">
	<tr>
		<td><div class="i_line">Cancelled Letter</div></td>
		<td align="right" valign="bottom"><span class="comment"><i><?php echo "Cancelled by ". $column["lt_cancelled_by_account"] . ", " . date("d-M-Y H:i:s", strtotime($column["lt_cancelled_timestamp"])) ?><i></span></td>
	</tr>
	<tr>
		<th width="17%">REASON</th>
		<td><?php echo $column['lt_cancelled_reason'] ?></td>
	</tr>
</table>
<?php } ?>
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