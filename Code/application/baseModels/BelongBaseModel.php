<?php
/**
 * Description of BelongBaseModel
 *
 * @author Yoshihide
 */
class BelongBaseModel extends Kyomu_AbstractBaseModel{
	
	public function getStudentBelong($studentId, $mix = false)
	{
		$sql = 'SELECT *
				FROM belong
					LEFT JOIN classes USING (class_id)
					LEFT JOIN courses USING (course_id)
				WHERE student_id = :student_id
					AND year_id = :year_id';
		if ($mix) {
			$sql .= ' ORDER BY class_mix DESC';
		}
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':student_id', $studentId);
		$stmt->bindParam(':year_id', $this->yearId);

		$stmt->execute();
		if ($mix) {
			return $stmt->fetch();
		} else {
			return $stmt->fetchAll();
		}
	}

	public function getBelongStudents($classId, $name = false)
	{
		$students = array();
		$sql = 'SELECT *
				FROM belong
					LEFT JOIN students USING( student_id )
				WHERE class_id = :class_id
					AND year_id = :year_id
				ORDER BY belong_number';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':class_id', $classId);
		$stmt->bindParam(':year_id', $this->yearId);
		$stmt->execute();
		while ($row = $stmt->fetch()) {
			$students[$row['student_id']] = $name ? $row['student_name'] : $row;
		}
		return $students;
	}

	public function hasForeignStudents($classId)
	{
		$sql = 'SELECT count(*)
				FROM belong
					LEFT JOIN students USING( student_id )
				WHERE class_id = :class_id
					AND student_foreign = 1
					AND year_id = :year_id';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':class_id', $classId);
		$stmt->bindParam(':year_id', $this->yearId);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_COLUMN);
	}
}
