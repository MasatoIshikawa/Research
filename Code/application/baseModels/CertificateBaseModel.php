<?php
/**
 * Description of CertificateBaseModel
 *
 * @author cota
 */
class CertificateBaseModel extends Kyomu_AbstractBaseModel{

	public function getGeneralRegists($student_id){
		$sql = 'SELECT `student_id`, `subject_id`, `subject_name`, `subject_genspe`, `subject_grade`, `subject_credit`
				FROM `regists` LEFT JOIN `subjects` USING(`subject_id`)
				WHERE `student_id`=:student_id  AND `subject_genspe`=1 AND `subject_credit`!=0 ORDER BY `subject_id` ';
		
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':student_id', $student_id);
		$stmt->execute();

		return $stmt->fetchAll();
	}


	public function getSpecialRegists($student_id){
		$sql = 'SELECT `student_id`, `subject_id`, `subject_name`, `subject_genspe`, `subject_grade`, `subject_credit`
				FROM `regists` LEFT JOIN `subjects` USING(`subject_id`)
				WHERE `student_id`=:student_id  AND `subject_genspe`=2 AND `subject_credit`!=0 ORDER BY `subject_id` ';

		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':student_id', $student_id);
		$stmt->execute();

		return $stmt->fetchAll();
	}

}
