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
ckperm(ZKP_SELECT, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//GLOBAL
$left_loc = "list_complain.php";
$period_from = isset($_GET['period_from'])? $_GET['period_from'] : date("j-M-Y", time() - 2592000);
$period_to = isset($_GET['period_to'])? $_GET['period_to'] : date("j-M-Y", time());
$_category = isset($_GET['cboCategory'])? $_GET['cboCategory'] : "all";

//DEFAULT PROCESS
$sqlQuery = new strSelect("SELECT * FROM ".ZKP_SQL."_tb_customer_complain");

$strGet = "";
//Search Option 1 : by Category
if(isset($_GET['cboCategory']) && $_GET['cboCategory'] != 'all') {
	$tmp[] = "cp_category = '$_category'";
	$strGet .= "&cboCategory=$_category";
}
$tmp[] = "cp_date BETWEEN DATE '$period_from' AND DATE '$period_to'";
$strGet .= "&period_from=$period_from&period_to=$period_to";
$strWhere = implode(" AND ", $tmp);
$sqlQuery->whereCaluse = $strWhere;
$sqlQuery->setOrderBy("cp_date");
if(isZKError($result =& query($sqlQuery->getSQL())))
	$M->goErrorPage($result,  HTTP_DIR . "$currentDept/$moduleDept/index.php");

//Total Rows
$numRow = numQueryRows($result);

//Declare Paging
$oPage = new strPaging($sqlQuery->getSQL(), 50);
$oPage->strPrev       = "";
$oPage->strNext       = "";
$oPage->strPrevDiv    = "<";
$oPage->strNextDiv    = ">";
$oPage->strLast       = ">>";
$oPage->strFirst      = "<<";
$oPage->strCurrentNum = "<strong>[%s]</strong>";
$oPage->strGet = $strGet;

if(isZKError($result =& query($oPage->getListQuery())))
	$m->goErrorPage($result, HTTP_DIR . "$currentDept/$moduleDept/index.php");
?>
<html>
<head>
<title><?php echo $cboFilter[0][ZKP_FUNCTION][0][1] ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
<script language="text/javascript" type="text/javascript" src="../../_script/js_category.php"></script>
<script language='text/javascript' type='text/javascript'>
function checkform(o) {
	if (verify(o)) {
		o.submit();
	}
}

function initPage() {
	
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
<strong class="title">[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] CUSTOMER COMPLAIN LIST</strong>
<table width="100%" class="table_box">
	<tr height="30px" valign="bottom">
		<td style="color:#000000"></td>
		<td align="right" colspan="2">
			<form name="frmSearch" method="GET">
			  Category:
			  <select name="cboCategory">
				<option value="all">==ALL==</option>
				<option value="product">PRODUCT</option>
				<option value="delivery">DELIVERY</option>
				<option value="others">OTHERS</option>
			  </select> &nbsp; &nbsp; 
              Period: 
			  From <input type="text" name="period_from" size="10" class="fmtd" value="<?php echo $period_from; ?>">&nbsp;
			  To <input type="text" name="period_to" size="10" class="fmtd"  value="<?php echo $period_to; ?>">
			</form>
		</td>
		<th width="4%">
			<a href="javascript:document.location.href='list_complain.php'"><img src="../../_images/icon/list_mini.gif" alt="Show all item"></a>&nbsp;
		</th>
	</tr>
</table><br />
<table width="100%" class="table_c">
	<tr>
    	<th width="2%">NO</th>
		<th width="8%">TANGGAL</th>
		<th width="12%">CUSTOMER</th>
		<th width="20%">COMPLAIN</th>
		<th width="20%">ACTION</th>
		<th width="20%">REMARK</th>
	</tr>
	<?php while ($column =& fetchRowAssoc($result)) { ?>
	<tr>
		<td><?php echo ++$oPage->serial ;?></td>
		<td align="center"><a href="detail_complain.php?_code=<?php echo $column['cp_idx'] ?>"><?php echo date('d-M-Y', strtotime($column['cp_date']))?></a></td>
		<td><?php echo $column['cp_customer']?></td>
		<td><?php echo cut_string($column['cp_complain_desc'], 29)?></td>
		<td><?php echo cut_string($column['cp_complain_completion'], 32)?></td>
		<td><?php echo cut_string($column['cp_remark'], 33)?></td>
	</tr>
	<?php } ?>
</table>
<table width="100%" cellpadding="0" cellspacing="2" border="0">
	<tr>
		<td align="center"><?php echo $oPage->putPaging();?></td>
	</tr>
</table>
<script language="javascript1.2" type="text/javascript">
	var f = window.document.frmSearch;
	var ts = <?php echo time() * 1000;?>;

	setSelect(f.cboCategory, "<?php echo isset($_GET['cboCategory']) ? $_GET['cboCategory'] : "all"?>");

	f.cboCategory.onchange = function() {
		f.submit();
	}

	f.period_from.onkeypress = function() {
		if(window.event.keyCode == 13 && validPeriod(f.period_from, f.period_to)) {
			f.submit();
		}
	}

	f.period_to.onkeypress = function() {
		if(window.event.keyCode == 13 && validPeriod(f.period_from, f.period_to)) {
			f.submit();
		}
	}
</script>
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