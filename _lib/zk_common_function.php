<?php
/**
* Copyright(c) 2005 PT. ZONEKOM All right reserved
* Homepage http://www.zonekom.com
* Contact us dskim@indosat.net.id
*
* author : zonekom
*
* $Id: zk_common_function.php,v 1.1.1.1 2007/10/30 09:34:38 dskim Exp $
*/

class ZKError {
	var $code;
	var $title;
    var $message;

    function ZKError($code, $title, $message) {
	$this->code = $code;
	$this->title = $title;
    	$this->message = $message;
    }

    function getMessage() {
        return $this->message;
    }

    function getCode() {
        return $this->code;
    }

    function getTitle() {
    	return $this->title;
    }
}

function dbg($var) {
	echo "<pre>";
	var_dump($var);
	echo "<pre>";
}

/*
 * ������ ������ �ִ� ��ü���� Ȯ���ϴ� �Լ�
 *
 * $o				 : �˻��� ��ü (�ʼ�)
 * $errorTemplate	 : MAIN_PAGE or MAIN_PAGE �������ȭ�� ������ ����
 * $afterConfirmPage : ������ �а� �Ŀ� Ȯ���� Ŭ���ϸ� �� �������� �̵��� ������
 * $code			 : Ư���� �����ڵ带 �����Ұ�, �������ϸ� �ش翡�� ��ü�� ������ �ִ� �ڵ�� ����ǥ��
 */
function isZKError($o){
    if (is_object($o) && (strtolower(get_class($o)) == "zkerror")) {
        return true;
    } else {
        return false;
    }
}

/**
* get microtime
* @return int microtimestamp
*/
function get_microtime()
{
    $mtime = microtime();
    $mtime = explode(" ", $mtime);
    $mtime = doubleval($mtime[1] + doubleval($mtime[0]));

    return ($mtime);
}


/**
* debug timing
* @param string "init"  : start to the benchmarks
*               "print" : print the output
*               "..."   : set timing point
* example
* dubug_timing("init") // start
* //somecode
* debug_timing("point1") //set timing point
* //somecode
* debug_timing("point1") //set timing point
* //somecode
* debug_timing("print") //print the output
*/
function debug_timing($label)
{
    static $basetime, $totaltime, $rpttimes;

    if ($label == 'init') {
        $rpttimes   = array();
        $basetime   = microtime();
        $totaltime  = 0;
        ereg("^([^ ]+) (.+)", $basetime, $r);
        $basetime = doubleval($r[2]) + doubleval($r[1]);
        return;
    }

    if ($label == "print") {
        echo "<!--\nZK Benchmarks : Timing results:\n";

        for ($i = 0; $i < count($rpttimes); $i++) {
            echo "  $rpttimes[$i]\n";
        }

        echo "  Total: $totaltime\n-->";
        return;
    }

    $newtime = microtime();
    ereg("^([^ ]+) (.+)", $newtime, $r);
    $newtime = doubleval($r[2]) + doubleval($r[1]);

    $diff       = $newtime - $basetime;
    $rpttimes[] = sprintf("%-20s %s", $label, $diff);
    $basetime   = $newtime;
    $totaltime  += $diff;
}


function keepGetString($str) {
    $arrKeys    = explode(",", $str);
    $strGet     = "";

    foreach($arrKeys as $value) {
        if(array_key_exists($value, $_GET)) {
            $strGet .= "$value=" . $_GET[$value] . "&";
        } else {
            $strGet .= "$value=&";
        }
    }

    return $strGet;
}

function getQueryString()
{
	$qs = array();

	foreach ($_GET as $key=>$item) {
        if ($item != '') {
            $qs[]= "$key=$item";
        }
    }

   return implode("&", $qs);
}

function delArrayByKey($arr, $strKey)
{
    if (array_key_exists($strKey, $arr)) {
        unset($arr[$strKey]);
    }

	return $arr;
}


function goPage()
{
    $numArgs    = func_num_args();
	$loc        = 'Location: http://';

	if ($numArgs <= 0) {
        return;
    }

	//First Arg : '/path/filename' or 'filename'
	$firstArg = func_get_arg(0);

	if (substr($firstArg, 0, 1)=='/') {
		$loc .= $_SERVER['HTTP_HOST'] . $firstArg;
    } else {
		$loc .= $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/' . $firstArg;
    }

	//second Arg : '?key=value&key=value& ...'
	if ($numArgs==2) {
        $arrTmp=func_get_arg(1);

		foreach ($arrTmp as $key => $val) {
            $bufTmp .= $key . '=' . $val . '&';
        }

		$loc .='?'.substr($bufTmp, 0, -1);
	}

	@header($loc);
    echo "<script>window.location.href='" . substr($loc, 9) . "';</script>";
	exit;
}

