<?php
/**
 * Description of TanninBaseModel
 *
 * @author Yoshihide
 */
class TanninBaseModel extends Kyomu_AbstractBaseModel{

	public function getTeacherTannin($teacherId)
	{
		$sql = 'SELECT *
				FROM tannin
				WHERE teacher_id = :teacher_id
					AND year_id = :year_id';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':teacher_id', $teacherId);
		$stmt->bindParam(':year_id', $this->yearId);
		$stmt->execute();
		return $stmt->fetch();
	}

	public function getClassTannin($classId){
		$sql = "SELECT * ".
			   "FROM `tannin` ".
			       "LEFT JOIN `teachers` USING (`teacher_id`) ".
			   "WHERE `class_id` = :class_id ".
				 "AND `year_id` = :year_id";
		$stmt = $this ->db ->prepare($sql);
		$stmt ->bindParam(':class_id', $classId);
		$stmt ->bindParam(':year_id', $this ->yearId);
		$stmt ->execute();
		return $stmt ->fetch();
	}

	public function isTannin($teacherId, $studentId)
	{
		$sql = 'SELECT *
				FROM belong
					LEFT JOIN tannin USING ( class_id, year_id )
				WHERE teacher_id = :teacher_id
					AND student_id = :student_id
					AND year_id = :year_id';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':teacher_id', $teacherId);
		$stmt->bindParam(':student_id', $studentId);
		$stmt->bindParam(':year_id', $this->yearId);
		$stmt->execute();
		$rows = $stmt->fetchAll();
		return count($rows) >= 1;
	}

	public function isTanninClass($teacherId, $classId)
	{
		$sql = 'SELECT *
				FROM tannin
				WHERE teacher_id = :teacher_id
					AND class_id = :class_id
					AND year_id = :year_id';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':teacher_id', $teacherId);
		$stmt->bindParam(':class_id', $classId);
		$stmt->bindParam(':year_id', $this->yearId);
		$stmt->execute();
		$rows = $stmt->fetchAll();
		return count($rows) >= 1;
	}

}
