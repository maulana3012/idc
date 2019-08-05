<?php
print <<<END
	<table width="100%" class="table_f">
		<tr>
			<th>TEAM</th>
			<th>BILLING<br />(Rp)</th>
			<th width="7%">COMPOSITION<br />RATE</th>
			<th width="12%">FULL BILLING<br />(Rp)</th>
			<th width="12%">BILLING REALTIME<br />(Rp)</th>
			<th width="12%">PAYMENT REALTIME<br />(Rp)</th>
			<th width="12%">PAID<br />BILLING</th>
			<th width="12%">REMAIN<br />BILLING</th>
		</tr>\n
END;

foreach ($channel as $value => $key) {

	//PRINT VALUE
	print "<tr>\n";	
	print "\t<td><a href=\"javascript:seeDetail('$value')\">$key</a></td>\n";
	cell((empty($amount[$value][0])) ? 0 : number_format((double)$amount[$value][0]), ' align="right"');			//billing before vat
	cell((empty($amount[$value][1])) ? 0.00 : number_format((double)$amount[$value][1],2)."%", ' align="right"');	//rate
	cell((empty($amount[$value][2])) ? 0 : number_format((double)$amount[$value][2]), ' align="right"');			//billing after vat
	cell((empty($amount[$value][3])) ? 0 : number_format((double)$amount[$value][3]), ' align="right"');			//billing after vat
	cell((empty($amount[$value][4])) ? 0 : number_format((double)$amount[$value][4]), ' align="right"');			//paid realtime
	cell((empty($amount[$value][5])) ? 0 : number_format((double)$amount[$value][5]), ' align="right"');			//paid billing
	cell((empty($amount[$value][6])) ? 0 : number_format((double)$amount[$value][6]), ' align="right"');			//remain billing
	print "</tr>\n";

	$amounttotal[0] += (empty($amount[$value][0])) ? 0 : $amount[$value][0];	
	$amounttotal[1] += (empty($amount[$value][1])) ? 0 : $amount[$value][1];
	$amounttotal[2] += (empty($amount[$value][2])) ? 0 : $amount[$value][2];
	$amounttotal[3] += (empty($amount[$value][3])) ? 0 : $amount[$value][3];
	$amounttotal[4] += (empty($amount[$value][4])) ? 0 : $amount[$value][4];
	$amounttotal[5] += (empty($amount[$value][5])) ? 0 : $amount[$value][5];
	$amounttotal[6] += (empty($amount[$value][6])) ? 0 : $amount[$value][6];
}

print "\t<tr>\n";
cell('TOTAL BILLING', ' align="right" style="color:brown; background-color:lightyellow"');
cell(number_format((double)$amounttotal[0]), ' align="right" style="color:brown; background-color:lightyellow"');	//billing before vat
cell(number_format((double)$amounttotal[1],2)."%", ' align="right" style="color:brown; background-color:lightyellow"');	//rate
cell(number_format((double)$amounttotal[2]), ' align="right" style="color:brown; background-color:lightyellow"');	//billing after vat
cell(number_format((double)$amounttotal[3]), ' align="right" style="color:brown; background-color:lightyellow"');	//paid realtime
cell(number_format((double)$amounttotal[4]), ' align="right" style="color:brown; background-color:lightyellow"');	//paid realtime
cell(number_format((double)$amounttotal[5]), ' align="right" style="color:brown; background-color:lightyellow"');	//paid billing
cell(number_format((double)$amounttotal[6]), ' align="right" style="color:brown; background-color:lightyellow"');	//remain billing
print "\t</tr>\n";
print "</table><br />\n";
?>