//Cutting a string
function cut_string($str, $length) {

    $hangul = 0;
    $alpha  = 0;
    //special character less than $length
    if(strlen($str) <= $length && !preg_match('/^[a-z]+$/i', $str)) {
        return $str;
    }

    if(strlen($str) <= $length) {
        return $str;
    }

    //preprocess
    $first = null;
    for($co = 1; $co <= $length; $co++) {
		if(is_hangul(substr($str, $co - 1, $co))) {
			if($first) {
				$second = 1;
				$first  = 0;
			} else {
				$first  = 1;
				$second = 0;
			}
			$hangul = 1;
		} else {
			$first = $second = 0;
			if(is_alpha(substr($str, $co - 1, $co)) == 2)
				$alpha++;
		}
	}

	if ($first) {
		$length--;
    }

	if ($hangul) {
		$str = chop(substr($str, 0, $length));
    } else {
		$str = substr($str, 0, $length - intval($alpha * 0.5));
    }

	return $str . "...";
}

/*
    Chop a string to a specified length and leave html tags / not
    based on the option

    $intLimit : max string length, 0 means no limit
    $option   : 0 - no html allowed
                1 - limited html tags allowed ( using changeValidHtml() function )
                2 - all html tags allowed
*/

//$string = '<xyz>baru</xyz><b>haha</b>';
//echo "$string<br>";
//echo displayFilter($string, 0, 1);
function displayFilter ($text, $intLimit = 0, $option = 0)
{

    $strlen = strlen($text);
    $result = '';

    // if no html allowed cut the string then convert the tags
    if ($option == 0) {
        if ($intLimit) {
            $result = htmlspecialchars(cut_string($text, $intLimit));
        } else {
            $result = htmlspecialchars($text);
        }
    } elseif (strlen(htmlspecialchars($text)) == $strlen) {
        // if html tags allowed but the $text doesn't have any html character
        // just return the chopped string
        if ($intLimit) {
            $result = cut_string($text, $intLimit);
        } else {
            $result = $text;
        }
        //$result = cut_string($text, $intLimit);
    } else {
        if ($option == 1) {
            $text = changeValidHtml($text);

            // update $strlen because changeValidHtml may make the string longer
            $strlen = strlen($text);
        }

        // if html tags allowed ( limited or not ), we cut the string only if
        // the *html text* ( the visible text ) length is > $intLimit
        //$trans  = array_flip(get_html_translation_table(HTML_SPECIALCHARS));
        //$temp   = strip_tags(strtr($text, $trans));
        $temp    = strip_tags($text);

        // changeValidHtml() will change some html tag into &lt;SOMETAG&gt;
        // but since &gt; &lt; will be displayed as one character we change them
        if ($option == 1) {
            $temp = str_replace(array('&gt;', '&lt;'), array('>', '<'), $temp);
        }

        $dots    = '';
        if ($intLimit && strlen($temp) > $intLimit) {
            // cut the *plain text* string
            // not the html formatted string
            $temp = cut_string($temp, $intLimit);

            // remove the trailing dots
            $temp    = substr($temp, 0, -3);
            $dots    = '...';
        }

        // restore any < or > into &gt; and &lt;
        if ($option == 1) {
            $temp = str_replace(array('>', '<'), array('&gt;', '&lt;'), $temp);
        }

        $tempLen = strlen($temp);

        $origPtr = 0;  // pointer to original string
        $tempPtr = 0;  // pointer to chopped string
        $tags    = array();

        // insert the chopped string back into the html tag
        for ($tempPtr = 0, $origPtr = 0; $tempPtr < $tempLen && $origPtr < $strlen; $tempPtr++, $origPtr++) {
            // found an opening tag,
            if ($text[$origPtr] == '<') {
                $tag = '';
                while($text[$origPtr] != '>' && $origPtr < $strlen) {
                    $result .= $text[$origPtr];
                    $tag    .= $text[$origPtr];
                    $origPtr += 1;
                }
                $result .= '>';
                $tag    .= '>';
                $tags[]  = $tag;

                $tempPtr -= 1; // decrease $tempPtr because we do not use any character from $temp string
            } else {
                $result .= $text[$origPtr];
            }
        }

        // put back the dots
        $result .= $dots;

        // close any open tags ( doesn't support xhtml / xml)
        $result .= closeTag($tags);
    }

    return $result;
}

