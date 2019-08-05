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
	foreach($_POST['_it_guarantee'] as $val)		$_it_guarantee[]		= $val;
	foreach($_POST['_it_guarantee_period'] as $val)	{
		if($val == '')	$_it_guarantee_period[]	= '1-1-1970';
		else			$_it_guarantee_period[]	= $val;
	}
	foreach($_POST['_it_cus_complain'] as $val)		$_it_cus_complain[]		= $val;
	foreach($_POST['_it_tech_analyze'] as $val)		$_it_tech_analyze[]		= $val;
	foreach($_POST['_it_grd_status'] as $val)		$_it_grd_status[]		= $val;
	foreach($_POST['_it_incoming'] as $val)			$_it_incoming[]			= $val;
	foreach($_POST['_it_finish'] as $val)			$_it_finish[]			= $val;
	foreach($_POST['_it_delivery'] as $val)			$_it_delivery[]			= $val;
	foreach($_POST['_it_replace_product'] as $val)	$_it_replace_product[]	= $val;
	foreach($_POST['_it_replace_part'] as $val)		$_it_replace_part[]		= $val;

	//make pgsql ARRAY String for many item
	$_it_idx				= implode(',', $_it_idx);
	$_it_guarantee			= implode(',', $_it_guarantee);
	$_it_guarantee_period	= 'date $$' . implode('$$, date $$', $_it_guarantee_period) . '$$';
	$_it_cus_complain		= '$$' . implode('$$,$$', $_it_cus_complain) . '$$';
	$_it_tech_analyze		= '$$' . implode('$$,$$', $_it_tech_analyze) . '$$';
	$_it_grd_status			= implode(',', $_it_grd_status);
	$_it_incoming			= '$$' . implode('$$,$$', $_it_incoming) . '$$';
	$_it_finish				= '$$' . implode('$$,$$', $_it_finish) . '$$';
	$_it_delivery			= '$$' . implode('$$,$$', $_it_delivery) . '$$';
	$_it_replace_product	= '$$' . implode('$$,$$', $_it_replace_product) . '$$';
	$_it_replace_part		= '$$' . implode('$$,$$', $_it_replace_part) . '$$';

	//checkbox status
	$i = 0;
	foreach($_POST['_it_idx'] as $val) {
		$chk = 0;
		if(isset($_POST['_chk'.$val]) && is_array($_POST['_chk'.$val])) {
			foreach($_POST['_chk'.$val] as $val2) {
				$_it_chk[$i] = $_it_chk[$i] + $val2;
			}
		} else {
			$_it_chk[$i] = 0;
		}
		$i++;
	}
	$_it_chk = implode(',', $_it_chk);

	//radio cost
	$i = 0;
	foreach($_POST['_it_idx'] as $val) {
		$_it_cost[$i]	= $_POST['_it_cost_'.$val];
		$i++;
	}
	$_it_cost = implode(',', $_it_cost);

	$result = executeSP(
		ZKP_SQL."_reviseRegItemStatus",
		"$\${$_code}$\$",
		"ARRAY[$_it_idx]",
		"ARRAY[$_it_guarantee]",
		"ARRAY[$_it_guarantee_period]",
		"ARRAY[$_it_cus_complain]",
		"ARRAY[$_it_tech_analyze]",
		"ARRAY[$_it_grd_status]",
		"ARRAY[$_it_incoming]",
		"ARRAY[$_it_finish]",
		"ARRAY[$_it_delivery]",
		"ARRAY[$_it_chk]",
		"ARRAY[$_it_replace_product]",
		"ARRAY[$_it_replace_part]",
		"ARRAY[$_it_cost]"
	);


	if(isZKError($result)) {
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/i_revise_item.php?_code=$_code");
	}
	goPage(HTTP_DIR . "$currentDept/$moduleDept/i_revise_item.php?_code=$_code");
}

//========================================================================================= DEFAULT PROCESS
$item_sql	= "SELECT * FROM ".ZKP_SQL."_tb_service_reg_item WHERE sg_code = '$_code' ORDER BY it_code, sgit_idx";
$item_res	= query($item_sql);
$numRow		= numQueryRows($item_res);
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script language="text/javascript" type="text/javascript" src="../../_script/aden.js"></script>
<script language='text/javascript' type='text/javascript'>
<?php
// Print Javascript Code
echo "var item = new Array();\n";
$i = 0;
while ($rows =& fetchRow($item_res,0)) {
	printf("    item[%s] = [%s,'%s','%s'];\n",
		$rows[0], $rows[7],$rows[8],$rows[9]
	);
}
?>

