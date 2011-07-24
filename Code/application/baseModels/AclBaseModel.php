<?php
require_once APPLICATION_PATH.BASEMODEL_PATH.'/LdapBaseModel.php';

class AclBaseModel extends AbstractBaseModel{
	public $LdapModel;

	public function _initModels(){
		$this ->LdapModel = new LdapBaseModel();
	}

	public function getUserRole($user_id){
		$roles = array();

		if($user_id == "admin"){
			$roles[0]['name'] = "admin";
			$roles[0]['name_ja'] = "管理者";
			return $roles;
		}

		$sql = 'SELECT `role_name`, `role_name_ja`, `role_type`, `role_regex`
				FROM `kyomu_extension`.`roles`
					LEFT JOIN `kyomu_extension`.`rolelists` USING (`role_id`)
				WHERE (`user_id` = :user_id OR `role_type` = 1) AND NOT(`role_name` = \'All\')';
		$stmt = $this ->db ->prepare($sql);
		$stmt ->bindParam(':user_id', $user_id);
		$stmt ->execute();
		
		while($row = $stmt ->fetch()){
			if($row['role_type'] == 0){
				$temp['name'] = $row['role_name'];
				$temp['name_ja'] = $row['role_name_ja'];
				$roles[] = $temp;
			}
			else{
				if(preg_match($row['role_regex'], $user_id) == 1){
					$temp['name'] = $row['role_name'];
					$temp['name_ja'] = $row['role_name_ja'];
					$roles[] = $temp;
				}
			}
		}

		$user_info = $this ->LdapModel ->getLDAPInfo($user_id);

		if(strpos(strtolower($user_info['group']), 'teacher') !== false){
			array_push($roles, array('name' => 'teacher', 'name_ja' => '教員'));
		}

		return $roles;
	}

	public function getDetailRole($role_name){
		$sql = 'SELECT `role_id`, `role_name`, `role_name_ja`, `role_type`, `role_regex`
				FROM `kyomu_extension`.`roles`
				WHERE `role_name` = :role_name';
		$stmt = $this ->db ->prepare($sql);
		$stmt ->bindParam(':role_name', $role_name);
		$stmt ->execute();

		$row = $stmt ->fetch();

		if($row == FALSE){
			return FALSE;
		}
		$temp['id'] = $row['role_id'];
		$temp['name'] = $row['role_name'];
		$temp['name_ja'] = $row['role_name_ja'];
		$temp['type'] = ($row['role_type'] == 0)?'list':'regular expression';
		$temp['type_raw'] = $row['role_type'];
		$temp['regex'] = $row['role_regex'];

		return $temp;
	}

	public function getRoles(){
		$sql = "SELECT `role_name` FROM `kyomu_extension`.`roles`";
		$stmt = $this ->db ->prepare($sql);
		$stmt ->execute();

		$ret = $stmt ->fetchAll(PDO::FETCH_COLUMN);

		//必ずAllを入れる
		//array_push($ret, 'All');

		return $ret;
	}

	public function getRolesWithJa(){
		$sql = "SELECT `role_name`, `role_name_ja` FROM `kyomu_extension`.`roles`";
		$stmt = $this ->db ->prepare($sql);
		$stmt ->execute();

		$ret = array();

		while($row = $stmt ->fetch()){
			$temp['name'] = $row['role_name'];
			$temp['name_ja'] = $row['role_name_ja'];
			$ret[] = $temp;
		}

		//必ずAllを入れる
		//array_push($ret, 'All');

		return $ret;
	}

	public function getDetailRoles(){
		$sql = "SELECT `role_id`,`role_name`,`role_name_ja`,`role_type`,`role_regex` ".
			   "FROM `kyomu_extension`.`roles`";
		$stmt = $this ->db ->prepare($sql);
		$stmt ->execute();

		$ret = array();
		while($row = $stmt ->fetch()){
			if(strtolower($row['role_name']) == 'all'){
				continue;
			}
			$temp['id'] = $row['role_id'];
			$temp['name'] = $row['role_name'];
			$temp['name_ja'] = $row['role_name_ja'];
			$temp['type'] = ($row['role_type'] == 0)?'list':'regular expression';
			$temp['type_raw'] = $row['role_type'];
			$temp['regex'] = $row['role_regex'];
			$ret[] = $temp;
		}

		return $ret;
	}

	public function getRoleUserList($role_name){
		$sql = "SELECT `user_id` FROM `kyomu_extension`.`rolelists` ".
			   "WHERE `role_id` = (SELECT `role_id` FROM `kyomu_extension`.`roles` WHERE `role_name` = :role_name)";
		$stmt = $this ->db ->prepare($sql);
		$stmt ->bindParam(':role_name', $role_name);
		$stmt ->execute();

		return $stmt ->fetchAll(PDO::FETCH_COLUMN);
	}

	public function isAclAllowed($acl_role_name, $acl_module, $acl_controller, $acl_action){
		//新しいACLを作成
		$acl = new Zend_Acl();

		//固定ロールの追加
		$acl ->addRole(new Zend_Acl_Role('admin'));
		$acl ->addRole(new Zend_Acl_Role('all_auth_user'));
		$acl ->addRole(new Zend_Acl_Role('multi'), 'all_auth_user');

		//Role一覧の取得
		$role_list = $this ->getRoles();

		//ACLに追加
		foreach($role_list as $role){
			if(strtolower($role) == 'all'){
				continue;
			}
			$acl ->addRole(new Zend_Acl_Role($role), 'all_auth_user');
		}

		//固定リソースの追加
		$acl ->add(new Zend_Acl_Resource('default:acl'));
		$acl ->add(new Zend_Acl_Resource('default:auth'));
		$acl ->add(new Zend_Acl_Resource('default:config'));
		$acl ->add(new Zend_Acl_Resource('default:error'));
		$acl ->add(new Zend_Acl_Resource('default:index'));
		$acl ->add(new Zend_Acl_Resource('default:main'));

		//Resourceを取得
		$sql = "SELECT `controller_name`, `module_name` ".
			   "FROM `kyomu_extension`.`controllers` ".
			       "LEFT JOIN `kyomu_extension`.`modules` USING (`module_id`)";
		$stmt = $this ->db ->prepare($sql);
		$stmt ->execute();

		//ACLにResourceを登録
		while($row = $stmt ->fetch()){
			$acl ->add(new Zend_Acl_Resource($row['module_name'].':'.$row['controller_name']));
		}

		//固定制御構文の追加
		$acl ->allow(null, 'default:index');
		$acl ->allow(null, 'default:auth', 'login');
		$acl ->allow(null, 'default:auth', 'logout');
	//	$acl ->allow('all_auth_user', 'default:auth', 'logout');
		$acl ->allow('admin', 'default:acl');
		$acl ->allow('admin', 'default:auth');
		$acl ->allow('admin', 'default:error');
		$acl ->allow('admin', 'default:index');
		$acl ->allow('admin', 'default:main');
		$acl ->allow('admin', 'default:config');
		$acl ->allow('multi', 'default:auth', 'multi');
		$acl ->allow('multi', 'default:auth', 'change-role');
		$acl ->allow('all_auth_user', 'default:error');
		$acl ->allow('all_auth_user', 'extension:error');

		//ACL一覧表を取得
		$sql = "SELECT `role_name`, `module_name`, `controller_name`, `action_name`, `privilege_isallowed` ".
			   "FROM `kyomu_extension`.`actions` ".
			       "CROSS JOIN `kyomu_extension`.`roles` ".
			       "LEFT JOIN `kyomu_extension`.`controllers` USING (`controller_id`) ".
			       "LEFT JOIN `kyomu_extension`.`modules` USING (`module_id`) ".
			       "LEFT JOIN `kyomu_extension`.`privileges` USING (`action_id`, `role_id`) ".
			   "ORDER BY `roles`.`role_id` ASC, ".
			            "`modules`.`module_id` ASC, ".
						"`controllers`.`controller_id` ASC, ".
						"`actions`.`action_id` ASC";
		$stmt = $this ->db ->prepare($sql);
		$stmt ->execute();

		//処理しやすいように配列を生成
		$privileges = array();
		while($row = $stmt ->fetch()){
			$privileges[$row['role_name']][$row['module_name']][$row['controller_name']][$row['action_name']] = $row['privilege_isallowed'];
		}

		foreach($privileges as $role_name => $mcap){
			if(strtolower($role_name) == 'all'){
				$role_name = null;
			}
			foreach($mcap as $module => $cap){
				foreach($cap as $controller => $ap){
					$allowed = array();
					$denyed = array();
					foreach($ap as $action => $privilege){
						if(empty($privilege)){
							continue;
						}

						if($privilege == 1){
							$allowed[] = $action;
						}
						else{
							$denyed[] = $action;
						}
					}
					if(count($allowed) !== 0){
						$acl ->allow($role_name, $module.':'.$controller, $allowed);
					}
					if(count($denyed) !== 0){
						$acl ->deny($role_name, $module.':'.$controller, $denyed);
					}
				}
			}
			if($role_name !== null){
				$acl ->allow($role_name, 'default:main', $role_name);
			}
		}

		$sql = "SELECT `role_name`, `role_auth` FROM `kyomu_extension`.`roles`";
		$stmt = $this ->db ->prepare($sql);
		$stmt ->execute();

		while($row = $stmt ->fetch()){
			if($row['role_auth'] == '1'){
				$acl ->allow($row['role_name'], 'default:auth', 'login-changer');
			}
		}

	/*	if($acl ->isAllowed($acl_role_name, $acl_module.':'.$acl_controller, $acl_action) == false){
			echo 'request: '.$acl_role_name.'+'.$acl_module.':'.$acl_controller.'+'.$acl_action. "<br />";
			die();
		}*/
		return true;
		return $acl ->isAllowed($acl_role_name, $acl_module.':'.$acl_controller, $acl_action);
	}
}

