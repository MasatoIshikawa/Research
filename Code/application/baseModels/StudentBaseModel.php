<?php
/**
 * Description of StudentBaseModel
 *
 * @author Yoshihide
 */
class StudentBaseModel extends Kyomu_AbstractBaseModel{
	public function getStudent($studentId)
	{
		$sql = 'SELECT *
				FROM students
				WHERE student_id = :student_id';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':student_id', $studentId);
		$stmt->execute();
		return $stmt->fetch();
	}

	public function getStudents($pair = false, $all = false, $sort = null, $order = null)
	{
		if ($sort) {
			if (!$order) {
				$order = 'ASC';
			}
			$query = $sort . ' ' . $order;
		} else {
			$query = 'student_regadv, student_grade, IF(student_grade = 1 AND student_regadv = 1, student_class, students.course_id), belong_number';
		}
		//$where = 'WHERE student_available = 1 AND class_mix = IF(student_grade = 1 and student_regadv = 1, 1, 0)';
		$where = 'WHERE student_available = 1 AND IF(class_id IS NOT NULL, (class_mix = IF(student_grade = 1 and student_regadv = 1, 1, 0)), 1)';
		//$where = 'WHERE 1 ';

		$students = array();
		$sql = 'SELECT *
				FROM students
					LEFT JOIN courses USING ( course_id )
					LEFT JOIN belong USING ( student_id )
					LEFT JOIN classes USING ( class_id )
				' . $where . '
					AND ((year_id = :year_id) OR (year_id IS NULL))
				'.((!empty($this ->user ->campus))?'AND students.campus_id = '.$this ->user ->campus:'').'
				ORDER BY ' . $query;
		//$this ->db ->query($sql);
	//	echo $sql."\n\n\n\n";
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':year_id', $this->yearId);
		$stmt->execute();
		if ($pair) {
			while ($row = $stmt->fetch()) {
				$students[$row['course_name']][$row['student_id']] = $row['student_name'];
			}
		} else {
			$students = $stmt->fetchAll();
		}
	//	var_dump($students);die();
		return $students;
	}

	public function getStudentDetail($studentId, $mix = false)
	{
		$sql = 'SELECT *
				FROM students
					LEFT JOIN belong USING ( student_id )
					LEFT JOIN classes USING ( class_id )
					LEFT JOIN courses ON students.course_id = courses.course_id
				WHERE student_id = :student_id
					AND year_id = :year_id
					AND class_mix = ';

		$sql .= $mix ? '1' : '0';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':student_id', $studentId);
		$stmt->bindParam(':year_id', $this->yearId);
		$stmt->execute();
		return $stmt->fetch();
	}

	public function isValidID($student_id, $using_ldap = FALSE){
		if($student_id[0] != 'a' && $student_id[0] != 's'){
			return FALSE;
		}

		if($this ->getStudent($student_id) === FALSE){
			return FALSE;
		}

		return TRUE;
	}

	public function convertIDtoNo($student_id){
		return substr($student_id, 1);
	}

	public function convertNotoID($student_no){
		if (strlen($student_no) == 5) {
			return 's'.$student_no;
		} elseif (strlen($student_no) == 4) {
			return 'a'.$student_no;
		} elseif (strlen($student_no) == 3) {
			return 'a0'.$student_no;
		} elseif (strlen($student_no) == 7) {
			$head = 's';
			switch(substr($student_no, 3, 2)){
				case '01':
					$head = 's';
					break;
				case '02':
					$head = 's';
					break;
				case '03':
					$head = 's';
					break;
				case '04':
					$head = 's';
					break;
				case '05':
					$head = 's';
					break;
				case '06':
					$head = 's';
					break;
				case '07':
					$head = 's';
					break;
				case '10':
					$head = 'a';
					break;
				case '20':
					$head = 'a';
					break;
			}
			return $head.$student_no;
		}
	}
}
