<?php
/**
* Copyright PT. ZONEKOM Indonesia All right reserved
* Contact us dskim@indosat.net.id
*
* @author : arman
*
* $Id: zk_pgsql.php,v 1.5 2008/02/28 08:12:06 dskim Exp $
*/

$dbconn         = "";

if (!($dbconn = pg_connect("host={$dbhost} port=5432 dbname={$dbname} user={$dbuser} password={$dbpass}"))) {
    //Error(ZK100) : Cannot connect to mysql database
    $oError = new ZKError(
        "ZKC_100",
        "DATABASE ERROR",
        "Cannot Connect Database"
    );
    $M->goErrorPage($oError, MAIN_PAGE);
}

// WebSite encoding is euc_kr
//@pg_query("SET CLIENT_ENCODING='EUC_KR'");
//@pg_query("SET TIME ZONE '+9'");

function &query($sql)
{
    global $dbconn;
    if (!($result = @pg_query($dbconn, $sql))) {
        if (RUNTIME_MODE == "DEBUG") {
            echo "DEBUG INFO <br>-------------------------------".
                 "<br>From. query()<br>-------------------------------<pre>";
            var_dump(debug_backtrace());
            echo "</pre>";
            exit;
        } else {
            $oError = new ZKError(
                "ZK104",
                "DATABASE ERROR",
                "Cannot execute query [$sql]"
            );
            return $oError;
        }
    }
    return $result;
}


/*
 * for pgsql
 * execute get_result();
 */
function send_query($sql)
{
    global $dbconn;
    if(!pg_connection_busy($dbconn)) {
        pg_send_query($dbconn, $sql);
        return true;
    } else {
        if (RUNTIME_MODE == "DEBUG") {
            echo "DEBUG INFO <br>-------------------------------".
                 "<br>From. send_query()<br>-------------------------------<pre>";
            var_dump(debug_backtrace());
            echo "</pre>";
            exit;
        } else {
            $oError = new ZKError(
                "ZK104",
                "DATABASE ERROR",
                "Cannot execute query [$sql]"
            );
            return $oError;
        }
    }
}


/*----------------------------------------
 * get result
 *
 *  On Error ERROR_HANDLING_MODE
 * 
 *  return : reference resource
 *----------------------------------------*/
function &get_result()
{
    global $dbconn;

    //even if success to get resorce we have to check the status
    if (!($result = pg_get_result($dbconn))) {
        if (RUNTIME_MODE == "DEBUG") {
            echo "DEBUG INFO <br>-------------------------------".
                 "<br>From. pg_get_result()<br>-------------------------------<pre>";
            echo pg_result_error($result) . "\n";
            var_dump(debug_backtrace());
            echo "</pre>";
            exit;

        } else {
            $oError = new ZKError(
                "ZK104",
                "DATABASE ERROR",
                "Cannot execute query</ br>". pg_result_error($result)
            );
            return $oError;            
        }
    }

    $statusCode = pg_result_status($result);
/*
    if ($statusCode == PGSQL_EMPTY_QUERY) {
    } elseif ($statusCode == PGSQL_COMMAND_OK) {
        echo 0;
    } elseif ($statusCode == PGSQL_TUPLES_OK) {
        echo 1;
    } elseif ($statusCode == PGSQL_COPY_TO) {
        echo 2;
    } elseif ($statusCode == PGSQL_COPY_FROM) {
        echo 3;
    } elseif ($statusCode == PGSQL_BAD_RESPONSE) {
        echo 4;
    } elseif ($statusCode == PGSQL_NONFATAL_ERROR) {
        echo 5;
    } elseif ($statusCode == PGSQL_FATAL_ERROR) {
        echo 6;
    }
*/

    if ($statusCode == PGSQL_TUPLES_OK OR $statusCode == PGSQL_COMMAND_OK) {
        return $result;
    } else {
        if (RUNTIME_MODE == "DEBUG") {
            echo "DEBUG INFO <br>-------------------------------".
                 "<br>From. pg_result_status()<br>-------------------------------<br><pre>";
            echo "<br>" . pg_result_error($result) . "\n";
            var_dump(debug_backtrace());
            echo "</pre>";
            exit;

        } else {
            $oError = new ZKError(
                "ZK104",
                "DATABASE ERROR",
                "Cannot execute query<br />". pg_result_error($result)
            );
            return $oError;
        }
    }
}


// only for Select
function numQueryRows($resResult)
{
    return pg_num_rows($resResult);
}

// only for DML
function numAffectedRows(&$resResult)
{
    return pg_affected_rows($resResult);
}


function freeResults($resResult)
{
    pg_free_result($resResult);
}


function &fetchRowAssoc(&$resResult, $encode = 1) {
	if (($out = pg_fetch_assoc($resResult)) && $encode) {
		foreach($out as $key => $val) {
			$out[$key] = htmlspecialchars($val, ENT_QUOTES);
		}
	}

	return $out;
}

function &fetchRow(&$resResult, $encode = 1) {
	if (($out = pg_fetch_row($resResult)) && $encode) {
		foreach($out as $key => $val) {
			$out[$key] = htmlspecialchars($val, ENT_QUOTES);
		}
	}

	return $out;
}


