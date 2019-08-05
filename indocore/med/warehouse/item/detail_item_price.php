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
$left_loc = "list_item_price.php";
$_code = urldecode($_GET['_code']);

//PROCESS FORM
require_once "tpl_process_form.php";

//========================================================================================== DEFAULT PROCESS
if(isZKError($result =& query("SELECT * FROM ".ZKP_SQL."_tb_item WHERE it_code = '$_code'")))
	$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/detail_item.php?_code=$code");
$column =& fetchRowAssoc($result);

//get category path from current icat_midx.
if(isZKError($path = executeSP(ZKP_SQL."_getCategoryPath", $column['icat_midx']))) {
	$M->goErrorPage($path, HTTP_DIR . "$currentDept/$moduleDept/index.php");
} else {
	eval(html_entity_decode($path[0]));	
	$path_cat = array_reverse($path);
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
<strong class="title">[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] DETAIL ITEM PRICE</strong><br /><br />
<strong>Item Information</strong>
<table width="100%" class="table_a">
	<tr>
		<th width="14%">ITEM CODE</th>
		<td width="30%"><?php echo $column['it_code']?></td>
		<th width="10%">CATEGORY</th>
		<td width="46%"><?php echo $path_cat[1][4]." > ".$path_cat[2][4]." > ".$path_cat[3][4]?></td>
	</tr>
	<tr>
		<th>MODEL NO</th>
		<td><?php echo $column['it_model_no']?></td>
		<th>TYPE</th>
		<td><?php echo $column['it_type']?></td>
	</tr>
	<tr>
		<th>DESCRIPTION</th>
		<td colspan="3"><?php echo $column['it_desc']?></td>
	</tr>
	<tr>
		<th>REMARK</th>
		<td colspan="3"><?php echo nl2br($column['it_remark'])?></td>
	</tr>
</table><br>
<strong>Item Price History</strong>
<form name="frmUpdate" method="POST">
<input type="hidden" name="p_mode" value="update">
<input type="hidden" name="_code" value="<?php echo $column['it_code']?>">
<table width="100%" class="table_c">
	<tr>
		<th width="10%">FROM</th>
		<th width="10%">TO</th>
		<th width="12%">User Price</th>
		<th colspan="2">REMARK</th>
	</tr>
<?php
$sql = "
SELECT ip_idx, to_char(ip_date_from, 'dd-Mon-YYYY') AS ip_date_from, to_char(ip_date_to, 'dd-Mon-YYYY') AS ip_date_to,
ip_user_price, ip_remark, ip_updated_by, ip_updated
FROM ".ZKP_SQL."_tb_item_price
WHERE it_code='$_code' ORDER BY ip_idx DESC";

isZKError($dh =& query($sql)) ? $M->printMessage($dh) : true;
while($price =& fetchRowAssoc($dh)) {
	echo "\t<tr>\n";
	if ($price['ip_date_to'] == '') {
		//only current can motify
		cell('<input type="text" name="_date_from" class="reqd" size="10" value="'.$price['ip_date_from'].'">');
		cell('<input type="hidden" name="_idx" value="'.$price['ip_idx'].'"> -', ' align="center"');
		cell('<input type="text" name="_user_price" class="reqn" size="10" value="'.number_format((double)$price['ip_user_price']).'" onKeyUp="formatNumber(this,\'dot\')">', ' align="right"');
		cell('<input type="text" name="_remark" class="fmt" value="'.$price['ip_remark'].'" style="width:95%">');
		cell('<input type="button" name="btnUpdate" class="input_sky" value="UPDATE">', ' width="10%"');
	} else {
		//cannot modify..
		cell($price['ip_date_from'], ' align="center"');
		cell($price['ip_date_to'], ' align="center"');
		cell('<span title="Last Updated by :'.$price['ip_updated_by'].' '.date(', j-M-Y g:i:s', strtotime($price['ip_updated'])).'">'.number_format((double)$price['ip_user_price']).'</span>', ' align="right"');
		cell($price['ip_remark'], ' colspan="2"');
	}

	echo "\t</tr>\n";
}
?>
</table><br />
</form>
<strong>New Price</strong> <span class="comment">* Please just enter the start date</span>
<form name="frmInsert" method="POST">
<input type="hidden" name="p_mode" value="update">
<input type="hidden" name="_code" value="<?php echo $column['it_code']?>">
<table width="100%" class="table_c">
	<tr>
		<th width="10%">FROM</th>
		<th width="12%">USER PRICE</th>
		<th colspan="2">REMARK</th>
	</tr>
	<tr>
		<td><input type="text" name="_date_from" class="reqd" size="10"></td>
		<td><input type="text" name="_user_price" class="reqn" size="10" onKeyUp="formatNumber(this, 'dot')"></td>
		<td><input type="text" name="_remark" class="fmt" style="width:95%"></td>
		<td width="10%"><input type="button" name="btnAddNew" class="input_sky" value="NEW PRICE"></td>
	</tr>
</table>
</form>

<!-- START BUTTON -->
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td align="right">
			<button name="btnListAll" class="input_sky">LIST</button>&nbsp;
			<button name="btnListCategory" class="input_sky">LIST CAT ITEMS</button>
		</td>
</table>
<script language="javascript" type="text/javascript">
	//Define the form that you want to handle
	var f1 = window.document.frmUpdate;
	var f2 = window.document.frmInsert;
	
	f1.btnUpdate.onclick = function() {
		
		if(confirm("Are you sure to update?")) {
			if(verify(f1)){
				f1.p_mode.value = 'update_item_price';
				f1.submit();
			}
		}
	}
	
	f2.btnAddNew.onclick = function() {
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
	
	window.document.all.btnListAll.onclick = function() {
		window.location.href = '<?php echo HTTP_DIR . "$currentDept/$moduleDept/" ?>list_item_price.php';
	}
	
	window.document.all.btnListCategory.onclick = function() {
		window.location.href = '<?php echo HTTP_DIR . "$currentDept/$moduleDept/" ?>list_item_price.php?lastCategoryNo=<?php echo $column['icat_midx']?>';
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