<?php
/**
 * Description of JugyoBaseModel
 *
 * @author Yoshihide
 */
class JugyoBaseModel extends Kyomu_AbstractBaseModel{

	public function getEntriedYears($subjectId)
	{
		$sql = 'SELECT *
				FROM jugyo_entries
					LEFT JOIN years USING (year_id)
				WHERE subject_id = :subject_id
				GROUP BY year_id';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':subject_id', $subjectId);
		$stmt->execute();
		return $stmt->fetchAll();
	}
}
