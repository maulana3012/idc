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

//ACCESS PERMISSION
require_once "./_access_local.php";
ckperm(ZKP_SELECT, "javascript:window.close();");

//SET PAGE PARAMETER
if(!isset($_GET['idx']) || $_GET['idx'] == '')
    die("<script language=\"javascript1.2\">window.close();</script>");

$idx = $_GET['idx'];

$sql = "
SELECT
 it_model_no,
 sg_code,
 to_char(sg_receive_date, 'dd-Mon-yyyy') AS reg_date,
 sgit_tech_analyze,
 'revise_registration.php?_code='||sg_code AS go_page
FROM
 ".ZKP_SQL."_tb_service_reg
 JOIN ".ZKP_SQL."_tb_service_reg_item AS sgit USING(sg_code) 
 JOIN ".ZKP_SQL."_tb_item AS it USING(it_code)
WHERE 
 TRIM(sgit_serial_number) = TRIM('$idx')";

if (isZKError($result =& query($sql))) $M->goErrorPage($result, "javascript:window.close();");
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<title>DETAIL LOG SERIAL NUMBER</title>
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script language="javascript1.2" type="text/javascript" src="../../_script/aden.js"></script>
</head>
<body style="margin:8pt">
<table width="100%" cellpadding="0">
    <tr><td colspan="2" background="../../_images/properties/p_leftmenu_bg05.gif"><img src="../../_images/properties/p_dot.gif"></td></tr>
    <tr bgcolor=#f9f9fb>
        <td height="25"><img src="../../_images/icon/setting_mini.gif">&nbsp;&nbsp;<strong>Detail Registration Log for <?php echo $_model ?> SN : <?php echo TRIM($idx) ?></strong></td>
    </tr>
    <tr><td colspan="2" background="../../_images/properties/p_leftmenu_bg05.gif"><img src="../../_images/properties/p_dot.gif"></td></tr>
</table><br />

<table width="100%" class="table_f">
    <tr>
        <th width="5%">No</th>
        <th width="20%">MODEL</th>
        <th width="15%">REG NO</th>
        <th width="15%">REG DATE</th>
        <th>TECHNICAL ANALYSIS</th>
    </tr>
    <?php 
    $i = 1;
    while ($col =& fetchRowAssoc($result)) {
    ?>
    <tr>
        <td align="center"><?php echo $i++ ;?></td>
        <td><?php echo $col['it_model_no']?></td>
        <td align="center"><a href="<?php echo $col['go_page']?>" target="_blank"><?php echo $col['sg_code']?></a></td>
        <td align="center"><?php echo $col['reg_date']?></td>
        <td><?php echo $col['sgit_tech_analyze']?></td>
    </tr>
    <?php } ?>
</table>


</body>
</html>
