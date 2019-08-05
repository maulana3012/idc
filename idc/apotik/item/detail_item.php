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
$left_loc = "list_item.php";
$_code = urldecode($_REQUEST['_code']);

//PROCESS FORM
require_once "tpl_process_form.php";

//DEFAULT PROCESS
if(isZKError($result =& query("SELECT * FROM ".ZKP_SQL."_tb_item WHERE it_code = '$_code'"))) {
	$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/detail_item.php?_code=$code");
}

$column =& fetchRowAssoc($result);

$sql = "SELECT *, it_model_no FROM ".ZKP_SQL."_tb_set_item JOIN ".ZKP_SQL."_tb_item USING(it_code) WHERE seit_code = '$_code' AND it_code != '$_code'";
$res =& query($sql);

//get category path from current icat_midx.
if(isZKError($path = executeSP(ZKP_SQL."_getCategoryPath", $column['icat_midx']))) {
	$M->goErrorPage($path, HTTP_DIR . "$currentDept/$moduleDept/index.php");
} else {
	eval(html_entity_decode($path[0]));	
	$path = array_reverse($path);
}

$sql_price = "
SELECT 
	ip_idx, 
	to_char(ip_date_from, 'dd-Mon-YYYY') AS ip_date_from, 
	to_char(ip_date_to, 'dd-Mon-YYYY') AS ip_date_to,
	ip_user_price, 
	ip_remark, 
	ip_updated_by, 
	ip_updated
FROM ".ZKP_SQL."_tb_item_price
WHERE it_code='$_code' ORDER BY ip_idx DESC";
$res_price =& query($sql_price);
$numRow1 = numQueryRows($res_price);

$sql_price_net = "
SELECT 
	ipn_idx, 
	to_char(ipn_date_from, 'dd-Mon-YYYY') AS ipn_date_from, 
	to_char(ipn_date_to, 'dd-Mon-YYYY') AS ipn_date_to,
	ipn_price_dollar,
	ipn_price_rupiah,
	ipn_updated_by, 
	ipn_updated,
	ipn_remark
FROM ".ZKP_SQL."_tb_item_price_net
WHERE it_code='$_code' ORDER BY ipn_idx DESC";
$res_price_net =& query($sql_price_net);
$numRow2 = numQueryRows($res_price_net);
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
function fillOption(target, pidx) {
	target.options.length = 1;
	for(var i = 0; i<icat.length; i++) {
		if (icat[i][1] == pidx) {
			target.options[target.options.length] = new Option(icat[i][4], icat[i][0]);
		}
	}
}

function fillOptionInit() {
	fillOption(window.document.frmUpdate.icat_1, 0);
<?php
//Set initial option value
if(isset($path) && is_array($path)) {
	$count = count($path);
	for($i = 1; $i < $count; $i++) {
		echo "\twindow.document.frmUpdate.icat_$i.value = \"{$path[$i][0]}\";\n";
		if($i<=2) echo "\tfillOption(window.document.frmUpdate.icat_".($i+1).", \"{$path[$i][0]}\");\n";
	}
}
?>
}

