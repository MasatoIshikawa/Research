<?php
/**
 * Description of StudentDetailModel
 *
 * @author cota
 */
class StudentDetailBaseModel extends Kyomu_AbstractBaseModel{

    public function getStudentsInfo($class){

		$students = Array();

		$sql = 'SELECT `class_regadv`, `class_grade`
				FROM `classes`
				WHERE `class_id` = :class';
		
		$stmt = $this ->db ->prepare($sql);
		$stmt ->bindParam(':class', $class);
		$stmt ->execute();
		$info = $stmt ->fetch();


		$sql = 'SELECT `students`.`student_id`, `student_name`, `student_kana`, `student_sex`,
					`student_detail_phonenumber` as `phonenumber`,
					IFNULL(`student_detail_zipcode`,`student_parent_postcode`) as `zipcode`,
					IFNULL(`student_detail_address`,`student_parent_address`) as `address`,
					IFNULL(`family_name`,`student_parent_name`) as `parent_name`,
					`student_birth_date` as `birth_date`,`student_detail_jschool_pref`, `student_detail_jschool_name`,
					`belong_number` as `student_number`
				FROM `students`
				LEFT JOIN `belong` USING (`student_id`)
				LEFT JOIN `student_details` USING (`student_id`)
				LEFT JOIN `familys` ON `student_detail_parent`=`family_id`
				WHERE `student_available` = 1 AND `student_regadv` = :regadv AND `student_grade` = :grade AND `class_id` = :class
				ORDER BY `student_number` ASC';
		
		$stmt = $this ->db ->prepare($sql);
		$stmt ->bindParam(':regadv', $info['class_regadv']);
		$stmt ->bindParam(':grade', $info['class_grade']);
		$stmt ->bindParam(':class', $class);
		$stmt ->execute();
		while($row = $stmt ->fetch()){
			$students[] = $row;
		}

		return $students;
    }

    public function getStudentPhoto($id){
		
		$sql = 'SELECT `student_detail_photo` FROM `student_details` WHERE `student_detail_id` = :id';
		$stmt = $this ->db ->prepare($sql);
		$stmt ->bindParam(':id', $id);
		$stmt ->execute();

		return $stmt ->fetch();
    }

    public function getStudentInfo($student_id){

		//familysに家族が誰も登録されてぁEかった場合、studentsから自動登録、及びstudent_details_parentに関連付け
		$sql = 'SELECT `family_id` FROM `familys` WHERE `student_id` = :student_id';
		$stmt = $stmt = $this ->db ->prepare($sql);
		$stmt ->bindParam(':student_id', $student_id);
		$stmt ->execute();
		if($stmt ->fetch()==FALSE){

			$sql = 'SELECT `student_parent_postcode`, `student_parent_address`, `student_parent_name`
					FROM `students` WHERE `student_id` = :student_id';
			$stmt = $stmt = $this ->db ->prepare($sql);
			$stmt ->bindParam(':student_id', $student_id);
			$stmt ->execute();
			$info = $stmt ->fetch();
			$bind = array(
				'student_id' => $student_id,
				'family_name' => $info['student_parent_name'],
				'family_zipcode' => $info['student_parent_postcode'],
				'family_address' => $info['student_parent_address']
			);
			$this ->db ->insert('familys', $bind);

			$sql = 'SELECT `family_id` FROM `familys` WHERE `student_id` = :student_id';
			$stmt = $stmt = $this ->db ->prepare($sql);
			$stmt ->bindParam(':student_id', $student_id);
			$stmt ->execute();
			$info = $stmt ->fetch();

			$sql = 'SELECT * FROM `student_details` WHERE `student_id` = :student_id';
			$stmt = $stmt = $this ->db ->prepare($sql);
			$stmt ->bindParam(':student_id', $student_id);
			$stmt ->execute();
			if($stmt ->fetch()==FALSE){
				$bind = array(
					'student_id' => $student_id,
					'student_detail_parent' => $info['family_id']
				);
				$this ->db ->insert('student_details', $bind);
			}
			else{
				$bind = array('student_detail_parent' => $info['family_id']);
				$where = $this ->db ->quoteInto('student_id = ?', $student_id);
				$this ->db ->update('student_details', $bind,$where);
			}
		}

		$sql = 'SELECT `students`.`student_id`, `student_name`, `student_kana`, `student_sex`, `student_birth_date`,
					(SELECT `course_name` FROM `courses` WHERE `course_id`=`students`.`course_id`) as `course_name`,
					`student_detail_domicile`, `student_detail_zipcode`,`student_detail_address`,
					`student_detail_phonenumber`, `student_detail_nearest_station`,`student_detail_jschool_pref`,
					`student_detail_jschool_name`,`student_detail_parent`, `student_detail_academic_record`,
					`student_detail_employment_record`,`student_detail_hobby`, `student_detail_career_desire`,
					`student_detail_note`, `family_name`,`family_kana`, `family_relation`, `family_zipcode`,
					`family_address`, `family_phonenumber`,`family_work_place`, `family_work_number`
				FROM `students`
				LEFT JOIN `student_details` USING (`student_id`)
				LEFT JOIN `familys` ON `student_detail_parent`=`family_id`
				WHERE `students`.`student_id` = :student_id';
		
		$stmt = $this ->db ->prepare($sql);
		$stmt ->bindParam(':student_id', $student_id);
		$stmt ->execute();
		$student = $stmt ->fetch();
		
		$tmp = explode('-',$student['student_birth_date']);
		$student['year'] = $tmp[0];
		$student['month'] = $tmp[1];
		$student['day'] = $tmp[2];

		return $student;
    }

    public function getFamilyList($student_id){
		
		$sql = 'SELECT * FROM familys WHERE student_id = :student_id';
		$stmt = $this ->db ->prepare($sql);
		$stmt ->bindParam(':student_id', $student_id);
		$stmt ->execute();
		$family = array();
		while ($row = $stmt->fetch()) {
			$family[] = $row;
		}
		return $family;
    }

    public function getFamily($family_id){

		$sql = 'SELECT * FROM familys WHERE family_id = :family_id';
		$stmt = $this ->db ->prepare($sql);
		$stmt ->bindParam(':family_id', $family_id);
		$stmt ->execute();
		$family = $stmt->fetch();

		$tmp = explode('-',$family['family_birth_date']);
		$family['year'] = $tmp[0];
		$family['month'] = $tmp[1];
		$family['day'] = $tmp[2];

		return $family;
    }
}

