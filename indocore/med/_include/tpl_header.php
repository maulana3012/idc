<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at indosat.net.id
*
* @author : daesung kim
*
* $Id: tpl_header.php,v 1.2 2008/05/03 06:29:37 neki Exp $
*/
?>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td width="60%" bgcolor="#FFFFFF">
		<h4>
			<a href="<?php echo CHOOSE_PAGE ?>"><img src="../../_images/icon/home.png"></a>
			&nbsp; <?php echo $cboFilter[0][ZKP_URL][0][0] ?>
		</h4>
    </td>
    <td width="40%" align="right" bgcolor="#FFFFFF">
		<?php include_once $login_include?>
    </td>
  </tr>
</table>