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
ckperm(ZKP_UPDATE, HTTP_DIR . "$currentDept/$moduleDept/list_group_policy.php");

//GLOBAL
$left_loc = "list_group_policy.php";
$_code = $_GET['_code'];

//========================================================================================== UPDATE PROCESS
if (ckperm(ZKP_UPDATE, HTTP_DIR . "$currentDept/$moduleDept/detail_group_policy.php", 'update')) {

	$_code		= $_POST['_code'];
	$_is_dirty_item	= $_POST['_is_dirty_item'];	
	$_cug_code	= $_POST['_cug_code'];
	$_desc		= $_POST['_desc'];
	$_basic_disc_pct = $_POST['_basic_disc_pct'];
	$_disc_pct	= $_POST['_disc_pct'];
	$_is_valid	= ($_POST['_is_valid'] == '1') ? "TRUE" : "FALSE";
	$_is_apply_all	= ($_POST['_is_apply_all'] == '1') ? "TRUE" : "FALSE";
	$_date_from	= $_POST['_date_from'];
	$_date_to	= $_POST['_date_to'];
	$_remark	= $_POST['_remark'];

	//Item Value
	foreach($_POST['_it_code'] as $val) $_it_code[] = $val;

	//make pgsql ARRAY String for many item
	$_it_code	= '$$' . implode('$$,$$', $_it_code) . '$$';

	$result = executeSP(
		ZKP_SQL."_updateGroupPrice",
		$_code, //ag_idx
		$_is_dirty_item,
		"$\${$_cug_code}$\$",
		"$\${$_desc}$\$",
		$_basic_disc_pct,
		$_disc_pct,
		$_is_valid,
		$_is_apply_all,
		"$\${$_date_from}$\$",
		"$\${$_date_to}$\$",
		"$\${$_remark}$\$",
		"ARRAY[$_it_code]",
		"$\$".$S->getValue("ma_account")."$\$"); // updated

	if (isZKError($result)) {
		if(preg_match("/_([0-9]+)_ITEM_([\w]+)/",$result->getMessage(), $match)) {
			$o = new ZKError("DUPLICATE_PERIOD_AND_ADD_DISCOUNT",
						 "DUPLICATE_PERIOD_AND_ADD_DISCOUNT",
						 "Duplicated period and additional discount found. GROUP POLICY #{$match[1]}, ITEM CODE: {$match[2]}<br>Please check the group policy of this item after click [CONFIRM]");
			$M->goErrorPage($o, HTTP_DIR . "$currentDept/$moduleDept/detail_group_policy.php?_code=$match[1]");
		}
		$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/list_group_policy.php");
	} else {
		$M->goPage(HTTP_DIR . "$currentDept/$moduleDept/detail_group_policy.php?_code=$_code");
	}
}

//========================================================================================== DELETE PROCESS
if(ckperm(ZKP_DELETE, HTTP_DIR . "$currentDept/$moduleDept/list_group_policy.php", "delete")) {
	$_code = $_POST['_code'];
	$sql = "DELETE FROM ".ZKP_SQL."_tb_group_policy WHERE ag_idx = $_code";
	isZKError($result =& query($sql)) ? $M->goErrorPage($result, HTTP_DIR . '$currentDept/$moduleDept/list_group_policy.php') : 0;	
	goPage(HTTP_DIR . "$currentDept/$moduleDept/list_group_policy.php");
}

//========================================================================================== DEFAULT PROCESS
$sql = "
SELECT ag,*, to_char(ag_date_from, 'dd-Mon-yyyy') AS date_from, to_char(ag_date_to, 'dd-Mon-yyyy') AS date_to, cug.cug_name FROM ".ZKP_SQL."_tb_group_policy AS ag INNER JOIN ".ZKP_SQL."_tb_customer_group AS cug ON (ag.cug_code = cug.cug_code) WHERE ag.ag_idx=$_code";
isZKError($result =& query($sql)) ? $M->goErrorPage($result, HTTP_DIR . '$currentDept/$moduleDept/list_group_policy.php') : 0;
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
<script language="javascript" type="text/javascript">
function checkform(o) {
	var d1 = parseDate(o._date_from.value, 'prefer_euro_format');
	var d2 = parseDate(o._date_to.value, 'prefer_euro_format');
	
	if(d1 == null || d2 == null) {
		alert("Please input correct date");
		o._date_from.value = '';
		o._date_to.value = '';
		o._date_from.focus();
		return;
	} else if (d1.getTime() > d2.getTime()) {
		alert("TO date is more earlier than FROM date");
		o._date_from.value = '';
		o._date_to.value = '';
		o._date_from.focus();
		return;
	}

	if (verify(o)) {
		o.submit();
	}
}

//Open window for search item
var wSearchItem;
function fillItem(){
	var f = window.document.frmInsert;
	if (f._basic_disc_pct.value == '' || f._disc_pct.value == '') {
		alert("please choose GROUP and specify ADDITIONAL DISCOUNT first");
		return;
	}

	var x = (screen.availWidth - 580) / 2;
	var y = (screen.availHeight - 620) / 2;
	wSearchItem = window.open('./p_list_item_for_apotik_policy.php','wSearchItem',
		'scrollbars,width=580,height=620,screenX='+x+',screenY='+y+',left='+x+',top='+y);
	wSearchItem.focus();
}

