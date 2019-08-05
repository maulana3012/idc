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
?>
<html>
<head>
<title>PT. INDOCORE PERKASA [APOTIK MANAGEMENT SYSTEM]</title>
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
<?php
/*
IO-00061A-A09,	2101, 1, 4
IO-00063A-A09,	2101, 1, 1
OO-A0585-L08,	2101, 1, 1
OO-A0595-L08, 	2101NE, 2, 5
IO-00225A-A09,	2101, 1.5, 3.5
OO-A0057-A09,	2101, 1, 2
OO-A0056-A09,	2101, 1, 3 
OO-A0063-A09, 	2101, 1, 3 
			2101NE, 1, 1.5 
OO-A0055-A09, 	2101, 1, 2 
OO-A0058-A09, 	2101NE, 1.5, 1.5
IX-00003P-A09,	2101NE, 1.5, 1.5
*/

$invoice = array('DF-004H-A09');
$item	 = array('00B03');
$qty	 = array(92,92);
for ($i=0; $i< count($item); $i++) {
	for ($j=0; $j< count($invoice); $j++) {

$sql	= "SELECT book_idx FROM ".ZKP_SQL."_tb_booking where book_doc_ref = '$invoice[$j]'";
$res	= query($sql);
$book	= fetchRow($res);

$sql	= "SELECT out_idx from ".ZKP_SQL."_tb_outgoing where out_book_idx= $book[0]";
$res	= query($sql);
$out	= fetchRow($res);

$sql	= "SELECT bor_idx,bor_qty FROM ".ZKP_SQL."_tb_borrow WHERE it_code='$item[$i]' AND out_idx= $out[0]";
$res	= query($sql);
$bor	= fetchRow($res);

$sql	= "SELECT ent_idx,ent_qty FROM ".ZKP_SQL."_tb_enter WHERE it_code='$item[$i]' AND bor_idx= $bor[0]";
$res	= query($sql);
$ent	= fetchRow($res);

$sql	= "SELECT bed_idx,bed_qty FROM ".ZKP_SQL."_tb_borrow_ed WHERE it_code='$item[$i]' AND out_idx=$out[0]";
$res	= query($sql);
$bed	= fetchRow($res);

$sql	= "SELECT eed_idx,eed_qty FROM ".ZKP_SQL."_tb_enter_ed WHERE it_code='$item[$i]' AND bed_idx= $bed[0]";
$res	= query($sql);
$eed	= fetchRow($res);

$sql	= "SELECT it_code, log_code FROM ".ZKP_SQL."_tb_stock_logs WHERE it_code='$item[$i]' AND log_document_no = 'D".substr($invoice[$j],1,15)."'";
$res	= query($sql);
$log	= fetchRow($res);

?>
<pre>
====================================================================================================================
					<b><?php echo $item[$i] ?> ON <?php echo $invoice[$j] ?></b>
item		= <?php echo $item[$i] ?> 
doc_ref		= <?php echo $invoice[$j] ?> 
book_idx		= <?php echo $book[0] ?> 

<B>out_idx		= <?php echo $out[0] ?> </B>

bor_idx		= <?php echo $bor[0] ?> 
ent_idx		= <?php echo $ent[0] ?> 
bed_idx		= <?php echo $bed[0] ?> 
eed_idx		= <?php echo $eed[0] ?> 


SELECT boit_idx,boit_it_code_for,boit_function,boit_qty FROM ".ZKP_SQL."_tb_booking_item where book_idx = '<?php echo $book[0] ?>' AND it_code='<?php echo $item[$i] ?>';

<!--
SELECT out_idx from ".ZKP_SQL."_tb_outgoing where out_book_idx= <?php echo $book[0] ?>;
SELECT bor_idx,bor_qty FROM ".ZKP_SQL."_tb_borrow WHERE it_code='<?php echo $item[$i] ?>' AND out_idx=<?php echo $out[0] ?>;
SELECT ent_idx,ent_qty FROM ".ZKP_SQL."_tb_enter WHERE it_code='<?php echo $item[$i] ?>' AND bor_idx=<?php echo $bor[0] ?>;

SELECT bed_idx,bed_qty FROM ".ZKP_SQL."_tb_borrow_ed WHERE it_code='<?php echo $item[$i] ?>' AND out_idx=<?php echo $out[0] ?>;
SELECT eed_idx,eed_qty FROM ".ZKP_SQL."_tb_enter_ed WHERE it_code='<?php echo $item[$i] ?>' AND bed_idx=<?php echo $bed[0] ?>;

SELECT oted_idx,oted_qty FROM ".ZKP_SQL."_tb_outgoing_ed WHERE it_code='<?php echo $item[$i] ?>' AND out_idx=<?php echo $out[0] ?>;
SELECT otst_idx,otst_qty FROM ".ZKP_SQL."_tb_outgoing_stock WHERE it_code='<?php echo $item[$i] ?>' AND out_idx=<?php echo $out[0] ?>;
SELECT it_code, log_code FROM ".ZKP_SQL."_tb_stock_logs WHERE it_code='<?php echo $item[$i] ?>' AND log_document_no ='<?php echo 'D'.substr($invoice[$j],1,15) ?>';
-->


UPDATE ".ZKP_SQL."_tb_booking_item SET boit_qty=<?php echo $qty[0] ?> WHERE book_idx=<?php echo $book[0] ?> and it_code='<?php echo $item[$i] ?>'; OR 
UPDATE ".ZKP_SQL."_tb_booking_item SET boit_qty=<?php echo $qty[0] ?> WHERE boit_idx=;

UPDATE ".ZKP_SQL."_tb_outgoing_item SET otit_qty=<?php echo $qty[1] ?> WHERE out_idx=<?php echo $out[0] ?> and it_code='<?php echo $item[$i] ?>';
UPDATE ".ZKP_SQL."_tb_outgoing_stock SET otst_qty=<?php echo $qty[1] ?> WHERE out_idx=<?php echo $out[0] ?> and it_code='<?php echo $item[$i] ?>';
UPDATE ".ZKP_SQL."_tb_outgoing_ed SET oted_qty=<?php echo $qty[1] ?> WHERE out_idx=<?php echo $out[0] ?> and it_code='<?php echo $item[$i] ?>';

UPDATE ".ZKP_SQL."_tb_borrow SET bor_qty=<?php echo $qty[1] ?> WHERE it_code='<?php echo $item[$i] ?>' AND bor_idx=<?php echo $bor[0] ?>;
UPDATE ".ZKP_SQL."_tb_enter SET ent_qty=<?php echo $qty[1] ?> WHERE it_code='<?php echo $item[$i] ?>' AND bor_idx=<?php echo $bor[0] ?>;
UPDATE ".ZKP_SQL."_tb_borrow_ed SET bed_qty=<?php echo $qty[1] ?> WHERE it_code='<?php echo $item[$i] ?>' AND bed_idx=<?php echo $bed[0] ?>;
UPDATE ".ZKP_SQL."_tb_enter_ed SET eed_qty=<?php echo $qty[1] ?> WHERE it_code='<?php echo $item[$i] ?>' AND bed_idx=<?php echo $bed[0] ?>;

UPDATE ".ZKP_SQL."_tb_stock_logs SET log_qty=<?php echo $qty[1] ?> WHERE it_code='<?php echo $item[$i] ?>' AND log_document_no ='<?php echo 'D'.substr($invoice[$j],1,15) ?>';


</pre>
<?php
	}
}
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