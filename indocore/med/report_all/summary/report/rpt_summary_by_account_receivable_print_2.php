<?php
print <<<END
	<span class="comment" align=>(In Rupiah)</span>
	<table width="1735px" class="table_f">
		<tr>
			<th width="115px" rowspan="2">TEAM</th>
			<th width="70px" rowspan="2">CASH</th>
			<th width="420px" colspan="6">CHECK</th>
			<th width="630px" colspan="9">TRANSFER</th>
			<th width="420px" colspan="6">GIRO</th>
			<th width="80px" rowspan="2">TOTAL</th>
		</tr>
		<tr>
			<th width="70px"><i>OLD</i></th>
			<th width="70px">BII2</th>
			<th width="70px">BII3</th>
			<th width="70px">DANAMON</th>
			<th width="70px">DANAMON2</th>
			<th width="70px">BNI SYR</th>

			<th width="70px">BCA1</th>
			<th width="70px">BCA2</th>
			<th width="70px">MANDIRI</th>
			<th width="70px">BII1</th>
			<th width="70px">BII2</th>
			<th width="70px">BII3</th>
			<th width="70px">DANAMON</th>
			<th width="70px">DANAMON2</th>
			<th width="70px">BNI SYR</th>

			<th width="70px"><i>OLD</i></th>
			<th width="70px">BII2</th>
			<th width="70px">BII3</th>			
			<th width="70px">DANAMON</th>
			<th width="70px">DANAMON2</th>			
			<th width="70px">BNI SYR</th>
		</tr>\n

END;

include "rpt_summary_by_account_receivable_print_3.php";
$result =& query($sql);
$amount		 = array();
$tot_channel = 0;
while($col =& fetchRowAssoc($result)) {
	if(isset($amount[$col['channel']][trim($col['method'])][trim($col['bank'])])) {
		$amount[$col['channel']][trim($col['method'])][trim($col['bank'])] += $col['amount'];
		$tot_channel += $col['amount'];
	} else {
		$amount[$col['channel']][trim($col['method'])][trim($col['bank'])] = $col['amount'];
		$tot_channel += $col['amount'];
	}
}

