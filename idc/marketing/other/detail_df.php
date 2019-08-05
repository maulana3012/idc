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
$left_loc = 'daily_summary_by_group.php';
if (!isset($_GET['_code']) || $_GET['_code'] == '') {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/index.php");
} else {
	$_code = urldecode($_GET['_code']);
}
$_do_type	= "df";

//PROCESS FORM
require_once APP_DIR . "_include/other/tpl_process_form.php";

//========================================================================================= DEFAULT PROCESS
//df
$sql	= "SELECT *,(SELECT book_idx FROM ".ZKP_SQL."_tb_booking WHERE book_doc_ref='$_code' AND book_doc_type=4) AS book_idx FROM ".ZKP_SQL."_tb_df WHERE df_code = '$_code'";
$result =& query($sql);
$column =& fetchRowAssoc($result);

if(numQueryRows($result) <= 0) {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/index.php");
} else if($column['df_cfm_wh_delivery_by_account'] == '') {
	goPage(HTTP_DIR . "$currentDept/$moduleDept/revise_df.php?_code=".urlencode($column['df_code']));
}

//[WAREHOUSE] billing item
$whitem_sql = "
SELECT
  a.it_code,			--0
  a.it_model_no,		--1
  a.it_desc,			--2
  b.boit_it_code_for,	--3
  b.boit_qty,			--4
  b.boit_function,		--5
  b.boit_remark, 		--6
  b.boit_type			--7
FROM
  ".ZKP_SQL."_tb_booking_item AS b
  JOIN ".ZKP_SQL."_tb_item AS a USING(it_code)
WHERE b.book_idx = ".$column['book_idx']."
ORDER BY a.it_code";
$whitem_res	=& query($whitem_sql);

//[CUSTOMER] billing item
$cusitem_sql = "
SELECT
  a.it_code,			--0
  a.it_model_no,		--1
  a.it_desc,			--2
  b.dfit_qty,			--3
  b.dfit_remark 		--4
FROM
  ".ZKP_SQL."_tb_df_item AS b
  JOIN ".ZKP_SQL."_tb_item AS a USING(it_code)
WHERE df_code = '$_code'
ORDER BY it_code";
$cusitem_res	=& query($cusitem_sql);
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
function recalculateAmount(){

	var countI		= window.itemWHPosition.rows.length;
	var countII		= window.itemCusPosition.rows.length;
	var sumOfQtyI	= 0;
	var sumOfQtyII	= 0;

	for (var i=0; i<countI; i++) {
		var oRow = window.itemWHPosition.rows(i);
		sumOfQtyI = sumOfQtyI + parseFloat(removecomma(oRow.cells(4).innerText));
	}

	for (var i=0; i<countII; i++) {
		var oRow = window.itemCusPosition.rows(i);
		sumOfQtyII = sumOfQtyII + parseFloat(removecomma(oRow.cells(3).innerText));
	}

	window.document.all.totalWhQty.value	= numFormatval(sumOfQtyI+'',2);
	window.document.all.totalCusQty.value	= addcomma(sumOfQtyII);
}
</script>
</head>
<body topmargin="0" leftmargin="0" onload="recalculateAmount()">
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
<strong class="title">[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] DETAIL DO FREE</strong><br /><br />
<table width="100%" class="table_box">
	<tr>
		<td colspan="2"><strong class="info">DF INFORMATION</strong></td>
		<td colspan="3" align="right">
			<i>Last updated by : <?php echo $column['df_lastupdated_by_account'].date(', j-M-Y g:i:s', strtotime($column['df_lastupdated_timestamp']))?></i>
		</td>
	</tr>
	<tr>
		<th width="12%">DF NO</th>
		<td width="30%"><b><?php echo $column['df_code'] ?></b></td>
		<th width="12%">DF DATE</th>
		<td><?php echo date('j-M-Y', strtotime($column['df_date'])) ?></td>
	</tr>
	<tr>
		<th>ISSUED BY</th>
		<td><?php echo $column['df_received_by'] ?></td>
		<th>ISSUED DATE</th>
		<td><?php echo ($column['df_issued_date']=='')?'':date('j-M-Y', strtotime($column['df_issued_date'])) ?></td>
	</tr>
	<tr>
		<th>REQUEST BY</th>
		<td><?php echo $column['df_issued_by'] ?></td>
		<th>TYPE ITEM</th>
		<td>
			<input type="radio" name="_type_vat" value="1" disabled <?php echo ($column['df_type_item']==1)?'checked':'' ?>> Vat &nbsp;
			<input type="radio" name="_type_vat" value="2" disabled <?php echo ($column['df_type_item']==2)?'checked':'' ?>> Non Vat &nbsp;
		</td>
	</tr>
</table>
<table width="100%" class="table_box" cellspacing="1">
	<tr>
		<th rowspan="2" width="12%">CUSTOMER</th>
		<th width="10%">CODE</th>
		<td width="20%"><?php echo $column['df_cus_to'] ?></td>
		<th width="12%">NAME</th>
		<td><?php echo $column['df_cus_name'] ?></td>
	</tr>
	<tr>
		<th>ADDRESS</th>
		<td colspan="3"><?php echo $column['df_cus_address'] ?></td>
	</tr>
	<tr>
		<th>SHIP TO</th>
		<th>CODE</th>
		<td><?php echo $column['df_ship_to'] ?></td>
		<th>NAME</th>
		<td><?php echo $column['df_ship_name'] ?></td>
	</tr>
