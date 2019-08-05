/*
	This is the JavaScript file for the AJAX Suggest Tutorial

	You may use this code in your own projects as long as this 
	copyright is left	in place.  All code is provided AS-IS.
	This code is distributed in the hope that it will be useful,
 	but WITHOUT ANY WARRANTY; without even the implied warranty of
 	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	
	For the rest of the code visit http://www.DynamicAJAX.com
	
	Copyright 2006 Ryan Smith / 345 Technical / 345 Group.	

*/
//Gets the browser specific XmlHttpRequest Object
function getXmlHttpRequestObject() {
	if (window.XMLHttpRequest) {
		return new XMLHttpRequest();
	} else if(window.ActiveXObject) {
		return new ActiveXObject("Microsoft.XMLHTTP");
	} else {
		alert("Your Browser Sucks!\nIt's about time to upgrade don't you think?");
	}
}

//Our XmlHttpRequest object to get the auto suggest
var searchReq = getXmlHttpRequestObject();

//Called from keyup on the search textbox.
//Starts the AJAX request.
function searchSuggest() {
	if (searchReq.readyState == 4 || searchReq.readyState == 0) {
		var strBy  = escape(document.getElementById('cboSeachBy').value);
		var strTxt = escape(document.getElementById('txtSearch').value);
		searchReq.open("GET", '../../_script/searchSuggest/searchSuggest.php?searchTxt=' + strTxt + '&searchBy=' + strBy, true);
		searchReq.onreadystatechange = handleSearchSuggest; 
		searchReq.send(null);
	}
}

function searchStock(dept, val) {
	if (searchReq.readyState == 4 || searchReq.readyState == 0) {
		var it_code = val.substr(val.indexOf("[")+1, val.indexOf("]")-1);
		searchReq.open("GET", '../../_script/searchSuggest/searchStock.php?dept=' + dept + '&it_code=' + it_code, true);
		searchReq.onreadystatechange = handleSearchStock; 
		searchReq.send(null);
	}
}

//Called when the AJAX response is returned.
function handleSearchSuggest() {
	if (searchReq.readyState == 4) {
		var ss = document.getElementById('search_suggest')
		ss.innerHTML = '';
		var str = searchReq.responseText.split("\n");
		for(i=0; i < str.length - 1; i++) {
			//Build our element string.  This is cleaner using the DOM, but
			//IE doesn't support dynamically added attributes.
			var suggest = '<div onmouseover="javascript:suggestOver(this);" ';
			suggest += 'onmouseout="javascript:suggestOut(this);" ';
			suggest += 'onclick="javascript:setSearch(this.innerHTML);" ';
			suggest += 'class="suggest_link">' + str[i] + '</div>';
			ss.innerHTML += suggest;
		}
	}
}

function handleSearchStock() {
	if (searchReq.readyState == 4) {
		var it = new Array();
		var str = searchReq.responseText.split("\n");
		for(i=0; i < str.length - 1; i++) { it[i] = str[i]; }

		var f = window.document.frmCreateItem;
		f._wh_it_icat_midx.value	= it[1];
		f._wh_it_type.value			= it[3];
		f._wh_it_code.value			= it[0];
		f._wh_it_model_no.value		= it[2];
		f._wh_it_desc.value			= it[4];
		if(it[0]=='2101' || it[0]=='2100') {
			f._real_stock.value			= numFormatval(it[5]+'',2);
			f._est_stock.value			= numFormatval(it[6]+'',2);
		} else {
			f._real_stock.value			= numFormatval(it[5]+'',0);
			f._est_stock.value			= numFormatval(it[6]+'',0);
		}
		f._wh_it_function.value = "1";
		f._wh_it_qty.focus();
	}
}

//Mouse over function
function suggestOver(div_value) {
	div_value.className = 'suggest_link_over';
}
//Mouse out function
function suggestOut(div_value) {
	div_value.className = 'suggest_link';
}
//Click function
function setSearch(value) {
	document.getElementById('txtSearch').value = value;
	document.getElementById('search_suggest').innerHTML = '';
	window.document.frmSearch.btnSetStock.focus();
}
