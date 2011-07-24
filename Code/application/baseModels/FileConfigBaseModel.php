<?php
/**
 * Description of FileConfigBaseModel
 *
 * @author Yoshihide
 */
class FileConfigBaseModel extends AbstractBaseModel{
	/**
	 *
	 * @var string
	 */
	public $file_path;

	public function init(){
		$this ->file_path = APPLICATION_PATH.'/AutoUpdateConfig.php';
	}

	/**
	 *
	 * @param string $extension_path
	 */
	public function updateStaticConfig($extension_path){
		$ar = $this ->getControllerModelList($extension_path);
		$output = $this ->template($ar['controllers'], $ar['models']);
		file_put_contents($this ->file_path, $output);
	}

	/**
	 *
	 * @param array $controller_list
	 * @param array $model_list
	 * @return string
	 */
	public function template($controller_list, $model_list){
		$generated_date = date('Y/m/d H:i:s');
		$controller_list_str = implode(',', $controller_list);
		$model_list_str = implode(',', $model_list);
		$output = <<<TEXT
<?php
//
//	このスクリプトは自動生成されています。
//	THIS SCRIPT HAS BEEN GENERATED AUTOMATICALLY.
//
//	Generated $generated_date
//
//ExtensionControllers
\$ExtensionController_path = array($controller_list_str);
//
//ExtensionModels
\$ExtensionModel_path = array($model_list_str);
//
//EOF
TEXT;
		return $output;
	}

	/**
	 *
	 * @param string $path
	 * @return array
	 */
	public function getControllerModelList($path){
		$ret = array();

		$sql = "SELECT `extension_name`, `extension_controller_path`, `extension_model_path` ".
			   "FROM `kyomu_extension`.`extensions`";
		$stmt = $this ->db ->query($sql);
		$stmt ->execute();

		while($row = $stmt ->fetch()){
			$ret['controllers'][] = 'APPLICATION_PATH.\''.$path.'\'.\'/'.$row['extension_name'].$row['extension_controller_path'].'\'';
			if(!empty($row['extension_model_path'])){
				$ret['models'][] = 'APPLICATION_PATH.\''.$path.'\'.\'/'.$row['extension_name'].$row['extension_model_path'].'\'';
			}
		}
		return $ret;
	}


}