// close an open tag
function closeTag($tags)
{
    // list of html tags which don't have
    // closing tags
    $noClose = array('<br>', '<hr>');
    $closeTags = array();
    $numTags = count($tags);

    for ($i = 0; $i < $numTags; $i++) {
        $tag = $tags[$i];

        if (!in_array($tag, $noClose) && $tag[1] != '/') {
            // get tag name
            $temp = substr($tag, 1, -1);

            // if tag have some attributes, remove them
            if (strpos($temp, ' ') !== false) {
                $temp = substr($temp, 0, strpos($temp, ' '));
            }

            $tempCloseTag = '</' . $temp . '>';

            // check if it's already in $tags
            $found = false;
            for ($j = $i + 1; $j < $numTags; $j++) {
                if ($tags[$j] == $tempCloseTag) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $closeTags[] = $tempCloseTag;
            }
        }
    }
    return implode('', array_reverse($closeTags));
}


//Check a korean letter
function is_hangul($char) {
	$char = ord($char);

	if($char >= 0xa1 && $char <= 0xfe)
		return 1;
}


// Check a alpabet
function is_alpha($char) {
	$char = ord($char);

	if($char >= 0x61 && $char <= 0x7a)
		return 1;
	if($char >= 0x41 && $char <= 0x5a)
		return 2;
}

//get filename and extension
function explodeFile($filename) {
    $filename = strtolower($filename);
    $elements = explode('.',$filename);
    $elemcnt  = count($elements)-1;

    if (count($elements) == 1) {
        $ext = '';
    } else {
        $ext = $elements[$elemcnt];
    }

    unset($elements[$elemcnt]);

    $fname              = implode($elements,'');
    $fileinfo["name"]   = $fname;
    $fileinfo["ext"]    = $ext;

    return $fileinfo;
}

function delete_file($dir,$file) {
    $is_file = is_file($dir.$file);
    if ($is_file) {
        unlink($dir.$file);
    };
}


//upload image
function image_upload($flName,$loc) {

    // These are the allowed extensions of the files that are uploaded
    static $count;
    $allowed_ext    = "jpg, jpeg, gif, png";
    $ext            = false;
    $size           = false;
    $extension      = pathinfo($_FILES[$flName]['name']);
    $extension      = $extension["extension"];
    $allowed_paths  = explode(", ", $allowed_ext);

    for ($i = 0; $i < count($allowed_paths); $i++) {
        if ($allowed_paths[$i] == $extension) {
            $ext = true;
        }
    }

    if ($_FILES[$flName]['size'] >= 1048576) {
        return new ZKError("ZK008", "Image Uplode", "fail to upload image<br>File Size is too big");
    } else {
        $size = true;
    }

    if ($ext && $size) {
        $count++;
        $newfilename    = "zk_".time()."_$count.".$extension;

        if (move_uploaded_file($_FILES[$flName]['tmp_name'], "{$loc}/{$newfilename}")) {
            chmod("{$loc}/{$newfilename}", 0644);
            $attached = $newfilename;
        } else {
            return new ZKError("ZK008", "Image Uplode", "fail to upload image<br>you don't have permission at the destination");
        }
    } else {
            return new ZKError("ZK008", "Image Uplode", "fail to upload image<br>illegal file type");
    }

    return $attached;
}

/*
  Upload file(s)
  Returns the uploaded file name(s) on success or ZKError
*/
function file_upload($flName,$loc) {

    // These are the allowed extensions of the files that are uploaded
    static $count;
    $deny_ext       = "html, php, htm, inc, js, asp, htm, aspx, phps";
    $ext            = true;
    $size           = false;
	$rtn            = array();

	$files = $_FILES[$flName];

	// to handle multiple file upload make sure that $_FILES[$flName]['name'] and the rest
	// is an array
	if (count($files['name']) == 1) {
		$files['name']     = array($files['name']);
		$files['type']     = array($files['type']);
		$files['tmp_name'] = array($files['tmp_name']);
		$files['error']    = array($files['error']);
		$files['size']     = array($files['size']);
	}

	$numFile = count($files['name']);
	for ($i = 0; $i < $numFile; $i++) {
		$extension      = pathinfo($files['name'][$i]);
		$extension      = $extension["extension"];
		$deny_paths     = explode(", ", $deny_ext);

		for ($j = 0; $j < count($deny_paths); $j++) {
			if ($deny_paths[$j] == $extension) {
				$ext = false;
			}
		}

		if ($files['size'][$i] >= 8388608) {
			if (RUNTIME_MODE == "DEBUG") {
				echo "DEBUG INFO <br>-------------------------------".
					 "<br>From. file_upload()<br>-------------------------------<pre>";
				var_dump(debug_backtrace());
				echo "</pre>";
				exit;
			} else {
				return new ZKError("ZK008", "FILE UPLOAD", "fail to upload a file (file size is too big)");
			}
		} else {
			$size = true;
		}

		if ($ext && $size) {
			$count++;
			$newfilename    = "zk_".time()."_$count.".$extension;
			if (move_uploaded_file($files['tmp_name'][$i], "{$loc}/{$newfilename}")) {//"{$loc}/{$files['name'][$i]}")) {
				chmod("{$loc}/{$newfilename}", 0644);
				//chmod("{$loc}/{$files['name'][$i]}", 0644);
				$rtn[] = $newfilename;
			} else {
				return new ZKError("ZK008", "FILE UPLOAD", "fail to upload a file.<br> You don't have write permission at the destination");
			}
		} else {
		                      return new ZKError("ZK008", "FILE UPLOAD", "fail to upload a file<br> illegal file type.");
		}
	}

	if (count($rtn) == 1) {
		$rtn = $rtn[0];
	}

	return $rtn;
}

