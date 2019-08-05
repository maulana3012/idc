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
ckperm(ZKP_SELECT, HTTP_DIR ."apotik/price_policy/index.php");

//GLOBAL
$left_loc	= "search_apotik_price_by_item.php";

$_date_from	= isset($_GET['_date_from']) ? $_GET['_date_from'] : date("Y-m-d",mktime(0,0,0,date("m"),1,date("Y")-2419200));
$_date_to	= isset($_GET['_date_to']) ? $_GET['_date_to'] : date("Y-m-d",mktime(0,0,0,date("m"),1,date("Y"))-86400);
$_item		= isset($_GET['_item']) ? $_GET['_item'] : "all";
$_customer	= isset($_GET['_customer']) ? $_GET['_customer'] : "all";

//make Where for Category
if ($_item == "by_category") {
	//get sub category, if under category
	isZKError($catList = executeSP(ZKP_SQL."_getSubCategory", $_GET['_lastCategoryNo'])) ? $M->printMessage($catList) : true;
	$sql_item = " AND icat_midx IN (" . (empty($catList[0]) ? "0" : $catList[0]) . ")";

	//get category path from current icat_midx.
	if(isZKError($path = executeSP(ZKP_SQL."_getCategoryPath", $_GET['_lastCategoryNo']))) {
		$M->PrintMessage($path);
	} else {
		eval(html_entity_decode($path[0]));	
		$path = array_slice(array_reverse($path),1);
		$itemCategory = array();
		foreach($path as $val) {
			$itemCategory[] = $val[4];
		}
	}
}
?>
<html>
<head>
<title><?php echo $cboFilter[0][ZKP_FUNCTION][0][1] ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
<script language='text/javascript' type='text/javascript'>
function checkform(o) {
	if (verify(o)) {
		o.submit();
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
			<h4>APOTIK PRICE</h4>
<?php
if ($_customer == 'all') {
		$sql  = "SELECT * FROM vw_apotik_price_by_item ";
		$sql .= "WHERE (ait_from, ait_to) OVERLAPS (DATE '$_date_from', DATE '$_date_to') ";
		$sql .= ($_item == "all") ? "" : $sql_item;

		//Declare Paging
		$oPage = new strPaging($sql, 60);
		$oPage->strPrev       = "";
		$oPage->strNext       = "";
		$oPage->strPrevDiv    = "<";
		$oPage->strNextDiv    = ">";
		$oPage->strLast       = ">>";
		$oPage->strFirst      = "<<";
		$oPage->strCurrentNum = "<strong>[%s]</strong>";
		$_GET = delArrayByKey($_GET, 'curpage');
		$oPage->strGet = getQueryString();

		$result =& query($oPage->getListQuery());
?>
			<table width="100%" class="table_box">
				<tr>
					<th width="10%">Item</th>
					<td width="40%"><?php echo ($_item == 'all') ? "ALL" : implode(" > ", $itemCategory);?></td>
					<th width="20%">SEARCH PERIOD</th>
					<td width="40%"><?php echo date("j-M-Y", strtotime($_date_from))." ~ ".date("j-M-Y", strtotime($_date_to))?></td>
				</tr>
			</table>
            <table width="100%" class="table_c">
              <tr>
                <th width="4%">No</th>
                <th width="7%">I/CODE</th>
                <th width="10%">ITEM NO</th>
                <th>DESCRIPTION</th>
                <th width="7%">C/CODE</th>
                <th width="10%">@ PRICE</th>
                <th width="10%">D/ PRICE</th>
                <th width="10%">FROM</th>
                <th width="10%">TO</th>
              </tr>
<?php
		//Make a Row
		while ($column =& fetchRow($result)) {
?>
              <tr>
                <td><?php echo ++$oPage->serial ;?></td>
				<td><a href="<?php echo HTTP_DIR ?>apotik/price_policy/detail_apotik_policy.php?_code=<?php echo $column[0]?>">
					<?php echo $column[2]?></a></td>
	            <td <?php echo ($column[10]=='f') ? "style=\"color:#FF6666\"" : ""?>><?php echo cut_string($column[3], 10);?></td>
                <td <?php echo ($column[10]=='f') ? "style=\"color:#FF6666\"" : ""?>><?php echo cut_string($column[4], 40);?></td>
                <td align="center"><?php echo $column[5]?></td>
                <td align="right"><?php echo "Rp. " . number_format((double)$column[6])?></td>
                <td align="right" style="color:#0000CC"><?php echo "Rp. " . number_format((double)$column[7])?></td>
                <td align="center" <?php echo ($column[10]=='f') ? "style=\"color:#FF6666\"" : ""?>>
					<?php echo date("j-M-y", strtotime($column[8]))?></td>
                <td align="center" <?php echo ($column[10]=='f') ? "style=\"color:#FF6666\"" : ""?>>
					<?php echo date("j-M-y", strtotime($column[9]))?></td>
              </tr>
<?php
		}//END Row
?>
			</table>
            <table width="100%" cellpadding="0" cellspacing="2" border="0">
              <tr>
                <td align="center"><?php echo $oPage->putPaging();?></td>
              </tr>
            </table>
<?php
//if $_customer is by_group or some group.... -------------------------------------------------------
} else {
	//Outer Query
	$sqlPusat = "SELECT cug_code, cug_name FROM ".ZKP_SQL."_tb_customer_group WHERE cug_code in (SELECT cug_code FROM ".ZKP_SQL."_tb_customer WHERE cus_channel = '002')";

	if(isZKError($resultOuter =& query($sqlPusat))) {$M->printMessage($resultOuter);}

	$controller = 1;
	while ($controller && $dataPusat =& fetchRow($resultOuter)) {
		$sql  = "SELECT * FROM vw_apotik_price_by_item ";
		$sql .= "WHERE (ait_from, ait_to) OVERLAPS (DATE '$_date_from', DATE '$_date_to') ";
		$sql .= ($_item == "all") ? "" : $sql_item;
		if ($_customer == 'by_group') {
			$sql .= " AND cus_code in (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '" . $dataPusat[0] . "')";
		} else {
			$controller = null;
			$sql .= " AND cus_code in (SELECT cus_code FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '" . $_GET['_customer_group'] . "')";
		}

		//Query
		isZKError($resultInner =& query($sql)) ? $M->printMessage($resultInner) : 0;// no meaning 0
		if(numQueryRows($resultInner) > 0) {
?>
			<table width="100%" class="table_box">
				<tr>
					<td><strong><?php echo ($_customer == 'some_group') ? urldecode($_GET['_customer_group_name']) : $dataPusat[1]?></strong></td>
					<th width="10%">Item</th>
					<td><?php echo ($_item == 'all') ? "ALL" : implode(" > ", $itemCategory);?></td>
					<th width="20%">SEARCH PERIOD</th>
					<td><?php echo date("j-M-Y", strtotime($_date_from))." ~ ".date("j-M-Y", strtotime($_date_to))?></td>
				</tr>
			</table>
            <table width="100%" class="table_c">
              <tr>
                <th width="4%">No</th>
                <th width="7%">I/CODE</th>
                <th width="10%">ITEM NO</th>
                <th>DESCRIPTION</th>
                <th width="7%">C/CODE</th>
                <th width="10%">@ PRICE</th>
                <th width="10%">D/ PRICE</th>
                <th width="10%">FROM</th>
                <th width="10%">TO</th>
              </tr>
<?php
			$i = 1;
			//Make a Row
			while ($column =& fetchRow($resultInner)) {
?>
              <tr>
                <td><?php echo $i++ ;?></td>
				<td><?php echo $column[2]?></td>
	            <td><?php echo cut_string($column[3], 10);?></td>
                <td><?php echo cut_string($column[4], 40);?></td>
                <td align="center"><?php echo $column[5]?></td>
                <td align="right"><?php echo "Rp. " . number_format((double)$column[6])?></td>
                <td align="right" style="color:#0000CC"><?php echo "Rp. " . number_format((double)$column[7])?></td>
                <td align="center"><?php echo date("j-M-y", strtotime($column[8]))?></td>
                <td align="center"><?php echo date("j-M-y", strtotime($column[9]))?></td>
              </tr>
<?php
			}//End make a row
?>
            </table><br>
<?php
		}//end if numQueryRows
	}//End Outer Row
}//End 
?>
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