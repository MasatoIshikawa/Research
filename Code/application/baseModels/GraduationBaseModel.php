<?php
class GraduationBaseModel extends Kyomu_AbstractBaseModel
{	
	function getStudents($classId)
	{
		$sql = 'SELECT * 
				FROM students
					LEFT JOIN courses USING ( course_id ) 
					LEFT JOIN belong USING (`student_id`)
					LEFT JOIN classes USING ( class_id ) 
					LEFT JOIN graduations USING ( student_id, year_id)
				WHERE class_id = :class_id
					AND year_id = :year_id
				ORDER BY belong_number';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':class_id', $classId);
		$stmt->bindParam(':year_id', $this->yearId);
		$stmt->execute();
		$students = $stmt->fetchAll();
		return $students;
	}
	
	public function getNumbers($yearId)
	{
		$i = 1;
		$sql = 'SELECT student_id, graduation_id
				FROM graduations
				WHERE year_id = :year_id';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':year_id', $yearId);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
	}
	
	function getGraduations($pair = false)
	{
		$grads = array();
		$sql = 'SELECT * 
				FROM graduations
				WHERE year_id = :year_id';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':year_id', $this->yearId);
		$stmt->execute();
		if ($pair) {
			while ($row = $stmt->fetch()) {
				$grads[] = $row['student_id'];
			}
		} else {
			$grads = $stmt->fetchAll();
		}
		return $grads;
	}
}