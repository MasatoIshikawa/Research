<?php
class RecordCommentBaseModel extends Kyomu_AbstractBaseModel
{
	public function getComment($studentId, $examId)
	{
		$sql = 'SELECT * 
				FROM record_comments 
				WHERE student_id = :student_id
					AND exam_id = :exam_id
					AND year_id = :year_id';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':student_id', $studentId);
		$stmt->bindParam(':exam_id', $examId);
		$stmt->bindParam(':year_id', $this->yearId);
		$stmt->execute();
		return $stmt->fetch();
	}
}