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
    
    var $initCommon = false;	// ���� ���ӿ���
    var $initMember = false;	// ȸ�� �������ӿ���
	
    function ZKSession() {

    	if (!isset($_SESSION['createTime'])) {

    		//�ʼ� ���Ǻ��� �ʱ�ȭ
    		$this->setValue("createTime", time());	//���� ������ �̷���� �ð�
    		$this->setValue("ma_isLogin", false);	//ȸ�� �α��� ����.  LOGIN_PROCESS���� ����
    		$this->setValue("ma_idx", 0);			//ȸ�� ��Ű. LOGIN_PROCESS���� ����
    		$this->setValue("ma_account", "");		//ȸ�� ���̵�. LOGIN_PROCESS���� ����
    		$this->setValue("loginTimestamp", 0);	//�α��� �� �ð�
			$this->setValue("isInitMember", true); //��� �ʱ�ȭ ������ �Ǿ����� ����
			$this->setValue("ma_authority", 0);
			$this->setValue("ma_workgroup", "");
			$this->setValue("ma_is_manager_all", 0);
			$this->setValue("ma_is_manager_idc", 0);
			$this->setValue("ma_is_manager_med", 0);
			$this->setValue("ma_is_manager_mep", 0);
			$this->setValue("ma_is_marketing_idc", false);
			$this->setValue("ma_is_marketing_med", false);
			$this->setValue("ma_see_all", false);
			$this->setValue("ma_see_tab", 0);

			// SET NULL PERMISSION FOR EACH MODUL
			$sql = "SELECT gr_access, gr_idx FROM tb_grade";
			$res =& query($sql);
			while($row =& fetchRow($res)) {
				$this->setValue("ma_".$row[0]."_".$row[1]."", 0);
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
        $this->setValue("ma_isLogin", false);	//ȸ�� �α��� ����.  LOGIN_PROCESS���� ����
        $this->setValue("ma_idx", 0);			//ȸ�� ��Ű. LOGIN_PROCESS���� ����
        $this->setValue("ma_account", "");		//ȸ�� ���̵�. LOGIN_PROCESS���� ����
        $this->setValue("loginTimestamp", 0);	//�α��� �� �ð�
        $this->setValue("isInitMember", true); //��� �ʱ�ȭ ������ �Ǿ����� ����
		$this->setValue("ma_authority", 0);
		$this->setValue("ma_workgroup", "");
		$this->setValue("ma_is_manager_all", 0);
		$this->setValue("ma_is_manager_idc", 0);
		$this->setValue("ma_is_manager_med", 0);
		$this->setValue("ma_is_manager_mep", 0);
		$this->setValue("ma_is_marketing_idc", false);
		$this->setValue("ma_is_marketing_med", false);
		$this->setValue("ma_see_all", false);
		$this->setValue("ma_see_tab", 0);

		// MAKE NULL PERMISSION FOR EACH MODUL
		$sql = "SELECT gr_access, gr_idx FROM tb_grade";
		$res =& query($sql);
		while($row =& fetchRow($res)) {
				$this->setValue("ma_".$row[0]."_".$row[1]."", 0);
		}
	}
}
?>