function enabledGuarantee(idx, value) {

	var f			= window.document.frmUpdateItem;
	var oCheck		= f.all.tags("INPUT");
	var numAllInput	= oCheck.length;
	var is_it_guarantee	= 3;	/////
	var idx1		= 4;		/////
	var idx2		= idx1+1;
	var idx3		= idx1+2;
	var numInput	= 24;

	if(value) {
		oCheck[idx2+(idx*numInput)].readOnly	= false;
		oCheck[idx2+(idx*numInput)].className	= 'reqd';
		oCheck[is_it_guarantee+(idx*numInput)].value	= '1';
		oCheck[idx2+(idx*numInput)].focus();
	} else {
		oCheck[idx2+(idx*numInput)].readOnly	= 'readOnly';
		oCheck[idx2+(idx*numInput)].className	= 'fmtd';
		oCheck[is_it_guarantee+(idx*numInput)].value	= '0';
		oCheck[idx2+(idx*numInput)].value		= '';
	}

}

function enabledDateStatus(idx, it_idx, val) {

	var f			= window.document.frmUpdateItem;
	var oCheck		= f.all.tags("INPUT");
	var numAllInput	= oCheck.length;
	var grade		= 2;	///// increse when input type added
	var idx1		= 12;	/////
	var idx2		= idx1+1;
	var idx3		= idx1+3;
	var numInput	= 24;

	if(val.value==2 && oCheck[idx2+(idx*numInput)].value=="") {
		alert("You have to input the finish date before input delivery date");
		oCheck[10+(idx*numInput)].checked			= true;
		oCheck[idx1+(idx*numInput)].readOnly		= 'readOnly';
		oCheck[idx1+(idx*numInput)].className		= 'fmtd';
		oCheck[idx2+(idx*numInput)].readOnly		= false;
		oCheck[idx2+(idx*numInput)].className		= 'reqd';
		oCheck[idx3+(idx*numInput)].readOnly		= 'readOnly';
		oCheck[idx3+(idx*numInput)].className		= 'fmtd';
		oCheck[idx2+(idx*numInput)].focus();
		return;	
	}

	if(val.value == 0 && val.checked) {
		oCheck[idx1+(idx*numInput)].readOnly		= false;
		oCheck[idx1+(idx*numInput)].className		= 'reqd';
		oCheck[idx2+(idx*numInput)].readOnly		= 'readOnly';
		oCheck[idx2+(idx*numInput)].className		= 'fmtd';
		oCheck[idx3+(idx*numInput)].readOnly		= 'readOnly';
		oCheck[idx3+(idx*numInput)].className		= 'fmtd';
		oCheck[idx1+(idx*numInput)].value 			= '<?php echo date ('d-M-Y') ?>';
		oCheck[idx1+(idx*numInput)].focus();
	} else if(val.value == 1 && val.checked) {	
		oCheck[idx1+(idx*numInput)].readOnly		= 'readOnly';
		oCheck[idx1+(idx*numInput)].className		= 'fmtd';
		oCheck[idx2+(idx*numInput)].readOnly		= false;
		oCheck[idx2+(idx*numInput)].className		= 'reqd';
		oCheck[idx3+(idx*numInput)].readOnly		= 'readOnly';
		oCheck[idx3+(idx*numInput)].className		= 'fmtd';
		oCheck[idx2+(idx*numInput)].value 			= '<?php echo date ('d-M-Y') ?>';
		oCheck[idx2+(idx*numInput)].focus();
	} else if(val.value == 2 && val.checked) {
		oCheck[idx1+(idx*numInput)].readOnly		= 'readOnly';
		oCheck[idx1+(idx*numInput)].className		= 'fmtd';
		oCheck[idx2+(idx*numInput)].readOnly		= 'readOnly';
		oCheck[idx2+(idx*numInput)].className		= 'fmtd';
		oCheck[idx3+(idx*numInput)].readOnly		= false;
		oCheck[idx3+(idx*numInput)].className		= 'reqd';
		oCheck[idx3+(idx*numInput)].value			= '<?php echo date ('d-M-Y') ?>';
		oCheck[idx3+(idx*numInput)].focus();
	}
	oCheck[grade+(idx*numInput)].value = val.value;

}

