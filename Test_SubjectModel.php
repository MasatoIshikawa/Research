<?php
class  Extension_SubjectModel extends  Extension_AbstractModel
{


	public function addSubject($subject, $subjectId)
	{
		$this->db->beginTransaction();
		
		$sql = 'UPDATE subjects	SET ';
		foreach ($subject as $column => $value) {
			$sets[] = $column . ' = ' . ':' . $column;
		}
		$sql .= implode(', ', $sets);
		$sql .= ' WHERE subject_id = :subject_id';
		$stmt = $this->db->prepare($sql);
		
		foreach ($subject as $column => $value) {
			$stmt->bindValue(':' . $column, $value);
		}
		$stmt->bindParam('subject_id', $subjectId);
		
		$stmt->execute();
		
		$this->db->commit();
	}
	



	/* */
	
	public function insert($post)
	{
		$this->validate($post, $this->getValidators());
		foreach ($post as &$pos) {
			if ($pos === '') $pos = null;
		}
		$post['campus_id'] = $this ->user ->campus;
		$this->db->insert('subjects', $post);
	}

	public function update($post, $subjectId)
	{
		$this->validate($post, $this->getValidators());
		foreach ($post as &$pos) {
			if ($pos === '') $pos = null;
		}
		$this->db->update('subjects', $post, "subject_id = '{$subjectId}'");
	}
	
	public function delete($subjectId)
	{
		$this->db->delete('subjects', "subject_id = '{$subjectId}'");
	}
	
	public function getValidators()
	{
		$validators['subject_name'] = new Zend_Validate();
		$validators['subject_name']->addValidator(new Zend_Validate_NotEmpty())
								   ->addValidator(new Zend_Validate_StringLength(1, 90));
		
		return $validators;
	}
	
	public function teacherName()
	{
		$this->db->beginTransaction();
		
		$sql = 'SELECT teacher_id, teacher_name
				FROM teachers
				WHERE teacher_available = 1';
		$stmt = $this->db->prepare($sql);
		$stmt->execute();
		$teachers = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
		

		$sql = 'SELECT subject_id, teacher_id
				FROM charges
					LEFT JOIN subjects USING (subject_id)
				WHERE year_id = :year_id
					AND subject_inout = 1
					AND subject_available = 1
				ORDER BY subject_id';
		$stmt = $this->db->prepare($sql);
		$stmt->bindValue(':year_id', $this->yearId - 1);
		$stmt->execute();
		$charges_lastyear = $stmt->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP);
		$stmt->bindValue(':year_id', $this->yearId);
		$stmt->execute();
		$charges_thisyear = $stmt->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP);
		
		foreach ($charges_thisyear as $subjectId => $charges) {
			if (isset($charges_lastyear[$subjectId]) AND $charges != $charges_lastyear[$subjectId]) {
				$name1 = null;
				$name1 = mb_substr($teachers[$charges[0]], 0, mb_strpos($teachers[$charges[0]], '　'));
				if ($name1 == null) {
					$name1 = mb_substr($teachers[$charges[0]], 0, mb_strpos($teachers[$charges[0]], ' '));
				}
				$name2 = null;
				if (count($charges) >= 2) {
					$name2 = mb_substr($teachers[$charges[1]], 0, mb_strpos($teachers[$charges[1]], '　'));
					if ($name2 == null) {
						$name2 = mb_substr($teachers[$charges[1]], 0, mb_strpos($teachers[$charges[1]], ' '));
					}
				}
				$str = $name2 ? $name1 . '，' . $name2 : $name1;
				$this->db->update('subjects', array('subject_teacher' => $str), "subject_id = {$subjectId}");
			}
		}
		
		$this->db->commit();
	}
}