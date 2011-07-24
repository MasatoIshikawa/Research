<?php
require_once APPLICATION_PATH.'/baseModels/SubjectBaseModel.php';
/**
 * Description of CreditBaseModel
 *
 * @author Yoshihide
 */
class CreditBaseModel extends Kyomu_AbstractBaseModel{
	/**
	 *
	 * @var SubjectBaseModel
	 */
	public $subjectModel;

	protected function _initModels(){
		$this ->subjectModel = new SubjectBaseModel();
	}

	public function getCreditsTotal($studentId)
	{
		$credits = $rows = array();
		$sql = 'SELECT credit_genspe, sum(credit_value) AS sum
				FROM credits
				WHERE student_id = :student_id
				GROUP BY credit_genspe';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':student_id', $studentId);
		$stmt->execute();
		$rows = array(1=>0, 2=>0);
		while ($row = $stmt->fetch()) {
			$rows[$row['credit_genspe']] = $row['sum'];
		}
		$credits['credit_general'] = $rows[1];
		$credits['credit_special'] = $rows[2];
		$credits['credit_total'] = $credits['credit_general'] + $credits['credit_special'];
		return $credits;
	}

	public function getCreditsRequired($studentId)
	{
		$sql = 'SELECT *
				FROM credits_required AS r
					LEFT JOIN students AS s ON s.student_grade = r.required_grade
						AND s.course_id = r.course_id
				WHERE student_id = :student_id';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':student_id', $studentId);
		$stmt->execute();
		return $stmt->fetch();
	}

	public function getCredits($studentId, $onlySubjectIds = false)
	{
		$credits = array();
		$sql = 'SELECT *
				FROM credits
					LEFT JOIN subjects USING (subject_id)
					LEFT JOIN evaluates USING (evaluate_id)
				WHERE student_id = :student_id
				ORDER BY credit_id';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':student_id', $studentId);
		$stmt->execute();

		if ($onlySubjectIds) {
			while ($row = $stmt->fetch()) {
				$credits[] = $row['subject_id'];
			}
		} else {
			$credits = $stmt->fetchAll();
		}
		return $credits;
	}

	public function getExtraCredits($studentId)
	{
		$sql = 'SELECT *
				FROM credits
					LEFT JOIN subjects USING (subject_id)
					LEFT JOIN evaluates USING (evaluate_id)
				WHERE student_id = :student_id
					AND credit_inout = 2
				ORDER BY credit_id';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':student_id', $studentId);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public function formCredits($credits)
	{
		$form = array(1 => array(), 2 => array());
		$blank = array(	'subject_name' 	=>	null,
						'credit_sum'	=>	null,
						'grade'			=>	array(
							'1'	=>	array('value' => null, 'evaluate' => null),
							'2'	=>	array('value' => null, 'evaluate' => null),
							'3'	=>	array('value' => null, 'evaluate' => null),
							'4'	=>	array('value' => null, 'evaluate' => null),
							'5'	=>	array('value' => null, 'evaluate' => null),
						)
		);
		$general['general'] = $special['special'] = $blank;
		$general['general']['subject_name'] = '一般科目';
		$general['general']['isLabel'] = true;
		$special['special']['subject_name'] = '専門科目';
		$special['special']['isLabel'] = true;
		$sum[1]['sum']['subject_name'] = '計（一般）';
		$sum[1]['sum']['isLabel'] = true;
		$sum[1]['sum']['credit_sum'] = 0;
		$sum[2]['sum']['subject_name'] = '計（専門）';
		$sum[2]['sum']['isLabel'] = true;
		$sum[2]['sum']['credit_sum'] = 0;
		$sum[3]['sum']['subject_name'] = '総単位数';
		$sum[3]['sum']['isLabel'] = true;
		$sum[3]['sum']['credit_sum'] = 0;

		$noRowCount = 0;
		foreach ($credits as $credit) {

			$value = sprintf('%0.1f', $credit['credit_value']);
			$hash =$this->subjectModel->GetSubjectHash($credit);

			$form[$credit['credit_genspe']][$hash]['subject_name'] = $credit['subject_name'];
			if (!isset($form[$credit['credit_genspe']][$hash]['credit_sum'])) {
				$form[$credit['credit_genspe']][$hash]['credit_sum'] = 0;
			}
			$form[$credit['credit_genspe']][$hash]['credit_sum'] += $value;
			$form[$credit['credit_genspe']][$hash]['grade'][$credit['credit_grade']]['value'] = $value;
			$form[$credit['credit_genspe']][$hash]['grade'][$credit['credit_grade']]['evaluate'] = $credit['evaluate_name'];

			$mb = mb_strlen($credit['subject_name']);
			$c = preg_match_all('/[｡-ﾟ!-~]/u', $credit['subject_name'], $match);
			$mb = $mb - $c / 2;

			if ($mb >= 16) {
				$form[$credit['credit_genspe']][$hash]['isLongName'] = true;
				$form[$credit['credit_genspe']]['noRow' . $noRowCount++]['noRow'] = true;
			}

			//合計用
			if (!isset($sum[$credit['credit_genspe']]['sum']['grade'][$credit['credit_grade']]['value'])) {
				$sum[$credit['credit_genspe']]['sum']['grade'][$credit['credit_grade']]['value'] = 0;
			}
			$sum[$credit['credit_genspe']]['sum']['grade'][$credit['credit_grade']]['value'] += $value;
			$sum[$credit['credit_genspe']]['sum']['credit_sum'] += $value;
		}

		$sum[3]['sum']['credit_sum'] = $sum[1]['sum']['credit_sum'] + $sum[2]['sum']['credit_sum'];

		$left = array_values($general + $form[1]);
		$right = array_values($special + $form[2]);



		return array('left' => $left, 'right' => $right, 'sum'	=>	$sum);
	}
}
