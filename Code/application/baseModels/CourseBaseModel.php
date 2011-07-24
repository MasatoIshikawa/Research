<?php
/**
 * Description of CourseBaseModel
 *
 * @author Yoshihide
 */
class CourseBaseModel extends Kyomu_AbstractBaseModel{
	
	public function getRegularCourses(){
		$sql = 'SELECT *
				FROM courses
				WHERE course_regadv = 1';
		if(isset($this ->user ->campus)){
			$sql .= " AND campus_id = ".$this ->user ->campus;
		}
		$stmt = $this->db->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public function getCourses($id_and_name_only = false){
		$sql = "SELECT * FROM `courses`";
		if(isset($this ->user ->campus)){
			$sql .= " WHERE campus_id = ".$this ->user ->campus;
		}
		$stmt = $this ->db ->prepare($sql);
		$stmt ->execute();
		if($id_and_name_only){
			$rets = array();
			while($row = $stmt ->fetch()){
				$rets[$row['course_id']] = $row['course_name'];
			}
			return $rets;
		}
		else{
			return $stmt ->fetchAll();
		}
	}

	public function getCourseId($course_name){
		$sql = "SELECT `course_id` FROM `courses` WHERE `course_course` = :course_name";
		$stmt = $this ->db ->prepare($sql);
		$stmt ->bindParam(':course_name', $course_name);
		$stmt ->execute();

		return $stmt ->fetch(PDO::FETCH_COLUMN);
	}
}
