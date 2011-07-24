<?php
/**
 * Description of RepeatBaseModel
 *
 * @author Yoshihide
 */
class RepeatBaseModel extends Kyomu_AbstractBaseModel{
	
	public function getRepeats($column = false)
	{
		$repeats = array();
		$sql = 'SELECT *
				FROM repeats
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
				$repeats[] = $row['student_id'];
			}
		} else {
			$repeats = $stmt->fetchAll();
		}
		return $repeats;
	}
}
