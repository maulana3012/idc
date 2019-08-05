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
?>
<head>
<title><?php echo $cboFilter[0][ZKP_URL][0][1] ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="../../_script/aden.css" rel="stylesheet" type="text/css">
<script src="../../_script/aden.js" type="text/javascript"></script>
<link href="../../_script/SpryTabbedPanels.css" rel="stylesheet" type="text/css">
<script src="../../_script/SpryTabbedPanels.js" type="text/javascript"></script>
<script language="javascript" type="text/javascript">
function checkform(url, group) {
	var f = window.document.frmWorkGroup;
	f._workgroup.value = group;

	if(url == 'ALL') {
		document.frmWorkGroup.action ="/indocore/admin/main/index.php";
		f.submit();
	} else if(url == 'IDC')	{
		document.frmWorkGroup.action ="/idc/admin/main/index.php";
		f.submit();
	} else if(url == 'MED')	{
		document.frmWorkGroup.action ="/med/admin/main/index.php";
		f.submit();
	} else if(url == 'MEP')	{
		document.frmWorkGroup.action ="/mep/admin/main/index.php";
		f.submit();
	} else if(url == 'SMD')	{
		document.frmWorkGroup.action ="/smd/admin/main/index.php";
		f.submit();
	}


}
</script>
<style type="text/css">
	div.TabbedPanels {
		background-color:#FFF;
		}
	div.TabbedPanelsContent div {
		position:absolute; 
		width:165; 
		height:80; 
		color:#999;
		background-color:#FFF;
		padding-top:25px;
		font-family:"Lucida Sans Unicode", "Lucida Grande", sans-serif;
		font-size:11px;
		text-align:center;
		font-weight:bold;
		background-color:#fff;
		position:absolute; 
		cursor:hand;
	}

	div.TabbedPanelsContent a:hover {
		font-family:"Lucida Sans Unicode", "Lucida Grande", sans-serif;
		color:#444;
		letter-spacing:2.5px;
		text-decoration:none;
		display: block; 
		border:#CCC;
	}
</style>
</head>
<body topmargin="0" leftmargin="0">
<table border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#eaecee">
  <tr>
    <td>
			<?php require_once APP_DIR . "_include/tpl_header.php"?>
    </td>
  </tr>
  <tr>
    <td style="padding:5 10 0 10" valign="bottom">
			<?php //require_once APP_DIR . "_include/tpl_topMenu.php";?>
    </td>
  </tr>
  <tr>
    <td style="padding:0 3 3 3">
    	<table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF">
        <tr>
			<?php //require_once "_left_menu.php";?>
			<td style="padding:10;" height="480" valign="top">
          	<!--START: BODY-->
<!-- dept, management, stock, others -->
<form name="frmWorkGroup" method="POST">
<input type='hidden' name='p_mode' value='set'>
<input type='hidden' name='_workgroup' value="">
<input type='hidden' name='showMsg' value="<?php echo  isset($_GET["showMsg"]) ? $_GET["showMsg"] : "" ?>">

<div id="table_deployment" class="TabbedPanels">
  <ul class="TabbedPanelsTabGroup">
    <?php if($S->getValue("ma_see_tab")&1) { ?><li class="TabbedPanelsTab">REPORT</li><?php } ?>
	<?php if($S->getValue("ma_see_tab")&2) { ?><li class="TabbedPanelsTab">INDOCORE</li><?php } ?>
    <?php if($S->getValue("ma_see_tab")&4) { ?><li class="TabbedPanelsTab">MEDISINDO</li><?php } ?>
    <?php if($S->getValue("ma_see_tab")&8) { ?><li class="TabbedPanelsTab">MEDIKUS</li><?php } ?>
    <?php if($S->getValue("ma_see_tab")&16) { ?><li class="TabbedPanelsTab">SAMUDIA</li><?php } ?>
  </ul>
  <div class="TabbedPanelsContentGroup">
    <?php if($S->getValue("ma_see_tab")&1) { ?>
    <div class="TabbedPanelsContent" style="height:430;">
		<!--START FIRST LAYOUT-->
			<div style="top:250; left:400;"><a href="javascript:checkform('ALL','management_all')">MANAGEMENT</a></div>
		<!--END FIRST LAYOUT-->
	</div>
    <?php } ?>
    <?php if($S->getValue("ma_see_tab")&2) { ?>
    <div class="TabbedPanelsContent" style="height:430;">
		<!--START SECOND LAYOUT-->
			<div style="top:175; left:250;"><a href="javascript:checkform('IDC','department')">DEPARTMENT</a></div>
			<div style="top:175; left:450;"><a href="javascript:checkform('IDC','management')">MANAGEMENT</a></div>
			<div style="top:175; left:650; width:75; height:210; padding-top:100; "><a href="#">LETTER</a></div>
			<div style="top:300; left:250;"><a href="javascript:checkform('IDC','warehouse')">PURCHASING &amp; WAREHOUSE</a></div>
			<div style="top:300; left:450;"><a href="javascript:checkform('IDC','others')">OTHERS</a></div>
		<!--END SECOND LAYOUT-->
	</div>
    <?php } ?>
    <?php if($S->getValue("ma_see_tab")&4) { ?>
    <div class="TabbedPanelsContent" style="height:430;">
		<!--START THIRD LAYOUT-->
			<div style="top:175; left:250;"><a href="javascript:checkform('MED','department')">DEPARTMENT</a></div>
			<div style="top:175; left:450;"><a href="javascript:checkform('MED','management')">MANAGEMENT</a></div>
			<div style="top:175; left:650; width:75; height:210; padding-top:100; "><a href="javascript:checkform('MED','letter')">LETTER</a></div>
			<div style="top:300; left:250;"><a href="javascript:checkform('MED','warehouse')">PURCHASING &amp; WAREHOUSE</a></div>
			<div style="top:300; left:450;"><a href="javascript:checkform('MED','others')">OTHERS</a></div>
		<!--END THIRD LAYOUT-->
	</div>
    <?php } ?>
    <?php if($S->getValue("ma_see_tab")&8) { ?>
    <div class="TabbedPanelsContent" style="height:430;">
		<!--START FOURTH LAYOUT-->
			<div style="top:250; left:300;"><a href="javascript:checkform('MEP','department')">DEPARTMENT</a></div>
			<div style="top:250; left:500;"><a href="javascript:checkform('MEP','management')">MANAGEMENT</a></div>
		<!--END FOURTH LAYOUT-->
	</div>
	<?php } ?>
    <?php if($S->getValue("ma_see_tab")&16) { ?>
    <div class="TabbedPanelsContent" style="height:430;">
		<!--START THIRD LAYOUT-->
			<div style="top:175; left:250;"><a href="javascript:checkform('SMD','department')">DEPARTMENT</a></div>
			<div style="top:175; left:450;"><a href="javascript:checkform('SMD','management')">MANAGEMENT</a></div>
			<div style="top:175; left:650; width:75; height:210; padding-top:100; "><a href="javascript:checkform('SMD','letter')">LETTER</a></div>
			<div style="top:300; left:250;"><a href="javascript:checkform('SMD','warehouse')">PURCHASING &amp; WAREHOUSE</a></div>
			<div style="top:300; left:450;"><a href="javascript:checkform('SMD','others')">OTHERS</a></div>
		<!--END THIRD LAYOUT-->
	</div>
    <?php } ?>

  </div>
</div>
<script type="text/javascript">
var table_deployment = new Spry.Widget.TabbedPanels('table_deployment');
</script>

</form>
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
