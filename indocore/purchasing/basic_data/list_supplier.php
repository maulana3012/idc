<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at indosat.net.id
*
* @author : daesung kim
*
* $Id: list_supplier.php,v 1.1 2008/04/28 06:52:13 neki Exp $
*/
//REQUIRE
require_once "../../zk_config.php";
require_once LIB_DIR . "zk_dbconn.php";
require_once LIB_DIR . "zk_listing.php";

//PAGE ACCESS PERMISSION
require_once "./_access_local.php";
ckperm(ZKP_INSERT, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//GLOBAL
$left_loc = "list_supplier.php";
$_name	  = (isset($_GET['_name'])) ? $_GET['_name'] : '';	

//DEFAULT PROCESS
$sqlQuery = new strSelect("SELECT sp_code, sp_name, substr(sp_address,0,70) AS sp_address, sp_phone, sp_fax FROM ".ZKP_SQL."_tb_supplier");
$sqlQuery->setWhere("%s ILIKE '%s%%'", array("sp_name" => "_name"), "AND");
$sqlQuery->setOrderBy("sp_name");

if(isZKError($result = query($sqlQuery->getSQL()))) $M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//Total Rows
$numRow = numQueryRows($result);

//Declare Paging
$oPage = new strPaging($sqlQuery->getSQL(), 20);
$oPage->strPrev       = "";
$oPage->strNext       = "";
$oPage->strPrevDiv    = "";
$oPage->strNextDiv    = "";
$oPage->strLast       = "&gt;&gt;";
$oPage->strFirst      = "&lt;&lt;";
$oPage->strCurrentNum = "&lt;strong&gt;[%s]&lt;/strong&gt;";
$oPage->strGet = $sqlQuery->getQueryString();

if(isZKError($result = & query($oPage->getListQuery()))) {
	$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/index.php");
}
?>
<html>
<head>
<title>PT. INDOCORE PERKASA [APOTIK MANAGEMENT SYSTEM]</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
<script language="javascript" type="text/javascript">
function searchRow(frmObj) {
	var isValid = false;
	for(i=0; i<frmObj.elements.length; ++i) {
		if(frmObj.elements[i].tagName == "INPUT" && frmObj.elements[i].value.length > 0) {
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
<body topmargin="0" leftmargin="0" onload="window.document.frmSearch._name.focus();">
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
<form method="get" name="frmSearch">
<table width="100%" class="table_no_02">
	<tr>
		<td width="70%"><h4>[<font color="blue"><?php echo strtoupper($currentDept) ?></font>] SUPPLIER LIST</h4></td>
		<td>
			<table width="100%" class="table_aa">
				<tr>
					<th rowspan="2">SEARCH<br />BY</th>
					<th width="50%">NAME</th>
					<th rowspan="2">
						<a href="javascript:searchRow(window.document.frmSearch)"><img src="../../_images/icon/search_mini.gif" alt="Search"></a> &nbsp;
						<a href="javascript:document.location.href='list_supplier.php'"><img src="../../_images/icon/list_mini.gif" alt="Show all supplier"></a>
					</th>
				</tr>
				<tr>
					<td align="center"><input type="text" name="_name" style="width:100%" class="fmt" value="<?php echo $_name ?>"></td>
				</tr>
			</table>
		</td>
	</tr>
</table>

</form>
TOTAL : <?php echo number_format($numRow)?> Records
<table width="100%" class="table_c">
	<tr>
		<th width="5%">No</th>
		<th width="5%">CODE</th>
		<th width="18%">NAME</th>
		<th width="12%">PHONE</th>
		<th width="12%">FAX</th>
		<th width="48%">ADDRESS</th>
	</tr>
<?php
while ($column = fetchRowAssoc($result)) {
?>
	<tr>
		<td align="center"><?php echo ++$oPage->serial ;?></td>
		<td><?php echo $column['sp_code'];?></td>
		<td>
			<a href="detail_supplier.php?_code=<?php echo $column['sp_code']?>" style="color:darkblue">
				<?php echo $column['sp_name'];?>
			</a>
		</td>
		<td><?php echo $column['sp_phone'];?></td>
		<td><?php echo $column['sp_fax'];?></td>
		<td><?php echo $column['sp_address'];?></td>
	</tr>
<?php
}//end repeat rows
?>
</table>
<table width="100%" class="table_no_02">
	<tr>
		<td align="center"><?php echo $oPage->putPaging();?></td>
	</tr>
</table>
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