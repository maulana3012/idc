<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at zonekom.com
*
* @generated : 25-May, 2007 16:16:33
* @author    : daesung kim
*/
require_once "../../zk_config.php";
require_once LIB_DIR . "zk_dbconn.php";
require_once LIB_DIR . "zk_listing.php";

//CHECK PARAMETER
$txtSearch = isset($_REQUEST['txtSearch']) ? $_REQUEST['txtSearch'] : "";
$cboSearch = isset($_REQUEST['cboSearch']) ? $_REQUEST['cboSearch'] : "";

$sqlQuery = new strSelect("SELECT * FROM ".ZKP_SQL."_tb_event");
$sqlQuery->setOrderBy("ev_tanggal_acara");

//Search Option 1 : by Name
if(isset($_GET['cboSearch']) && $_GET['cboSearch'] == "by_name") {
	$sqlQuery->setWhere("%s ILIKE '%%%s%%'", array("ev_nama_acara" => "txtSearch"), "AND");
	$strGet = $sqlQuery->getQueryString(). "cboSearch=by_name";
}

//Search Option 2 : by Date
if(isset($_GET['cboSearch']) && $_GET['cboSearch'] == "by_date") {
	$sqlQuery->whereCaluse = "ev_tanggal_acara = '$txtSearch'";
	$strGet = $sqlQuery->getQueryString(). "cboSearch=by_date";
}

if(isZKError($result =& query($sqlQuery->getSQL())))
	$M->goErrorPage($result,  "javascript:window.close();");

//Total Rows
$numRow = numQueryRows($result);

//Declare Paging
$oPage = new strPaging($sqlQuery->getSQL(), 75);
$oPage->strPrev       = "";
$oPage->strNext       = "";
$oPage->strPrevDiv    = "<";
$oPage->strNextDiv    = ">";
$oPage->strLast       = ">>";
$oPage->strFirst      = "<<";
$oPage->strCurrentNum = "<strong>[%s]</strong>";
$oPage->strGet = $sqlQuery->getQueryString();

if(isZKError($result =& query($oPage->getListQuery())))
	$M->goErrorPage($result, "javascript:window.close();");
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<title>CUSTOMER CODE LIST</title>
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script language="text/javascript" type="text/javascript" src="../../_script/aden.js"></script>
<script language="javascript" type="text/javascript">
<?php
// Print Javascript Code
echo "var event = new Array();\n";
while ($rows =& fetchRow($result,0)) {
	printf("event['%s']=['%s','%s','%s','%s'];\n", $rows[0], $rows[0], $rows[1], $rows[2], $rows[3]);
}
?>

function searchRow(frmObj) {
	var isValid = false;

	if(frmObj.txtSearch.value.length > 0) {
		if(frmObj.cboSearch.value == 'by_date') {
			var d = parseDate(frmObj.txtSearch.value, 'prefer_euro_format');
			if (d == null) {
				alert("You must be input date with proper format");
				frmObj.txtSearch.value = '';
				frmObj.txtSearch.focus();
				return;
			} else {
				frmObj.txtSearch.value = formatDate(d, "d-NNN-yyyy");
				isValid = true;
			}
		} else {
			isValid = true;
		}
	}

	if (isValid) {
		frmObj.submit();
	} else {
		alert("Please Input keyword for search data");
		frmObj.txtSearch.focus();
	}
}

function fillField(idx) {
	var f = window.opener.document.frmSearch;
	f._kode_acara.value		= event[idx][0];
	f._nama_acara.value		= event[idx][1];
	f.chkAcara.checked 		= true;

	window.opener.document.frmSearch.submit();
	window.close();
}

function initPage() {
	window.frmSearch.txtSearch.focus();
	setSelect(window.document.frmSearch.cboSearch, "<?php echo $cboSearch ?>")
}
</script>
</head>
<body style="margin:8pt" onLoad="initPage()">
<form method="get" name="frmSearch">
<input type="hidden" name="p_mode" value="search">
<table width="100%" class="table_box">
	<tr>
		<td rowspan="2" width="70%"><h4>EVENT LIST</h4></td>
		<td align="center">
			<select name="cboSearch">
            	<option value="by_name">Event Name</option>
            	<option value="by_date">Event Date</option>
			</select>
		</td>
		<th rowspan="2" width="15%">
			<a href="javascript:searchRow(window.document.frmSearch)"><img src="../../_images/icon/search_mini.gif" alt="search"></a>
			<a href="javascript:document.location.href='p_list_cus_code.php'"><img src="../../_images/icon/list_mini.gif" alt="see all"></a>
		</th>
	</tr>
	<tr>
		<td align="center"><input type="text" name="txtSearch" style="width:100%" class="fmt" value="<?php echo $txtSearch?>" onKeyPress="if(window.event.keyCode == 13) searchRow(window.document.frmSearch)"></td>
	</tr>
</table><br />
</form>
<table width="100%" class="table_box">
	<tr height="30px">
		<th width="8%">No</th>
		<th>EVENT NAME</th>
		<th width="30%">DATE</th>
	</tr>
</table>
<div style="height:440; overflow-y:scroll">
<table width="100%" class="table_box">
<?php
pg_result_seek($result, 0);
while ($column =& fetchRowAssoc($result)) {
?>
	<tr>
		<td width="8%"><?php echo ++$oPage->serial ;?></td>
		<td><a href="javascript:fillField('<?php echo addslashes(html_entity_decode($column['ev_idx']))?>')"><?php echo $column['ev_nama_acara']?></a></td>
		<td width="20%"><?php echo date('d-M-y', strtotime($column['ev_tanggal_acara'])) ?></td>
	</tr>
<?php } ?>
</table>
</div><br />
<table width="100%" cellpadding="0" cellspacing="2" border="0">
	<tr>
		<td align="center"><?php echo $oPage->putPaging();?></td>
	</tr>
</table>
</body>
</html>