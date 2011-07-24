<?php
/**
 * Description of OpenBaseModel
 *
 * @author Yoshihide
 */
class OpenBaseModel extends Kyomu_AbstractBaseModel{
	
	public function getClassOpens($classId)
	{
		$sql = 'SELECT *
				FROM opens
					LEFT JOIN subjects USING( subject_id )
				WHERE class_id = :class_id
					AND year_id = :year_id';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':class_id', $classId);
		$stmt->bindParam(':year_id', $this->yearId);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public function getOpenClasses($subjectId)
	{
		$sql = 'SELECT *
				FROM opens
					LEFT JOIN classes USING ( class_id )
				WHERE subject_id = :subject_id
					AND year_id = :year_id';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':subject_id', $subjectId);
		$stmt->bindParam(':year_id', $this->yearId);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public function getOpenSubjects($classId, $name = false)
	{
		$subjects = array();
		$sql = 'SELECT *
				FROM opens
					LEFT JOIN subjects USING ( subject_id )
				WHERE class_id = :class_id
					AND year_id = :year_id
				ORDER BY subject_id';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':class_id', $classId);
		$stmt->bindParam(':year_id', $this->yearId);
		$stmt->execute();
		while ($row = $stmt->fetch()) {
			$subjects[$row['subject_id']] = $name ? $row['subject_name'] : $row;
		}
		return $subjects;
	}

	public function getExamOpenSubjects($classId, $examId, $name = false) {
		$subjects = array();
		$sql = 'SELECT *
				FROM opens
					LEFT JOIN subjects USING ( subject_id )
					LEFT JOIN exam_semester USING ( semester_id )
				WHERE class_id = :class_id
					AND exam_id = :exam_id
					AND year_id = :year_id
				ORDER BY subject_id';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':class_id', $classId);
		$stmt->bindParam(':exam_id', $examId);
		$stmt->bindParam(':year_id', $this->yearId);
		$stmt->execute();
		while ($row = $stmt->fetch()) {
			$subjects[$row['subject_id']] = $name ? $row['subject_name'] : $row;
		}
		return $subjects;
	}

	public function getOpen($subjectId)
	{
		$sql = 'SELECT *
				FROM opens
					LEFT JOIN classes USING ( class_id )
				WHERE subject_id = :subject_id
					AND year_id = :year_id';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':subject_id', $subjectId);
		$stmt->bindParam(':year_id', $this->yearId);
		$stmt->execute();
		return $stmt->fetchAll();
	}
}
