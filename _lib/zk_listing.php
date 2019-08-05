<?php
/**
* Copyright(c) 2005 PT. ZONEKOM All right reserved
* Homepage http://www.zonekom.com
* Contact us dskim@indosat.net.id
*
* author : dskim
*
* You must make a instance like below in order to work a deconstructor.
* $pg =& new strPaging();
* $Id: zk_listing.php,v 1.1.1.1 2007/06/11 11:19:23 dskim Exp $
*/

if (!is_object($dbconn)){
    require_once APP_DIR . "../_lib/zk_dbconn.php";
}

class strPaging
{
	var $strGet;

    function strPaging($strSelect, $numScreenRows = 20)
	{
        global $dbconn;
        $result =& query($strSelect);

        if (isZKError($result)) {
            die($result->getMessage());
        }

        $this->strSelect        = $strSelect;
        $this->strFirst         = "&lt;&lt;";
        $this->strLast          = "&gt;&gt;";
        $this->strPrevDiv       = "&lt;";
        $this->strNextDiv       = "&gt;";
        $this->strPrev          = "prev";
        $this->strNext          = "next";
        $this->strNum           = "%d";
		$this->strCurrentNum    = "<strong>[%s]</strong>";
        $this->strSep           = "| ";
		$this->numScreenDivs    = 10;
		$this->numScreenRows    = $numScreenRows;
		$this->numTotalRows     = numQueryRows($result);
		$this->numTotalPages    = ($this->numScreenRows<=0) ? 0 : ceil($this->numTotalRows / $this->numScreenRows);
   
        freeResults($result);
        unset($result);

        if (!array_key_exists('curpage', $_GET)) {
            $this->curPage = 1;
        } else {
            $this->curPage = (int) $_GET['curpage'];

            if ($this->curPage > $this->numTotalPages) {
                $this->curPage = $this->numTotalPages;
            }
        }
		$this->numTotalDivs     = ceil($this->numTotalPages / $this->numScreenDivs);
        $this->numCurDiv        = ceil($this->curPage / $this->numScreenDivs);
		$this->startDivPageNo   = ($this->numCurDiv - 1) * $this->numScreenDivs + 1;
		$this->endDivPageNo     = $this->numCurDiv * $this->numScreenDivs;
/*        
        echo "numScreenDiv:".$this->numScreenDivs." ".
        "numScreenRows:".$this->numScreenRows." ".
        "numTotalRows:".$this->numTotalRows." ".
        "numTotalPages:".$this->numTotalPages." ".
        "numTotalDivs:".$this->numTotalDivs." ".
        "numCurDiv:".$this->numCurDiv." ".
        "endDivPageNo:".$this->endDivPageNo." ".
        "startDivPageNo:".$this->startDivPageNo;
*/
	}
	

    
    function _strPaging()
    {

    }

    
    function getListQuery()
    {
        $last_row = $this->curPage * $this->numScreenRows;
        $this->serial = $last_row - $this->numScreenRows;

//for mysql 3.23
//		return $this->strSelect . " LIMIT " . $this->serial . ", ". $this->numScreenRows;

//cannot use mysql 3.23
		return $this->strSelect . " LIMIT " . $this->numScreenRows . " OFFSET ". $this->serial;
    }

    function setStrGet($strGet)
	{
		$this->strGet = $strGet;
	}

	function _first()
	{
		if ($this->curPage == 1) {
			$strFirst = $this->strFirst."&nbsp;";
		} else {
			$strFirst = sprintf("<a href=\"%s?curpage=1&%s\"> %s </a>&nbsp;",
				$_SERVER['PHP_SELF'],
				$this->strGet,
				$this->strFirst
			);
		}
		
		return $strFirst;
	}

	function _prevDiv()
	{
		if ($this->numCurDiv == 1) {
			$strPrevDiv = $this->strPrevDiv . "&nbsp;";
        } else {
			$strPrevDiv = sprintf("<a href=\"%s?curpage=%d&%s\">%s</a>&nbsp;",
				$_SERVER['PHP_SELF'],
				($this->startDivPageNo - $this->numScreenDivs),
				$this->strGet,
				$this->strPrevDiv
			);
        }
		
		return $strPrevDiv;
	}
	
