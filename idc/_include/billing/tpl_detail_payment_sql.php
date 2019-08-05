<?php
//deposit
$dep_sql = "SELECT deg_amount FROM ".ZKP_SQL."_tb_deposit_group WHERE cus_code = '".$column['bill_cus_to']."'";
$dep_res =& query($dep_sql);
$dep_col =& fetchRow($dep_res);

//payment
$total_paid		= 0;
$used_deposit	= false;
$pay_sql = "
SELECT
  pay_idx,					--0
  to_char(pay_date, 'dd-Mon-yy') AS pay_date,	--1
  pay_paid,					--2
  pay_remark,				--3
  pay_inputed_by,			--4
  pay_inputed_timestamp,	--5
  pay_note,					--6
  pay_method,				--7
  CASE 
	WHEN pay_bank = 'BNIS' THEN 'BNI Syariah'
	WHEN pay_bank = 'DANAMON2' THEN 'DANAMON'
	WHEN pay_bank = 'BII3' THEN 'BII'
	ELSE pay_bank
  END AS pay_bank, 			--8
  pay_date AS date,			--9
  CASE 
	WHEN pay_is_deposit_cross_ref IS NULL THEN 0
	ELSE pay_is_deposit_cross_ref
  END AS pay_ref, 			--10
  pay_is_deposit_cross,		--11
  pay_paid_charge			--12
FROM ".ZKP_SQL."_tb_payment
WHERE bill_code = '$_code'
ORDER BY date, pay_idx";
$pay_res	=& query($pay_sql);

// deduction payment
$depa_sql = "SELECT *, CASE WHEN pade_type=1 THEN 'Return' WHEN pade_type=2 THEN 'Pot.Tagihan' END AS type FROM ".ZKP_SQL."_tb_payment_deduction WHERE bill_code = '$_code'";
$depa_res =& query($depa_sql);
$num_depa = ($depa_res > 0) ? numQueryRows($depa_res) : 0;

//variable
$pay_amount = array('delivery'=>array($column['bill_delivery_freight_charge'], 0, 0));	// total freight charge, paid, remain
echo "<pre>";
//var_dump($pay_amount);
echo "</pre>";
?>