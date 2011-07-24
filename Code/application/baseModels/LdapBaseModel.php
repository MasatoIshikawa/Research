<?php
/**
 * Description of LdapBaseModel
 *
 * @author yoshihide
 */
class LdapBaseModel extends AbstractBaseModel{

	//LDAPから区分を取得（職員、教員、学生）
	public function getLDAPInfo($username){
		$ret = array();

	/*	if(DEBUGMODE && strpos($_SERVER['SERVER_ADDR'], '133.104.') === FALSE){
			$ret['username'] = $username;
			return $ret;
		}*/

		$ldap_settings = Zend_Registry::get('ldap_settings');

		//接続
		$conn = @ldap_connect($ldap_settings['host'], $ldap_settings['port']);

		//失敗したらZFのエラーページに飛ばす
		if($conn == false){
			throw new Zend_Exception("Cannot connect to LDAP server.");
		}

		//バージョン設定
		ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, $ldap_settings['protocol_version']);

		if (!@ldap_bind($conn, 'cn=snct,'.$ldap_settings['base_dn'], 'snct')) {
			throw new Zend_Exception("Cannot bind to LDAP server.");
		}

		$ous = array('student', 'teacher', 'staff', 'tempstudent', 'tempteacher', 'tempstaff');

		$base_dn = array();
		$conns = array();
		foreach($ous as $ou){
			$base_dn[] = 'ou='.$ou.',ou=Users,'.$ldap_settings['base_dn'];
			$conns[] = $conn;
		}

		$filter = "(uid=".$username.")";
		$getonly = array('cn', 'mail');
		$res = ldap_search($conns, $base_dn, $filter, $getonly);

		if($res == false){
			throw new Zend_Exception("Cannot search data");
		}

		$search = false;
		foreach($res as $key => $result){
			if(ldap_count_entries($conn, $result) > 0){
				$search = $result;
				break;
			}
		}

		if($search === false){
			return false;
		}

		$info = ldap_get_entries($conn, $search);

		$ret['username'] = $username;
		$ret['mail'] = $info[0]['mail'][0];
		$ret['name'] = $info[0]['cn'][0];
		$ret['group'] = $ous[$key];

		return $ret;
	}
}