function download_file($filename, $suggestedFilename) {

    $file   = APP_DIR . "..//data/files/" . $filename;

    if(!is_file($file)) {
        //return new ZKError('ZKError : DOWNLOAD_FILE_NOT_FOUND', 'ZKC_008');
        return new ZKError("ZK008", "FILE DOWNLOAD", "fail to download file. File does not founded.");
    }

    if(preg_match("/(MSIE 5.0|MSIE 5.1|MSIE 5.5|MSIE 6.0)/", $_SERVER["HTTP_USER_AGENT"])) {
     if(strstr($_SERVER["HTTP_USER_AGENT"], "MSIE 5.5")) {

            header("Content-Type: doesnt/matter");
            header("Content-Length: ".(string)(filesize("$file")));
            header("Content-Disposition: filename=$suggestedFilename");
            header("Content-Transfer-Encoding: binary");
            header("Pragma: no-cache");
            header("Expires: 0");

      } elseif(strstr($_SERVER["HTTP_USER_AGENT"], "MSIE 5.0")) {

            header("Content-type: file/unknown");
            header("Content-Disposition: attachment; filename=$suggestedFilename");
            header("Content-Description: PHP3 Generated Data");
            header("Cache-Control: cache, must-revalidate");
            header("Pragma: no-cache");
            header("Expires: 0");

      } elseif(strstr($_SERVER["HTTP_USER_AGENT"], "MSIE 5.1")) {

            header("Content-type: file/unknown");
            header("Content-Disposition: attachment; filename=$suggestedFilename");
            header("Content-Description: PHP3 Generated Data");
            header("Cache-Control: cache, must-revalidate");
            header("Pragma: no-cache");
            header("Expires: 0");

      } elseif(strstr($_SERVER["HTTP_USER_AGENT"], "MSIE 6.0")) {

            header("Content-type: application/x-msdownload");
            header("Content-Length: ".(string)(filesize($file)));
            header("Content-Disposition: attachment; filename=$suggestedFilename");
            header("Content-Transfer-Encoding: binary");
            header("Cache-Control: cache, must-revalidate");
            header("Pragma: no-cache");
            header("Expires: 0");
      }
    } else {

      header("Content-type: file/unknown");
      header("Content-Length: ".(string)(filesize($file)));
      header("Content-Disposition: attachment; filename=$suggestedFilename");
      header("Content-Description: PHP3 Generated Data");
      header("Cache-Control: cache, must-revalidate");
      header("Pragma: no-cache");
      header("Expires: 0");
     }

    readfile($file);
}

// increase unique counts
function incrementTotalCount($countfile)
{
    return;

    // build time range
    $hour  = date("H");

    $start = 2 * (int) ($hour/2);
    $end   = $start + 2;
    $hour  = $start;

    if (strlen($start) < 2) {
        $start = '0' . $start;
    }

     if (strlen($end) < 2) {
        $end = '0' . $end;
    }
    $range   = "$start-$end";

    // check the count file
    $pattern = "/" . date("Y-m-d") . ", $range, (.*)\r\n/";

    // open the counter file
    $fp = fopen($countfile, "a+");

    // do an exclusive lock
    if (flock($fp, LOCK_EX)) {
        $content = @fread($fp, filesize($countfile));

        // if found a match increment the counter
        if (preg_match($pattern, $content, $match)) {
            $count = $match[1];

            // replace the string in the countfile
            $content = str_replace(date("Y-m-d") . ", $range, $count", date("Y-m-d") . ", $range, " . ($count + 1) , $content);
        } else {
            // no match was found, create a new row
            $newrow   = date("Y-m-d") . ", $range, 1\r\n";

            // check for an empty time slot, by fetching the last row in content
            $temp = explode("\r\n", $content);
            $n    = count($temp);

            // the file is just created, insert the first row
            if ($n == 0 || trim($content) === "") {
                $content .= $newrow;
            } else {
                // fill in every empty time slots with visit count = 0
                $lastrow = $temp[$n - 2];
                $pattern = "/(.*),(.*)-(.*),(.*)/";

                preg_match_all($pattern, $lastrow, $matches);

                $hend = $matches[3][0];

                $today     = date("Y-m-d");
                $lastday   = $matches[1][0];
                $lastyear  = substr($lastday, 0, 4);
                $lastmonth = substr($lastday, 5, 2);
                $lastdate  = substr($lastday, -2);
                $lasthour  = $matches[3][0];

                $i         = $lasthour;
                $newdate   = $lastday;
                $tmprow    = '';
                while (($newdate < $today) || ($newdate == $today && ($i % 24) < $hour)) {
                    $newstart = $i % 24;
                    $newend   = ($i + 2) % 24;
                    if (strlen($newstart) < 2) {
                        $newstart = '0' . $newstart;
                    }

                    if (strlen($newend) < 2) {
                        $newend = '0' . $newend;
                    }

                    $newrange = "$newstart-$newend";
                    $newdate  = date("Y-m-d", mktime($i, 0, 0, $lastmonth, $lastdate, $lastyear));
                    $tmprow  .= $newdate . ", $newrange, 0\r\n";
                    $i       +=2;
                }

                $content .= $tmprow . $newrow;
            }
        }

        // write the file and release the lock
        ftruncate($fp, 0);
        fwrite($fp, $content);
        flock($fp, LOCK_UN);
    } else {
        echo "Couldn't lock the file !";
    }
    fclose($fp);
}