/*
 * Execute a stored procedure
 * Input : - stored procedure name
 *         - parameter values ( if any )
 * 
 * Output : - two dimensional array containing all the rows returned
 *            by the stored procedure
 * 
 * This function can only be used to run a stored procedure which use cursor to
 * select from a table. Other type of stored procedure can be executed with a
 * simple SELECT query
 * 
 * 
 * Example : - $result = getRowsFromSp('getlistnotice', 1, 10);           -
 * $result = getRowsFromSp('loginmember2', 'arman', 'mypass');
 * 
 *  */
function getRowsFromSP()
{
    global $dbconn;
    
    $numArgs = func_num_args();

    // this function need at least one paramater
    // the name of the stored procedure
    if ($numArgs = 1) {
        die("You have to specify the stored procedure name");
    }

    $SPName = func_get_arg(0);
    
    $params = array();
    for ($i = 1; $i < $numArgs; $i++) {
        $params[] = func_get_arg($i);
    }
    
    $params = implode(', ', $params);

    $sql1   = "BEGIN; ". // result #1
              "SELECT $SPName('cursor'" .(($params != '') ? ', ' . $params : '').");"; //result #2

    $sql2   = "FETCH ALL IN cursor;". //result #3
              "CLOSE cursor;". //result #4
              "END;"; //result #5

    //execute query;
    send_query($sql1);

    //start transaction;
    if (isZKError($result =& get_result())) { //#1 (Begin)
        // Do NOT use ERROR_HANDLING_MODE.
        // because, this error object is get_result()'s, so just sent.
        return $result;
    }

    if (isZKError($result =& get_result())) { //#2 (Execute Process)
        return $result;
    }

    //After execute SP, examine Notice.
    //Notice also regards as an error. But SP SHOULD BE END normally.
    $noticeMessage = pg_last_notice($dbconn);

    //When some notice Not Found
    if ($noticeMessage == "") {
        send_query($sql2);
        $result =& get_result(); //#3 (Fetch : need a resource for fetch);
        while ($row = fetchRow($result)) {
            $rtn[] = $row;
        }

        if (isZKError($result =& get_result())) { //#4 (Close Curdor)
            return $result;
        }
        if (isZKError($result =& get_result())) { //#5 (END Transaction)
            return $result;
        }

    } else {
        send_query("COMMIT;");

        if (RUNTIME_MODE == "DEBUG") {
            echo "DEBUG INFO <br>-------------------------------".
                 "<br>From. getRowsFromSP()<br>-------------------------------<br><pre>";
            echo "<br>" . $noticeMessage . "\n";
            var_dump(debug_backtrace());
            echo "</pre>";
            exit;
        } else {
            $oError = new ZKError(
                "ZK104",
                "DATABASE ERROR",
                "Cannot execute query ".$noticeMessage
            );
            return $oError;
        }
    }

    return $rtn;
}


function executeSP()
{
    global $dbconn;
    
    $numArgs = func_num_args();

    // this function need at least one paramater
    // the name of the stored procedure
    if ($numArgs  <= 1) {
        die("You have to specify the stored procedure name");
    }

    $SPName = func_get_arg(0);
    $params = array();
	
	if(get_magic_quotes_gpc()) {
		for ($i = 1; $i  < $numArgs; $i++) {
			//WARNING. ALL ARGUEMENT VARIABLE TYPE IS STRING. & PLS KEEP THIS FACT. because it will returned from $_POST
			//NULL can inserted 0 length string or $$$$ If you want to insert zero length string use '' instead of $$$$
			$arg = trim(func_get_arg($i));
			$params[] = (($arg === "") || $arg == "\$\$\$\$") ? "NULL" : stripslashes($arg);
		}
	} else {
		for ($i = 1; $i  < $numArgs; $i++) {
			$arg = trim(func_get_arg($i));
			$params[] = (($arg === "") || $arg == "\$\$\$\$") ? "NULL" : $arg;
		}
	}

    $params = implode(', ', $params);
    $sql   = "SELECT $SPName(" . (($params != '') ? '' . $params : '').");";
//die($sql);
    send_query($sql);

    if (isZKError($result =& get_result())) {
        //return fetch level error
        return $result;
    }

    //Notice also regards as an error. But SP SHOULD BE END normally.
    $noticeMessage = pg_last_notice($dbconn);
    if ($noticeMessage != "") {
        if (RUNTIME_MODE == "DEBUG") {
            echo "DEBUG INFO <br>-------------------------------".
                 "<br>From. executeSP()<br>-------------------------------<br><pre>";
            echo "<br>" . $noticeMessage . "\n";
            var_dump(debug_backtrace());
            echo "</pre>";
            exit;
        } else {
            $oError = new ZKError(
                "ZK104",
                "DATABASE ERROR",
//                "Cannot execute query". $noticeMessage
                $noticeMessage
            );
            return $oError;
        }
    }

    $statusCode = pg_result_status($result);

    if ($statusCode == PGSQL_TUPLES_OK) {
        return fetchRow($result);
    } else {
        if (RUNTIME_MODE == "DEBUG") {
            echo "DEBUG INFO <br>-------------------------------".
                 "<br>From. executeSP()<br>-------------------------------<br><pre>";
            echo "<br>" . pg_result_error($result) . "\n";
            var_dump(debug_backtrace());
            echo "</pre>";
            exit;
        } else {
            $oError = new ZKError(
                "ZK104",
                "DATABASE ERROR",
//                "Cannot execute query". pg_result_error($result)
                pg_result_error($result)
            );
            return $oError;
        }
    }
}
