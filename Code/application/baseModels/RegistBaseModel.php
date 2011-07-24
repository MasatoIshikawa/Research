<?php
/**
 * Description of RegistBaseModel
 *
 * @author Yoshihide
 */
class RegistBaseModel extends Kyomu_AbstractBaseModel{

	public function getRegists($studentId)
	{
		$regists = array();
		$sql = 'SELECT * 
				FROM regists
					LEFT JOIN subjects USING ( subject_id )
				WHERE student_id = :student_id
					AND year_id = :year_id';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':student_id', $studentId);
		$stmt->bindParam(':year_id', $this->yearId);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public function isRegist($studentId, $subjectId, $yearId = null)
	{
		$sql = 'SELECT *
				FROM regists
				WHERE student_id = :student_id
					AND subject_id = :subject_id
					AND year_id = :year_id';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':student_id', $studentId);
		$stmt->bindParam(':subject_id', $subjectId);
		if ($yearId) {
			$stmt->bindParam(':year_id', $yearId);
		} else {
			$stmt->bindParam(':year_id', $this->yearId);
		}
		$stmt->execute();
		return $stmt->rowCount() > 0;
	}

	public function getRegistStudentsByClass($subjectId)
	{
		$sql = 'SELECT *
				FROM regists
					LEFT JOIN students USING ( student_id )
					LEFT JOIN belong USING ( student_id, year_id )
					LEFT JOIN classes USING ( class_id )
					LEFT JOIN (
						SELECT class_mix AS is_class_mix, subject_id
						FROM opens
							LEFT JOIN classes USING (class_id)
						) AS mix_checker USING ( subject_id )
				WHERE subject_id = :subject_id
					AND year_id = :year_id
					AND
						(
							( student_regadv = 1 AND class_grade = 1 AND class_mix = is_class_mix )
						OR
							( student_regadv = 1 AND class_grade IN (2,3,4,5) AND class_mix = 0 )
						OR
							student_regadv = 2
						)
				ORDER BY class_id, belong_number';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':subject_id', $subjectId);
		$stmt->bindParam(':year_id', $this->yearId);
		$stmt->execute();
		$rows = $stmt->fetchAll();



		return $rows;
		/*
		$students = array();
		foreach ($rows as $row) {
			if ($row['student_grade'] == 1 && $row['class_mix'] == 1) {
				$students[] = $row;
			} elseif (in_array($row['student_grade'], array(2,3,4,5)) && $row['class_mix'] == 0) {
				$students[] = $row;
			}
		}
		return $students;*/
	}

	/* 09.2.27 */
	public function getReportSubjects($studentId)
	{
		$sql = 'SELECT *
				FROM records
					LEFT JOIN subjects USING ( subject_id )
				WHERE student_id = :student_id
					AND year_id = :year_id
					AND subject_record = 1
					AND subject_inout = 1
				GROUP BY subject_id
				ORDER BY subject_id';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':student_id', $studentId);
		$stmt->bindParam(':year_id', $this->yearId);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public function getClassRegists($classId)
	{
		$regists = array();
		$sql = 'SELECT *
				FROM regists
					LEFT JOIN belong USING ( year_id, student_id )
					LEFT JOIN subjects USING ( subject_id )
				WHERE class_id = :class_id
					AND year_id = :year_id';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':class_id', $classId);
		$stmt->bindParam(':year_id', $this->yearId);
		$stmt->execute();
		while ($row = $stmt->fetch()) {
			$regists[$row['student_id']][] = $row['subject_id'];
		}
		return $regists;
	}

	public function getRegistStudents($subjectId, $onlyId = false)
	{
		$students = array();
		$sql = 'SELECT *
				FROM regists
					LEFT JOIN students USING ( student_id )
				WHERE subject_id = :subject_id
					AND year_id = :year_id
				ORDER BY student_id';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':subject_id', $subjectId);
		$stmt->bindParam(':year_id', $this->yearId);
		$stmt->execute();
		if ($onlyId) {
			while ($row = $stmt->fetch()) {
				$students[] = $row['student_id'];
			}
		} else {
			$students = $stmt->fetchAll();
		}
		return $students;
	}
}
