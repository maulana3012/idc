<?php
/*
 *  Copyright 2005 PT. ZONEKOM All Right Reserved
 *  Contact us	: zonekom at zonekom dot com
 *
 *  Author	: dskim
 *
 * $Id: zk_session.php,v 1.5 2008/02/28 04:49:13 dskim Exp $
 */

require_once "zk_dbconn.php";

Class ZKSession {
    
    var $initCommon = false;	// 최초 접속여부
    var $initMember = false;	// 회원 최초접속여부
	
    function ZKSession() {

    	if (!isset($_SESSION['createTime'])) {

    		//필수 세션변수 초기화
    		$this->setValue("createTime", time());	//최초 접속이 이루어진 시간
    		$this->setValue("ma_isLogin", false);	//회원 로그인 여부.  LOGIN_PROCESS에서 설정
    		$this->setValue("ma_idx", 0);			//회원 주키. LOGIN_PROCESS에서 설정
    		$this->setValue("ma_account", "");		//회원 아이디. LOGIN_PROCESS에서 설정
    		$this->setValue("loginTimestamp", 0);	//로그인 한 시각
			$this->setValue("isInitMember", true); //멤버 초기화 파일이 되었는지 여부
			$this->setValue("ma_authority", 0);
			$this->setValue("ma_grade", "");

			// SET NULL PERMISSION FOR EACH MODUL
			$res =& query("SELECT gr_idx FROM tb_grade ORDER BY gr_priority");
			while($row =& fetchRow($res)) {
				$this->setValue("ma_{$row[0]}", 0);
			}

    		$this->setInitCommon(true);
    	}
    }
    
    function getInitCommon() {
       return $this->initCommon;
    }
    
    function setInitCommon($boolean) {
        return $this->initCommon = $boolean;
    }
    
    function getValue($key) {
        return $_SESSION[$key];
    }
    
    function setValue($key, $value) {
        $_SESSION[$key] = $value;
    }

    function isLogin() {
        if($_SESSION['ma_isLogin']) {
            return true;
        } else {
            return new ZKError("ZK001", "LOGIN", "You need to login to access the system.</br>If you not access the system without log-out, Your account automatically will logout<br/></br>Please, Login First");
        }
    }

    function logout() {
        $this->setValue("ma_isLogin", false);	//회원 로그인 여부.  LOGIN_PROCESS에서 설정
        $this->setValue("ma_idx", 0);			//회원 주키. LOGIN_PROCESS에서 설정
        $this->setValue("ma_account", "");		//회원 아이디. LOGIN_PROCESS에서 설정
        $this->setValue("loginTimestamp", 0);	//로그인 한 시각
        $this->setValue("isInitMember", true); //멤버 초기화 파일이 되었는지 여부
		$this->setValue("ma_authority", 0);
		$this->setValue("ma_grade", "");

		// MAKE NULL PERMISSION FOR EACH MODUL
		$res =& query("SELECT gr_idx FROM tb_grade ORDER BY gr_priority");
		while($row =& fetchRow($res)) {
			$this->setValue("ma_{$row[0]}", 0);
		}

	}
}
?>