var isItemDirty = false; //if the item is modified.
function createItem(arrItem) {

	var oTR = window.document.createElement("TR");
	var oTD = new Array();
	var oHidden = new Array();
	
	//Check has same CODE
	var count = rowPosition.rows.length;
	for (var i=0; i<count; i++) {
		var oRow = window.rowPosition.rows(i);
		if (trim(oRow.cells[0].innerText) == trim(arrItem[0])) {
			alert("Same Item code already exist!");
			return false;
		}
	}

	//Price
	var basic_disc = parseFloat(window.document.frmInsert._basic_disc_pct.value);
	var additional_disc = parseFloat(window.document.frmInsert._disc_pct.value);
	var user_price = Math.round(parseFloat(arrItem[3])/10)*10;
	var apotik_price = Math.round(((user_price - user_price * basic_disc/100)/1.1)/10)*10;
	var disc_price = Math.round(((user_price - user_price * (basic_disc + additional_disc)/100)/1.1)/10)*10;

	//If you add more cell
	// 1. increase tthe count as number of td
	// 2. add Case
	// the Cell order match with p_list_item.php field.
	for (var i=0; i<7; i++) { //5 is number of TD
		oTD[i] = window.document.createElement("TD");
		oHidden[i] = window.document.createElement("INPUT");
		oHidden[i].type = "hidden";

		switch (i) {
			case 0: // CODE
				oTD[i].innerText = arrItem[0];
				oHidden[i].name = "_it_code[]";
				oHidden[i].value = arrItem[i];
				break;

			case 1: // ITEM NO
				oTD[i].innerText = cut_string(arrItem[1],10);
				break;

			case 2: // DESCRIPTION
				oTD[i].innerText = cut_string(arrItem[2],40);
				break;

			case 3: // USER PRICE
				oTD[i].innerText = numFormatval(user_price + '', 0);
				oTD[i].align = "right";
				break;

			case 4: // APOTIK PRICE
				oTD[i].innerText = numFormatval(apotik_price + '', 0);
				oTD[i].align = "right";
				break;

			case 5: // DISC PRICE
				oTD[i].innerText = numFormatval(disc_price + '', 0);
				oTD[i].align = "right";
				break;

			case 6: // DELETE
				oTD[i].innerHTML = "<a href=\"javascript:deleteItem('" + arrItem[0] + "')\"><img src='../../_images/icon/delete.gif' width='12px'></a>";
				oTD[i].align = "center";
				break;
		}

		oTR.appendChild(oTD[i]);
		oTR.appendChild(oHidden[i]);
	}

	isItemDirty = true;
	window.rowPosition.appendChild(oTR);
}

