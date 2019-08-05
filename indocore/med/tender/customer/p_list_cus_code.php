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

//PAGE ACCESS PERMISSION
require_once "./_access_local.php";
ckperm(ZKP_INSERT, HTTP_DIR . "javascript:window.close();");

//CHECK PARAMETER
$_check_code = isset($_REQUEST['_check_code']) ? $_REQUEST['_check_code'] : "";

$sqlQuery = new strSelect("SELECT cus_code, cus_name, cus_full_name FROM ".ZKP_SQL."_tb_customer");
$sqlQuery->setWhere("%s ILIKE '%s%%'", array("cus_code" => "_check_code"), "AND");
$sqlQuery->setOrderBy("cus_code");

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
function searchRow(frmObj) {
	var isValid = false;
	for(i=0; i<frmObj.elements.length; ++i) {
		if(frmObj.elements[i].type == "text" && frmObj.elements[i].value.length > 0) {
			isValid = true;
		}
	}
	
	if (isValid) {
		frmObj.submit();
	} else {
		alert("Please Input keyword for search data");
	}
}
</script>
</head>
<body style="margin:8pt">
<!--START: html_body.tpl-->
<form method="get" name="frmSearch">
<input type="hidden" name="p_mode" value="search">
<table width="100%" class="table_box">
	<tr>
		<td width="50%"><h4>CUSTOMER CODE</h4></td>
		<td align="center"><input type="text" name="_check_code" style="width:100%" class="fmt" value="<?php echo $_check_code?>"></td>
		<th width="15%">
			<a href="javascript:searchRow(window.document.frmSearch)"><img src="../../_images/icon/search_mini.gif" alt="search"></a>
			<a href="javascript:document.location.href='p_list_cus_code.php?_check_code=<?php echo $_check_code ?>'"><img src="../../_images/icon/list_mini.gif" alt="see all"></a>
		</th>
	</tr>
</table><br />
</form>
<!--END: SEARCH-->
<!--START: LIST-->
<table width="100%" class="table_box">
	<tr height="25px">
		<th width="5%">No</th>
		<th width="25%">CODE</th>
	    <th>NAME</th>
	</tr>
</table>
<div style="height:460; overflow-y:scroll">
<table width="100%" class="table_box">
<?php while ($column =& fetchRowAssoc($result)) { ?>
	<tr>
		<td><?php echo ++$oPage->serial ;?></td>
		<td><b><?php echo $column['cus_code']?></b></td>
		<td><?php echo $column['cus_full_name']?></td>
	</tr>
<?php } ?>
</table>
</div><br />
<table width="100%" cellpadding="0" cellspacing="2" border="0">
  <tr>
    <td align="center"><?php echo $oPage->putPaging();?></td>
  </tr>
</table>
<!--END: html_body.tpl-->
</body>
</html>