	function _prev()
	{
		if ($this->curPage == 1) {
			$strPrev = $this->strPrev."&nbsp;";
        } else {
            $strPrev = sprintf("<a href=\"%s?curpage=%d&%s\">%s</a>&nbsp;",
				$_SERVER['PHP_SELF'],
				$this->curPage - 1,
				$this->strGet,
				$this->strPrev
			);
        }

        return $strPrev . '&nbsp;&nbsp;';
	}
	
	function _numLink()
	{
		$bufNum = "";
		$startDivPageNo = $this->startDivPageNo;
        while($startDivPageNo <= $this->endDivPageNo
		      && $startDivPageNo <= $this->numTotalPages) {

			if ($startDivPageNo == $this->curPage) {
				$bufNum .= sprintf($this->strCurrentNum, $startDivPageNo) ."&nbsp;";
			} else {
				$bufNum .= sprintf("<a href=\"%s?curpage=%d&%s\">%s</a>&nbsp;",
					$_SERVER['PHP_SELF'],
					$startDivPageNo,
					$this->strGet,
					sprintf($this->strNum, $startDivPageNo)
				);
			}
			$startDivPageNo += 1;
						
			if ($startDivPageNo <= $this->endDivPageNo && $startDivPageNo <= $this->numTotalPages) {
				$bufNum .= $this->strSep;
			}

		}
		
		return $bufNum;
	}
	
	function _next()
	{
		if ($this->curPage == $this->numTotalPages) {
			$strNext = $this->strNext."&nbsp;";
		} else {
			$strNext = sprintf("<a href=\"%s?curpage=%d&%s\">%s</a>&nbsp;",
				$_SERVER['PHP_SELF'],
				$this->curPage + 1,
				$this->strGet,
				$this->strNext
			);
		}
		
		return '&nbsp;&nbsp;' . $strNext;
	}

	function _nextDiv()
	{
		if ($this->numCurDiv == $this->numTotalDivs) {
			$strNextDiv = $this->strNextDiv ."&nbsp;";
		} else {
			$strNextDiv = sprintf("<a href=\"%s?curpage=%d&%s\">%s</a>&nbsp;",
				$_SERVER['PHP_SELF'],
				$this->endDivPageNo + 1,
				$this->strGet,
				$this->strNextDiv
			);
		}	
			
		return $strNextDiv;
	}

	function _last()
	{
		if ($this->curPage == $this->numTotalPages) {
			$strLast = $this->strLast;
		} else {
			$strLast = sprintf("<a href=\"%s?curpage=%d&%s\">%s</a>",
				$_SERVER['PHP_SELF'],
				$this->numTotalPages,
				$this->strGet,
				$this->strLast
			);
		}
		
		return $strLast;
	}
	
	function putPaging()
	{
		echo $this->getPaging();
	}
	
	function getPaging()
	{
		$paging = '';
		if ($this->numTotalRows > $this->numScreenRows){

            $paging = $this->_first() .
 			          $this->_prevDiv() .
			          $this->_prev() .
			          $this->_numLink() .
			          $this->_next() .
			          $this->_nextDiv() .
			          $this->_last();
		}
		
		return $paging;
	}	
}

/*
$arrHeader[] = array("check", 20, "left");
$arrHeader[] = array("num", 20, "left");
$arrHeader[] = array("name", 20, "left");
$arrHeader[] = array("email", 20, "center");
$arrHeader[] = array("userid", 20, "center");
//$arrHeader[] = array("name", 20, "left", "<select name='seluser_%s'><option>1<option>2</select>", "idx");

$td = "\n<td><input type='checkbox' id='chk_%s'>\n<td>%s\n<td>%s\n<td>%s\n<td>%s\n"; 

//$print =& new printTable($arrHeader, $td, "SELECT mb_idx, mb_name, mb_email, mb_userid FROM tb_member");

//$print->putPaging();
*/
?>