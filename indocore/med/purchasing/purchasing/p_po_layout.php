<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at zonekom.com
*
* @generated : 25-May, 2007 16:16:33
* @author    : daesung kim
*/
require_once "../../zk_config.php";

//CHECK PARAMETER
if(!isset($_GET['_type']) && $_GET['_type'] == '')
	die("<script language=\"javascript1.2\">window.close();</script>");

$_type	 = $_GET['_type'];
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<title>PO LAYOUT</title>
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script language="text/javascript" type="text/javascript" src="../../_script/aden.js"></script>
</head>
<body style="margin:8pt">
<h4>LAYOUT TYPE <?php echo $_type ?></h4>
<table width="100%" class="table_c">
<?php if($_type == 1) {?>
	<tr>
		<th width="7%">NO</th>
		<th>ITEM</th>
		<th width="12%">QTY<br />(Pcs)</th>
		<th width="25%">UNIT PRICE<br />(US$)</th>
		<th width="25%">AMOUNT<br />(US$)</th>
	</tr>
	<tr>
		<td> &nbsp; </td>
		<td> (Model No) </td>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
	</tr>
	<tr>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
	</tr>
	<tr>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
	</tr>
	<tr>
		<td colspan="2"><b>TOTAL</b></td>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
	</tr>
<?php } else if($_type == 2) {?>
	<tr>
		<th width="7%">NO</th>
		<th>ITEM</th>
		<th width="12%">QTY<br />(Box)</th>
		<th width="25%">UNIT PRICE<br />(US$)</th>
		<th width="25%">AMOUNT<br />(US$)</th>
	</tr>
	<tr>
		<td> &nbsp; </td>
		<td> (Model No) </td>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
	</tr>
	<tr>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
	</tr>
	<tr>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
	</tr>
	<tr>
		<td colspan="2"><b>TOTAL</b></td>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
	</tr>
<?php } else if($_type == 3) {?>
	<tr>
		<th width="7%">NO</th>
		<th>ITEM</th>
		<th width="18%">PRODUCT<br />CODE</th>
		<th width="10%">QTY<br />(Rolls)</th>
		<th width="20%">UNIT PRICE<br />(US$)</th>
		<th width="20%">AMOUNT<br />(US$)</th>
	</tr>
	<tr>
		<td> &nbsp; </td>
		<td> (Description) </td>
		<td> (Product Code) </td>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
	</tr>
	<tr>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
	</tr>
	<tr>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
	</tr>
	<tr>
		<td colspan="3"><b>TOTAL</b></td>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
	</tr>
<?php } else if($_type == 4) {?>
	<tr>
		<th width="7%">NO</th>
		<th>ITEM</th>
		<th width="10%">QTY<br />(Pcs)</th>
		<th width="7%">SIZE</th>
		<th width="18%">UNIT PRICE<br />(USD)</th>
		<th width="18%">AMOUNT<br />(USD)</th>
		<th>REMARK</th>
	</tr>
	<tr>
		<td> &nbsp; </td>
		<td> (Model No) </td>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
	</tr>
	<tr>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
	</tr>
	<tr>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
	</tr>
	<tr>
		<td colspan="4"><b>TOTAL</b></td>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
		<td> &nbsp; </td>
	</tr>
<?php } ?>
</table>
<p align="right"><button class="input_sky" onclick="window.close();">CLOSE</button></p>
</body>
</html>