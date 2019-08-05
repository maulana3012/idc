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
$left_loc = "input_return_order_step_1.php";

//PROCESS FORM
require_once APP_DIR . "_include/order/tpl_process_return_form.php"; 

//============================================================================================ DEFAULT PROCESS
$sql = "SELECT *, ".ZKP_SQL."_getTurnCode(ord_code,2) AS return_code, (SELECT book_idx FROM ".ZKP_SQL."_tb_booking WHERE book_doc_ref='$_ord_code' AND book_doc_type=2) AS book_idx FROM ".ZKP_SQL."_tb_order WHERE ord_code = '$_ord_code'";
if(isZKError($result =& query($sql)))
	$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/index.php");
$column =& fetchRowAssoc($result);
$numRow = numQueryRows($result);

if($numRow > 0) {
	$column["book_idx"] = ($column["book_idx"]=='')? 0:$column["book_idx"];
	//[WAREHOUSE] order item
	$whitem_sql = "
	SELECT
	  a.it_code,			--0
	  a.icat_midx,			--1
	  a.it_model_no,		--2
	  a.it_type,			--3
	  a.it_desc,			--4
	  b.boit_it_code_for,	--5
	  b.boit_qty,			--6
	  b.boit_function,		--7
	  b.boit_remark, 		--8
	  b.boit_type			--9
	FROM
	  ".ZKP_SQL."_tb_booking_item AS b
	  JOIN ".ZKP_SQL."_tb_item AS a USING(it_code)
	WHERE b.book_idx = '{$column["book_idx"]}'
	ORDER BY a.it_code";
	$wh_res	=& query($whitem_sql);
	
	//[CUSTOMER] order item
	$cusitem_sql =
	"SELECT
	  a.it_code,			--0
	  a.it_model_no,		--1
	  a.it_desc,			--2
	  b.odit_unit_price,	--3
	  b.odit_qty,			--4
	  b.odit_unit_price * b.odit_qty AS amount,				--5
	  to_char(b.odit_delivery, 'DD-Mon-YYYY') AS delivery,	--6
	  b.odit_remark											--7
	FROM ".ZKP_SQL."_tb_item AS a JOIN ".ZKP_SQL."_tb_order_item AS b ON (a.it_code = b.it_code)
	WHERE b.ord_code = '$_ord_code'";
	$cus_res =& query($cusitem_sql);
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
<script src="../../_include/order/input_order_return.js" type="text/javascript"></script>
<script language="javascript" type="text/javascript">
function checkform(o) {
	var f = window.document.frmInsert;

	if(o._type_ord.value == '0') {
		if (window.itemWHPosition.rows.length <= 0 || window.itemCusPosition.rows.length <= 0) {
			alert("You need to choose at least 1 item");
			return;
		}
	} else if(o._type_ord.value == '1') {
		if (window.itemCusPosition.rows.length <= 0) {
			alert("You need to choose at least 1 item");
			return;
		}
	}

	if (verify(o)) {
		if(confirm("Are you sure to save order?")) {
			o.submit();
		}
	}
}
</script>
</head>
<body topmargin="0" leftmargin="0" onload="initPageInput('<?php echo ZKP_SQL ?>')">
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
<table width="100%">
  <tr>
	<td>
		<strong style="font-size:18px;font-weight:bold">
		[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] NEW RETURN ORDER (STEP 2 / 2)<br />
		</strong>
	</td>
  </tr>
  <tr>
	<td><small class="comment">* <?php echo $title[$_type_ord+1] ?></small></td>
  </tr>
</table>
<hr><br />

<form name='frmInsert' method='POST'>
<input type='hidden' name='p_mode' value='insert'>
<input type="hidden" name="_type_ord" value="<?php echo $_type_ord?>">
<input type="hidden" name="_dept" value="<?php echo $department?>">
<input type="hidden" name="_ord_code" value="<?php echo $_ord_code?>">
<input type="hidden" name="_ord_date" value="<?php echo $_ord_date?>">
<input type="hidden" name="_po_date" value="<?php echo $_po_date?>">
<input type="hidden" name="_po_no" value="<?php echo $_po_no?>">
<input type="hidden" name="_type" value="<?php echo $_type?>">
<input type="hidden" name="_received_by" value="<?php echo addslashes($_received_by)?>">
<input type="hidden" name="_confirm_by" value="<?php echo addslashes($_confirm_by)?>">
<input type="hidden" name="_cus_to" value="<?php echo $_cus_to?>">
<input type="hidden" name="_ship_to" value="<?php echo $_ship_to?>">
<input type="hidden" name="_bill_to" value="<?php echo $_bill_to?>">
<input type="hidden" name="_cus_to_attn" value="<?php echo addslashes($_cus_to_attn)?>">
<input type="hidden" name="_ship_to_attn" value="<?php echo addslashes($_ship_to_attn)?>">
<input type="hidden" name="_bill_to_attn" value="<?php echo addslashes($_bill_to_attn)?>">
<input type="hidden" name="_cus_to_address" value="<?php echo addslashes($_cus_to_address)?>">
<input type="hidden" name="_ship_to_address" value="<?php echo addslashes($_ship_to_address)?>">
<input type="hidden" name="_bill_to_address" value="<?php echo addslashes($_bill_to_address)?>">
<?php 
require_once APP_DIR . "_include/order/tpl_input_return_order_top.php"; 
require_once APP_DIR . "_include/order/tpl_input_return_item_". ((int)$_type_ord+1) .".php";
require_once APP_DIR . "_include/order/tpl_input_return_order_bottom.php"; 
?>
</form>
<p align='center'>
	<button name='btnSave' class='input_btn' style='width:120px;' onclick='checkform(window.document.frmInsert)'><img src="../../_images/icon/btnSave-blue.gif" width="15px" align="middle" alt="Save order"> &nbsp; Save return</button>&nbsp;
	<button name='btnCancel' class='input_btn' style='width:120px;' onclick='window.location.href="input_return_order_step_1.php"'><img src="../../_images/icon/delete.gif" width="15px" align="middle" alt="Cancel order"> &nbsp; Cancel return</button>
</p>
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