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
ckperm(ZKP_INSERT, HTTP_DIR . "javascript:window.close()");

//CHECK PARAMETER
$_check_code = isset($_REQUEST['_check_code']) ? $_REQUEST['_check_code'] : "";

$sql = "SELECT cus_code, cus_full_name, to_char(cus_since, 'DD-Mon-YY') AS since FROM ".ZKP_SQL."_tb_customer WHERE cug_code = '" . urlencode($_GET['_code']) . "' ORDER BY cus_code";

if(isZKError($result =& query($sql)))
	$M->goErrorPage($result,  "javascript:window.close();");

//Total Rows
$numRow = numQueryRows($result);

//Declare Paging
$oPage = new strPaging($sql, 50);
$oPage->strPrev       = "";
$oPage->strNext       = "";
$oPage->strPrevDiv    = "<";
$oPage->strNextDiv    = ">";
$oPage->strLast       = ">>";
$oPage->strFirst      = "<<";
$oPage->strCurrentNum = "<strong>[%s]</strong>";
$_GET = delArrayByKey($_GET, 'curpage');
$oPage->strGet = getQueryString();

if(isZKError($result =& query($oPage->getListQuery())))
	$M->goErrorPage($result, "javascript:window.close();");
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<title>CUSTOMER LIST</title>
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script language="text/javascript" type="text/javascript" src="../../_script/aden.js"></script>
</head>
<body style="margin:8pt">
<h5>GROUP: <?php echo urldecode($_GET['_name'])?></h5>
Total Record : <?php echo number_format((double)$numRow) ?>
<table width="100%" class="table_box">
	<tr height="35px">
		<th width="10%">No</th>
		<th width="15%">CODE</th>
		<th>NAME</th>
		<th width="24%">SINCE</th>
	</tr>
</table>
<div style="height:470; overflow-y:auto">
<table width="100%" class="table_c">
<?php while ($column =& fetchRowAssoc($result)) { ?>
	<tr>
		<td width="7%"><?php echo ++$oPage->serial ;?></td>
		<td width="12%"><?php echo $column['cus_code']?></td>
		<td><?php echo $column['cus_full_name']?></td>
		<td width="20%"><?php echo $column['since']?></td>
	</tr>
<?php } ?>
</table>
</div>
<table width="100%" cellpadding="0" cellspacing="2" border="0">
  <tr>
    <td align="center"><?php echo $oPage->putPaging();?></td>
  </tr>
</table>
</body>
</html>