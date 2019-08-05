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

//Parameter
$period_from 	= isset($_GET['period_from'])? $_GET['period_from'] : date("j-M-Y", time()-2592000);
$period_to 		= isset($_GET['period_to'])? $_GET['period_to'] : date("j-M-Y", time());

//============================================================================================ DEFAULT PROCESS
$sqlQuery = new strSelect("
SELECT
  sg_code, 
  to_char(sg_receive_date, 'dd-Mon-YYYY') as sg_date, 
  sg_cus_to, 
  sg_cus_to_name, 
  sg_cus_to_address,
  CASE	
	when sg_complete_service is true and ".ZKP_SQL."_getServiceBill(sg_code,null,1) is null then 'true'
	else 'false'
  END AS status
FROM ".ZKP_SQL."_tb_service_reg");
$sqlQuery->setOrderBy("sg_receive_date DESC");

$sqlQuery->whereCaluse = ZKP_SQL."_issetChargeItem(sg_code)='true' AND sg_receive_date BETWEEN DATE '$period_from' AND DATE '$period_to'";

if(isZKError($result =& query($sqlQuery->getSQL())))
	$M->goErrorPage($result,  "javascript:window.close();");

//Total Rows
$numRow = numQueryRows($result);

//Declare Paging
$oPage = new strPaging($sqlQuery->getSQL(), 100);
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
echo "var sg = new Array();\n";
$i = 0;
$ptn = array("/(['\"])/", "/[\r\n][\s]+/");
$rpm = array("\\1", " ");

while ($rows =& fetchRow($result,0)) {
	printf("sg['%s']=['%s','%s','%s',\"%s\"];\n",
		addslashes($rows[0]),
		addslashes($rows[1]),				//0
		addslashes($rows[2]),				//1
		addslashes($rows[3]),				//2
		preg_replace($ptn, $rpm, $rows[4])	//3
	);
}
?>

function fillField(idx) {
	var f = window.opener.document.frmInsert;

	f._reg_no.value = idx
	f._cus_to.value = sg[idx][1];
	f._cus_name.value = sg[idx][2];
	f._cus_address.value = sg[idx][3];
	f._cus_to.className = 'req';
	f._cus_name.className = 'req';
	f._cus_to.disabled = false;
	f._cus_name.disabled = false;
	f._cus_address.disabled = false;
	f._cus_to.readOnly = 'readOnly';
	f._make_cus_name.className = 'fmt';
	f._make_cus_name.disabled = true;
	f._make_cus_phone.disabled = true;
	f._make_cus_hphone.disabled = true;
	f._make_cus_address.disabled = true;
	f._make_cus_name.value = '';
	f._make_cus_phone.value = '';
	f._make_cus_hphone.value = '';
	f._make_cus_address.value = '';
	f._source_customer.value = 1;
	f._source_cus[0].checked = true;
	f._source_cus[0].disabled = true;
	f._source_cus[1].disabled = true;

	window.close();
}
</script>
</head>
<body style="margin:8pt">
<table width="100%" class="table_box">
	<tr>
		<td>
			<strong>
			<font color="black">
			[<font color="blue"><?php echo strtoupper($currentDept) ?></font>] CURRENT REGISTRATION<br />
			<small>* Recorder registration no</small>
			</font>
			</strong>
		</td>
	</tr>
</table><hr>
<form name="frmSearch" action="<?php echo $_SERVER['PHP_SELF']?>" method="GET">
<div align="right">
<span class="comment">
	FROM <input type="text" name="period_from" size="10" class="fmtd" value="<?php echo $period_from; ?>">&nbsp;
	TO <input type="text" name="period_to" size="10" class="fmtd"  value="<?php echo $period_to; ?>">
</span>
</div><br />
</form>
<script language="javascript1.2" type="text/javascript">
	var f = window.document.frmSearch;
	var ts = <?php echo time() * 1000;?>;

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
<table width="100%" class="table_box">
  <tr height="30">
    <th width="18%">CODE</th>
    <th width="18%">DATE</th>
    <th>CUSTOMER NAME</th>
	<th width="18%">DETAIL</th>
  </tr>
</table>
<div style="height:400; overflow-y:scroll">
<table width="100%" class="table_box">
<?php
pg_result_seek($result,0);
while ($column =& fetchRowAssoc($result)) {
?>
  <tr>
	<td width="18%" align="center">
		<?php if($column['status']=='true') { ?>
		<a href="javascript:fillField('<?php echo $column['sg_code'] ?>')"><b><?php echo $column['sg_code']?></b></a>
		<?php } else { ?>
		<?php echo $column['sg_code']?>
		<?php } ?>
	</td>
	<td width="20%"><?php echo $column['sg_date']?></td>
	<td><?php echo $column['sg_cus_to_name']?></td>
	<td><a href="../registration/revise_registration.php?_code=<?php echo $column['sg_code'] ?>" target="_blank">view</a></td>
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