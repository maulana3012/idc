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

//PAGE ACCESS PERMISSION
require_once "./_access_local.php";
ckperm(ZKP_SELECT, "javascript:window.close();");
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
</script>
</head>
<body style="margin:8px">
<strong>
[<font color="blue"><?php echo strtoupper($currentDept) ?></font>] STOCK E/D MANAGEMENT<br />
<small>* Information about limit E/D in each department.</small>
</strong>
<hr><br />
<p>Currently you see as <code style="color:#004686"><?php echo ucfirst($currentDept) ?></code> :</p>
<table class="table_c" style="width:100%"> 
	<tr>
		<th width="15%">Code</th>
		<th>Desc</th>
		<th width="15%">Allowed E/D<br /><b>&gt;=</b><br />Month(s)</th>
	</tr>
<?php
if($currentDept == 'apotik') { ?>
	<tr>
		<td>2101</td>
		<td>AGS-50</td>
		<td align="center">8</td>
	</tr>
	<tr>
		<td>2101NE</td>
		<td>AGS-50SS</td>
		<td align="center">8</td>
	</tr>
	<tr>
		<td>2200</td>
		<td>AGL-28</td>
		<td align="center">6</td>
	</tr>
<?php } else if($currentDept == 'dealer') { ?>
	<tr>
		<td>2101</td>
		<td>AGS-50</td>
		<td align="center">8</td>
	</tr>
	<tr>
		<td>2101NE</td>
		<td>AGS-50SS</td>
		<td align="center">8</td>
	</tr>
	<tr>
		<td>2200</td>
		<td>AGL-28</td>
		<td align="center">6</td>
	</tr>
	<tr>
		<td></td>
		<td align="center"><span class="comment"><i>--- all Sekisui ---</i></span></td>
		<td align="center">6</td>
	</tr>
<?php } else if($currentDept == 'hospital' || $currentDept == 'pharmaceutical') { ?>
	<tr>
		<td>2101</td>
		<td>AGS-50</td>
		<td align="center">4</td>
	</tr>
	<tr>
		<td>2101NE</td>
		<td>AGS-50SS</td>
		<td align="center">4</td>
	</tr>
	<tr>
		<td>2200</td>
		<td>AGL-28</td>
		<td align="center">4</td>
	</tr>
	<tr>
		<td></td>
		<td align="center"><span class="comment"><i>--- all Sekisui ---</i></span></td>
		<td align="center">2</td>
	</tr>
<?php } ?>
</table><br />
<table width="100%" class="table_box">
	<tr>
		<td>
			<span class="comment"><i>* To change the setting, please contact Administrator.</i></span>
		</td>
		<th width="20%">
			<a href="javascript:window.history.go(-1)"><img src="../../_images/icon/back.gif" alt="Back to invoice list"> Back</a> &nbsp; &nbsp;
			<a href="javascript:window.close()"><img src="../../_images/icon/close.gif" alt="Close pop-up"></a>
		</th>
	</tr>
</table>
</body>
</html>