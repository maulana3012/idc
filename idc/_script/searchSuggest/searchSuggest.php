<?php
/*
	This is the back-end PHP file for the AJAX Suggest Tutorial
	
	You may use this code in your own projects as long as this 
	copyright is left	in place.  All code is provided AS-IS.
	This code is distributed in the hope that it will be useful,
 	but WITHOUT ANY WARRANTY; without even the implied warranty of
 	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	
	For the rest of the code visit http://www.DynamicAJAX.com
	
	Copyright 2006 Ryan Smith / 345 Technical / 345 Group.	
*/

//Send some headers to keep the user's browser from caching the response.
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" ); 
header("Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" ); 
header("Cache-Control: no-cache, must-revalidate" ); 
header("Pragma: no-cache" );
header("Content-Type: text/xml; charset=utf-8");

//Get our database abstraction file
//require('database.php');
require_once "../../zk_config.php";
require_once LIB_DIR . "zk_dbconn.php";

///Make sure that a value was sent.
if (isset($_GET['searchBy']) && $_GET['searchBy'] != '' &&
	isset($_GET['searchTxt']) && $_GET['searchTxt'] != ''
	) {
	//Add slashes to any quotes to avoid SQL problems.
	$searchTxt	= addslashes($_GET['searchTxt']);
	$searchBy	= addslashes($_GET['searchBy']);
	//Get every page title for the site.
	$sql	= "SELECT it_code , '[' || ltrim(it_code) || '] ' || it_model_no AS it_model_no FROM ".ZKP_SQL."_tb_item WHERE it_status = 0 AND $searchBy ILIKE '%$searchTxt%' ORDER BY it_code";
	$result	=& query($sql);
	while($suggest = fetchRowAssoc($result)) {
		//Return each page title seperated by a newline.
		echo $suggest['it_model_no'] . "\n";
	}
}
?>