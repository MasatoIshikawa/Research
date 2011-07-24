<?php
/**
 * Description of CertificateStudentCommuteBaseModel
 *
 * @author yoshihide
 */
class StudentCertificateCommuteBaseModel extends Kyomu_AbstractBaseModel{
	
	private $types = array('1', '3', '6');
	
	public function getTypes(){
		return $this ->types;
	}

	public function existType($type){
		return (array_search($type, $this ->types) !== FALSE)?TRUE:FALSE;
	}

	public function getNearestStation($student_id){
		$sql = "SELECT `student_detail_nearest_station`
				FROM `student_details`
				WHERE `student_id` = :student_id";
		$stmt = $this ->db ->prepare($sql);
		$stmt ->bindParam(':student_id', $student_id);
		$stmt ->execute();

		if(($info = $stmt ->fetch(PDO::FETCH_COLUMN)) == FALSE){
			return FALSE;
		}

		if(empty($info)){
			return FALSE;
		}
		
		return $info;
	}
}
