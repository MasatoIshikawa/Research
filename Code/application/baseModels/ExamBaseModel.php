<?php
/**
 * Description of ExamBaseModel
 *
 * @author Yoshihide
 */
class ExamBaseModel extends Kyomu_AbstractBaseModel{

	public function getExam($examId)
	{
		$sql = 'SELECT *
				FROM exams
				WHERE exam_id = :exam_id';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':exam_id', $examId);
		$stmt->execute();
		return $stmt->fetch();
	}

	public function getExams($pair = false)
	{
		$exams = array();
		$sql = 'SELECT *
				FROM exams
				ORDER BY exam_id';
		$stmt = $this->db->prepare($sql);
		$stmt->execute();
		if ($pair) {
			while ($row = $stmt->fetch()) {
				$exams[$row['exam_id']] = $row['exam_name'];
			}
		} else {
			$exams = $stmt->fetchAll();
		}
		return $exams;
	}

	public function getExamSemesters($reverse = false)
	{
		$semesters = array();
		$sql = 'SELECT exam_id, semester_id
				FROM exam_semester';
		$stmt = $this->db->prepare($sql);
		$stmt->execute();

		if ($reverse) {
			$first = 'semester_id';
			$second = 'exam_id';
		} else {
			$first = 'exam_id';
			$second = 'semester_id';
		}

		while ($row = $stmt->fetch()) {
			$semesters[$row[$first]][] = $row[$second];
		}
		return $semesters;
	}

	public function getHalfExams($pair = false)
	{
		$exams = $this->getExams($pair);
		return array(2 => $exams[2], 4 => $exams[4]);
	}
}