function enabledText(idx, it_idx, val, position) { //kolom, sgit_idx, value, text_top/bottom

	var f			= window.document.frmUpdateItem;
	var oCheck		= f.all.tags("INPUT");
	var numAllInput	= oCheck.length;
	var text1		= 18;	/////
	var text2		= text1+3;
	var numInput	= 24;

	if(val.checked && position=='top') {
		oCheck[text1+(idx*numInput)].readOnly	= false;
		oCheck[text1+(idx*numInput)].className	= 'req';
		oCheck[text1+(idx*numInput)].focus();
	} else if(val.checked==false && position=='top') {
		oCheck[text1+(idx*numInput)].readOnly	= true;
		oCheck[text1+(idx*numInput)].className	= 'fmt';
		oCheck[text1+(idx*numInput)].value		= '';
	}
	
	if(val.checked && position=='bottom') {
		oCheck[text2+(idx*numInput)].readOnly	= false;
		oCheck[text2+(idx*numInput)].className	= 'req';
		oCheck[text2+(idx*numInput)].focus();
	} else if(val.checked==false && position=='bottom') {
		oCheck[text2+(idx*numInput)].readOnly	= true;
		oCheck[text2+(idx*numInput)].className	= 'fmt';
		oCheck[text2+(idx*numInput)].value		= '';
	}

}

function checkedDate(idx, it_idx, val, val_text) {

	var f			= window.document.frmUpdateItem;
	var oCheck		= f.all.tags("INPUT");
	var numAllInput	= oCheck.length;
	var idx1		= 12;	/////
	var idx2		= idx1+1;
	var idx3		= idx1+3;
	var numInput	= 24;

	var j = idx-1;
	var d	= parseDate(val_text.value, 'prefer_euro_format');

	if(d == null) {
		if(val==0) {
			alert("Please input column with date format");
			oCheck[idx1+(idx*numInput)].value = '';
			oCheck[idx1+(idx*numInput)].focus();
		} else if(val==1) {
			alert("Please input column with date format");
			oCheck[idx2+(idx*numInput)].value = '';
			oCheck[idx2+(idx*numInput)].focus();
		} else if(val==2) {
			alert("Please input column with date format");
			oCheck[idx3+(idx*numInput)].value = '';
			oCheck[idx3+(idx*numInput)].focus();
		}
		return;
	} 

	val_text.value = formatDate(d, "d-NNN-yyyy");
	updateTotalTime();

	var d1	= parseDate(oCheck[idx1+(idx*numInput)].value, 'prefer_euro_format');
	var d2	= parseDate(oCheck[idx2+(idx*numInput)].value, 'prefer_euro_format');
	var d3	= parseDate(oCheck[idx3+(idx*numInput)].value, 'prefer_euro_format');

	if(d1!=null && d2!=null && val==1) {
		if(d1.getTime() > d2.getTime()) {
			alert("Finish date has to later or same than incoming date");
			oCheck[idx2+(idx*numInput)].value = '';
			oCheck[idx2+(idx*numInput)].focus();
			return;
		}
	} else if(d2!=null && d3!=null && val==2) {
		if(d2.getTime() > d3.getTime()) {
			alert("Delivery date has to later or same than finish date");
			oCheck[idx3+(idx*numInput)].value = '';
			oCheck[idx3+(idx*numInput)].focus();
			return;
		}
	}
	updateTotalTime();

}

function updateTotalTime() {

	var f			= window.document.frmUpdateItem;
	var oCheck		= f.all.tags("INPUT");
	var numAllInput	= oCheck.length;
	var idx1		= 12;	/////
	var idx2		= idx1+1;
	var time		= idx1+2;
	var numInput	= 24;

	var j=0;
	for(var i=1; i<numAllInput; i=i+numInput) {
		var d1	= parseDate(oCheck[idx1+(j*numInput)].value, 'prefer_euro_format');
		var d2	= parseDate(oCheck[idx2+(j*numInput)].value, 'prefer_euro_format');

		if(d1!=null && d2!=null) {
			oCheck[time+(j*numInput)].value = (d2-d1)/86400000;
		}
		j++;
	}

}