/*
    increment count for : age, gender, income, marriage status, and resident type
    change the function name to a more descriptive one
    the data to increment will be in $count[$index]
*/
function incrementDataCount($countfile, $index, $numcolumns)
{

    // increase unique counts
    // build time range
    $hour  = date("H");

    $start = 2 * (int) ($hour/2);
    $end   = $start + 2;
    $hour  = $start;

    if (strlen($start) < 2) {
        $start = '0' . $start;
    }

     if (strlen($end) < 2) {
        $end = '0' . $end;
    }
    $range   = "$start-$end";

    // check the count file
    $pattern = "/" . date("Y-m-d") . ", $range, (.*)\r\n/";

    // open the counter file
    $fp = fopen($countfile, "a+");

    // do an exclusive lock
    if (flock($fp, LOCK_EX)) {
        $content = @fread($fp, filesize($countfile));

        // if found a match increment the counter
        if (preg_match($pattern, $content, $match)) {
            $count          = $match[1];
            $count          = explode(', ', $count);
            $count[$index] += 1;
            $count          = implode(', ', $count);

            // replace the string in the countfile
            $content = preg_replace("/" . date("Y-m-d") . ", $range, (.*)\r\n/", date("Y-m-d") . ", $range, $count\r\n" , $content);
        } else {
            // no match was found, create a new row
            $zerocount = array();
            for ($i = 0; $i < $numcolumns; $i++) {
                $zerocount[$i] = 0;
            }

            $newcount         = $zerocount;
            $newcount[$index] = 1;
            $newcount         = implode(', ', $newcount);
            $newrow           = date("Y-m-d") . ", $range, $newcount\r\n";

            // check for an empty time slot, by fetching the last row in content
            $temp = explode("\r\n", $content);
            $n    = count($temp);

            // the file is just created, insert the first row
            if ($n == 0 || trim($content) == '') {
                $content .= $newrow;
            } else {
                // fill in every empty time slots with visit count = 0
                $lastrow = $temp[$n - 2];

                $pattern = "/(.*),(.*)-(.*),(.*)/";

                preg_match_all($pattern, $lastrow, $matches);

                $hend      = $matches[3][0];

                $today     = date("Y-m-d");
                $lastday   = $matches[1][0];
                $lastyear  = substr($lastday, 0, 4);
                $lastmonth = substr($lastday, 5, 2);
                $lastdate  = substr($lastday, -2);
                $lasthour  = $matches[3][0];

                $i         = $lasthour;
                $newdate   = $lastday;
                $tmprow    = '';
                $zerocount = implode(', ', $zerocount);
                while (($newdate < $today) || ($newdate == $today && ($i % 24) < $hour)) {
                    $newstart = $i % 24;
                    $newend   = ($i + 2) % 24;
                    if (strlen($newstart) < 2) {
                        $newstart = '0' . $newstart;
                    }

                    if (strlen($newend) < 2) {
                        $newend = '0' . $newend;
                    }

                    $newrange = "$newstart-$newend";
                    $newdate  = date("Y-m-d", mktime($i, 0, 0, $lastmonth, $lastdate, $lastyear));
                    $tmprow  .= $newdate . ", $newrange, $zerocount\r\n";
                    $i       +=2;
                }
                $content .= $tmprow . $newrow;
            }
        }

        // write the file and release the lock
        ftruncate($fp, 0);
        fwrite($fp, $content);
        flock($fp, LOCK_UN);
    } else {
        echo "Couldn't lock the file !";
    }
    fclose($fp);
}

