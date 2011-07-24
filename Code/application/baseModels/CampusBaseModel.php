<?php
/**
 * Description of CampusBaseModel
 *
 * @author yoshihide
 */
class CampusBaseModel extends Kyomu_AbstractBaseModel{

	public function getCampusIdFromName($name){
		$sql = "SELECT `campus_id` FROM `campuses` ".
			   "WHERE `campus_name` = :campus_name";
		$stmt = $this ->db ->prepare($sql);
		$stmt ->bindValue(':campus_name', $name);
		$stmt ->execute();
		return $stmt ->fetch(PDO::FETCH_COLUMN);
	}
}
