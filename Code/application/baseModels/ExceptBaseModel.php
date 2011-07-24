<?php
class ExceptBaseModel extends Kyomu_AbstractBaseModel
{
	public function getExcepted($examId)
	{
		$excepted = array();
		$sql = 'SELECT *
				FROM excepted_students
					LEFT JOIN excepted_subjects USING (excepted_student_id)
				WHERE exam_id = :exam_id
					AND year_id = :year_id';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':exam_id', $examId);
		$stmt->bindParam(':year_id', $this->yearId);
		$stmt->execute();
		while ($row = $stmt->fetch()) {
			$excepted[$row['student_id']][] = $row['subject_id'];
		}
		return $excepted;
	}
}