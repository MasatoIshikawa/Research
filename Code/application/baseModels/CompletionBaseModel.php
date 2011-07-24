<?php
class CompletionBaseModel extends Kyomu_AbstractBaseModel
{	
	public function isCompleted($studentId, $classGrade)
	{
		$sql = 'SELECT * 
				FROM completions
				WHERE student_id = :student_id
					AND completion_grade = :completion_grade';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':student_id', $studentId);
		$stmt->bindParam(':completion_grade', $classGrade);
		$stmt->execute();
		return count($stmt->fetchAll());
	}
	
	public function getCompletions($pair = false)
	{
		$comps = array();
		$sql = 'SELECT * 
				FROM completions
					LEFT JOIN students USING (student_id)
					LEFT JOIN belong USING (student_id, year_id)
					LEFT JOIN classes USING (class_id)
				WHERE year_id = :year_id
					AND NOT(class_grade = 1 AND class_mix = 1)
				ORDER BY class_id,  belong_number';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':year_id', $this->yearId);
		$stmt->execute();
		if ($pair) {
			while ($row = $stmt->fetch()) {
				$comps[] = $row['student_id'];
			}
		} else {
			$comps = $stmt->fetchAll();
		}
		return $comps;
	}
	
	public function getCompletion($studentId)
	{
		$sql = 'SELECT * 
				FROM completions
				WHERE student_id = :student_id
				ORDER BY completion_grade DESC';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':student_id', $studentId);
		$stmt->execute();
		return $stmt->fetch();
	}

}