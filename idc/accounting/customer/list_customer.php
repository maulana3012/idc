<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at zonekom.com
*
* @generated : 18-May, 2007 15:20:22
* @author    : daesung kim
*/

//REQUIRE
require_once "../../zk_config.php";
require_once LIB_DIR . "zk_dbconn.php";
require_once LIB_DIR . "zk_listing.php";

//PAGE ACCESS PERMISSION
require_once "./_access_local.php";
ckperm(ZKP_SELECT, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//GLOBAL
$_channel	= isset($_REQUEST['_channel'])? $_REQUEST['_channel'] : $_REQUEST['_channel'] = $cus_channel[$department];
$left_loc	= "list_customer.php?_channel=" . $_channel;
$cboSearchType 	= isset($_GET['cboSearchType'])? $_GET['cboSearchType'] : "";
$txtSearch 		= isset($_GET['txtSearch'])? $_GET['txtSearch'] : "";

$page_title["000"] = "Medical Dealer";
$page_title["001"] = "Medicine Dist";
$page_title["002"] = "Pharmacy Chain";
$page_title["003"] = "Gen/ Specialty";
$page_title["004"] = "Pharmaceutical";
$page_title["005"] = "Hospital";
$page_title["6.1"] = "M/L Marketing";
$page_title["6.2"] = "Mail Order";
$page_title["6.3"] = "Internet Business";
$page_title["007"] = "Promotion&Other";
$page_title["008"] = "Individual";
$page_title["009"] = "Private use";
$page_title["00S"] = "Service";

//DEFAULT PROCESS
$sqlQuery = new strSelect("
SELECT
cus_code,
cus_full_name,
cus_tax_code_status,
cus_type_of_biz
FROM ".ZKP_SQL."_tb_customer");

$tmp = array();
$tmp[] = "cus_channel = '$_channel'";
if($_marketing != '') $tmp[] = "cus_responsibility_to = $_marketing";
if($cboSearchType != '' && $txtSearch != '') {
	$type = array("byCode"=>"cus_code", "byName"=>"cus_name", "byCity"=>"cus_city");
	$tmp[] = $type[$cboSearchType] . " ILIKE '%$txtSearch%'";
}
$strWhere = implode(" AND ", $tmp);
//echo $strWhere;exit;
$sqlQuery->whereCaluse = $strWhere;
$sqlQuery->setOrderBy("cus_code");

if(isZKError($result =& query($sqlQuery->getSQL()))) {
	$M->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/index.php");
}

$findStrGet = str_replace('curpage=','',getQueryString());
$strGet		= $findStrGet;

//Total Rows
$numRow = numQueryRows($result);

//Declare Paging
$oPage = new strPaging($sqlQuery->getSQL(), 22);
$oPage->strPrev       = "";
$oPage->strNext       = "";
$oPage->strPrevDiv    = "<";
$oPage->strNextDiv    = ">";
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
function initPage() {
	setSelect(window.document.frmSearch.cboSearchType, "<?php echo $cboSearchType ?>");
}
</script>
</head>
<body topmargin="0" leftmargin="0" onLoad="initPage()">
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
<!--START: SEARCH-->
<form method="GET" name="frmSearch">
<input type="hidden" name="_channel" value="<?php echo $_channel?>">
<input type="hidden" name="p_mode" value="search">
<table width="100%" class="table_no_02">
	<tr>
		<td width="30%">
			<h4>CUSTOMER: <span style="color:#6633FF"><?php echo $page_title[$_channel]?></span></h4>
			<p>TOTAL : <?php echo number_format((double)$numRow)?> Records</p>
		</td>
		<td width="57%">
			<table width="100%">
				<tr>
					<td width="80%"> </td>
					<td>SEARCH BY</td>
				</tr>
				<tr>
					<td></td>
					<td>
						<select name="cboSearchType">
							<option value="byCode">CODE</option>
							<option value="byName">CUSTOMER NAME</option>
							<option value="byCity">CITY</option>
						</select> &nbsp; 
						<input type="text" name="txtSearch" size="30" class="fmt" value="<?php echo $txtSearch ?>">
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
</form>
<!--END: SEARCH-->
<table width="80%" class="table_c">
	<tr>
		<th width="5%">No</th>
		<th width="5%">CODE</th>
		<th>FULL NAME</th>
		<th width="25%">NPWP</th>
  </tr>
<?php while ($cus =& fetchRowAssoc($result)) { ?>
	<tr>
		<td><?php echo ++$oPage->serial ;?></td>
        <td><a href="javascript:openWindow('p_detail_customer.php?_code=<?php echo urlencode(html_entity_decode($cus['cus_code']))?>', 470, 200)"><?php echo $cus['cus_code'];?></a></td>
		<td><?php echo cut_string($cus['cus_full_name'], 35);?></td>
		<td><?php echo $cus['cus_tax_code_status'];?> - <?php echo $cus['cus_type_of_biz'];?></td>
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