function writeLog($logfile, $logarray)
{
    $log = date("Y-m-d H:i:s") . ', ' . implode(', ', $logarray) . "\r\n";
    if ($fp  = fopen($logfile, 'a+')) {
        if (flock($fp, LOCK_EX)) {
            fwrite($fp, $log);
            flock($fp, LOCK_UN);
        }

        fclose($fp);
    }
}


function isEmail($email)
{
    return(preg_match("/^[-_.[:alnum:]]+@((([[:alnum:]]|[[:alnum:]][[:alnum:]-]*[[:alnum:]])\.)+(ad|ae|aero|af|ag|ai|al|am|an|ao|aq|ar|arpa|as|at|au|aw|az|ba|bb|bd|be|bf|bg|bh|bi|biz|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|ca|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|co|com|coop|cr|cs|cu|cv|cx|cy|cz|de|dj|dk|dm|do|dz|ec|edu|ee|eg|eh|er|es|et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gh|gi|gl|gm|gn|gov|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|in|info|int|io|iq|ir|is|it|jm|jo|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|mg|mh|mil|mk|ml|mm|mn|mo|mp|mq|mr|ms|mt|mu|museum|mv|mw|mx|my|mz|na|name|nc|ne|net|nf|ng|ni|nl|no|np|nr|nt|nu|nz|om|org|pa|pe|pf|pg|ph|pk|pl|pm|pn|pr|pro|ps|pt|pw|py|qa|re|ro|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sk|sl|sm|sn|so|sr|st|su|sv|sy|sz|tc|td|tf|tg|th|tj|tk|tm|tn|to|tp|tr|tt|tv|tw|tz|ua|ug|uk|um|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|ye|yt|yu|za|zm|zw)$|(([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5])\.){3}([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5]))$/i"
            ,$email));
}

/*
	Check if the given domain is valid. Return true if it is,
	false otherwise.

	The domain form is like : www.google.com OR google.com
	DO NOT precede with http://
*/
function isDomainValid($domain)
{
	$isValid = false;

@	$fp = fsockopen($domain, 80, $errno, $errstr, $timeout = 30);

	if ($fp) {
		//send HEAD request
		fputs($fp, "HEAD / HTTP/1.1\r\n");
		fputs($fp, "Host: $domain\r\n");
		fputs($fp, "Connection: close\r\n\r\n");

		//loop through the response from the server
		$result = '';
		while(!feof($fp)) {
			$result .= fgets($fp, 4096);
		}

		fclose($fp);

		/*
			The domain is considered valid if we receive the
			following response code :
			 - 200 : found
			 - 301 : moved permanently
			 - 302 : moved temporarily
		*/
		$result = explode("\n", $result);
		$responseCode = $result[0];

		if (strpos($responseCode, '200') !== false
		    || strpos($responseCode, '301') !== false
		    || strpos($responseCode, '302') !== false) {

			$isValid = true;
		}

	}

	return $isValid;
}


function isMailServerExist($to) {
	$isExist     = false;

	$from        = '<zonekom@zonekom.com>';
	$fromdomain  = 'zonekom.com';
	$fromname    = 'zonekom';

	$todomain    = substr(strstr($to, '@'), 1);
	$to          = "<$to>";

	$errno  = 0;
	$errstr = 0;

//echo "Connect Start... <br>";

	if(false === getmxrr($todomain, $mxhosts)) {
//echo "no mx record\n";
	} else {
		$rows    = count($mxhosts);
		$fastmx  = 0;
		$oldtime = 999;
		for($i = 0; $i < $rows; $i ++) {
			$starttime = explode(' ', microtime());
			if(false === ($socks[$i] = fsockopen($mxhosts[$i], 25, $errno, $errstr, 30))) {
				continue;
			} else {
				// open socket successful, now store the socket(s)
				// that need to be closed later
				$sock_close[] = $i;
			}

			$endtime = explode(' ', microtime());

			$elapsed    = ($endtime[0] - $starttime[0]) + ($endtime[1] - $starttime[1]);
//echo "Host : $mxhosts[$i] , Elapsed time : $elapsed <br>";

			// mark the fastest host
			if($elapsed < $oldtime) {
				$oldtime = $elapsed;
				$fastmx = $i;
			}
		}

		// now we use the fastest host
		$sock = &$socks[$fastmx];

//echo "The fastest host is : " . $mxhosts[$fastmx] . " , Connected  <br>";//errno:$errno errstr:$errstr <br>";

		$response   = fread($sock, 256);
		$socketStatus = socket_get_status($sock);
		if ($socketStatus['unread_bytes'] > 0) {
			$response .= fread($sock, $socketStatus['unread_bytes']);
		}

//echo "1 $response <br>";
		if(substr($response, 0, 3) == '220') {
			fputs($sock, "EHLO $fromdomain\r\n");
			$response   = fread($sock, 256);
			$socketStatus = socket_get_status($sock);
			if ($socketStatus['unread_bytes'] > 0) {
				$response .= fread($sock, $socketStatus['unread_bytes']);
			}

//echo "2 $response <br>";
			if(substr($response, 0, 3) == '250') {
				fputs($sock, "MAIL FROM: $from\r\n");
				$response   = fread($sock, 256);
				$socketStatus = socket_get_status($sock);
				if ($socketStatus['unread_bytes'] > 0) {
					$response .= fread($sock, $socketStatus['unread_bytes']);
				}

//echo "3 $response <br>";
				if(substr($response, 0, 3) == '250') {
					fputs($sock, "RCPT TO: $to\r\n");
					$response = fread($sock, 256);
					$socketStatus = socket_get_status($sock);
					if ($socketStatus['unread_bytes'] > 0) {
						$response .= fread($sock, $socketStatus['unread_bytes']);
					}

//echo "4 $response <br>";
					if (substr($response, 0, 3) == '250') {
						$isExist = true;

						// close the sockets
						$numOpened = count($sock_close);
						for ($i = 0; $i < $numOpened; $i ++) {
							fclose($socks[$sock_close[$i]]);
						}
					}
				}
			}
		}
	}

	return $isExist;
}

