<?php
/**
* Copyright PT. ZONEKOM All right reserved
* Contact us dskim at zonekom.com
*
* @generated : 18-May, 2007 15:20:22
* @author    : daesung kim

*
* $_po_date : Inquire Date
*
*/
//Variable Color (make same with the javascript)
$display_css['before_due'] 	= "color:black";
$display_css['over_due'] 	= "background-color:lightyellow; color:red";
$display_css['paid'] 		= "background-color:lightgrey; color:black";

//SET WHERE PARAMETER
$tmp	= array();

if ($some_date != "") {
	$tmp[]   = "sv_date = DATE '$some_date'";
} else {
	$tmp[]   = "sv_date BETWEEN DATE '$period_from' AND '$period_to'";
}

if($_cus_code != '') {
	$tmp[] = "cus_code = '$_cus_code'";
}

$strWhere	= implode(" AND ", $tmp);

$sql = "
SELECT
  cus_code,
  cus_full_name,
  sv_code AS service_code,
  sv_date,
  sv_reg_no AS reg_no,
  to_char(sv_date, 'dd-Mon-yy') AS service_date,
  sv_total_discount,
  CASE
	WHEN sv_total_remain <= 0 THEN 'paid'
	WHEN sv_total_remain > 0 AND sv_due_date > CURRENT_TIMESTAMP THEN 'before_due'
	WHEN sv_total_remain > 0 AND sv_due_date < CURRENT_TIMESTAMP THEN 'over_due'
  END AS payment_status,
  'revise_service.php?_code=' || sv_code AS go_page1,
  '../registration/revise_registration.php?_code=' || sv_reg_no AS go_page2
FROM
  ".ZKP_SQL."_tb_customer
  JOIN ".ZKP_SQL."_tb_service ON cus_code = sv_cus_to 
WHERE " . $strWhere . "
ORDER BY sv_date, sv_code";

// raw data
$rd = array();
$rdIdx = 0;
$cache = array("","","");
$group0 = array();
$res =& query($sql);
while($col =& fetchRowAssoc($res)) {

	$rd[] = array(
		$col['cus_code'],			//0
		$col['cus_full_name'],		//1
		$col['service_code'],		//2
		$col['service_date'],		//3
		$col['reg_no'],				//4
		$col['sv_total_discount'],	//5
		$col['payment_status'],		//6
		$col['go_page1'],			//7
		$col['go_page2']			//8
	);

	//1st grouping
	if($cache[0] != $col['service_code']) {
		$cache[0] = $col['service_code'];
	}

	$group0[$col['service_code']] = 1;
}

//GROUP TOTAL
$grand_total = array(0,0,0,0);

print <<<END
<table width="100%" class="table_f">
	<tr>
		<th width="9%">SERVICE NO</th>
		<th width="10%">SERVICE DATE</th>
		<th width="9%">REG NO</th>
		<th width="15%">CUSTOMER</th>
		<th>MODEL &amp; SERIAL NUMBER</th>
		<th>DETAIL OF REPAIRS</th>
		<th>PART REPLACED</th>
		<th width="8%">DISC<br />(Rp)</th>
		<th width="8%">AMOUNT<br />(Rp)</th>
	</tr>\n
END;