for($i=0; $i<23; $i++) $total[$i] = 0;
foreach ($channel as $value => $key) {

	$t_total = 0;
	foreach($amount[$value] as $a=>$b) { foreach($b as $c) $t_total += $c; }

	print "<tr>\n";
	cell($channel[$value]);	// Channel
	cell((isset($amount[$value]['cash']['']))			? number_format((double)$amount[$value]['cash'][''])			: '', ' align="right"');
	cell((isset($amount[$value]['check']['']))			? number_format((double)$amount[$value]['check'][''])			: '', ' align="right"');
	cell((isset($amount[$value]['check']['BII2']))		? number_format((double)$amount[$value]['check']['BII2'])		: '', ' align="right"');
	cell((isset($amount[$value]['check']['BII3']))		? number_format((double)$amount[$value]['check']['BII3'])		: '', ' align="right"');
	cell((isset($amount[$value]['check']['DANAMON']))	? number_format((double)$amount[$value]['check']['DANAMON'])	: '', ' align="right"');
	cell((isset($amount[$value]['check']['DANAMON2']))	? number_format((double)$amount[$value]['check']['DANAMON2'])	: '', ' align="right"');
	cell((isset($amount[$value]['check']['BNIS']))		? number_format((double)$amount[$value]['check']['BNIS'])		: '', ' align="right"');
	cell((isset($amount[$value]['transfer']['BCA1']))	? number_format((double)$amount[$value]['transfer']['BCA1'])	: '', ' align="right"');
	cell((isset($amount[$value]['transfer']['BCA2']))	? number_format((double)$amount[$value]['transfer']['BCA2'])	: '', ' align="right"');
	cell((isset($amount[$value]['transfer']['MANDIRI']))? number_format((double)$amount[$value]['transfer']['MANDIRI']) : '', ' align="right"');
	cell((isset($amount[$value]['transfer']['BII1']))	? number_format((double)$amount[$value]['transfer']['BII1'])	: '', ' align="right"');
	cell((isset($amount[$value]['transfer']['BII2']))	? number_format((double)$amount[$value]['transfer']['BII2'])	: '', ' align="right"');
	cell((isset($amount[$value]['transfer']['BII3']))	? number_format((double)$amount[$value]['transfer']['BII3'])	: '', ' align="right"');
	cell((isset($amount[$value]['transfer']['DANAMON']))? number_format((double)$amount[$value]['transfer']['DANAMON'])	: '', ' align="right"');
	cell((isset($amount[$value]['transfer']['DANAMON2']))? number_format((double)$amount[$value]['transfer']['DANAMON2'])	: '', ' align="right"');
	cell((isset($amount[$value]['transfer']['BNIS']))	? number_format((double)$amount[$value]['transfer']['BNIS'])	: '', ' align="right"');
	cell((isset($amount[$value]['giro']['']))			? number_format((double)$amount[$value]['giro'][''])			: '', ' align="right"');
	cell((isset($amount[$value]['giro']['BII2']))		? number_format((double)$amount[$value]['giro']['BII2'])		: '', ' align="right"');
	cell((isset($amount[$value]['giro']['BII3']))		? number_format((double)$amount[$value]['giro']['BII3'])		: '', ' align="right"');
	cell((isset($amount[$value]['giro']['DANAMON']))	? number_format((double)$amount[$value]['giro']['DANAMON'])		: '', ' align="right"');
	cell((isset($amount[$value]['giro']['DANAMON2']))	? number_format((double)$amount[$value]['giro']['DANAMON2'])	: '', ' align="right"');
	cell((isset($amount[$value]['giro']['BNIS']))		? number_format((double)$amount[$value]['giro']['BNIS'])		: '', ' align="right"');
	cell(number_format((double)$t_total), ' align="right"');
	print "</tr>\n";

	$total[0]  += (isset($amount[$value]['cash']['']))				? $amount[$value]['cash'][''] 				: 0;
	$total[1]  += (isset($amount[$value]['check']['']))				? $amount[$value]['check']['']				: 0;
	$total[2]  += (isset($amount[$value]['check']['BII2']))			? $amount[$value]['check']['BII2']			: 0;
	$total[3]  += (isset($amount[$value]['check']['BII3']))			? $amount[$value]['check']['BII3']			: 0;
	$total[4]  += (isset($amount[$value]['check']['DANAMON']))		? $amount[$value]['check']['DANAMON']		: 0;
	$total[5]  += (isset($amount[$value]['check']['DANAMON2']))		? $amount[$value]['check']['DANAMON2']		: 0;
	$total[6]  += (isset($amount[$value]['check']['BNIS']))			? $amount[$value]['check']['BNIS']			: 0;
	$total[7]  += (isset($amount[$value]['transfer']['BCA1']))		? $amount[$value]['transfer']['BCA1']		: 0;
	$total[8]  += (isset($amount[$value]['transfer']['BCA2']))		? $amount[$value]['transfer']['BCA2']		: 0;
	$total[9]  += (isset($amount[$value]['transfer']['MANDIRI']))	? $amount[$value]['transfer']['MANDIRI'] 	: 0;
	$total[10] += (isset($amount[$value]['transfer']['BII1']))		? $amount[$value]['transfer']['BII1']		: 0;
	$total[11] += (isset($amount[$value]['transfer']['BII2']))		? $amount[$value]['transfer']['BII2']		: 0;
	$total[12] += (isset($amount[$value]['transfer']['BII3']))		? $amount[$value]['transfer']['BII3']		: 0;
	$total[13] += (isset($amount[$value]['transfer']['DANAMON']))	? $amount[$value]['transfer']['DANAMON']	: 0;
	$total[14] += (isset($amount[$value]['transfer']['DANAMON2']))	? $amount[$value]['transfer']['DANAMON2']	: 0;
	$total[15] += (isset($amount[$value]['transfer']['BNIS']))		? $amount[$value]['transfer']['BNIS']		: 0;
	$total[16] += (isset($amount[$value]['giro']['']))				? $amount[$value]['giro']['']				: 0;
	$total[17] += (isset($amount[$value]['giro']['BII2']))			? $amount[$value]['giro']['BII2']			: 0;
	$total[18] += (isset($amount[$value]['giro']['BII3']))			? $amount[$value]['giro']['BII3']			: 0;
	$total[19] += (isset($amount[$value]['giro']['DANAMON']))		? $amount[$value]['giro']['DANAMON']		: 0;
	$total[20] += (isset($amount[$value]['giro']['DANAMON2']))		? $amount[$value]['giro']['DANAMON2']		: 0;
	$total[21] += (isset($amount[$value]['giro']['BNIS']))			? $amount[$value]['giro']['BNIS']			: 0;
	$total[22] += $t_total;

}
/*
echo "<pre>";
var_dump($total);
echo "</pre>";
*/
print "<tr>\n";
cell("<b>TOTAL</b>", ' rowspan="2" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$total[0]), ' rowspan="2" align="right" style="color:brown; background-color:lightyellow"');
for($i=1; $i<22; $i++) cell(number_format((double)$total[$i]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$total[22]), ' rowspan="2" align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "<tr>\n";
cell(number_format((double)$total[1]+$total[2]+$total[3]+$total[4]+$total[5]+$total[6]), ' colspan="6" align="center" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$total[7]+$total[8]+$total[9]+$total[10]+$total[11]+$total[12]+$total[13]+$total[14]+$total[15]), ' colspan="9" align="center" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$total[16]+$total[17]+$total[18]+$total[19]+$total[20]+$total[21]), ' colspan="6" align="center" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>";
?>