function displayNumber($number) {
	if ($number == NULL) {
		$display = "<i>Null</i>";
	} else if (strpos($number, ".") == true) {
		$display = number_format((double)$number,2,'.',',');
	} else {
		$display = number_format((double)$number);
	}

	return $display;
}

function getWeek($ts="") {

	if(empty($ts)) {
		$ts = time();
	}

	$thisMonth	= date("n", $ts);
	$renValue 	= array();
	$arrMonth 		= array(1=>"January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");

	$thisMMon = date("W",
		mktime(0,0,0, date("n", $ts), (4 - date("w", mktime(0,0,0,date("n", $ts),4,date("Y"))) + 1), date("Y")));
	$nextMMon = date("W",
		mktime(0,0,0, date("n", $ts) + 1, (4 - date("w", mktime(0,0,0,date("n", $ts) + 1,4,date("Y"))) + 1), date("Y")));
	$thisMon = date("W",
		mktime(0,0,0, date("n", $ts), date("j",$ts) - date("w", $ts) + 1, date("Y")));

/*
	echo "This Month first monday : " . $thisMMon . "<br>";
	echo "This monday : " . $thisMon . "<br>";
	echo "Next Month first Monday : ". $nextMMon . "<br>";
*/
	//
	if($thisMon < $thisMMon) {
		$lastMMon = date("W",
			mktime(0,0,0, date("n", $ts) - 1, (4 - date("w", mktime(0,0,0,date("n", $ts) - 1,4,date("Y"))) + 1), date("Y")));

		$rtnValue["week"]	= ($thisMon - $lastMMon + 1) . "th";
		$rtnValue["month"]	= $arrMonth[$thisMonth - 1];
	//
	} elseif ($nextMMon == $thisMon) {
		$rtnValue["week"]	= "1st";
		$rtnValue["month"]	= $arrMonth[$thisMonth + 1];
	//
	} else {
		$arrTh		= array(1=>"st", "nd", "rd", "th", "th", "th");
		$rtnValue["week"]	= ($thisMon - $thisMMon + 1) . $arrTh[$thisMon - $thisMMon + 1];
		$rtnValue["month"]	= $arrMonth[$thisMonth];

	}

	$rtnValue["th"] = $thisMon;
	$rtnValue["string"] = $rtnValue["week"]. " week of ". $rtnValue['month'];
	return $rtnValue;
}

