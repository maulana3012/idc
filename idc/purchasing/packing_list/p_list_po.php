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
if(!isset($_GET['_code']) || $_GET['_code'] == "")
	die("<script language=\"javascript1.2\">window.close();</script>");

$_sp_code	 = trim($_GET['_code']);
$strGet		 = "";

//DEFAULT PROCESS
$sqlQuery = new strSelect("
SELECT
  po_code,		--0
  po_date,		--1
  CASE
  	WHEN po_type = 1 THEN 'NORMAL'
  	WHEN po_type = 2 THEN 'DOOR TO DOOR'
  END AS shipment_type,	--2
  po_shipment_mode,		--3
  po_layout_type,		--4
  po_type_invoice
FROM
  ".ZKP_SQL."_tb_po");

$sqlQuery->whereCaluse = "po_sp_code = '$_sp_code' AND po_confirmed_by_account != ''";

$strGet = "_code=" . $_sp_code;

$sqlQuery->setOrderBy("po_date DESC, po_code");
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
<title>PO LIST</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script language="text/javascript" type="text/javascript" src="../../_script/aden.js"></script>
<script language="javascript" type="text/javascript" src="../../_script/js_category.php"></script>
<script language="javascript" type="text/javascript">
<?php
// Print Javascript Code
echo "var po = new Array();\n";

while ($rows =& fetchRow($result,0)) {
	printf("po['%s']=['%s', '%s', '%s', '%s', '%s'];\n",
		addslashes($rows[0]), //code from query
		addslashes($rows[0]), //po code		0
		addslashes($rows[1]), //po date		1
		addslashes($rows[2]), //shipment type		2
		addslashes($rows[3]), //shipment mode		3
		addslashes($rows[4])  //layout type			4
	);
}
?>

function searchByKeyword() {
	var o = window.document.frmSrarchByKeyword;
	
	if(o.txtKeyword.value <=0 ) {
		alert("Please insert the invoice number or SJ number");
		o.txtKeyword.focus();
	} else {
		o.submit();
	}
}

function fillField(idx) {
	var f = window.opener.document.frmInsert;

	var d1 = parseDate(po[idx][1], 'prefer_euro_format');

	f._po_code.value	= po[idx][0];
	f._po_date.value	= formatDate(d1, "d-NNN-yyyy");
	f._layout_type.value = po[idx][4];

	if(po[idx][2] == 'NORMAL') {
		f._type[0].checked = true;
		f._pl_type.value 	= '1';
	} else {
		f._type[1].checked = true;
		f._pl_type.value 	= '2';
	}

	if(po[idx][3] == 'sea') {
		f._shipment_mode[0].checked = true;
	} else if(po[idx][3] == 'air') {
		f._shipment_mode[1].checked = true;
	} else if(po[idx][3] == 'other') {
		f._shipment_mode[2].checked = true;
	}

	f._type[0].disabled = true;
	f._type[1].disabled = true;
	window.close();
}
</script>
</head>
<body style="margin:8pt">
<!--START: BODY-->
<strong>
[<font color="blue"><?php echo strtoupper($currentDept) ?></font>] CURRENT SUPPLIER PO<br />
<small>* Only confirmed PO will be shown here</small>
</strong>
<hr>
<table width="100%" class="table_box">
	<tr>
		<th width="10%">No</th>
		<th width="18%">PO NO</th>
		<th width="20%">PO DATE</th>
		<th>TYPE</th>
		<th width="15%">SHIP BY</th>
		<th width="10%">VIEW</th>
	</tr>
</table>
<div style="height:470; overflow-y:scroll">
<table width="100%" class="table_c">
<?php
pg_result_seek($result, 0);
while ($column =& fetchRowAssoc($result)) {
?>
	  <tr>
		<td width="10%" align="center"><?php echo ++$oPage->serial ;?></td>
		<td width="18%" align="center">
			<?php if($column['po_type_invoice'] == 1)  { ?>
			<a href="javascript:fillField('<?php echo $column['po_code']?>')"><b><?php echo $column['po_code']?></b></a>
			<?php } else { ?>
			<b><?php echo $column['po_code']?></b>
			<?php } ?>
		</td>
		<td width="22%" align="center"><?php echo date('j-M-Y', strtotime($column['po_date']))?></td>
		<td><?php echo $column['shipment_type'] ?></td>
		<td width="15%"><?php echo strtoupper($column['po_shipment_mode']) ?></td>
		<td width="10%" align="center"><a href="p_detail_po.php?_code=<?php echo $column['po_code'] ?>&_sp_code=<?php echo $_sp_code ?>">view</a></td>
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