<?php
/**
 * Description of ExtensionBaseModel
 *
 * @author Yoshihide
 */
class ExtensionBaseModel extends AbstractBaseModel{
    /**
	 *
	 * @return array
	 */
	public function getExtensionList(){
		$ret = array();
		$extension_dir = APPLICATION_PATH.EXTENSION_PATH;

		$installed_array = array_keys($this ->getInstalledList());
		$file_array = scandir($extension_dir);

		foreach($file_array as $file){
			//.がつくものは排除
			if(strstr($file, '.') == FALSE){
				//_が先頭に付くものはロードしない
				if(strpos($file, '_') === 0){
					continue;
				}
				//ディレクトリかどうか確認
				if(is_dir($extension_dir.'/'.$file) == TRUE){
					//そこにinstall.phpが存在するか確認
					if(file_exists($extension_dir.'/'.$file.'/install.php')){
						include_once $extension_dir.'/'.$file.'/install.php';
						//ハイフンが入っている場合はPascal形式(UCC)に修正
						$file = HStoCC($file);
						$class_name = $file.'Installer';
						$temp = new $class_name($this ->db);
						$ret[$file]['name'] = $temp ->name;
						$ret[$file]['name_ja'] = $temp ->name_ja;
						$ret[$file]['summary'] = $temp ->summary;
						if(in_array($temp ->name, $installed_array) == TRUE){
							$ret[$file]['is_installed'] = TRUE;
						}
						else{
							$ret[$file]['is_installed'] = FALSE;
						}
						$temp = NULL;
					}
				}
			}
		}
		
		return $ret;
	}

	/**
	 *
	 * @param ExtensionName $extension_name
	 * @return bool
	 */
	public function is_installed(ExtensionName $extension_name){
		$sql = "SELECT `extension_id` FROM `kyomu_extension`.`extensions` WHERE `extension_name` = :ext_name";
		$stmt = $this ->db ->prepare($sql);
		$stmt ->bindParam(':ext_name', $extension_name ->hs);
		$stmt ->execute();
		if($stmt ->rowCount() > 0){
			return TRUE;
		}
		return FALSE;
	}

	/**
	 *
	 * @return array
	 */
	public function getInstalledList(){
		$sql = "SELECT `extension_name`, `extension_name_ja`, `extension_summary` FROM `kyomu_extension`.`extensions`";
		$stmt = $this ->db ->prepare($sql);
		$stmt ->execute();

		$ret = array();
		while($row = $stmt ->fetch()){
			$ret[$row['extension_name']]['name'] = $row['extension_name'];
			$ret[$row['extension_name']]['name_ja'] = $row['extension_name_ja'];
			$ret[$row['extension_name']]['summary'] = $row['extension_summary'];
		}

		return $ret;
	}

	/**
	 *
	 * @param ExtensionName $extension_name
	 * @param string $extension_dir
	 * @return Extension_AbstractInstaller
	 */
	public function getExtensionInstallerInstance(ExtensionName $extension_name, $extension_dir){

		if(is_dir($extension_dir.'/'.$extension_name ->hs) == FALSE){
			throw new Zend_Exception('Extension \''.$extension_name ->cc.'\' does not exist.');
		}

		if(file_exists($extension_dir.'/'.$extension_name ->hs.'/install.php') == FALSE){
			throw new Zend_Exception('Extension \''.$extension_name->cc.'\' has no installer file.');
		}

		$installer_name = $extension_name ->cc.'Installer';
		if(class_exists($installer_name) == FALSE){
			require_once $extension_dir.'/'.$extension_name->hs.'/install.php';
		}

		if(class_exists($installer_name) == FALSE){
			throw new Zend_Exception('Extension \''.$extension_name->cc.'\' has no installer ('.$installer_name.').');
		}

		return new $installer_name($this ->db);
	}

	/**
	 *
	 * @param string $role_name
	 * @return array
	 */
	public function getRoleExtension($role_name){
		$sql = "SELECT `extension_name`, `ext_interface_action`, `role_ext_interface_id`, `ext_interface_name`, ".
					  "`role_ext_group_id`, `role_ext_group_name` ".
			   "FROM `kyomu_extension`.`role_ext_interfaces` ".
			       "LEFT JOIN `kyomu_extension`.`ext_interfaces` USING (`ext_interface_id`)".
			       "LEFT JOIN `kyomu_extension`.`extensions` USING (`extension_id`)".
			       "LEFT JOIN `kyomu_extension`.`role_ext_groups` USING (`role_ext_group_id`)".
				   "LEFT JOIN `kyomu_extension`.`roles` USING(`role_id`)".
			   "WHERE `role_name` = :role_name ".
			   "ORDER BY `role_ext_group_order` ASC, `role_ext_group_id` ASC, ".
					    "`role_ext_interface_order` ASC, `role_ext_interface_id` ASC";
		$stmt = $this ->db ->prepare($sql);
		$stmt ->bindParam(':role_name', $role_name);
		$stmt ->execute();

		$ret = array();
		while($row = $stmt ->fetch()){
			$temp = array();
			$temp['group_id'] = $row['role_ext_group_id'];
			$temp['name'] = $row['ext_interface_name'];
			$temp['role_if_id'] = $row['role_ext_interface_id'];
			$temp['extension'] = $row['extension_name'];
			$temp['action'] = $row['ext_interface_action'];

			$ret[$row['role_ext_group_name']][] = $temp;
		}

		return $ret;
	}
}
