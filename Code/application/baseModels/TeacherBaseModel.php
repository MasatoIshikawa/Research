<?php
/**
 * Description of TeacherBaseModel
 *
 * @author Yoshihide
 */
class TeacherBaseModel extends Kyomu_AbstractBaseModel{

	public function getTeacher($teacherId)
	{
		$sql = 'SELECT *
				FROM teachers
					LEFT JOIN courses USING ( course_id )
				WHERE teacher_id = :teacher_id';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':teacher_id', $teacherId);
		$stmt->execute();
		return $stmt->fetch();
	}

	public function getTeachers($pair = false, $all = false, $sort = null, $order = null)
	{
		if ($sort) {
			if (!$order) {
				$order = 'ASC';
			}
			$query = $sort . ' ' . $order;
		} else {
			$query = 'teacher_available DESC, IF(course_id = 0, 100, course_id), teacher_id';
		}
		$campus = (isset($this ->user ->campus)) ? 'teachers.campus_id = '.$this ->user ->campus : '';
		$where = $all ? ((empty($campus))?'':'WHERE '.$campus) : 'WHERE teacher_available = 1 '.((empty($campus))?'':'AND '.$campus);
		$teachers = array();
		$sql = 'SELECT *
				FROM teachers
					LEFT JOIN courses USING ( course_id )
				' . $where . '
				ORDER BY ' . $query;
		$stmt = $this->db->prepare($sql);
		$stmt->execute();
		if ($pair) {
			while ($row = $stmt->fetch()) {
				if (empty($row['course_name'])) {
					$row['course_name'] = '非常勤';
				}
				$teachers[$row['course_name']][$row['teacher_id']] = $row['teacher_name'];
			}
		} else {
			$teachers = $stmt->fetchAll();
		}
		return $teachers;
	}
}