function defaultCondition() {

	var f			= window.document.frmUpdateItem;
	var oCheck		= f.all.tags("INPUT");
	var numAllInput	= oCheck.length;
	var idx1		= 12;	/////
	var idx2		= idx1+1;
	var idx3		= idx1+3;
	var chk1		= idx1+5;
	var text1		= idx1+6;
	var chk2		= idx1+8;
	var text2		= idx1+9;
	var numInput	= 24;

	var j=0;
	for(var i=1; i<numAllInput; i=i+numInput) {
		idx = oCheck[1+(j*numInput)].value;

		//radio 
		if(item[idx] == 0) {
			oCheck[idx1+(j*numInput)].readOnly		= false;
			oCheck[idx1+(j*numInput)].className		= 'reqd';
			oCheck[idx2+(j*numInput)].readOnly		= 'readOnly';
			oCheck[idx2+(j*numInput)].className		= 'fmtd';
			oCheck[idx3+(j*numInput)].readOnly		= 'readOnly';
			oCheck[idx3+(j*numInput)].className		= 'fmtd';
			oCheck[idx1+(j*numInput)].focus();
		} else if(item[idx] == 1) {
			oCheck[idx1+(j*numInput)].readOnly		= 'readOnly';
			oCheck[idx1+(j*numInput)].className		= 'fmtd';
			oCheck[idx2+(j*numInput)].readOnly		= false;
			oCheck[idx2+(j*numInput)].className		= 'reqd';
			oCheck[idx3+(j*numInput)].readOnly		= 'readOnly';
			oCheck[idx3+(j*numInput)].className		= 'fmtd';
			oCheck[idx2+(j*numInput)].focus();
		} else if(item[idx] == 2) {
			oCheck[idx1+(j*numInput)].readOnly		= 'readOnly';
			oCheck[idx1+(j*numInput)].className		= 'fmtd';
			oCheck[idx2+(j*numInput)].readOnly		= 'readOnly';
			oCheck[idx2+(j*numInput)].className		= 'fmtd';
			oCheck[idx3+(j*numInput)].readOnly		= false;
			oCheck[idx3+(j*numInput)].className		= 'reqd';
			oCheck[idx3+(j*numInput)].focus();
		}

		//checkbox
		if(oCheck[chk1+(j*numInput)].checked) {
			oCheck[text1+(j*numInput)].readOnly		= false;
			oCheck[text1+(j*numInput)].className	= 'req';
			oCheck[text1+(j*numInput)].focus();
		}
		if(oCheck[chk2+(j*numInput)].checked) {
			oCheck[text2+(j*numInput)].readOnly		= false;
			oCheck[text2+(j*numInput)].className	= 'req';
			oCheck[text2+(j*numInput)].focus();
		}
		j++;
	}

}

