<?php
class Extension_TeacherModel extends Extension_AbstractModel
{
	public function insert($post)
	{
		$this->validate($post, $this->getValidators());
		$post['campus_id'] = $this ->user ->campus;
		$this->db->insert('teachers', $post);
	}

	public function update($post, $teacherId)
	{
		$this->validate($post, $this->getValidators());
		$this->db->update('teachers', $post, "teacher_id = '{$teacherId}'");
		$this->db->update('charges', array('teacher_id' => $post['teacher_id']), "teacher_id = '{$teacherId}'");
	}
	
	public function delete($teacherId)
	{
		$this->db->delete('teachers', "teacher_id = '{$teacherId}'");
	}
	
	public function getValidators()
	{
		$validators['teacher_id'] = new Zend_Validate();
		$validators['teacher_id']->addValidator(new Zend_Validate_NotEmpty())
							   //->addValidator(new Zend_Validate_Alpha())
								 ->addValidator(new Zend_Validate_StringLength(1, 16));
		
		$validators['teacher_name'] = new Zend_Validate();
		$validators['teacher_name']->addValidator(new Zend_Validate_NotEmpty())
								   ->addValidator(new Zend_Validate_StringLength(1, 30));
		
		return $validators;
	}
}