</table><br />
<strong>[<font color="#446FBE">WAREHOUSE</font>] ITEM LIST</strong>  &nbsp; <i>( <a href="revise_df_2.php?_code=<?php echo $_code ?>">Revised item</a> )</i>
<table width="100%" class="table_nn">
	<thead>
		<tr height="30px">
			<th width="7%">CODE</th>
			<th width="7%">FOR</th>
			<th width="15%">ITEM NO</th>
			<th>DESCRIPTION</th>
			<th width="7%">QTY</th>
			<th width="8%">(x)</th>
			<th width="15%">REMARK</th>
		</tr>
	</thead>
	<tbody id="itemWHPosition">
<?php while($items =& fetchRow($whitem_res)) { ?>
		<tr id="<?php echo trim($items[0]).'-'.trim($items[3])?>">
			<td><?php echo $items[0]?></td>
			<td><?php echo $items[3]?></td>
			<td><?php echo $items[1]?></td>
			<td><?php echo $items[2]?></td>
			<td align="right"><?php echo number_format((double)$items[4],2)?></td>
			<td align="right"><?php echo $items[5]?></td>
			<td><?php echo $items[6]?></td>
		</tr>
<?php } ?>
	</tbody>
</table>
<table width="100%" class="table_nn">
	<tr>
		<th align="right">TOTAL</th>
		<th width="8%"><input name="totalWhQty" type="text" class="reqn" style="width:100%" readonly></th>
		<th width="23%">&nbsp;</th>
	</tr>
</table><br />
<strong>[<font color="#446FBE">CUSTOMER</font>] ITEM LIST</strong>
<table width="100%" class="table_nn">
	<thead>
		<tr height="30px">
			<th width="5%">CODE</th>
			<th width="13%">ITEM NO</th>
			<th>DESCRIPTION</th>
			<th width="8%">QTY</th>
			<th width="15%">REMARK</th>
		</tr>
	</thead>
	<tbody id="itemCusPosition">
<?php while($items =& fetchRow($cusitem_res)) { ?>
		<tr id="<?php echo trim($items[0])?>">
			<td><?php echo $items[0]?></td>
			<td><?php echo $items[1]?></td>
			<td><?php echo $items[2]?></td>
			<td align="right"><?php echo number_format((double)$items[3]) ?></td>
			<td><?php echo $items[4]?></td>
		</tr>
<?php } ?>
	</tbody>
</table>
<table width="100%" class="table_nn">
	<tr>
		<th align="right">TOTAL</th>
		<th width="8%"><input name="totalCusQty" type="text" class="reqn" style="width:100%" readonly></th>
		<th width="15%">&nbsp;</th>
	</tr>
</table><br />
<strong class="info">CONDITION</strong>
<table class="table_box" width="100%">
	<tr>
		<th width="12%">DELIVERY</th>
		<td>1.<input type="text" name="_delivery_warehouse" class="fmt" size="2" maxlength="2" value="<?php echo $column['df_delivery_warehouse'] ?>" disabled>ex W/house(P/C/D)</td>
		<td>2.<input type="text" name="_delivery_franco" class="fmt" size="2" maxlength="2" value="<?php echo $column['df_delivery_franco'] ?>" disabled>Franco(P/D)</td>
		<td>by <input type="text" name="_delivery_by" size="6" class="fmt" value="<?php echo $column['df_delivery_by'] ?>" disabled></td>
		<td>Freight charge : Rp <?php echo number_format((double)$column['df_delivery_freight_charge']) ?></td>
	</tr>
	<tr>
		<th>REMARK</th>
		<td colspan="4"><textarea name="_remark" style="width:100%" rows="4" readonly><?php echo $column['df_remark'] ?></textarea></td>
	</tr>
</table>
<table width="100%" cellpadding="2" cellspacing="0">
	<tr>
		<td><span class="comment"><i>Confirm outgoing item from warehouse by : <?php echo $column['df_cfm_wh_delivery_by_account'].date(', j-M-Y g:i:s', strtotime($column['df_cfm_wh_delivery_timestamp']))?></i></span></td>
		<td align="right">
			Rev No:
			<select name="_revision_time" id="_revision_time">
			<?php
				for($counter = $column['df_revesion_time']; $counter >= 0; $counter--) {
					echo "\t\t\t<option value=\"$counter\">$counter</option>\n";
				}
			?>
			</select>&nbsp;
			<button name='btnPrint' class='input_btn' style='width:100px;'><img src="../../_images/icon/print.gif" width="18px" align="middle" alt="Print pdf"> &nbsp; Print PDF</button>&nbsp;
			<button name='btnList' class='input_btn' style='width:130px;'><img src="../../_images/icon/list.gif" width="20px" align="middle" alt="Go to list"> &nbsp; Go to summary</button>
		</td>
	</tr>
</table><br /><br />
<script language="javascript" type="text/javascript">
	//Define the form that you want to handle
	var oForm = window.document.frmInsert;

	window.document.all.btnPrint.onclick = function() {
		var winforPrint = window.open('','','toolbar=no,width=780,height=580,resizable=yes');
		winforPrint.document.location.href = "../../_include/other/pdf/download_pdf.php?_code=<?php echo trim($_code)."&_dept=".$currentDept."&_date=".date("Ym", strtotime($column['df_date']))?>&_rev=" + window._revision_time.value;
	}

	window.document.all.btnList.onclick = function() {
		window.location.href = '<?php echo HTTP_DIR . "$currentDept/$moduleDept/daily_summary_by_group.php?cboSource=df" ?>';
	}
</script>
<?php include APP_DIR . "_include/other/tpl_delivery.php"; ?>
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