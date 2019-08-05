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

//GLOBAL
$left_loc = "summary_by_percentage.php";
?>
<html>
<head>
<title>PT. INDOCORE PERKASA [APOTIK MANAGEMENT SYSTEM]</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
<script language='text/javascript' type='text/javascript'>
function fillAcara() {
	var x = (screen.availWidth - 400) / 2;
	var y = (screen.availHeight - 600) / 2;
	var win = window.open('p_list_event.php', '', 'scrollbars,width=400,height=600,screenX='+x+',screenY='+y+',left='+x+',top='+y);
}

function initPage() {
	setSelect(window.document.frmSearch.cbojk, "<?php echo isset($_GET['cbojk']) ? $_GET['cbojk'] : "all"?>");
}
</script>
</head>
<body topmargin="0" leftmargin="0" onload="initPage()">
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
<?php
$period_from 	= isset($_GET['period_from'])? urldecode($_GET['period_from']) : "";
$period_to 		= isset($_GET['period_to'])? urldecode($_GET['period_to']) : "";
$usia_from 		= isset($_GET['usia_from'])? urldecode($_GET['usia_from']) : "";
$usia_to 		= isset($_GET['usia_to'])? urldecode($_GET['usia_to']) : "";
$jk		 		= isset($_GET['jk'])? urldecode($_GET['jk']) : "all";
?>
<h4>DAFTAR HASIL PEMERIKSAAN</h4>
<form name="frmSearch" action="<?php echo $_SERVER['PHP_SELF']?>" method="GET">
<table width="100%" class="table_layout">
	<tr height="5px"><td></td></tr>
	<tr>
		<td width="25%"></td>
		<td>
			PERIOD 
			<input type="text" name="period_from" size="10" class="fmtd" value="<?php echo $period_from; ?>">&nbsp;-&nbsp;
			<input type="text" name="period_to" size="10" class="fmtd"  value="<?php echo $period_to; ?>">
		</td>
		<td>
			RANGE USIA 
			<input type="text" name="usia_from" size="5" class="fmtd" value="<?php echo $usia_from; ?>">&nbsp;-&nbsp;
			<input type="text" name="usia_to" size="5" class="fmtd"  value="<?php echo $usia_to; ?>">
		</td>
		<td> 
			JK 
			<select name="cbojk">
				<option value="all"></option>
				<option value="l">LAKI-LAKI</option>
				<option value="p">PEREMPUAN</option>
			</select>
		</td>
	</tr>
</table><br /><br />
</form>
<center>
<img src="<?php echo "../../_include/tpl_graphPie.php" ?>?data=10*9*11*10&label=Denmark*Germany*USA*Sweden" />
</center>
<script language="javascript1.2" type="text/javascript">
	var f = window.document.frmSearch;
	var ts = <?php echo time() * 1000;?>;
	
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