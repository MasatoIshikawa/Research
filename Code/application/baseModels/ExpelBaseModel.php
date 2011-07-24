<?php
/**
 * Description of ExpelBaseModel
 *
 * @author Yoshihide
 */
class ExpelBaseModel extends Kyomu_AbstractBaseModel{

	public function getExpels($column = false)
	{
		$expels = array();
		$sql = 'SELECT *
				FROM expels
					LEFT JOIN students USING (student_id)
					LEFT JOIN belong USING (student_id, year_id)
					LEFT JOIN classes USING (class_id)
				WHERE year_id = :year_id
					AND class_mix = IF(student_grade = 1 AND student_regadv = 1, 1, 0)';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':year_id', $this->yearId);
		$stmt->execute();
		if ($column) {
			while ($row = $stmt->fetch()) {
				$expels[] = $row['student_id'];
			}
		} else {
			$expels = $stmt->fetchAll();
		}
		return $expels;
	}

	public function getExpelStudent($studentId)
	{
		$sql = 'SELECT *
				FROM expels
				WHERE student_id = :student_id';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':student_id', $studentId);
		$stmt->execute();
		return $stmt->fetch();
	}
}
