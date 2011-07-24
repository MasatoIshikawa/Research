<?php
/**
 * Description of ConfigBaseModel
 *
 * @author Yoshihide
 */
class ConfigBaseModel extends AbstractBaseModel{
	public $_yearId;

	public function setYearId($yearId)
	{
		$this->_yearId = $yearId;
	}

	public function getYearId()
	{
		if ($this->_yearId) {
			return $this->_yearId;
		} else {
			return $this->getConfig('year_id');
		}
	}

	public function getConfig($configName)
	{
		$sql = 'SELECT *
				FROM configs
				WHERE config_name = :configName';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':configName', $configName);
		$stmt->execute();
		$row = $stmt->fetch();
		return $row['config_value'];
	}

	public function setConfig($configName, $configValue)
	{
		$sql = 'UPDATE configs
				SET config_value = :configValue
				WHERE config_name = :configName';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':configName', $configName);
		$stmt->bindParam(':configValue', $configValue);
		$stmt->execute();
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

	public function get($configName)
	{
		$sql = 'SELECT `config_value`
				FROM configs
				WHERE config_name = :configName';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':configName', $configName);
		$stmt->execute();
		$row = $stmt->fetch();
		return $row['config_value'];
	}

	public function set($configName, $configValue)
	{
		$sql = 'UPDATE configs
				SET config_value = :configValue
				WHERE config_name = :configName';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':configName', $configName);
		$stmt->bindParam(':configValue', $configValue);
		$stmt->execute();
	}

	public function getExamId()
	{
		return $this->get('exam_id');
	}
}
