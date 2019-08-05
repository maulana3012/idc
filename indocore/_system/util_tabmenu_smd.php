<?php
//MODULE: ADMIN
$top_menu['admin'][] = array("account", "list_group.php", "USER GROUP");
$top_menu['admin'][] = array("account", "list_account.php", "USER ACCOUNT");

//MODULE: APOTIK
$top_menu['apotik'][] = array("item", "list_item.php", "ITEM");
$top_menu['apotik'][] = array("customer", "list_customer.php", "CUSTOMER");
$top_menu['apotik'][] = array("billing", "input_billing_step_1.php", "BILLING & RETURN");
$top_menu['apotik'][] = array("summary", "daily_billing_by_group.php", "SUMMARY BILLING");
$top_menu['apotik'][] = array("other", "input_do_step_1.php", "OTHERS");
$top_menu['apotik'][] = array("stock", "list_stock.php", "STOCK");

//MODULE: DEALER
$top_menu['dealer'][] = array("item", "list_item.php", "ITEM");
$top_menu['dealer'][] = array("customer", "list_customer.php", "CUSTOMER");
$top_menu['dealer'][] = array("billing", "input_billing_step_1.php", "BILLING & RETURN");
$top_menu['dealer'][] = array("summary", "daily_billing_by_group.php", "SUMMARY BILLING");
$top_menu['dealer'][] = array("other", "input_do_step_1.php", "OTHERS");
$top_menu['dealer'][] = array("stock", "list_stock.php", "STOCK");

//MODULE: HOSPITAL
$top_menu['hospital'][] = array("item", "list_item.php", "ITEM");
$top_menu['hospital'][] = array("customer", "list_customer.php", "CUSTOMER");
$top_menu['hospital'][] = array("billing", "input_billing_step_1.php", "BILLING & RETURN");
$top_menu['hospital'][] = array("summary", "daily_billing_by_group.php", "SUMMARY BILLING");
$top_menu['hospital'][] = array("other", "input_do_step_1.php", "OTHERS");
$top_menu['hospital'][] = array("stock", "list_stock.php", "STOCK");

//MODULE: MARKETING
$top_menu['marketing'][] = array("item", "list_item.php", "ITEM");
$top_menu['marketing'][] = array("customer", "list_customer.php", "CUSTOMER");
$top_menu['marketing'][] = array("billing", "input_billing_step_1.php", "BILLING & RETURN");
$top_menu['marketing'][] = array("summary", "daily_billing_by_group.php", "SUMMARY BILLING");
$top_menu['marketing'][] = array("other", "input_do_step_1.php", "OTHERS");
$top_menu['marketing'][] = array("stock", "list_stock.php", "STOCK");

//MODULE: PHARMACEUTICAL
$top_menu['pharmaceutical'][] = array("item", "list_item.php", "ITEM");
$top_menu['pharmaceutical'][] = array("customer", "list_customer.php", "CUSTOMER");
$top_menu['pharmaceutical'][] = array("billing", "input_billing_step_1.php", "BILLING & RETURN");
$top_menu['pharmaceutical'][] = array("summary", "daily_billing_by_group.php", "SUMMARY BILLING");
$top_menu['pharmaceutical'][] = array("other", "input_do_step_1.php", "OTHERS");
$top_menu['pharmaceutical'][] = array("stock", "list_stock.php", "STOCK");

//MODULE: TENDER
$top_menu['tender'][] = array("item", "list_item.php", "ITEM");
$top_menu['tender'][] = array("customer", "list_customer.php", "CUSTOMER");
$top_menu['tender'][] = array("billing", "input_billing_step_1.php", "BILLING & RETURN");
$top_menu['tender'][] = array("summary", "daily_billing_by_group.php", "SUMMARY BILLING");
$top_menu['tender'][] = array("other", "input_do_step_1.php", "OTHERS");
$top_menu['tender'][] = array("stock", "list_stock.php", "STOCK");

//MODULE: ACCOUNTING
$top_menu['accounting'][] = array("summary", "daily_billing_by_group.php", "BILLING SUMMARY");
$top_menu['accounting'][] = array("summary", "debit_by_group.php", "DEBIT SUMMARY");
$top_menu['accounting'][] = array("summary", "payment_by_group.php", "PAYMENT SUMMARY");

//MODULE: REPORT
$top_menu['report'][] = array("summary", "daily_billing_by_group.php", "BILLING SUMMARY");
$top_menu['report'][] = array("summary", "debit_by_group.php", "DEBIT SUMMARY");
$top_menu['report'][] = array("summary", "payment_by_group.php", "PAYMENT SUMMARY");
$top_menu['report'][] = array("summary", "summary_by_channel.php", "SUMMARY");
$top_menu['report'][] = array("summary", "summary_monthly_bill_customer_by_amount.php", "MONTHLY REPORT");
$top_menu['report'][] = array("monthly", "summary_estimate_income_by_dept.php", "ESTIMATE SUMMARY");

//MODULE: INCENTIVE
$top_menu['incentive'][] = array("summary", "daily_incentive_by_team.php", "INCENTIVE");

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

//MODULE: PRODUCT
$top_menu['product'][] = array("warranty", "summary_warranty_by_item.php", "WARRANTY");

//MODULE: COMPLAIN
$top_menu['complain'][] = array("complain", "list_complain.php", "COMPLAIN");

//MODULE: LETTER
$top_menu['letter']["apotik"][]			= array("apotik", "input_letter.php", "LETTER");
$top_menu['letter']["hospital"][]		= array("dealer", "input_letter.php", "LETTER");
$top_menu['letter']["dealer"][]			= array("hospital", "input_letter.php", "LETTER");
$top_menu['letter']["pharmaceutical"][]	= array("pharmaceutical", "input_letter.php", "LETTER");
$top_menu['letter']["general"][]		= array("general", "input_letter.php", "LETTER");
$top_menu['letter']["management"][]		= array("management", "input_letter.php", "LETTER");
$top_menu['letter']["summary"][]		= array("summary", "list_letter_summary.php", "LETTER");
?>