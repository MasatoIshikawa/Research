<?php
/**
 * Description of ChangeBaseModel
 *
 * @author Yoshihide
 */
class ChangeBaseModel extends Kyomu_AbstractBaseModel{

	public function getChange($changeId)
	{
		$sql = 'SELECT *
				FROM changes
				WHERE change_id = :change_id';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':change_id', $changeId);
		$stmt->execute();
		return $stmt->fetch();
	}

	public function getChangeCourses()
	{
		$changes = array();
		$sql = 'SELECT *
				FROM changes
					LEFT JOIN courses USING (course_id)
				WHERE year_id = :year_id';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':year_id', $this->yearId);
		$stmt->execute();
		while ($row = $stmt->fetch()) {
			$changes[$row['student_id']] = $row['course_course'];
		}
		return $changes;
	}

	public function getChanges($column = false)
	{
		$changes = array();
		$sql = 'SELECT *
				FROM changes
					LEFT JOIN courses USING (course_id)
					LEFT JOIN students USING (student_id)
					LEFT JOIN belong USING (student_id, year_id)
					LEFT JOIN classes USING (class_id)
				WHERE year_id = :year_id
					AND class_mix = 0';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':year_id', $this->yearId);
		$stmt->execute();
		if ($column) {
			while ($row = $stmt->fetch()) {
				$changes[] = $row['student_id'];
			}
		} else {
			$changes = $stmt->fetchAll();
		}
		return $changes;
	}
}
