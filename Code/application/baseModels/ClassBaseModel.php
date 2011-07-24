<?php
/**
 * Description of ClassBaseModel
 *
 * @author Yoshihide
 */
class ClassBaseModel extends Kyomu_AbstractBaseModel{

	public function getClass($classId)
	{
		$sql = 'SELECT * 
				FROM classes
				WHERE class_id = :class_id
				ORDER BY class_id';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':class_id', $classId);
		$stmt->execute();
		return $stmt->fetch();
	}

	public function getClasses($pair = false, $grade = false, $abbr = false, $available_only = false)
	{
		$classes = array();
		$sql = 'SELECT *
				FROM classes '.
		((empty($this ->user ->campus)) ? '' : 'WHERE campus_id = '.$this ->user ->campus.' ');
		if ($grade) {
			$sql .= ((empty($this ->user ->campus))?'WHERE':'AND').' class_grade = :class_grade ';
		}
		if($available_only){
			$sql .= ((strpos($sql, 'WHERE') === FALSE)?'WHERE':'AND').' class_available = 1 ';
		}
		$sql .= 'ORDER BY class_id ASC';
		$stmt = $this->db->prepare($sql);
		if ($grade) {
			$stmt->bindParam(':class_grade', $grade);
		}
		$stmt->execute();
		if ($pair) {
			while ($row = $stmt->fetch()) {
				if ($abbr) {
					$str = $row['class_abbr'];
				} else {
					switch ($row['class_class']) {
						case 'R':
						case 'E':
						case 'C':
							$space = ' ';
							break;
						case 'I':
							$space = '  ';
							break;
						default:
							$space = ' ';
					}
					$str = '[' . $row['class_abbr'] . ']' . $space . $row['class_name'];
				}
				$classes[$row['class_id']] = $str;
			}
		} else {
			$classes = $stmt->fetchAll();
		}
		return $classes;
	}

	public function getKeyClasses($available_only = false)
	{
		foreach ($this ->getClasses(false, false, false, $available_only) as $class) {
			$regadv = $class['class_regadv'];
			$foreign = $class['class_foreign'];
			$transfer = $class['class_transfer'];
			$grade = $class['class_grade'];
			$c_class = $class['class_class'];
			$classes[$regadv][$foreign][$transfer][$grade][$c_class] = $class;
		}
		return $classes;
	}

	public function getPreviousClass($classId)
	{
		$sql = 'SELECT *
				FROM classes
				WHERE campus_id = '.$this ->user ->campus.'
				  AND class_id < :class_id
				ORDER BY class_id DESC
				LIMIT 0, 1';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':class_id', $classId);
		$stmt->execute();
		return $stmt->fetch();
	}

	public function getNextClass($classId)
	{
		$sql = 'SELECT *
				FROM classes
				WHERE campus_id = '.$this ->user ->campus.'
				  AND class_id > :class_id
				ORDER BY class_id
				LIMIT 0, 1';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':class_id', $classId);
		$stmt->execute();
		return $stmt->fetch();
	}
}