/*
function getWeek($ts="") {

	if(empty($ts)) {
		$ts = time();
	}

	$thisMonth	= date("n", $ts);
	$renValue 	= array();
	$arrMonth 		= array(1=>"January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");


	$thisMMon = date("W",
		mktime(0,0,0, date("n", $ts), (4 - date("w", mktime(0,0,0,date("n", $ts),4,date("Y", $ts))) + 1), date("Y", $ts)));
	$nextMMon = date("W",
		mktime(0,0,0, date("n", $ts) + 1, (4 - date("w", mktime(0,0,0,date("n", $ts) + 1,4,date("Y", $ts))) + 1), date("Y", $ts)));
	$thisMon = date("W",
		mktime(0,0,0, date("n", $ts), date("j",$ts) - date("w", $ts) + 1, date("Y", $ts)));


	echo "This Month first monday : " . $thisMMon . "<br>";
	echo "This monday : " . $thisMon . "<br>";
	echo "Next Month first Monday : ". $nextMMon . "<br>";


	//���� �������� �������� ������ ���� ���
	if($thisMon < $thisMMon) {
		$lastMMon = date("W",
			mktime(0,0,0, date("n", $ts) - 1, (4 - date("w", mktime(0,0,0,date("n", $ts) - 1,4,date("Y", $ts))) + 1), date("Y", $ts)));

		$rtnValue["week"]	= ($thisMon - $lastMMon + 1) . "th";
		$rtnValue["month"]	= $arrMonth[$thisMonth - 1];

	//���� �������� �������� ù���� ���
	} elseif ($nextMMon == $thisMon) {
		$rtnValue["week"]	= "1st";
		$rtnValue["month"]	= $arrMonth[$thisMonth + 1];

	//���� �������� �ݿ��� ��°���� ���
	} else {
		$arrTh		= array(1=>"st", "nd", "rd", "th", "th", "th");
		$rtnValue["week"]	= ($thisMon - $thisMMon + 1) . $arrTh[$thisMon - $thisMMon + 1];
		$rtnValue["month"]	= $arrMonth[$thisMonth];
	}

	$rtnValue["th"] = $thisMon;
	$rtnValue["string"] = $rtnValue["week"]. " week of ". $rtnValue['month'];
	return $rtnValue;
}
*/

// Escapes strings to be included in javascript
function jsspecialchars($s) {
    return preg_replace('/([^ !#$%@()*+,-.\x30-\x5b\x5d-\x7e])/e',
        "'\\x'.(ord('\\1')<16? '0': '').dechex(ord('\\1'))",$s);
}

function cell($data, $att='') {
	print "\t<td$att>$data</td>\n";
}

function cell_link($data, $cellAtt='', $linkAtt='') {
	print "\t<td$cellAtt><a$linkAtt>$data</a></td>\n";
}

//Compare two data, and get the different value
function get_diff_data($item) {

	foreach($item[0] as $val)	$_zwh_it_code[] = trim($val);
	foreach($item[1] as $val)	$_zwh_it_qty[] = $val;
	foreach($item[2] as $val)	$_zout_it_code[] = $val;
	foreach($item[3] as $val)	$_zout_it_qty[] = $val;

	for ($i=0; $i<count($_zwh_it_code); $i++)
	{
		if(!isset($_zrcp_it_code[$_zwh_it_code[$i]])) {
			$_zrcp_it_code[$_zwh_it_code[$i]][0] = $_zwh_it_code[$i];
			$_zrcp_it_code[$_zwh_it_code[$i]][1] = $_zwh_it_qty[$i];
		} else {
			$_zrcp_it_code[$_zwh_it_code[$i]][1] += $_zwh_it_qty[$i];
		}
	}
	foreach ($_zrcp_it_code as $key => $val)
	{
		$_rcp_it_code[] = $val[0];
		$_rcp_it_qty[] = $val[1];
	}
	$_rcp_it_code = '$$' . implode('$$,$$', $_rcp_it_code) . '$$';
	$_rcp_it_qty = implode(',', $_rcp_it_qty);

	//
	for($i = 0; $i < count($_zout_it_code); $i++) {
		$it['old'][(string) $_zout_it_code[$i]] = $_zout_it_qty[$i];
	}
	foreach($_zrcp_it_code as $val) {
		$it['new'][(string) $val[0]] = $val[1];
	}

	$it['old1'] = array_diff_assoc($it['old'], $it['new']);
	$it['new1'] = array_diff_assoc($it['new'], $it['old']);
	$it['diff'] = $it['old1'] + $it['new1'];

	// Check item different and calculate the difference qty
	foreach ($it['diff'] as $key => $val)
	{
		$qty = 0;
		if (isset($it['old'][$key])) {
			if(isset($it['new'][$key]))
				$qty = $it['new'][$key] - $it['old'][$key];
			else	$qty = $it['old'][$key] * -1;
		}
		else if (isset($it['new'][$key])) {
			if(isset($it['old'][$key]))
				$qty = $it['old'][$key] - $it['new'][$key];
			else	$qty = $it['new'][$key];
		}
		$it['rcp']['item'][$key] = $key;
		$it['rcp']['qty'][$key] = (float)$qty;
	}

	$rcp = array();
	if(isset($it['rcp'])) {
		foreach($it['rcp']['item'] as $val)	$_yrcp_it_code[]	= $val;
		foreach($it['rcp']['qty'] as $val)	 $_yrcp_it_qty[]	= $val;
		$rcp['item']	= '$$' . implode('$$,$$', $_yrcp_it_code) . '$$';
		$rcp['qty']	= implode(',', $_yrcp_it_qty);
	}

	return $rcp;
}
?>
