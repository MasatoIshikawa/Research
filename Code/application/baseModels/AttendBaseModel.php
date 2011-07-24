<?php
class AttendBaseModel extends Kyomu_AbstractBaseModel
{
	public function getAttend($studentId, $examId)
	{
		$sql = 'SELECT * 
				FROM shuxtuseki_joukyou 
				WHERE student_id = :student_id
					AND exam_id = :exam_id
					AND year_id = :year_id';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':student_id', $studentId);
		$stmt->bindParam(':exam_id', $examId);
		$stmt->bindParam(':year_id', $this->yearId);
		$stmt->execute();
		return $stmt->fetch();
	}
	
	/*public function getAbsence($studentId, $examId)
	{
		$absence = array();
		$sql = 'SELECT * 
				FROM kextuka_joukyou 
				WHERE student_id = :student_id
					AND exam_id = :exam_id
					AND year_id = :year_id';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':student_id', $studentId);
		$stmt->bindParam(':exam_id', $examId);
		$stmt->bindParam(':year_id', $this->yearId);
		$stmt->execute();
		while ($row = $stmt->fetch()) {
			$absence[$row['subject_id']] = $row['kextuka_suu'];
		}
		return $absence;
	}*/

	public function getAbsence($studentId, $examId){
		$absence = array();
		$sql = 'SELECT `absent_count_number`, `class_count_number`,
					   `absent_count_table`.`subject_id`
				FROM `absent_count_table`
					LEFT JOIN `class_count_table` USING (`subject_id`)
				WHERE `absent_count_table`.`student_id` = :student_id
					AND `absent_count_table`.`exam_id` = :exam_id
					AND `absent_count_table`.`year_id` = :year_id';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':student_id', $studentId);
		$stmt->bindParam(':exam_id', $examId);
		$stmt->bindParam(':year_id', $this->yearId);
		$stmt->execute();
		while ($row = $stmt->fetch()) {
			$absence[$row['subject_id']]['absent_count'] = $row['absent_count_number'];
			$absence[$row['subject_id']]['class_count'] = $row['class_count_number'];
		}
		return $absence;
	}
	
/* 2011.4.25 バックアップ
 *
 	public function getClassAttend($classId, $examId)
	{
		$rows = array();
		$sql = 'SELECT student_id, you_shu_suu, sou_kextuseki_suu
				FROM shuxtuseki_joukyou j 
					LEFT JOIN belong USING (student_id, year_id)
				WHERE class_id = :class_id
					AND year_id = :year_id
					AND exam_id = :exam_id';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':class_id', $classId);
		$stmt->bindParam(':exam_id', $examId);
		$stmt->bindParam(':year_id', $this->yearId);
		$stmt->execute();
		while ($row = $stmt->fetch()) {
			$rows[$row['student_id']] = $row;
		}
		return $rows;
	}*/

	public function getClassAttend($classId, $examId)
	{
		$rows = array();
		$sql = 'SELECT student_id, attendance_statistics_needed_class_days, attendance_statistics_absence_days
				FROM attendance_statistics_table
					LEFT JOIN belong USING (student_id, year_id)
				WHERE class_id = :class_id
					AND year_id = :year_id
					AND exam_id = :exam_id';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':class_id', $classId);
		$stmt->bindParam(':exam_id', $examId);
		$stmt->bindParam(':year_id', $this->yearId);
		$stmt->execute();
		while ($row = $stmt->fetch()) {
			$row['you_shu_suu'] = $row['attendance_statistics_needed_class_days'];//要出席日数
			$row['sou_kextuseki_suu'] = $row['attendance_statistics_absence_days'];//総欠席日数
			$rows[$row['student_id']] = $row;
		}
		return $rows;
	}
}
