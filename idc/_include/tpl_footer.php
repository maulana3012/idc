<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at indosat.net.id
*
* @author : daesung kim
*
* $Id: tpl_footer.php,v 1.1 2008/04/28 06:52:13 neki Exp $
*/

//------------------------------------------------------------------
// Footer Contents (for Admin Page)
//------------------------------------------------------------------
if(!isset($week)) {
	$week = getWeek();
}
?>
<table cellpadding="0" cellspacing="0" align="center" border="0" width="100%">
  <tr>
    <td align="center">PT. INDOCORE PERKASA</td>
    <td align="right"><?php echo date("j-M, Y [D] /") . " " . $week["string"]?></td>
  </tr>
</table>
