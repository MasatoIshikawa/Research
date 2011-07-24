<?php
class Extension_TanninModel extends Extension_AbstractModel
{
	public function getTannin($tanninId)
	{
		$sql = 'SELECT * 
				FROM tannin
					LEFT JOIN classes USING (class_id)
					LEFT JOIN teachers USING (teacher_id)
				WHERE tannin_id = :tannin_id';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':tannin_id', $tanninId);
		$stmt->execute();
		return $stmt->fetch();
	}
	
	public function getTannins($column = false)
	{
		$tannin = array();
		$sql = 'SELECT * 
				FROM tannin
					LEFT JOIN classes USING (class_id)
					LEFT JOIN teachers USING (teacher_id)
				WHERE year_id = :year_id
					'.(empty($this ->user ->campus) ? '' : 'AND classes.campus_id = '.$this ->user ->campus.' ');
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':year_id', $this->yearId);
		$stmt->execute();
		if ($column) {
			while ($row = $stmt->fetch()) {
				$tannin[$row['tannin_id']] = $row['class_id'];
			}
		} else {
			$tannin = $stmt->fetchAll();
		}
		return $tannin;
	}
	
	public function insert($post)
	{
		$this->validate($post, $this->getValidators());
		$this->db->insert('tannin', $post);
	}

	public function update($post, $tanninId)
	{
		$this->validate($post, $this->getValidators());
		$this->db->update('tannin', $post, "tannin_id = '{$tanninId}'");
	}
	
	public function delete($tanninId)
	{
		$this->db->delete('tannin', "tannin_id = '{$tanninId}'");
	}
	
	public function getValidators()
	{
		$validators['teacher_id'] = new Zend_Validate();
		$validators['teacher_id']->addValidator(new Zend_Validate_NotEmpty())
								 ->addValidator(new Zend_Validate_Alpha())
								 ->addValidator(new Zend_Validate_StringLength(1, 10));
		$validators['class_id'] = new Zend_Validate();
		$validators['class_id']->addValidator(new Zend_Validate_NotEmpty())
							   ->addValidator(new Zend_Validate_Int());
		
		return $validators;
	}
	
}