function resetOption() {
	window.document.frmUpdate.icat_3.options.length = 1;
	window.document.frmUpdate._midx.value = "";
}
</script>
</head>
<body topmargin="0" leftmargin="0" onLoad="fillOptionInit()">
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
<strong class="title">[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] DETAIL ITEM</strong><br /><br />
<form name='frmUpdate' method='POST'>
<input type='hidden' name='p_mode' value='update'>
<input type="hidden" name="_code" value="<?php echo addslashes(html_entity_decode($column['it_code'], ENT_QUOTES))?>">
<strong class="info">ITEM INFORMATION</strong>
<table width="100%" class="table_a">
	<tr>
		<th width="14%">ITEM CODE</th>
		<td width="30%"><?php echo $column['it_code']?></td>
		<th width="10%">CATEGORY</th>
		<td width="46%">
		<input type="hidden" name="_midx" class="req" value="<?php echo $column['icat_midx']?>">
			<select name="icat_1" onChange="fillOption(window.document.frmUpdate.icat_2, this.value)" onClick="resetOption()">
				<option>==SELECT==</option>
			</select>&nbsp;
			<select name="icat_2" onChange="fillOption(window.document.frmUpdate.icat_3, this.value)" onClick="window.document.frmUpdate._midx.value=''">
				<option>==SELECT==</option>
			</select>&nbsp;
			<select name="icat_3" onChange="window.document.frmUpdate._midx.value = this.value">
				<option>==SELECT==</option>
			</select>
		</td>
	</tr>
	<tr>
		<th>MODEL NO</th>
		<td>
			<input name="_model_no" type="text" class="fmt" size="40" value="<?php echo $column['it_model_no']?>">
		</td>
		<th>TYPE</th>
		<td>
			<input name="_type" type="text" class="fmt" size="40" value="<?php echo $column['it_type']?>">
		</td>
	</tr>
	<tr>
		<th>DESCRIPTION</th>
		<td colspan="3"><input type="text" name="_desc" class="fmt" size="80" value="<?php echo $column['it_desc']?>"></td>
	</tr>
	<tr>
		<th>REMARK</th>
		<td colspan="3">
			<textarea name="_remark" cols="60" rows="5"><?php echo $column['it_remark']?></textarea>
		</td>
	</tr>
	<tr>
		<th width="14%">TYPE ITEM</th>
		<td width="35%">
			<input type="radio" name="_item_type" value="0" disabled <?php echo ($column['it_status']==0)?'checked':'' ?>> Parent &nbsp; &nbsp;
			<input type="radio" name="_item_type" value="1" disabled <?php echo ($column['it_status']==1)?'checked':'' ?>> Child &nbsp; &nbsp;
			<input type="radio" name="_item_type" value="2" disabled <?php echo ($column['it_status']==2)?'checked':'' ?>> Mixed
		</td>
		<th width="14%">HAS E/D</th>
		<td>
			<input type="radio" name="_has_ed" value="t" disabled <?php echo ($column['it_ed']=='t')?'checked':'' ?>> Yes &nbsp; &nbsp;
			<input type="radio" name="_has_ed" value="f" disabled <?php echo ($column['it_ed']=='f')?'checked':'' ?>> No
		</td>
	</tr>
	<tr>
		<th>MANAGE</th>
		<td colspan="3">
			<table width="50%" class="table_nn">
			  <thead>
				<tr>
					<th width="15%">CODE</th>
					<th>MODEL NO</th>
				</tr>
			  </thead>
			  <tbody id="rowPosition">
			  <?php while ($col =& fetchRowAssoc($res)) { ?>
			  	<tr>
			  		<td><?php echo $col["it_code"]  ?></td>
			  		<td><?php echo $col["it_model_no"]  ?></td>
			  	</tr>
			  <?php } ?>
			  </tbody>
			</table>
		</td>
	</tr>
</table>
</form>
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td>
			<button name="btnDelete" class="input_sky">DELETE</button>
		</td>
		<td align="right">
			<span class="comment">* If you want to change the price, Please use item price menu</span>
		</td>
		<td align="right">
			<button name="btnUpdate" class="input_sky">UPDATE</button>&nbsp;
			<button name="btnListAll" class="input_sky">LIST</button>&nbsp;
			<button name="btnListCategory" class="input_sky">LIST CAT ITEMS</button>
		</td>
</table><br /><br />
<script language="javascript" type="text/javascript">
	//Define the form that you want to handle
	var oForm = window.document.frmUpdate;
	
	window.document.all.btnDelete.onclick = function() {
		if(confirm("Are you sure to delete? If you delete the item, you will loose all price information also.")) {
			oForm.p_mode.value = 'delete_item';
			oForm.submit();
		}
	}
	
	window.document.all.btnUpdate.onclick = function() {
		if(confirm("Are you sure to update?")) {
			if(verify(oForm)){
				oForm.p_mode.value = 'update_item';
				oForm.submit();
			}
		}
	}
	
	window.document.all.btnListAll.onclick = function() {
		window.location.href = '<?php echo HTTP_DIR . "$currentDept/$moduleDept/" ?>list_item.php';
	}
	
	window.document.all.btnListCategory.onclick = function() {
		window.location.href = '<?php echo HTTP_DIR . "$currentDept/$moduleDept/" ?>list_item.php?lastCategoryNo=<?php echo $column['icat_midx']?>';
	}
