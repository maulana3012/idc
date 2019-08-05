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

//CHECK PARAMETER
$_code	= isset($_REQUEST['_code'])? $_REQUEST['_code'] : "";

//DEFAULT PROCESS
$sqlQuery = new strSelect("
SELECT
  po_code,		--0
  po_date,		--1
  sp_code,		--2
  po_sp_name,	--3
  po_sp_attn,	--4
  po_sp_phone,	--5
  po_sp_fax,	--6
  po_sp_address,	--7
  '1' AS partial
FROM
  ".ZKP_SQL."_tb_po_local");

if($_code != '') {
	$sqlQuery->whereCaluse = "po_confirmed_timestamp is not null and sp_code='$_code' AND po_ordered_by = ".$cboFilter[1][ZKP_URL][0][0];
} else {
	$sqlQuery->whereCaluse = "po_confirmed_timestamp is not null AND po_ordered_by = ".$cboFilter[1][ZKP_URL][0][0];
}
$sqlQuery->setOrderBy("po_code DESC");

if(isZKError($result =& query($sqlQuery->getSQL())))
	$M->goErrorPage($result,  "javascript:window.close();");

//Total Rows
$numRow = numQueryRows($result);

//Declare Paging
$strGet = "";
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
	printf("po['%s']=['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'];\n",
		addslashes($rows[0]), //code from query
		addslashes($rows[0]), //po code			0
		addslashes($rows[1]),	
		addslashes($rows[2]),
		addslashes($rows[3]),
		addslashes($rows[4]),
		addslashes($rows[5]),
		addslashes($rows[6]),
		addslashes($rows[7]),
		addslashes($rows[8])
	);
}
?>

function submitSearch() {
	window.location.href = 'p_list_po.php?_code=' + window.document.all.cboSupplier.value;
}

function fillField(idx) {
	window.opener.location.href = "input_pl.php?_code=" + po[idx][0];
	window.close();
}

function initPage() {
	setSelect(window.document.all.cboSupplier, "<?php echo $_code ?>");	
}
</script>
</head>
<body style="margin:8pt" onLoad="initPage()">
<!--START: BODY-->
<strong>
[<font color="blue"><?php echo strtoupper($currentDept) ?></font>] CURRENT PO LOCAL<br />
<small>* Only confirmed PO will be shown here</small>
</strong>
<hr>
<div align="right">
			Supplier :
<?php
$sql = "SELECT sp_code, sp_full_name FROM ".ZKP_SQL."_tb_supplier_local ORDER BY sp_full_name";
isZKError($res = & query($sql)) ? $M->printMessage($result):0;
	if(numQueryRows($res) <= 0) {
		$o = new ZKError("INFORMATION", "INFORMATION", "Please register the supllier name first");
		$M->printMessage($res);
	} else {
		print "\t\t\t<select name=\"cboSupplier\" class=\"fmt\" onchange=\"submitSearch()\">\n";
		print "\t\t\t\t<option value=\"\">==SELECT==</option>\n";
	
		while ($col = fetchRow($res)) {
			print "\t\t\t\t<option value=\"".$col[0]."\">".ucfirst($col[1])."</option>\n";
		}
		print "\t\t\t</select>\n";
	}
?>
</div><br />
<table width="100%" class="table_box">
	<tr>
		<th width="5%">No</th>
		<th width="30%">PO NO</th>
		<th width="20%">PO DATE</th>
		<th>SUPPLIER</th>
	</tr>
</table>
<div style="height:450; overflow-y:scroll">
<?php
if($numRow <= 0) { echo "<br />\t\t\t<span class=\"comment\"><i>(No recorded PO for this supplier)</i></span>";
} else {
?>
<table width="100%" class="table_c">
<?php
pg_result_seek($result, 0);
while ($column =& fetchRowAssoc($result)) {
?>
	  <tr>
		<td width="7%" align="center"><?php echo ++$oPage->serial ;?></td>
		<td width="30%"><a href="javascript:fillField('<?php echo $column['po_code']?>')"><b><?php echo $column['po_code']?></b></a></td>
		<td width="20%"><?php echo date('j-M-Y', strtotime($column['po_date']))?></td>
		<td><?php echo $column['po_sp_name'] ?></td>
	  </tr>
<?php }} ?>
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