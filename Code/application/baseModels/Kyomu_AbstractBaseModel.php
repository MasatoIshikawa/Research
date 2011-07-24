<?php
require_once APPLICATION_PATH.'/baseModels/ConfigBaseModel.php';

/**
 * Description of Kyomu_AbstractBaseModel
 *
 * @author Yoshihide
 */
abstract class Kyomu_AbstractBaseModel extends AbstractBaseModel{
    /**
	 *
	 * @var int
	 */
	public $yearId;
	/**
	 *
	 * @var ConfigBaseModel
	 */
	public $configModel;

	protected function _initYearId(){
		$this ->configModel = new ConfigBaseModel();
		$this ->yearId = $this ->configModel ->getYearId();
	}

	public function validate($data, $validators) {
		$messages = array();
		foreach ($validators as $columnName => $validator) {
			if (!$validator->isValid($data[$columnName])) {
				foreach ($validator->getMessages() as $error) {
					$messages[] = $columnName . ': ' . $error;
				}
			}
		}
		if ($messages) {
			$error = '<ul>';
			foreach ($messages as $message) {
				$error .= '<li>' . $message . '</li>';
			}
			$error .= '</ul>';
			throw new Kyomu_Validate_Exception($error);
		}
	}
}
