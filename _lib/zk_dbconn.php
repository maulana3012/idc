<?php
/**
* Copyright(c) 2005 PT. ZONEKOM All right reserved
* Homepage http://www.zonekom.com
* Contact us dskim@indosat.net.id
*
* author : dskim
*
* $Id: zk_dbconn.php,v 1.2 2007/06/12 20:08:02 dskim Exp $
*/

class strSelect {
	var $selectCaluse	= "";
	var $whereCaluse 	= false;
	var $groupByCaluse	= false;
	var $orderByCaluse	= false;
	var $strQueryString = false;
	var $tempWhere	= array();

	function strSelect($str) {
		$this->selectCaluse = $str;
	}
	
	function setWhere($format, $arr, $operator) {
		$tmp1 = array();
		$tmp2 = array();

		foreach ($arr as $key => $val) {
			if (isset($_REQUEST[$val]) && $_REQUEST[$val] != "") {
				$tmp1[] = sprintf($format, $key, $_REQUEST[$val]);
				$tmp2[]	= $val . "=" . urlencode($_REQUEST[$val]);
			}
		}

		if(count($tmp1) == 1) {
			$this->whereCaluse .= $tmp1[0];
		} elseif(count($tmp1) > 1) {
			$this->whereCaluse .= "(" . implode(" $operator ", $tmp1) . ")";
		}

		if(count($tmp2) > 0) {
			$this->strQueryString .= implode("&", $tmp2) . "&";
		}
	}

	//For attach where condition
	function setConnector($str) {
		$this->whereCaluse .= " $str ";
	}

	function setOrderBy($str) {
		$this->orderByCaluse = $str;
	}

	function setGroupBy($str) {
		$this->groupByCaluse = $str;
	}

	function getSql() {
		$rtn = $this->selectCaluse .
				(($this->whereCaluse) ? " WHERE " . $this->whereCaluse : "") .
				(($this->groupByCaluse) ? " GROUP BY " . $this->groupByCaluse : "") .
				(($this->orderByCaluse) ? " ORDER BY " . $this->orderByCaluse : "");

		return $rtn;
	}
	
	//must be used after addFilter
	function getQueryString() {
		return $this->strQueryString;
	}
}

require_once "zk_{$dbms}". ".php";
?>
