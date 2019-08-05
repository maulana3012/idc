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
require_once LIB_DIR . "zk_listing.php";

//PAGE ACCESS PERMISSION
require_once "./_access_local.php";
ckperm(ZKP_SELECT, "javascript:window.close();");

//Check PARAMETER
if(!isset($_GET['_cus_code']) || $_GET['_cus_code'] == "")
	die("<script language=\"javascript1.2\">window.close();</script>");

$_order_by	= $_GET['_order_by'];
$_cus_code	= trim($_GET['_cus_code']);
$_cus_name	= $_GET['_cus_name'];
$strGet		= "";

//============================================================================================ DEFAULT PROCESS
$sqlQuery = new strSelect("
SELECT
  bill_code,
  bill_inv_date,
  to_char(bill_inv_date, 'dd-Mon-YYYY') as bill_date,
  CASE
	WHEN bill_vat > 0 then 'vat'
	WHEN bill_vat <= 0 then 'non'
  END AS bill_type_vat,
  CASE
	WHEN bill_type_invoice=0 AND (select book_idx from ".ZKP_SQL."_tb_booking where book_doc_type=1 and book_doc_ref=b.bill_code) is not null THEN (select book_idx from ".ZKP_SQL."_tb_booking where book_doc_type=1 and book_doc_ref=b.bill_code) 
	ELSE '0'
  END AS book_idx,
  bill_vat_inv_no,
  bill_do_no AS do_no,
  to_char(bill_do_date, 'dd-Mon-YYYY') as do_date,
  bill_sj_code AS sj_no,
  to_char(bill_sj_date, 'dd-Mon-YYYY') as sj_date,
  bill_po_no AS po_no,
  to_char(bill_po_date, 'dd-Mon-YYYY') as po_date,
  CASE
	WHEN bill_type_invoice=0 AND bill_cfm_wh_delivery_timestamp is null THEN 'Unconfirmed'
	WHEN bill_type_invoice=0 AND bill_cfm_wh_delivery_timestamp is not null THEN to_char(bill_cfm_wh_delivery_timestamp, 'dd/Mon/YY hh24:mi:ss')
	WHEN bill_type_invoice=1 THEN '-'
  END AS cfm_date,
  CASE
	WHEN bill_total_billing = 0 THEN 'Free'
    WHEN ".ZKP_SQL."_isBillingUsed(bill_code) = 't' and ".ZKP_SQL."_isIssetPayment(bill_code) = 'unpaid' THEN 'Unpaid'
	WHEN ".ZKP_SQL."_isBillingUsed(bill_code) = 't' and ".ZKP_SQL."_isIssetPayment(bill_code) = 'paid' THEN 'Paid'
	WHEN bill_remain_amount = bill_total_billing THEN 'Unpaid'
  	WHEN bill_remain_amount = 0 THEN 'Paid'
  	WHEN bill_remain_amount != bill_total_billing THEN 'Half Paid'
  END AS payment_status,
  CASE
	WHEN bill_code in ('') THEN false
	WHEN bill_type_invoice = 0 AND bill_cfm_wh_delivery_timestamp is null THEN true
	WHEN bill_total_billing = 0 THEN false
	WHEN ".ZKP_SQL."_isBillingUsed(bill_code) = 't' and ".ZKP_SQL."_isIssetPayment(bill_code) = 'unpaid' and bill_type_invoice = 0 AND to_number((current_date - bill_cfm_wh_delivery_timestamp)::text,'99999') / 30 <= 2 THEN false
	WHEN ".ZKP_SQL."_isBillingUsed(bill_code) = 't' and ".ZKP_SQL."_isIssetPayment(bill_code) = 'paid' and bill_type_invoice = 1 AND to_number((current_date - bill_inv_date)::text,'99999') / 30 <= 2 THEN true
	WHEN bill_remain_amount != bill_total_billing THEN true
	WHEN bill_remain_amount = 0 THEN true
	WHEN bill_type_invoice = 0 AND to_number((current_date - bill_cfm_wh_delivery_timestamp::date)::text,'99999') / 30 > 2 THEN true
	WHEN bill_type_invoice = 0 AND to_number((current_date - bill_cfm_wh_delivery_timestamp::date)::text,'99999') / 30 <= 2 THEN false
	WHEN bill_type_invoice = 1 AND to_number((current_date - bill_inv_date)::text,'99999') / 30 > 2 THEN true
	WHEN bill_type_invoice = 1 AND to_number((current_date - bill_inv_date)::text,'99999') / 30 <= 2 THEN false
 	ELSE false
  END AS lock_status,
  CASE
	WHEN bill_type_invoice = 0 THEN 'check_small.gif'
	WHEN bill_type_invoice = 1 THEN 'cross_small.gif'
  END AS type_invoice,
  bill_ordered_by
FROM
  ".ZKP_SQL."_tb_billing AS b");

$sqlQuery->whereCaluse = "bill_ship_to = '$_cus_code' AND bill_ordered_by=$_order_by";

//Search Option 1 : by bill_code
if(isset($_GET['searchBy']) && $_GET['searchBy'] == "bill_code") {
	$sqlQuery->setWhere(" AND %s ILIKE '%%%s%%'", array("bill_code" => "txtKeyword"), "AND");
	$strGet = $sqlQuery->getQueryString() . "searchBy=bill_code";
}

$strGet = "_cus_code=$_cus_code&_cus_name=$_cus_name";

$sqlQuery->setOrderBy("bill_inv_date DESC, bill_code");
if(isZKError($result =& query($sqlQuery->getSQL())))
	$M->goErrorPage($result,  "javascript:window.close();");

//Total Rows
$numRow = numQueryRows($result);

//Declare Paging
$oPage = new strPaging($sqlQuery->getSQL(), 40);
$oPage->strPrev       = "";
$oPage->strNext       = "";
$oPage->strPrevDiv    = "<";
$oPage->strNextDiv    = ">";
$oPage->strLast       = ">>";
$oPage->strFirst      = "<<";
$oPage->strCurrentNum = "<strong>[%s]</strong>";
$oPage->strGet = $strGet;

if(isZKError($result =& query($oPage->getListQuery())))
	$m->goErrorPage($result, "javascript:window.close();");
?>
<html>
<head>
<title>INVOICE LIST</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script language="text/javascript" type="text/javascript" src="../../_script/aden.js"></script>
<script language="javascript" type="text/javascript" src="../../_script/js_category.php"></script>
<script language="javascript" type="text/javascript">
<?php
// Print Javascript Code
echo "var inv = new Array();\n";

while ($rows =& fetchRow($result,0)) {
	printf("inv['%s']=['%s', %s, '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', %s];\n",
		addslashes($rows[0]),
		$rows[0], 	//bill code			0
		$rows[4], 	//book idx			1
		$rows[2], 	//bill date			2
		$rows[5], 	//faktur no			3
		$rows[6],	//DO no				4
		$rows[7],   //DO date			5
		$rows[8],	//SJ no				6
		$rows[9],	//SJ date			7
		$rows[10],	//PO no				8
		$rows[11],	//PO date			9
		$rows[3],	//type vat			10
		$rows[13],	//payment status	11
		$rows[16]	//ordered by 		12
	);
}
?>

function searchByKeyword() {
	var o = window.document.frmSrarchByKeyword;
	
	if(o.txtKeyword.value <=0 ) {
		alert("Please insert the keyword");
		o.txtKeyword.focus();
	} else {
		o.submit();
	}
}

function fillField(idx) {
	var f = window.opener.document.frmInsert;

	f._bill_code.value			= inv[idx][0];
	f._book_idx.value			= inv[idx][1];
	f._bill_inv_date.value		= inv[idx][2];
	f._bill_vat_inv_no.value	= inv[idx][3];

	f._do_no.value		= inv[idx][4];
	f._do_date.value	= inv[idx][5];
	f._sj_code.value	= inv[idx][6];
	f._sj_date.value	= inv[idx][7];
	f._po_no.value		= inv[idx][8];
	f._po_date.value	= inv[idx][9];

	if(f._access.value == 'ALL') {
		var cboOrd = inv[idx][12]-1;
		f.cboOrdBy[cboOrd].checked = true;
		f.cboOrdBy[0].disabled	= true;
		f.cboOrdBy[1].disabled	= true;
		window.opener.highlighter('order_by');
	}
	f._type_return.value	= f._type.value;
	f._type.disabled		= true;
	f._ordered_by.value		= inv[idx][12];

	if(inv[idx][10]=='vat') {
		f._is_vat.value			= '1';
		f._vat.value			= '10';
		f._btnVat[0].checked	= true;
	} else {
		f._is_vat.value			= '0';
		f._vat.value			= '0';
		f._btnVat[1].checked	= true;
	}
	f._btnVat[0].disabled = true;
	f._btnVat[1].disabled = true;

	if(inv[idx][11]=='Free' || inv[idx][11]=='Unpaid') {
		f._bill_paid.value				= '0';
		f._money_back.value				= '';
		f._is_bill_paid[1].checked		= true;
		f._is_money_back[0].disabled	= true;
		f._is_money_back[1].disabled	= true;
		f._is_money_back[0].checked		= false;
		f._is_money_back[1].checked		= false;
	} else {
		f._bill_paid.value				= '1';
		f._is_bill_paid[0].checked		= true;
	}
	f._is_bill_paid[0].disabled = true;
	f._is_bill_paid[1].disabled = true;

	window.close();
}
</script>
</head>
<body style="margin:8pt" onload="window.document.frmSrarchByKeyword.txtKeyword.focus();">
<table width="100%" class="table_box">
	<tr>
		<td>
			<strong>
			<font color="black">
			[<font color="blue"><?php echo strtoupper($currentDept) ?></font>] CURRENT INVOICE<br />
			<small>* Recorder invoice(s) for customer ship to : [<?php echo $_cus_code ?>] <?php echo $_cus_name ?></small>
			</font>
			</strong>
		</td>
		<td align="right"><a href="javascript:window.location.href='p_information_return.php'"><img src="../../_images/icon/info.gif" alt="Help solution ;)"></a></td>
	</tr>
</table><hr>
<table width="100%" class="table_box">
	<tr>
		<td align="right">
			<form name="frmSrarchByKeyword" method="get">
			<input type="hidden" name="_order_by" value="<?php echo $_order_by?>">
			<input type="hidden" name="_cus_code" value="<?php echo $_cus_code?>">
			<input type="hidden" name="_cus_name" value="<?php echo $_cus_name?>">
			Search : &nbsp;
			<select name="searchBy">
				<option value="bill_code">INVOICE NO</option>
				<option value="sj_code" <?php echo (isset($_GET['searchBy']) && $_GET['searchBy'] == "sj_code") ? "selected":""?>>SJ CODE</option>
			</select> &nbsp;
			<input type="text" name="txtKeyword" size="15" class="fmt" value="<?php echo isset($_GET['txtKeyword']) ? $_GET['txtKeyword'] : ""?>" accesskey="k">
			</form>
		</td>
		<th width="12%">
			<a href="javascript:searchByKeyword()"><img src="../../_images/icon/search_mini.gif" alt="Search"></a>&nbsp;
			<a href="javascript:document.location.href='p_list_invoice.php?_cus_code=<?php echo $_cus_code?>&_cus_name=<?php echo $_cus_name ?>'"><img src="../../_images/icon/list_mini.gif" alt="Show all invoices"></a>
		</th>
	</tr>
</table><br />
<table width="100%" class="table_box">
	<tr height="25px">
		<th width="3%">No</th>
		<th width="5%">Issue<br >Item</th>
		<th width="15%">Invoice No</th>
		<th width="10%">Invoice Date</th>
		<th width="15%">WH Confirm<br />Date</th>
		<th width="8%">STATUS</th>
		<th width="6%">VIEW</th>
	</tr>
</table>
<div style="height:430; overflow-y:scroll">
<?php if($numRow <= 0) { ?>
<br /><span class="comment"><i>(No recorder invoice)</i></span>
<?php } else { ?>
<table width="100%" class="table_c">
<?php
pg_result_seek($result, 0);
while ($column =& fetchRowAssoc($result)) {
?>
	  <tr>
		<td width="3%"><?php echo ++$oPage->serial ;?></td>
		<td width="10%" align="center"><img src="../../_images/icon/<?php echo $column['type_invoice']?>"></td>
		<td width="23%">
			<?php if($column['lock_status']=='t') { ?>
			<?php echo "\t\t\t{$column['bill_code']}\n" ?>
			<?php } else if($column['lock_status']=='f') {?>
			<a href="javascript:fillField('<?php echo addslashes(html_entity_decode($column['bill_code']))?>')"><b><?php echo $column['bill_code']?></b></a>
			<?php } ?>
		</td>
		<td width="18%"><?php echo $column['bill_date']?></td>
		<td width="23%"><?php echo $column['cfm_date']?></td>
		<td width="13%"><?php echo $column['payment_status'] ?></td>
		<td width="7%" align="center"><a href="p_detail_billing.php?_code=<?php echo $column['bill_code'] ?>&_cus_code=<?php echo $_cus_code ?>">view</a></td>
	  </tr>
<?php } ?>
</table>
<?php } ?>
</div>
<table width="100%" cellpadding="0" cellspacing="2" border="0">
	<tr>
		<td align="center"><?php echo $oPage->putPaging();?></td>
	</tr>
</table>
</body>
</html>