//Delete Item wtd rows collection
function deleteItem(idx) {
	var count = rowPosition.rows.length;
	
	if (count-1 <= 0) {
		alert("At least, you must leave 1 item at the list");
		return;
	}

	for (var i=0; i<count; i++) {
		var oRow = window.rowPosition.rows(i);
		if (oRow.cells(0).innerText == idx) {
			isItemDirty = true;
			var n = window.rowPosition.removeChild(oRow);
			count = count - 1; //decrease loop - 1
		}
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
<h4>DETAIL GROUP PRICE</h4>
<form name='frmInsert' method='POST'>
<input type="hidden" name="p_mode" value="update">
<input type="hidden" name="_code" value="<?php echo $column['ag_idx']?>">
<input type="hidden" name="_cug_code" value="<?php echo $column['cug_code']?>">
<input type="hidden" name="_basic_disc_pct" value="<?php echo $column['ag_basic_disc_pct']?>">
<input type="hidden" name="_is_dirty_item" value="FALSE">
<table width="100%" class="table_box">
	<tr>
		<th width="12%">POLICY NO</th>
		<td colspan="3"><?php echo $column['ag_idx']?></td>
	</tr>
	<tr>
		<th width="12%">GROUP CODE</th>
		<td><?php echo "[".$column['cug_code']."] ".$column['cug_name']?></td>
		<th>BASIC DISC%</th>
		<td><?php echo $column['ag_basic_disc_pct']?></td>
	</tr>
	<tr>
		<th width="12%">DESC</th>
		<td><input name="_desc" class="req" style="width:90%" value="<?php echo $column['ag_desc']?>"></td>
		<th>VALID</th>
		<td>
			<input type="radio" name='_is_valid' value='1' <?php echo ($column['ag_is_valid']=='t')?"checked":""?>>YES,<input type="radio" name='_is_valid' value='0' <?php echo ($column['ag_is_valid']=='f')?"checked":""?>>No</td>
	</tr>
	<tr>
		<th width="12%">PERIOD</th>
		<td>FROM: <input type="text" name="_date_from" class="reqd" size="8" value="<?php echo $column['date_from'];?>"> 
			TO: <input type="text" name="_date_to" class="reqd" size="8" value="<?php echo $column['date_to']?>"> &nbsp;
			ADDITIONAL DISCOUNT: <input type="text" class="fmtn" maxlength="4" name="_disc_pct" size="3" value="<?php echo $column['ag_disc_pct']?>">%</td>
		<th>ALL ITEM</th>
		<td><input type="radio" name='_is_apply_all' value='1' <?php echo ($column['ag_is_apply_all']=='t')?"checked":""?> disabled>YES,<input type="radio" name='_is_apply_all' value='0' <?php echo ($column['ag_is_apply_all']=='f')?"checked":""?> disabled>No</td>
	</tr>
</table>
<?php if($column['ag_is_apply_all'] != "t") {?>
<br>
<strong>APPLIED ITEM</strong> &nbsp; <small class="comment"><i><a href="javascript:fillItem()">( search <img src="../../_images/icon/search_mini.gif"> )</a></i></small>
<table width="100%" class="table_l">
	<thead>
		<tr>
			<th width="8%">CODE</th>
			<th width="12%">ITEM NO</th>
			<th>DESCRIPTION</th>
			<th width="15%">USER PRICE<br>(CURRENT @PRICE)</th>
			<th width="10%">A/ PRICE<br/>(W/O VAT)</th>
			<th width="10%">DISC PRICE<br/>(W/O VAT)</th>
			<th width="5%">DEL</th>
		</tr>
	</thead>
	<tbody id="rowPosition">
<?php
$sql = "SELECT git.*, it.it_model_no, it.it_desc, ".ZKP_SQL."_getUserPrice(it.it_code, CURRENT_DATE) AS user_price FROM ".ZKP_SQL."_tb_group_price AS git INNER JOIN ".ZKP_SQL."_tb_item AS it ON (git.it_code = it.it_code) WHERE git.ag_idx = " . $column['ag_idx'] . " ORDER BY git.it_code";
isZKError($result =& query($sql)) ? $M->printMessage($result) : 0;
while($item = fetchRowAssoc($result)) {
	$user_price = $item['user_price'];
	$apotik_price = round(($user_price -($user_price * $column['ag_basic_disc_pct']/100))/1.1);
	$disc_price = round(($user_price -($user_price *($column['ag_basic_disc_pct']+ $column['ag_disc_pct'])/100))/1.1);
?>
	<tr>
		<td><?php echo trim($item['it_code'])?></td>
		<td><?php echo cut_string($item['it_model_no'], 17)?></td>
		<td><?php echo cut_string($item['it_desc'], 65)?></td>
		<td align="right"><?php echo number_format((double)$user_price)?>
		<input type="hidden" name="_it_code[]" value="<?php echo $item['it_code']?>"></td>
		<td align="right"><?php echo number_format((double)$apotik_price)?></td>
		<td align="right"><?php echo number_format((double)$disc_price)?></td>
		<td align="center"><a href="javascript:deleteItem('<?php echo trim($item['it_code'])?>')">[-]</a></td>
	</tr>
<?php } ?>
	</tbody>
</table><br>
<?php } ?>
<table width="100%" class="table_box">
	<tr>
		<th width="12%">REMARK</th>
		<td colspan="3"><textarea name="_remark" cols="80" rows="5"><?php echo $column['ag_remark']?></textarea></td>
</table>
</form>
<table width="100%" class="table_box">
	<tr>
		<td><I>
		<?php echo "Created by ".$column['ag_created_by'].date(', j-M-Y g:i:s', strtotime($column['ag_created']))?></I></td>
		<td align="right"><I>
		<?php echo "Updated by ".$column['ag_updated_by'].date(', j-M-Y g:i:s', strtotime($column['ag_updated']))?></I></td>
	</tr>
</table>
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td>
			<button name='btnDelete' class='input_red' style='width:150px;'><img src="../../_images/icon/trash.gif" width="15px" align="middle"> &nbsp; Delete Group</button>
		</td>
		<td align="right">
			<button name='btnUpdate' class='input_btn' style='width:80px;'><img src="../../_images/icon/update.gif" width="20px" align="middle"> &nbsp; Update</button>&nbsp;
			<button name='btnList' class='input_btn' style='width:130px;'><img src="../../_images/icon/list.gif" width="20px" align="middle"> &nbsp; Go to summary</button>
		</td>
</table>
<script language="javascript" type="text/javascript">
	//Define the form that you want to handle
	var oForm = window.document.frmInsert;

	window.document.all.btnDelete.onclick = function() {
		if(confirm("Are you sure to delete?")) {
			oForm.p_mode.value = 'delete';
			oForm.submit();
		}
	}

	window.document.all.btnUpdate.onclick = function() {
		if(confirm("Are you sure to update?")) {
			if(verify(oForm)){
				if (isItemDirty) oForm._is_dirty_item.value = 'TRUE';
				oForm.p_mode.value = 'update';
				oForm.submit();
			}
		}
	}

	window.document.all.btnList.onclick = function() {
		window.location.href = '<?php echo HTTP_DIR . '$currentDept/$moduleDept/' ?>list_group_policy.php';
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