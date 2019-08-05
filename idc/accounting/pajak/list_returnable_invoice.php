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

//PAGE ACCESS PERMISSION
require_once "./_access_local.php";
ckperm(ZKP_SELECT, HTTP_DIR . "$currentDept/$moduleDept/index.php");

//Global
$left_loc = "list_returnable_invoice.php";
$_ordered_by = $cboFilter[1][ZKP_FUNCTION][0][0];

//PROCESS FORM
require_once "tpl_process_form.php"; 

//DEFAULT PROCESS
$sqlQuery = "SELECT *, to_char(bill_inv_date,'dd-Mon-yyyy') as inv_date,
case 
 when bill_dept = 'A' THEN 'Apotik'
 when bill_dept = 'D' THEN 'Dealer'
 when bill_dept = 'H' THEN 'Hospital'
 when bill_dept = 'T' THEN 'Tender'
END AS dept
FROM ".ZKP_SQL."_tb_billing WHERE bill_is_returnable = 'TRUE' ORDER BY bill_inv_date DESC";
$query = query($sqlQuery);
?>
<html>
<head>
<title><?php echo $cboFilter[0][ZKP_FUNCTION][0][1] ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
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
		      <strong class="title">[<font color="#446fbe"><?php echo strtoupper($currentDept) ?></font>] LIST RETURNABLE INVOICE</strong><br /><br />
			<table width="80%" class="table_aa">
              <tr>
                <th width="10%">BILL CODE</th>
                <th width="10%">DATE</th>
                <th width="10%">DEPT</th>
                <th width="25%">CUSTOMER</th>
				<th width="15%">TOTAL</th>
				<th width="5%"></th>
			  </tr>	
			  <?php
				while ($column =& fetchRowAssoc($query)) {
			    if($column['bill_dept']=='A'){ $dept = "APOTIK"; }elseif($column['bill_dept']=='D'){ $dept = "DEALER"; }elseif($column['bill_dept']=='H'){ $dept = "HOSPITAL"; }elseif($column['bill_dept']=='M'){ $dept = "MARKETING"; }elseif($column['bill_dept']=='P'){ $dept = "PHARMACEUCITICAL"; }elseif($column['bill_dept']=='T'){ $dept = "TENDER"; }elseif($column['bill_dept']=='C'){ $dept = "CS"; }
			  ?>
			  <tr>
				<td align="center" valign="center"><?php echo $column['bill_code'] ?></td>
				<td align="center" valign="center"><?php echo $column['inv_date'] ?></td>
				<td align="center" valign="center"><?php echo $column['dept'] ?></td>
				<td align="left" valign="center"><?php echo $column['bill_cus_to_name']; ?></td>
				<td align="right" valign="center">Rp <?php echo number_format($column['bill_total_billing'],2) ?></td>
				<td align="center" valign="center">				
				<form method="POST">
				<input type="hidden" name="p_mode" value="update_return_opposite">
				<input type="hidden" name="_bill_code" value="<?php echo $column['bill_code'] ?>">
				<input type="submit" class="input_btn" value="Delete">
				</td>
				</form>
			  </tr>
				<?php } ?>
			</table><br />
				  <h3>New Returnable Invoice</h3>
			
				<form action="<?php echo $_SERVER[PHP_SELF]; ?>?module=result" method="POST">
				<b>Input Bill Code : </b><input type="text" name="bill" class="req" size="15" rowspan="4"> &nbsp; <input type="submit" class="input_btn" value="Cari">
				</form>
			<table width="55%" class="table_aa">
				<?php
				if($_GET['module']=='result'){
					if(empty($_POST['bill'])){
					echo "<script>window.alert('Input Bill Terlebih Dahulu !');
						  window.location=('".$_SERVER[PHP_SELF]."')</script>";
					}else{
				$sql_cari = "SELECT * FROM ".ZKP_SQL."_tb_billing WHERE bill_code = '$_POST[bill]'";
				$query2 = query($sql_cari);
				$column2 =& fetchRowAssoc($query2);
				// echo "<pre>"; var_dump($sql_cari);
				?><br />
				<form method="POST">
				<input type="hidden" name="p_mode" value="update_return">
				<input type="hidden" name="_bill_code" value="<?php echo $column2['bill_code']; ?>">
				<tr><td>Cus Code</td><td><input type="text" name="cus_code" class="fmt" value="<?php echo $column2['bill_cus_to']; ?>"></td><td>Cus Name</td><td><input type="text" name="cus_to_name" class="fmt" size="50" value="<?php echo $column2['bill_cus_to_name']; ?>"></td></tr>
				<tr><td>Ship To</td><td><input type="text" name="ship_to" class="fmt" value="<?php echo $column2['bill_ship_to']; ?>"></td><td>Ship To Name</td><td><input type="text" name="ship_to_name" class="fmt" size="50" value="<?php echo $column2['bill_ship_to_name']; ?>"></td></tr>
				<tr><td>Pajak To</td><td><input type="text" name="pajak_to" class="fmt" value="<?php echo $column2['bill_pajak_to']?>"></td><td>Pajak To Name</td><td><input type="text" name="pajak_to_name" class="fmt" size="50" value="<?php echo $column2['bill_pajak_to_name']; ?>"></td></tr>
				<tr><td colspan="4">Total Billing Rp <input type="text" name="total_billing" class="fmt" size="50" value="<?php echo number_format($column2['bill_total_billing'],2); ?>"></td></tr>
				<tr><td colspan="4"><input type="submit" class="input_btn" value="OK"></td></tr>
				</form>
				<?php }} ?>
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