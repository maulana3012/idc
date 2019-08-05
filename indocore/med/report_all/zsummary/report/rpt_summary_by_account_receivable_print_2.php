<?php
print <<<END
	<span class="comment" align=>(In Rupiah)</span>
	<table width="1200px" class="table_f">
		<tr>
			<th width="115px" rowspan="2">TEAM</th>
			<th width="70px" rowspan="2">CASH</th>
			<th width="280px" colspan="4">CHECK</th>
			<th width="490px" colspan="7">TRANSFER</th>
			<th width="280px" colspan="4">GIRO</th>
			<th width="80px" rowspan="2">TOTAL</th>
		</tr>
		<tr>
			<th width="70px"><i>OLD</i></th>
			<th width="70px">BII2</th>
			<th width="70px">DANAMON</th>
			<th width="70px">BNI SYR</th>
			<th width="70px">BCA1</th>
			<th width="70px">BCA2</th>
			<th width="70px">MANDIRI</th>
			<th width="70px">BII1</th>
			<th width="70px">BII2</th>
			<th width="70px">DANAMON</th>
			<th width="70px">BNI SYR</th>
			<th width="70px"><i>OLD</i></th>
			<th width="70px">BII2</th>
			<th width="70px">DANAMON</th>
			<th width="70px">BNI SYR</th>
		</tr>\n

END;

for($i=0; $i<17; $i++) $total[$i] = 0;
foreach ($channel as $value => $key) {


	include "rpt_summary_by_account_receivable_print_3.php";
	$result =& query($sql);

	$amount		 = array();
	$tot_channel = 0;
	while($col =& fetchRowAssoc($result)) {
		if(isset($amount[trim($col['method'])][trim($col['bank'])])) {
			$amount[trim($col['method'])][trim($col['bank'])] += $col['amount'];
			$tot_channel += $col['amount'];
		} else {
			$amount[trim($col['method'])][trim($col['bank'])] = $col['amount'];
			$tot_channel += $col['amount'];
		}
	}

	print "<tr>\n";
	cell($channel[$value]);	// Channel
	cell((isset($amount['cash']['']))			? number_format((double)$amount['cash'][''])			: '', ' align="right"');
	cell((isset($amount['check']['']))			? number_format((double)$amount['check'][''])			: '', ' align="right"');
	cell((isset($amount['check']['BII2']))		? number_format((double)$amount['check']['BII2'])		: '', ' align="right"');
	cell((isset($amount['check']['DANAMON']))	? number_format((double)$amount['check']['DANAMON'])	: '', ' align="right"');
	cell((isset($amount['check']['BNIS']))		? number_format((double)$amount['check']['BNIS'])		: '', ' align="right"');
	cell((isset($amount['transfer']['BCA1']))	? number_format((double)$amount['transfer']['BCA1'])	: '', ' align="right"');
	cell((isset($amount['transfer']['BCA2']))	? number_format((double)$amount['transfer']['BCA2'])	: '', ' align="right"');
	cell((isset($amount['transfer']['MANDIRI']))? number_format((double)$amount['transfer']['MANDIRI']) : '', ' align="right"');
	cell((isset($amount['transfer']['BII1']))	? number_format((double)$amount['transfer']['BII1'])	: '', ' align="right"');
	cell((isset($amount['transfer']['BII2']))	? number_format((double)$amount['transfer']['BII2'])	: '', ' align="right"');
	cell((isset($amount['transfer']['DANAMON']))? number_format((double)$amount['transfer']['DANAMON'])	: '', ' align="right"');
	cell((isset($amount['transfer']['BNIS']))	? number_format((double)$amount['transfer']['BNIS'])	: '', ' align="right"');
	cell((isset($amount['giro']['']))			? number_format((double)$amount['giro'][''])			: '', ' align="right"');
	cell((isset($amount['giro']['BII2']))		? number_format((double)$amount['giro']['BII2'])		: '', ' align="right"');
	cell((isset($amount['giro']['DANAMON']))	? number_format((double)$amount['giro']['DANAMON'])		: '', ' align="right"');
	cell((isset($amount['giro']['BNIS']))		? number_format((double)$amount['giro']['BNIS'])		: '', ' align="right"');
	cell(number_format((double)$tot_channel), ' align="right"');
	print "</tr>\n";

	$total[0]  += (isset($amount['cash']['']))				? $amount['cash'][''] 				: 0;
	$total[1]  += (isset($amount['check']['']))				? $amount['check']['']				: 0;
	$total[2]  += (isset($amount['check']['BII2']))			? $amount['check']['BII2']			: 0;
	$total[3]  += (isset($amount['check']['DANAMON']))		? $amount['check']['DANAMON']		: 0;
	$total[4]  += (isset($amount['check']['BNIS']))			? $amount['check']['BNIS']			: 0;
	$total[5]  += (isset($amount['transfer']['BCA1']))		? $amount['transfer']['BCA1']		: 0;
	$total[6]  += (isset($amount['transfer']['BCA2']))		? $amount['transfer']['BCA2']		: 0;
	$total[7]  += (isset($amount['transfer']['MANDIRI']))	? $amount['transfer']['MANDIRI'] 	: 0;
	$total[8]  += (isset($amount['transfer']['BII1']))		? $amount['transfer']['BII1']		: 0;
	$total[9]  += (isset($amount['transfer']['BII2']))		? $amount['transfer']['BII2']		: 0;
	$total[10] += (isset($amount['transfer']['DANAMON']))	? $amount['transfer']['DANAMON']	: 0;
	$total[11] += (isset($amount['transfer']['BNIS']))		? $amount['transfer']['BNIS']		: 0;
	$total[12] += (isset($amount['giro']['']))				? $amount['giro']['']				: 0;
	$total[13] += (isset($amount['giro']['BII2']))			? $amount['giro']['BII2']			: 0;
	$total[14] += (isset($amount['giro']['DANAMON']))		? $amount['giro']['DANAMON']		: 0;
	$total[15] += (isset($amount['giro']['BNIS']))			? $amount['giro']['BNIS']			: 0;
	$total[16] += $tot_channel;

}

print "<tr>\n";
cell("<b>TOTAL</b>", ' rowspan="2" align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$total[0]), ' rowspan="2" align="right" style="color:brown; background-color:lightyellow"');
for($i=1; $i<16; $i++) cell(number_format((double)$total[$i]), ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$total[16]), ' rowspan="2" align="right" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "<tr>\n";
cell(number_format((double)$total[1]+$total[2]+$total[3]+$total[4]), ' colspan="4" align="center" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$total[5]+$total[6]+$total[7]+$total[8]+$total[9]+$total[10]+$total[11]), ' colspan="7" align="center" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$total[12]+$total[13]+$total[14]+$total[15]), ' colspan="4" align="center" style="color:brown; background-color:lightyellow"');
print "</tr>\n";
print "</table>";
?>