<?php
//MODULE: ADMIN
$top_menu['admin'][] = array("account", "list_group.php", "USER GROUP");
$top_menu['admin'][] = array("account", "list_account.php", "USER ACCOUNT");

//MODULE: REPORT
$top_menu['report_all'][] = array("summary", "daily_billing_by_group.php", "BILLING SUMMARY");
$top_menu['report_all'][] = array("summary", "debit_by_group.php", "DEBIT SUMMARY");
$top_menu['report_all'][] = array("summary", "payment_by_group.php", "PAYMENT SUMMARY");
$top_menu['report_all'][] = array("summary", "summary_by_channel.php", "SUMMARY");
$top_menu['report_all'][] = array("summary", "summary_monthly_bill_customer_by_amount.php", "MONTHLY REPORT");
$top_menu['report_all'][] = array("monthly", "summary_estimate_income_by_dept.php", "ESTIMATE SUMMARY");

//MODULE: INCENTIVE
$top_menu['incentive'][] = array("summary", "daily_incentive_by_team.php", "INCENTIVE");
/*
//MODULE: PURCHASING
$top_menu['purchasing'][] = array("item", "list_item.php", "ITEM");
$top_menu['purchasing'][] = array("basic_data", "list_supplier.php", "BASIC DATA");
$top_menu['purchasing'][] = array("summary", "summary_by_item.php", "SUMMARY BILLING");
$top_menu['purchasing'][] = array("purchasing", "summary_po_by_supplier.php", "P O");
$top_menu['purchasing'][] = array("packing_list", "summary_pl_by_supplier.php", "PACKING LIST");
$top_menu['purchasing'][] = array("setup_stock", "list_initial_stock.php", "<img src=\"../../_images/icon/setting_mini.gif\"> SETUP STOCK");
$top_menu['purchasing'][] = array("stock", "list_stock.php", "STOCK");
$top_menu['purchasing'][] = array("delivery", "daily_booking_by_group.php", "BOOKING & OUTGOING");
$top_menu['purchasing'][] = array("delivery", "daily_return_by_group.php", "RETURN");

//MODULE: WAREHOUSE
$top_menu['warehouse'][] = array("item", "list_item.php", "ITEM");
$top_menu['warehouse'][] = array("basic_data", "list_supplier.php", "BASIC DATA");
$top_menu['warehouse'][] = array("purchasing", "summary_po_by_supplier.php", "PO LOCAL");
$top_menu['warehouse'][] = array("packing_list", "summary_pl_by_supplier.php", "PACKING LIST");
$top_menu['warehouse'][] = array("stock", "list_stock.php", "STOCK");
$top_menu['warehouse'][] = array("demo", "list_demo.php", "DEMO");
$top_menu['warehouse'][] = array("delivery", "daily_booking_by_group.php", "BOOKING & OUTGOING");
$top_menu['warehouse'][] = array("delivery", "daily_return_by_group.php", "RETURN");

//MODULE: DEMO
$top_menu['demo'][] = array("stock", "list_demo.php", "DEMO STOCK");
$top_menu['demo'][] = array("request_demo", "daily_request_demo_by_reference.php", "REQUEST STOCK");
$top_menu['demo'][] = array("using_demo", "daily_booking_demo_by_reference.php", "USING DEMO");

//MODULE: EVENT
$top_menu['event'][] = array("event", "input_event_step_1.php", "EVENT");

//MODULE: WARRANTY
$top_menu['product'][] = array("warranty", "summary_warranty_by_item.php", "WARRANTY");
*/
?>