function initPage() {
	defaultCondition();
	updateTotalTime();
}
</script>
</head>
<body style="margin:8pt" onload="initPage()">
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
<input type="hidden" name="_it_guarantee[]" value="<?php echo $items['sgit_is_guarantee'] ?>">
<div style="border:#016fa1 1px solid;padding:5pt 0 5pt 0;">
<table width="100%" class="table_box">
  <tr>
    <th width="5%" rowspan="4" style="border-right:#016fa1 1px solid"><font style="font-size:35px;"><?php echo $i ?></font></th>
    <td width="10%"><span style="font-family:verdana;color:#016fa1;font-weight:bold">Description</span></td>
    <td>
      <table width="100%" class="table_box">
        <tr>
          <th width="15%">Item</th>
          <td width="30%"><b><?php echo $items['sgit_model_no'].', '.$items['sgit_serial_number'] ?></b></td>
          <th width="15%">Guarantee</th>
          <td>
            <input type="radio" name="_it_is_guarantee_<?php echo $items['sgit_idx']?>" value="1" onclick="enabledGuarantee(<?php echo $j ?>, true)"<?php echo ($items['sgit_is_guarantee']==1) ? ' checked':''?>> Yes, <input type="text" name="_it_guarantee_period[]" class="fmtd" size="10" value="<?php echo ($items['sgit_is_guarantee']==1) ? date('j-M-Y', strtotime($items['sgit_guarantee'])) : '' ?>">
            <input type="radio" name="_it_is_guarantee_<?php echo $items['sgit_idx']?>" value="0" onclick="enabledGuarantee(<?php echo $j ?>, false)"<?php echo ($items['sgit_is_guarantee']==0) ? ' checked':''?>> No
          </td>
        </tr>
        <tr>
          <th>Cus Complain</th>
          <td colspan="3"><input type="text" name="_it_cus_complain[]" class="fmt" style="width:100%" value="<?php echo $items['sgit_cus_complain'] ?>"></td>
        </tr>
        <tr>
          <th>Tech Analyze</th>
          <td colspan="3"><input type="text" name="_it_tech_analyze[]" class="fmt" style="width:100%" value="<?php echo $items['sgit_tech_analyze'] ?>"></td>
        </tr>
      </table>
	</td>
  </tr>
  <tr>
    <td width="10%"><span style="font-family:verdana;color:#016fa1;font-weight:bold">Status</span></td>
	<td>
	  <table width="50%" class="table_box">
       <tr>
        <th align="left" width="27%"><input type="radio" name="_it_status_<?php echo $items['sgit_idx']?>" value="0" onclick="enabledDateStatus(<?php echo $j.','.$items['sgit_idx']?>, this)"<?php echo ($items['sgit_status']==0) ? ' checked':''?>> Incoming</th>
        <th align="left" width="27%"><input type="radio" name="_it_status_<?php echo $items['sgit_idx']?>" value="1" onclick="enabledDateStatus(<?php echo $j.','.$items['sgit_idx']?>, this)"<?php echo ($items['sgit_status']==1) ? ' checked':''?>> Finish</th>
        <th width="15%">time (days)</th>
        <td></td>
        <th align="left" width="27%"><input type="radio" name="_it_status_<?php echo $items['sgit_idx']?>" value="2" onclick="enabledDateStatus(<?php echo $j.','.$items['sgit_idx']?>, this)"<?php echo ($items['sgit_status']==2) ? ' checked':''?>> Delivery</th>
      </tr>
      <tr>
          <td><input type="text" class="fmtd" style="width:100%" name="_it_incoming[]" onblur="checkedDate(<?php echo $j.','.$items['sgit_idx']?>, 0, this)" value="<?php echo date('j-M-Y', strtotime($items['sgit_incoming_date'])) ?>" readonly></td>
          <td><input type="text" class="fmtd" style="width:100%" name="_it_finish[]" onblur="checkedDate(<?php echo $j.','.$items['sgit_idx']?>, 1, this)" value="<?php echo ($items['sgit_finishing_date']=='') ? '' : date('j-M-Y', strtotime($items['sgit_finishing_date'])) ?>" readonly></td>
          <td><input type="text" class="fmtn" style="width:100%" name="_it_total_time[]" disabled></td>
          <td></td>
          <td><input type="text" class="fmtd" style="width:100%" name="_it_delivery[]" onblur="checkedDate(<?php echo $j.','.$items['sgit_idx']?>, 2, this)" value="<?php echo ($items['sgit_delivery_date']=='') ? '' : date('j-M-Y', strtotime($items['sgit_delivery_date'])) ?>" readonly></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td width="10%"><span style="font-family:verdana;color:#016fa1;font-weight:bold">Action</span></td>
	<td>
	  <table width="100%" class="table_box">
        <tr>
          <td><input type="checkbox" name="_chk<?php echo $items['sgit_idx']?>[]" value="1"<?php echo ($items['sgit_service_action_chk'] & 1)? ' checked':''?>> Service</td>
          <td>
			<input type="checkbox" name="_chk<?php echo $items['sgit_idx']?>[]" value="8"<?php echo ($items['sgit_service_action_chk'] & 8)? ' checked':''?> onclick="enabledText(<?php echo $j.','.$items['sgit_idx']?>, this, 'top')"> 
			Replacement product &nbsp; <input type="text" name="_it_replace_product[]" class="fmt" size="30" value="<?php echo $items['sgit_replacement_product'] ?>" readonly>
		  </td>
        </tr>
        <tr>
          <td><input type="checkbox" name="_chk<?php echo $items['sgit_idx']?>[]" value="2"<?php echo ($items['sgit_service_action_chk'] & 2)? ' checked':''?>> Return back to Customer</td>
          <td>
		    <input type="checkbox" name="_chk<?php echo $items['sgit_idx']?>[]" value="16"<?php echo ($items['sgit_service_action_chk'] & 16)? ' checked':''?> onclick="enabledText(<?php echo $j.','.$items['sgit_idx']?>, this, 'bottom')"> 
			Replacement part &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <input type="text" name="_it_replace_part[]" class="fmt" size="30" value="<?php echo $items['sgit_replacement_part'] ?>" readonly>
		  </td>
        </tr>
        <tr>
          <td><input type="checkbox" name="_chk<?php echo $items['sgit_idx']?>[]" value="4"<?php echo ($items['sgit_service_action_chk'] & 4)? ' checked':''?>> Calibation</td>
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
            <input type="radio" name="_it_cost_<?php echo $items['sgit_idx']?>" value="0"<?php echo ($items['sgit_cost']==0)? ' checked':''?>> Free charge &nbsp;
            <input type="radio" name="_it_cost_<?php echo $items['sgit_idx']?>" value="1"<?php echo ($items['sgit_cost']==1)? ' checked':''?>> Service charge
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
		if(confirm("Are you sure to update item in this registration?")) {
			if(verify(window.document.frmUpdateItem)){
				window.document.frmUpdateItem.submit();
			}
		}
	}
</script>
</body>
</html>