</script>
<!--============================================= USER PRICE =============================================-->
<strong>ITEM PRICE HISTORY</strong>
<table width="100%" class="table_c">
<form name="frmInsertPrice" method="POST">
<input type="hidden" name="p_mode" value="update">
<input type="hidden" name="_code" value="<?php echo $column['it_code']?>">
	<tr>
		<th width="12%">FROM</th>
        <th width="12%">TO</th>
		<th width="13%">USER PRICE</th>
		<th colspan="2">REMARK</th>
	</tr>
	<tr>
		<td align="center"><input type="text" name="_date_from" class="reqd" size="10"></td>
        <td></td>
		<td align="right"><input type="text" name="_user_price" class="reqn" style="width:100%" onKeyUp="formatNumber(this, 'dot')"></td>
		<td><input type="text" name="_remark" class="fmt" style="width:95%"></td>
		<td width="10%"><input type="button" name="btnAddNewPrice" class="input_sky" value="NEW PRICE"></td>
	</tr>
</form>
<form name="frmUpdatePrice" method="POST">
<input type="hidden" name="p_mode" value="update">
<input type="hidden" name="_code" value="<?php echo $column['it_code']?>">
<?php
while($col =& fetchRowAssoc($res_price)) {
	echo "\t<tr>\n";
	if ($col['ip_date_to'] == '') {
		//only current can motify
		cell('<input type="text" name="_date_from" class="reqd" size="10" value="'.$col['ip_date_from'].'">', ' align="center"');
		cell('<input type="hidden" name="_idx" value="'.$col['ip_idx'].'"> -', ' align="center"');
		cell('<input type="text" name="_user_price" class="reqn"  style="width:100%" value="'.number_format((double)$col['ip_user_price']).'" onKeyUp="formatNumber(this,\'dot\')">', ' align="right"');
		cell('<input type="text" name="_remark" class="fmt" value="'.$col['ip_remark'].'" style="width:100%">');
		cell('<input type="button" name="btnUpdatePrice" class="input_sky" value="UPDATE">');
	} else {
		//cannot modify..
		cell($col['ip_date_from'], ' align="center"');
		cell($col['ip_date_to'], ' align="center"');
		cell(number_format((double)$col['ip_user_price']), ' align="right"');
		cell('<span title="Last Updated by : '.$col['ip_updated_by'].' '.date(', j-M-Y g:i:s', strtotime($col['ip_updated'])).'">'.$col['ip_remark'].'</span>', ' colspan="2"');
	}

	echo "\t</tr>\n";
} 
?>
</form>
</table><br /><br />
<script language="javascript" type="text/javascript">
	var f1 = window.document.frmUpdatePrice;
	var f2 = window.document.frmInsertPrice;
	
	f1.btnUpdatePrice.onclick = function() {
		if(confirm("Are you sure to update?")) {
			if(verify(f1)){
				f1.p_mode.value = 'update_item_price';
				f1.submit();
			}
		}
	}
	
	f2.btnAddNewPrice.onclick = function() {
		var start_date = validDate(f1._date_from);
		var new_date = validDate(f2._date_from);

		if(start_date.getTime() + 86400000 > new_date.getTime()) {
			alert("New FROM date cannot earlier than last FROM date");
			return false;
		}

		if(confirm("Are you sure to make a new price from this date?")) {
			if(verify(f2)) {
				f2.p_mode.value = 'insert_item_price';
				f2.submit();
			}
		}
	}
