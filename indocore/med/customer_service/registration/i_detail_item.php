<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at zonekom.com
*
* @generated : 29-May, 2007 23:50:32
* @author    : daesung kim
*/
//REQUIRE
require_once "../../zk_config.php";
require_once LIB_DIR . "zk_dbconn.php";

//PAGE ACCESS PERMISSION
require_once "./_access_local.php";
ckperm(ZKP_SELECT, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//GLOBAL
$_code 		= $_GET['_code'];

//---------------------------------------------------------------------------------------------------- update
if(ckperm(ZKP_UPDATE,  HTTP_DIR . "$currentDept/$moduleDept/index.php", 'update_item')) {

	//Item Value
	foreach($_POST['_it_idx'] as $val)				$_it_idx[]				= $val;
	foreach($_POST['_it_grd_status'] as $val)		$_it_grd_status[]		= $val;
	foreach($_POST['_it_delivery'] as $val)			$_it_delivery[]			= $val;

	//make pgsql ARRAY String for many item
	$_it_idx				= implode(',', $_it_idx);
	$_it_grd_status			= implode(',', $_it_grd_status);
	$_it_delivery			= '$$' . implode('$$,$$', $_it_delivery) . '$$';

	$result = executeSP(
		"reviseRegItemStatusDeli",
		"$\${$_code}$\$",
		"ARRAY[$_it_idx]",
		"ARRAY[$_it_grd_status]",
		"ARRAY[$_it_delivery]"
	);
	
	if(isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/i_detail_item.php?_code=$_code");
	}
	goPage(HTTP_DIR . "$currentDept/$moduleDept/i_detail_item.php?_code=$_code");
}

//========================================================================================= DEFAULT PROCESS
$item_sql	= "SELECT *,sgit_finishing_date-sgit_incoming_date AS total_time FROM ".ZKP_SQL."_tb_service_reg_item WHERE sg_code = '$_code' ORDER BY it_code, sgit_idx";
$item_res	= query($item_sql);
$numRow		= numQueryRows($item_res);
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script language="text/javascript" type="text/javascript" src="../../_script/aden.js"></script>
<script language='text/javascript' type='text/javascript'>

</script>
</head>
<body style="margin:8pt">
<form name="frmUpdateItem" method="post">
<input type='hidden' name='p_mode' value="update_item">
<?php 
$i = 1;
$j = 0;
pg_result_seek($item_res, 0);
while($items =& fetchRowAssoc($item_res)) { 
?>
<input type="hidden" name="_it_idx[]" value="<?php echo $items['sgit_idx']?>">
<input type="hidden" name="_it_grd_status[]" value="<?php echo $items['sgit_status'] ?>">
<div style="border:#016fa1 1px solid;padding:5pt 0 5pt 0;">
<table width="100%" class="table_box">
  <tr>
    <th width="5%" rowspan="4" style="border-right:#016fa1 1px solid"><font style="font-size:35px;"><?php echo $i ?></font></th>
    <td width="10%"><span style="font-family:verdana;color:#016fa1;font-weight:bold">Description</span></td>
    <td>
      <table width="100%" class="table_l">
        <tr>
          <th width="15%">Item</th>
          <td width="30%"><b><?php echo $items['sgit_model_no'].', '.$items['sgit_serial_number'] ?></b></td>
          <th width="15%">Guarantee</th>
          <td>
            <input type="radio" name="_it_is_guarantee_<?php echo $items['sgit_idx']?>" value="1" disabled<?php echo ($items['sgit_is_guarantee']==1) ? ' checked':''?>> Yes, <input type="text" name="_it_guarantee_period[]" class="fmtd" size="10" value="<?php echo ($items['sgit_is_guarantee']==1) ? date('j-M-Y', strtotime($items['sgit_guarantee'])) : '' ?>"  disabled>
            <input type="radio" name="_it_is_guarantee_<?php echo $items['sgit_idx']?>" value="0" disabled<?php echo ($items['sgit_is_guarantee']==0) ? ' checked':''?>> No
          </td>
        </tr>
        <tr>
          <th>Cus Complain</th>
          <td colspan="3"><?php echo $items['sgit_cus_complain'] ?></td>
        </tr>
        <tr>
          <th>Tech Analyze</th>
          <td colspan="3"><?php echo $items['sgit_tech_analyze'] ?></td>
        </tr>
      </table>
	</td>
  </tr>
  <tr>
    <td width="10%"><span style="font-family:verdana;color:#016fa1;font-weight:bold">Status</span></td>
	<td>
	  <table width="50%" class="table_l">
       <tr>
        <th width="27%">Incoming</th>
        <th width="27%">Finish</th>
        <th width="15%">time (days)</th>
        <td></td>
        <th>Delivery</th>
      </tr>
      <tr>
          <td align="center"><?php echo date('j-M-Y', strtotime($items['sgit_incoming_date'])) ?></td>
          <td align="center"><?php echo date('j-M-Y', strtotime($items['sgit_finishing_date'])) ?></td>
          <td align="right"><?php echo $items['total_time'] ?></td>
          <td></td>
          <td align="center">
			<input type="text" class="fmtd" style="width:100%" name="_it_delivery[]" value="<?php echo ($items['sgit_delivery_date']=='') ? '' : date('j-M-Y', strtotime($items['sgit_delivery_date'])) ?>">
		  </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td width="10%"><span style="font-family:verdana;color:#016fa1;font-weight:bold">Action</span></td>
	<td>
	  <table width="100%" class="table_box">
        <tr>
          <td><input type="checkbox" name="_chk<?php echo $items['sgit_idx']?>[]" value="1" disabled<?php echo ($items['sgit_service_action_chk'] & 1)? ' checked':''?>> Service</td>
          <td>
			<input type="checkbox" name="_chk<?php echo $items['sgit_idx']?>[]" value="8" disabled<?php echo ($items['sgit_service_action_chk'] & 8)? ' checked':''?> onclick="enabledText(<?php echo $j.','.$items['sgit_idx']?>, this, 'top')"> 
			Replacement product &nbsp; <input type="text" name="_it_replace_product[]" class="fmt" size="30" value="<?php echo $items['sgit_replacement_product'] ?>" disabled readonly>
		  </td>
        </tr>
        <tr>
          <td><input type="checkbox" name="_chk<?php echo $items['sgit_idx']?>[]" value="2" disabled<?php echo ($items['sgit_service_action_chk'] & 2)? ' checked':''?>> Return back to Customer</td>
          <td>
		    <input type="checkbox" name="_chk<?php echo $items['sgit_idx']?>[]" value="16" disabled<?php echo ($items['sgit_service_action_chk'] & 16)? ' checked':''?> onclick="enabledText(<?php echo $j.','.$items['sgit_idx']?>, this, 'bottom')"> 
			Replacement part &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <input type="text" name="_it_replace_part[]" class="fmt" size="30" value="<?php echo $items['sgit_replacement_part'] ?>" disabled readonly>
		  </td>
        </tr>
        <tr>
          <td><input type="checkbox" name="_chk<?php echo $items['sgit_idx']?>[]" value="4" disabled<?php echo ($items['sgit_service_action_chk'] & 4)? ' checked':''?>> Calibation</td>
        </tr>
      </table>
	</td>
  </tr>
  <tr>
    <td width="10%"><span style="font-family:verdana;color:#016fa1;font-weight:bold">Cost</span></td>
    <td>
      <table width="100%" class="table_box">
        <tr>
          <td>
            <input type="radio" name="_it_cost_<?php echo $items['sgit_idx']?>" value="0" disabled<?php echo ($items['sgit_cost']==0)? ' checked':''?>> Free charge &nbsp;
            <input type="radio" name="_it_cost_<?php echo $items['sgit_idx']?>" value="1" disabled<?php echo ($items['sgit_cost']==1)? ' checked':''?>> Service charge
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</div><br />
<?php 
	$i++;
	$j++;
}
?>
</form>
<div align="right">
  <button name='btnUpdate' class='input_btn'><img src="../../_images/icon/update.gif" width="20px" align="middle" alt="Update item status"> &nbsp; Update item status</button>&nbsp;
</div>
<script language="javascript" type="text/javascript">
	window.document.all.btnUpdate.onclick = function() {
		if(confirm("Are you sure to update delivery date?")) {
			if(verify(window.document.frmUpdateItem)){
				window.document.frmUpdateItem.submit();
			}
		}
	}
</script>	
</body>
</html>