<?php
/**
 * Description of ChargeBaseModel
 *
 * @author Yoshihide
 */
class ChargeBaseModel extends Kyomu_AbstractBaseModel{
	
	public function getChargeTeachers($subjectId)
	{
		$sql = 'SELECT *
				FROM charges
					LEFT JOIN teachers USING ( teacher_id )
					LEFT JOIN courses USING ( course_id )
				WHERE subject_id = :subject_id
					AND year_id = :year_id';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':subject_id', $subjectId);
		$stmt->bindParam(':year_id', $this->yearId);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public function isCharge($teacherId, $subjectId, $yearId = null)
	{
		$sql = 'SELECT *
				FROM charges
				WHERE teacher_id = :teacher_id
					AND subject_id = :subject_id
					AND year_id = :year_id';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':teacher_id', $teacherId);
		$stmt->bindParam(':subject_id', $subjectId);
		if ($yearId) {
			$stmt->bindParam(':year_id', $yearId);
		} else {
			$stmt->bindParam(':year_id', $this->yearId);
		}
		$stmt->execute();
		return (bool)$stmt->fetch();
	}

	public function getTeacherCharges($teacherId)
	{
		$sql = 'SELECT *, count(student_id) AS students
				FROM charges
					LEFT JOIN subjects USING ( subject_id )
					LEFT JOIN semesters USING (semester_id)
					LEFT JOIN regists USING ( subject_id, year_id )
				WHERE teacher_id = :teacher_id
					AND year_id = :year_id
				GROUP BY subject_id
				ORDER BY subject_regadv, subject_grade, subject_class, semester_id';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':teacher_id', $teacherId);
		$stmt->bindParam(':year_id', $this->yearId);
		$stmt->execute();
		return $stmt->fetchAll();
	}
}