</script>
<!--============================================= NET PRICE =============================================-->
<?php if($currentDept == 'purchasing') { ?>
<strong>ITEM PRICE NET HISTORY</strong>
<table width="100%" class="table_c">
<form name="frmInsertPriceNet" method="POST">
<input type="hidden" name="p_mode" value="update">
<input type="hidden" name="_code" value="<?php echo $column['it_code']?>">
	<tr>
		<th width="12%">FROM</th>
        <th width="12%">TO</th>
		<th width="8%">PRICE ($)</th>
		<th width="8%">PRICE (Rp)</th>
		<th colspan="2">REMARK</th>
	</tr>
	<tr>
		<td align="center"><input type="text" name="_date_from" class="reqd" size="10"></td>
        <td></td>
		<td align="right"><input type="text" name="_price_dollar" class="reqn" value="0" style="width:100%" onKeyUp="formatNumber(this, 'dot')"></td>
		<td align="right"><input type="text" name="_price_rupiah" class="reqn" value="0" style="width:100%" onKeyUp="formatNumber(this, 'dot')"></td>
		<td><input type="text" name="_remark" class="fmt" style="width:100%"></td>
		<td width="10%"><input type="button" name="btnAddNewPriceNet" class="input_sky" value="NEW PRICE"></td>
	</tr>
</form>
<form name="frmUpdatePriceNet" method="POST">
<input type="hidden" name="p_mode" value="update">
<input type="hidden" name="_code" value="<?php echo $column['it_code']?>">
<?php
while($col =& fetchRowAssoc($res_price_net)) {
	echo "\t<tr>\n";
	if ($col['ipn_date_to'] == '') {
		//only current can motify
		cell('<input type="text" name="_date_from" class="reqd" size="10" value="'.$col['ipn_date_from'].'">', ' align="center"');
		cell('<input type="hidden" name="_idx" value="'.$col['ipn_idx'].'"> -', ' align="center"');
		cell('<input type="text" name="_price_dollar" class="reqn"  style="width:100%" value="'.number_format($col['ipn_price_dollar'],2).'" onKeyUp="formatNumber(this,\'dot\')">', ' align="right"');
		cell('<input type="text" name="_price_rupiah" class="reqn"  style="width:100%" value="'.number_format($col['ipn_price_rupiah'],2).'" onKeyUp="formatNumber(this,\'dot\')">', ' align="right"');
		cell('<input type="text" name="_remark" class="fmt" value="'.$col['ipn_remark'].'" style="width:100%">');
		cell('<input type="button" name="btnUpdatePriceNet" class="input_sky" value="UPDATE">');
	} else {
		//cannot modify..
		cell($col['ipn_date_from'], ' align="center"');
		cell($col['ipn_date_to'], ' align="center"');
		cell(number_format((double)$col['ipn_price_dollar'],2), ' align="right"');
		cell(number_format((double)$col['ipn_price_rupiah'],2), ' align="right"');
		cell('<span title="Last Updated by : '.$col['ipn_updated_by'].' '.date(', j-M-Y g:i:s', strtotime($col['ipn_updated'])).'">'.$col['ipn_remark']."</span>", ' colspan="2"');
	}
	echo "\t</tr>\n";
} 
?>
</form>
</table><br />
<script language="javascript" type="text/javascript">
	var f3 = window.document.frmUpdatePriceNet;
	var f4 = window.document.frmInsertPriceNet;

<?php if($numRow2 > 0) { ?>
	f3.btnUpdatePriceNet.onclick = function() {
		if(parseFloat(f3._price_dollar.value) > 0 && parseFloat(f3._price_rupiah.value))
		{
			alert("Please fill only one price, US$ or IDR");
			return false;
		}

		if(confirm("Are you sure to update?")) {
			if(verify(f3)){
				f3.p_mode.value = 'update_item_price_net';
				f3.submit();
			}
		}
	}
<?php } ?>

	f4.btnAddNewPriceNet.onclick = function() {
		if(typeof(f3._date_from) == 'object') {
			var start_date = validDate(f3._date_from);
			var new_date = validDate(f4._date_from);
	
			if(start_date.getTime() + 86400000 > new_date.getTime()) {
				alert("New FROM date cannot earlier than last FROM date");
				return false;
			}
		}

		if(parseFloat(f4._price_dollar.value) > 0 && parseFloat(f4._price_rupiah.value))
		{
			alert("Please fill only one price, US$ or IDR");
			return false;
		}

		if(confirm("Are you sure to make a new price from this date?")) {
			if(verify(f4)) {
				f4.p_mode.value = 'insert_item_price_net';
				f4.submit();
			}
		}
	}
</script>
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