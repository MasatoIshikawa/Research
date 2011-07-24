<?php
/**
 * Description of SubjectBaseModel
 *
 * @author Yoshihide
 */
class SubjectBaseModel extends Kyomu_AbstractBaseModel{
	
    public function getSubject($subjectId)
	{
		$sql = 'SELECT *
				FROM subjects
					LEFT JOIN semesters USING (semester_id)
				WHERE subject_id = :subject_id';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':subject_id', $subjectId);
		$stmt->execute();
		return $stmt->fetch();
	}


	public function getSubjects($pair = false, $all = false, $sort = null, $order = null)
	{
		if ($sort) {
			if (!$order) {
				$order = 'ASC';
			}
			$query = $sort . ' ' . $order;
		} else {
			$query = 'subject_inout, subject_regadv, subject_grade, subject_abbr, subject_id';
		}
		$where = 'WHERE subject_available = 1 '
				 .(empty($this ->user ->campus) ? '' : 'AND campus_id = '.$this ->user ->campus.' ')
				 .($all ? '' : 'AND subject_inout = 1 ');
		$subjects = array();
		$sql = 'SELECT *
				FROM subjects
				'. $where .'
				ORDER BY ' . $query;
		$stmt = $this->db->prepare($sql);
		$stmt->execute();
		if ($pair) {
			while ($row = $stmt->fetch()) {
				if (empty($row['subject_abbr'])) {
					$row['subject_abbr'] = 'その他';
				}
				$subjects[$row['subject_abbr']][$row['subject_id']] = $row['subject_name'];
			}
		} else {
			//$subjects = $stmt->fetchAll();
			while ($row = $stmt->fetch()) {
				$subjects[$row['subject_id']] = $row;
			}
		}
		return $subjects;
	}

		public function getAbbrs()
	{
		$sql = 'SELECT DISTINCT subject_abbr
				FROM subjects
				WHERE subject_available = 1
					AND subject_inout = 1
					'.(empty($this ->user ->campus) ? '' : 'AND campus_id = '.$this ->user ->campus.' ').'
				ORDER BY subject_regadv, subject_grade, subject_abbr, subject_id';
		$stmt = $this->db->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_COLUMN);
	}

	public function getSubjectHash($subject)
	{
		if (is_object($subject)) {
			return $subject->subject_name . $subject->subject_genspe . $subject->subject_foreign;
		} else {
			return $subject['subject_name'] . $subject['subject_genspe'] . $subject['subject_foreign'];
		}
	}

	public function getOutsideSubjects()
	{
		$sql = 'SELECT *
				FROM subjects
				WHERE subject_available = 1
					AND subject_inout = 2
					'.(empty($this ->user ->campus) ? '' : 'AND campus_id = '.$this ->user ->campus.' ').'
				ORDER BY subject_name';
		$stmt = $this->db->prepare($sql);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public function getRegistSubjects($classId)
	{
		$subjects = array();
		$sql = 'SELECT *
				FROM opens
					LEFT JOIN belong USING ( year_id, class_id )
					LEFT JOIN subjects USING ( subject_id )
				WHERE class_id = :class_id
					AND year_id = :year_id
				GROUP BY subject_id
				ORDER BY subject_foreign, subject_id';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':class_id', $classId);
		$stmt->bindParam(':year_id', $this->yearId);
		$stmt->execute();
		$subjects = $stmt->fetchAll();
		return $subjects;
	}
}
