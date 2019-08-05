<?php
/**
* Copyright PT. ZONEKOM Indonesia All right reserved
* Contact us dskim@indosat.net.id
*
* @author : 
*
* $Id: tpl_afterLogin.php,v 1.1 2008/04/28 06:52:13 neki Exp $
*/
?>

Welcome <?php echo $S->getValue('ma_account')?> (<a href="../../admin/user/my_config.php?ma_idx=<?php echo $S->getValue("ma_idx");?>" class="top">My Info</a> | <a href="?logout=1&returnUrl=<?php echo urlencode($_SERVER['REQUEST_URI'])?>" class="top">Log-out  <img src="../../_images/icon/logout.gif" alt="Logout"></a>)