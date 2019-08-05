<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at zonekom.com
*
* @generated : 22-May, 2007 12:33:22
* @author    : daesung kim
*/
//REQUIRE
require_once "../../zk_config.php";
require_once LIB_DIR . "zk_dbconn.php";
require_once LIB_DIR . "zk_listing.php";

//PAGE ACCESS PERMISSION
require_once "./_access_local.php";
ckperm(ZKP_SELECT, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//GLOBALS
$left_loc = "list_cus_group.php";

//DEFAULT PROCESS
$sqlQuery = new strSelect("
SELECT
cug_code,
cug_name,
to_char(cug_regtime, 'DD-Mon-YYYY') as cug_regtime,
cug_remark,
cug_basic_disc_pct
FROM ".ZKP_SQL."_tb_customer_group");

$sqlQuery->setWhere("%s ILIKE '%%%s%%'", array("cug_name" => "_name"), "AND");

if(isset($_GET['p_mode']) && $_GET['p_mode'] == 'search') {
	$strGet = $sqlQuery->getQueryString() . "p_mode=" . $_GET['p_mode'];
} else {
	$strGet = $sqlQuery->getQueryString();
}


$sqlQuery->setOrderBy("cug_code");

if(isZKError($result =& query($sqlQuery->getSQL())))
	$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//Total Rows
$numRow = numQueryRows($result);

//Declare Paging
$oPage = new strPaging($sqlQuery->getSQL(), 15);
$oPage->strPrev       = "";
$oPage->strNext       = "";
$oPage->strPrevDiv    = "";
$oPage->strNextDiv    = "";
$oPage->strLast       = ">>";
$oPage->strFirst      = "<<";
$oPage->strCurrentNum = "<strong>[%s]</strong>";
$oPage->strGet = $strGet;

if(isZKError($result = & query($oPage->getListQuery())))
	$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/index.php");
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
<form method="get" name="frmSearch">
<input type="hidden" name="p_mode" value="search">
<table width="100%" class="table_no_02">
	<tr>
		<td width="58%">
			<strong class="title">[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] CUSTOMER GROUP LIST</strong>
			<p>TOTAL : <?php echo number_format((double)$numRow)?> Records</p></td>
		<td width="30%">
			<table width="100%" class="table_aa">
				<tr>
					<th rowspan="2">SEARCH<br />BY</th>
					<th>NAME</th>
					<th rowspan="2">
						<a href="javascript:searchRow(window.document.frmSearch)"><img src="../../_images/icon/search_mini.gif" alt="Search"></a> &nbsp; &nbsp;
						<a href="javascript:document.location.href='list_cus_group.php'"><img src="../../_images/icon/list_mini.gif" alt="Show all customer"></a>
					</th>
				</tr>
				<tr>
					<td align="center"><input type="text" name="_name" class="fmt" value="<?php echo isset($_GET['_name']) ? $_GET['_name']:""?>" onKeyPress="if(window.event.keyCode == 13) searchRow(window.document.frmSearch);"></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
</form>
<table width="100%" class="table_c">
	<tr>
		<th width="5%">No</th>
		<th width="5%">CODE</th>
		<th>NAME</th>
		<th width="12%">BASIC DISC %</th>
		<th width="14%">REGTIME</th>
		<th width="11%">GROUP LIST </th>
	</tr>
<?php while ($columns = fetchRowAssoc($result)) { ?>
	<tr>
		<td><?php echo ++$oPage->serial ;?></td>
		<td><a href="detail_cus_group.php?_code=<?php echo urlencode(html_entity_decode($columns['cug_code']))?>"><?php echo $columns['cug_code'];?></a></td>
		<td><?php echo $columns['cug_name'];?></td>
		<td align="right"><?php echo $columns['cug_basic_disc_pct'];?></td>
		<td align="center"><?php echo $columns['cug_regtime'];?></td>
		<td align="center"><a href="javascript:openWindow('p_list_in_group.php?_code=<?php echo urlencode(html_entity_decode($columns['cug_code']))?>&_name=<?php echo urlencode(html_entity_decode($columns['cug_name']))?>', 470, 600)">view</a></td>
	</tr>
<?php } ?>
</table>
<table width="100%" cellpadding="0" cellspacing="2" border="0">
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