foreach ($group0 as $total1 => $group1) {
	$total		= array(0,0,0,0);
	$total[2]	= $rd[$rdIdx][5];
	
	print "<tr>\n";
	cell_link("<span class=\"bar\">".$rd[$rdIdx][2]."</span>", ' style="'.$display_css[$rd[$rdIdx][6]].'" align="center" valign="top"', 
		' href="'.$rd[$rdIdx][7].'"');
	cell($rd[$rdIdx][3], ' style="'.$display_css[$rd[$rdIdx][6]].'" align="center" valign="top"');
	cell_link("<span class=\"bar\">".$rd[$rdIdx][4]."</span>", ' style="'.$display_css[$rd[$rdIdx][6]].'" align="center" valign="top"', 
		' href="'.$rd[$rdIdx][8].'"');
	cell($rd[$rdIdx][1], ' style="'.$display_css[$rd[$rdIdx][6]].'" align="left" valign="top"');

	$model_sql = "select * from ".ZKP_SQL."_tb_service_item where sv_code = '{$rd[$rdIdx][2]}'";
	$model_res = query($model_sql);
	print "\t<td style=\"{$display_css[$rd[$rdIdx][6]]}\" valign=\"top\">\n";
		if(numQueryRows($model_res) > 0) {
			print "\t\t<table width=\"150px\" class=\"table_l\">\n";
			print "\t\t\t<tr>\n";
			print "\t\t\t\t<td style=\"{$display_css[$rd[$rdIdx][6]]}\" width=\"60%\"></td>\n";
			print "\t\t\t\t<td style=\"{$display_css[$rd[$rdIdx][6]]}\" width=\"40%\"></td>\n";
			print "\t\t\t</tr>\n";
			while($col =& fetchRow($model_res)) {
				print "\t\t\t<tr>\n";
				print "\t\t\t\t<td style=\"{$display_css[$rd[$rdIdx][6]]}\" valign=\"top\">{$col[3]}</td>\n";
				print "\t\t\t\t<td style=\"{$display_css[$rd[$rdIdx][6]]}\" valign=\"top\">{$col[4]}</td>\n";
				print "\t\t\t</tr>\n";			
			}
			print "\t\t</table>\n";
		}
	print "\t</td>\n";

	$repair_sql = "select * from ".ZKP_SQL."_tb_service_repair where sv_code = '{$rd[$rdIdx][2]}'";
	$repair_res = query($repair_sql);
	print "\t<td style=\"{$display_css[$rd[$rdIdx][6]]}\" valign=\"top\">\n";
		if(numQueryRows($repair_res) > 0) {
			print "\t\t<table width=\"150px\" class=\"table_l\">\n";
			print "\t\t\t<tr>\n";
			print "\t\t\t\t<td style=\"{$display_css[$rd[$rdIdx][6]]}\" width=\"70%\"></td>\n";
			print "\t\t\t\t<td style=\"{$display_css[$rd[$rdIdx][6]]}\" width=\"30%\"></td>\n";
			print "\t\t\t</tr>\n";
			while($col =& fetchRow($repair_res)) {
				print "\t\t\t<tr>\n";
				print "\t\t\t\t<td style=\"{$display_css[$rd[$rdIdx][6]]}\" valign=\"top\">{$col[2]}</td>\n";
				print "\t\t\t\t<td style=\"{$display_css[$rd[$rdIdx][6]]}\" valign=\"top\" align=\"right\">".number_format($col[3]*$col[4])."</td>\n";
				print "\t\t\t</tr>\n";	
				$total[0] += $col[3]*$col[4];
			}
			print "\t\t</table>\n";
		}
	print "\t</td>\n";

	$replace_sql = "select * from ".ZKP_SQL."_tb_service_replace where sv_code = '{$rd[$rdIdx][2]}'";
	$replace_res = query($replace_sql);
	print "\t<td style=\"{$display_css[$rd[$rdIdx][6]]}\" valign=\"top\">\n";
		if(numQueryRows($replace_res) > 0) {
			print "\t\t<table width=\"150px\" class=\"table_l\">\n";
			print "\t\t\t<tr>\n";
			print "\t\t\t\t<td style=\"{$display_css[$rd[$rdIdx][6]]}\" width=\"70%\"></td>\n";
			print "\t\t\t\t<td style=\"{$display_css[$rd[$rdIdx][6]]}\" width=\"30%\"></td>\n";
			print "\t\t\t</tr>\n";
			while($col =& fetchRow($replace_res)) {
				print "\t\t\t<tr>\n";
				print "\t\t\t\t<td style=\"{$display_css[$rd[$rdIdx][6]]}\" valign=\"top\">{$col[2]}</td>\n";
				print "\t\t\t\t<td style=\"{$display_css[$rd[$rdIdx][6]]}\" valign=\"top\" align=\"right\">".number_format($col[3]*$col[4])."</td>\n";
				print "\t\t\t</tr>\n";
				$total[1] += $col[3]*$col[4];
			}
			print "\t\t</table>\n";
		}
	print "\t</td>\n";

	cell(number_format($rd[$rdIdx][5]), ' style="'.$display_css[$rd[$rdIdx][6]].'" align="right" valign="top"');		//total discount
	cell('',' style="'.$display_css[$rd[$rdIdx][6]].'"');																//total amount
	print "</tr>\n";
	print "<tr>\n";
	cell('Service <b>'.$rd[$rdIdx][2]."</b>", ' style="'.$display_css[$rd[$rdIdx][6]].'" align="right" colspan="5" style="color:darkblue"');
	cell(number_format($total[0]), ' style="'.$display_css[$rd[$rdIdx][6]].'" align="right" style="color:darkblue"');
	cell(number_format($total[1]), ' style="'.$display_css[$rd[$rdIdx][6]].'" align="right" style="color:darkblue"');
	cell(number_format($total[2]), ' style="'.$display_css[$rd[$rdIdx][6]].'" align="right" style="color:darkblue"');
	$total[3] = $total[0]+$total[1]-$total[2]; 
	cell(number_format($total[3]), ' style="'.$display_css[$rd[$rdIdx][6]].'" align="right" style="color:darkblue"');
	print "</tr>\n";

	$grand_total[0] += $total[0];
	$grand_total[1] += $total[1];
	$grand_total[2] += $total[2];
	$grand_total[3] += $total[3];
	$rdIdx++;
}

print "<tr height=\"20px\">\n";
cell("<b>TOTAL</b>", ' colspan="5" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($grand_total[0]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($grand_total[1]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($grand_total[2]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format($grand_total[3]), ' align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table><br />\n";
?>