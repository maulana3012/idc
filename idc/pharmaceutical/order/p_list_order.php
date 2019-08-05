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

$_cus_code	 = trim($_GET['_cus_code']);
$_cus_name	 = $_GET['_cus_name'];
$strGet		 = "";

//DEFAULT PROCESS
$sqlQuery = new strSelect("
SELECT
  ord_code,
  ord_po_date,
  ord_po_no,
  CASE
  	WHEN ord_type = 'OO' THEN 'RO'
  	WHEN ord_type = 'OK' THEN 'RK'
  END AS ord_type,
  CASE
	WHEN ord_cfm_deli_timestamp is null THEN true
	WHEN ord_cfm_deli_timestamp is not null THEN false
  END AS lock_status
FROM
  ".ZKP_SQL."_tb_order");

$sqlQuery->whereCaluse = "ord_ship_to = '$_cus_code' AND ord_dept = '$department'";

//Search Option 1 : by ord_code
if(isset($_GET['searchBy']) && $_GET['searchBy'] == "ord_code") {
	$sqlQuery->setWhere(" AND %s ILIKE '%%%s%%'", array("ord_code" => "txtKeyword"), "AND");
	$strGet = $sqlQuery->getQueryString() . "searchBy=ord_code";
}

//Search Option 2 : by ord_po_date
if(isset($_GET['searchBy']) && $_GET['searchBy'] == "ord_po_date") {
	$sqlQuery->setWhere(" AND ord_po_date = DATE '{$_GET['txtKeyword']}' ", array("ord_po_date" => "txtKeyword"), "AND");
	$strGet = $sqlQuery->getQueryString(). "searchBy=ord_po_date";
}

$strGet = "_cus_code=" . $_cus_code;

$sqlQuery->setOrderBy("ord_po_date DESC, ord_code");
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
<title>ORDER LIST</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script language="text/javascript" type="text/javascript" src="../../_script/aden.js"></script>
<script language="javascript" type="text/javascript" src="../../_script/js_category.php"></script>
<script language="javascript" type="text/javascript">
<?php
// Print Javascript Code
echo "var ord = new Array();\n";

while ($rows =& fetchRow($result,0)) {
	printf("ord['%s']=['%s', '%s', '%s', '%s'];\n",
		addslashes($rows[0]),	//idx
		addslashes($rows[0]), 	//ord code
		$rows[1],				//po date
		$rows[2],				//po no
		$rows[3]				//type
	);
}
?>

function searchByKeyword() {
	var o = window.document.frmSrarchByKeyword;

	if(o.txtKeyword.value <=0 ) {
		alert("Please insert the Invoice or Order Date");
		o.txtKeyword.focus();
	} else {
		if(o.searchBy.value == 'ord_po_date') {
			var d = parseDate(o.txtKeyword.value, 'prefer_euro_format');
			if(d == null) {
				alert("You have to input correct date");
				o.txtKeyword.value = '';
				o.txtKeyword.focus();
				return;
			}
			o.txtKeyword.value = formatDate(d, 'd-NNN-yyyy');
		}
		o.submit();
	}
}

function fillField(idx) {

	var f = window.opener.document.frmInsert;
	
	var d = parseDate(ord[idx][1], 'prefer_euro_format');

	f._ord_code.value		= ord[idx][0];
	f._ord_date.value		= formatDate(d, "d-NNN-yyyy");
	f._po_no.value			= ord[idx][2];
	f._return_type.value	= ord[idx][3];
	f._type.value			= ord[idx][3];
	f._type.disabled		= true;
	window.close();
}
</script>
</head>
<body style="margin:8pt" onLoad="window.frmSrarchByKeyword.txtKeyword.focus()">
<!--START: BODY-->
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
			<input type="hidden" name="_cus_code" value="<?php echo $_cus_code?>">
			<input type="hidden" name="_cus_name" value="<?php echo $_cus_name?>">
			Search : &nbsp;
			<select name="searchBy">
				<option value="ord_code">INVOICE NO</option>
				<option value="ord_po_date" <?php echo (isset($_GET['searchBy']) && $_GET['searchBy'] == "ord_po_date") ? "selected":""?>>ORDER DATE</option>
			</select> &nbsp;
			<input type="text" name="txtKeyword" size="15" class="fmt" value="<?php echo isset($_GET['txtKeyword']) ? $_GET['txtKeyword'] : ""?>" accesskey="k" onKeyPress="if(window.event.keyCode == 13) searchByKeyword();"> &nbsp;
			</form>
		</td>
		<th width="12%">
			<a href="javascript:searchByKeyword()"><img src="../../_images/icon/search_mini.gif" alt="Search"></a>&nbsp;
			<a href="javascript:document.location.href='p_list_order.php?_cus_code=<?php echo $_cus_code?>&_cus_name=<?php echo $_cus_name ?>'"><img src="../../_images/icon/list_mini.gif" alt="Show all invoices"></a>
		</th>
	</tr>
</table><br />
<table width="100%" class="table_box">
	<tr height="35px">
		<th>No</th>
		<th>ORDER CODE</th>
		<th>ORDER DATE</th>
		<th>VIEW</th>
	</tr>
</table>
<div style="height:420; overflow-y:scroll">
<table width="100%" class="table_c">
<?php
pg_result_seek($result, 0);
while ($column =& fetchRowAssoc($result)) {
?>
	  <tr>
		<td><?php echo ++$oPage->serial ;?></td>
		<?php if($column['lock_status'] == 'f') { ?>
		<td><a href="javascript:fillField('<?php echo addslashes(html_entity_decode($column['ord_code']))?>')"><b><?php echo $column['ord_code']?></b></a></td>
		<?php } else { ?>
		<td style="color:#696969"><?php echo $column['ord_code']?></td>		
		<?php } ?>
		<td><?php echo date('j-M-Y', strtotime($column['ord_po_date']))?></td>
		<td><a href="p_detail_order.php?_code=<?php echo $column['ord_code']?>&_list=y">view</a></td>
	  </tr>
<?php } ?>
</table>
</div>
<table width="100%" cellpadding="0" cellspacing="2" border="0">
	<tr>
		<td align="center"><?php echo $oPage->putPaging();?></td>
	</tr>
</table>
<!--END: BODY-->
</body>
</html>