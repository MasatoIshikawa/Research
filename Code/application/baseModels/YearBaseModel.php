<?php
/**
 * Description of YearBaseModel
 *
 * @author Yoshihide
 */
class YearBaseModel extends Kyomu_AbstractBaseModel{

    public function getYear($yearId)
	{
		$sql = 'SELECT *
				FROM years
				WHERE year_id = :year_id';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':year_id', $yearId);
		$stmt->execute();
		return $stmt->fetch();
	}


	public function getYears($pair = false, $length = false, $minYearId = false)
	{
		$years = array();
		$sql = 'SELECT *
				FROM years ';
		if ($minYearId) {
			$sql .= "WHERE year_id >= $minYearId ";
		}
		$sql .= 'ORDER BY year_year DESC ';
		if ($length) {
			$sql .= "LIMIT 0, $length";
		}
		$stmt = $this->db->prepare($sql);
		$stmt->execute();
		if ($pair) {
			while ($row = $stmt->fetch()) {
				$years[$row['year_id']] = $row['year_name'];
			}
		} else {
			$years = $stmt->fetchAll();
		}
		return $years;
	}
}
