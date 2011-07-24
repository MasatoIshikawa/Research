<?php
require_once APPLICATION_PATH.'/baseModels/AttendBaseModel.php';
require_once APPLICATION_PATH.'/baseModels/BelongBaseModel.php';
require_once APPLICATION_PATH.'/baseModels/ExceptBaseModel.php';
require_once APPLICATION_PATH.'/baseModels/RecordSpecialBaseModel.php';
require_once APPLICATION_PATH.'/baseModels/SubjectBaseModel.php';
require_once APPLICATION_PATH.'/baseModels/YearBaseModel.php';
/**
 * Description of RecordBaseModel
 *
 * @author Yoshihide
 */
class RecordBaseModel extends Kyomu_AbstractBaseModel{
	/**
	 *
	 * @var AttendBaseModel
	 */
	public $attendModel;
	/**
	 *
	 * @var BelongBaseModel
	 */
	public $belongModel;
	/**
	 *
	 * @var ExceptBaseModel
	 */
	public $exceptModel;
	/**
	 *
	 * @var RecordSpecialBaseModel
	 */
	public $recordSpecialModel;
	/**
	 *
	 * @var SubjectBaseModel
	 */
	public $subjectModel;
	/**
	 *
	 * @var YearBaseModel
	 */
	public $yearModel;

	protected function _initModels(){
		$this ->attendModel = new AttendBaseModel();
		$this ->belongModel = new BelongBaseModel();
		$this ->exceptModel = new ExceptBaseModel();
		$this ->recordSpecialModel = new RecordSpecialBaseModel();
		$this ->subjectModel = new SubjectBaseModel();
		$this ->yearModel = new YearBaseModel();
	}

	/*
	 *  成績一覧表の取得 */
	public function getRecordTable($yearId, $examId, $classId, $noAll = false)
	{
		$allExams = false;
	//	$allExams = $examId == 5 && !$noAll ? true : false;//4->5
		$students = $subjects = $records = $subjects_regist = $subjects_open = array();
		$allOfScore = $foreignStudents = array();

		/* 所属学生の取得 */
		$students = $this->belongModel->getBelongStudents($classId);
		/* 出席状況*/
		$__e = ($examId > 4)?4:$examId;
		$attends = $this->attendModel->getClassAttend($classId, $__e);


		/* 初期化 */
		foreach ($students as $student) {
			$studentId = $student['student_id'];
			$records[$studentId]['failedSubjects'] = 0;
			$records[$studentId]['failedCredits'] = 0;
			$records[$studentId]['sumOfScore'] = null;
			$records[$studentId]['orderOfScore'] = null;
			$records[$studentId]['averageOfScore'] = null;
			$records[$studentId]['numberOfScore'] = null;
			$records[$studentId]['reqAttendanceDays'] = null;
			$records[$studentId]['absencedDays'] = null;
			$records[$studentId]['specialSubjects'] = array();

			if (isset($attends[$studentId])) {
				/* 要出席日数 */
				$records[$studentId]['reqAttendanceDays'] = $attends[$studentId]['you_shu_suu'];
				/* 欠席日数 */
				$records[$studentId]['absencedDays'] = $attends[$studentId]['sou_kextuseki_suu'];
			}

			/* 留学生順位除外用 */
			if ($student['student_foreign']) {
				$foreignStudents[] = $studentId;
			}
		}

		/* 科目の取得 開講＋成績ベース */
			$where = 'WHERE class_id = :class_id ';
			if ($allExams) {
				$where .= 'AND exam_id in (5)';//2,4 -> 5
			} else {
				$where .= 'AND exam_id = :exam_id';
			}
			$where .= '	 AND subject_record = 1
					AND year_id = :year_id';

			$basedOpen = '(SELECT subject_id, subject_name, subject_genspe, subject_credit, subject_teacher, subject_foreign
					FROM subjects
						LEFT JOIN opens USING ( subject_id )
						LEFT JOIN exam_semester USING ( semester_id ) '
						. $where
						. ')';
			$basedRecord = '(SELECT subject_id, subject_name, subject_genspe, subject_credit, subject_teacher, subject_foreign
					FROM subjects
						LEFT JOIN records USING ( subject_id )
						LEFT JOIN belong USING ( student_id, year_id)'
						. $where
						. ')';
			$order = 'ORDER BY subject_foreign, subject_id';
			$sql = $basedOpen . ' union ' . $basedRecord . $order;
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':class_id', $classId);
			if (!$allExams) {
				$stmt->bindParam(':exam_id', $examId);
			}
			$stmt->bindParam(':year_id', $yearId);
			$stmt->execute();
			while ($row = $stmt->fetch()) {
				//genspe -> 課題研究
				//foregin -> 電子工学演習
				$subjectHash = $this->subjectModel->GetSubjectHash($row);
				$subjects[$subjectHash] = $row;
				/* 初期化 */
				$sumOfSubject[$subjectHash] = null;
				$numberOfSubject[$subjectHash] = null;
			}


		/* 特別試験成績 09.2.15 */
		if ($examId == 5) {//4->5
			$record_specials = $this->recordSpecialModel->getSpecialRecords($classId);
		}

		/* 成績の取得 */
		$sql = 'SELECT student_id, record_score, subject_name, subject_genspe, subject_credit, subject_foreign, subject_id
				FROM records
					LEFT JOIN subjects USING ( subject_id )
					LEFT JOIN students USING ( student_id )
					LEFT JOIN belong USING ( student_id, year_id )
					LEFT JOIN regists USING ( student_id, subject_id, year_id )
				WHERE class_id = :class_id ';
		if ($allExams) {
			$sql .= 'AND (exam_id = 5)';//(semester_id = 1 AND exam_id = 2) OR (semester_id in (2,3) AND exam_id = 4)
		} else {
			$sql .= 'AND exam_id = :exam_id';
		}
		$sql .= '	 AND subject_record = 1
					 AND year_id = :year_id';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':class_id', $classId);
		if (!$allExams) {
			$stmt->bindParam(':exam_id', $examId);
		}
		$stmt->bindParam(':year_id', $yearId);

		$stmt->execute();


		/* 成績無し特例学生 08.12.18 */
		/* 休学・退学者 09.03.18 */
		$excepted = $this->exceptModel->getExcepted($examId);


		while ($row = $stmt->fetch()) {
			/* 休学・退学者 */
			if (isset($excepted[$row['student_id']]) AND !in_array($row['subject_id'], $excepted[$row['student_id']])) {
				continue;
			}


			/* 特別試験成績 */
			$subjectHash = $this->subjectModel->GetSubjectHash($row);
			if (isset($record_specials[$row['student_id']][$subjectHash])) {
				$row['record_score']  = $record_specials[$row['student_id']][$subjectHash]['record_score'];
				$records[$row['student_id']]['specialSubjects'][] = $subjectHash;
			}

			/* 点数 (subjectHashは混合学級のため) */
			$records[$row['student_id']][$subjectHash] = $row['record_score'];
			if (isset($row['record_score']) && $row['record_score'] < 60) {
				/* 不合格科目数 */
				$records[$row['student_id']]['failedSubjects']++;
				/* 不合格単位数 */
				$records[$row['student_id']]['failedCredits'] += $row['subject_credit'];
			}
			/* 合計点 */
			$records[$row['student_id']]['sumOfScore'] += $row['record_score'];
			/* 点数個数 */
			$records[$row['student_id']]['numberOfScore']++;

			/* 留学生除外 */
			if (in_array($row['student_id'], $foreignStudents)) {
				continue;
			}

			/* 科目毎合計点 */
			$sumOfSubject[$subjectHash] += $row['record_score'];

			/* 科目毎履修者数 */
			$numberOfSubject[$subjectHash]++;

			if (!isset($excepted[$row['student_id']])) {
				/* クラス平均用の全点数 */
				$allOfScore[] = $row['record_score'];
			}

		}

		/* 成績無し学生数 */
		$numberOfNoRecordStudent = 0;


		/* 順位計算ここから */
		foreach ($records as $student_id => &$record) {

			/* 休学・退学者 */
			if (isset($excepted[$student_id])) {
				$numberOfNoRecordStudent++;
				continue;
			}

			/* 平均点 */
			if ($record['numberOfScore'] > 0) {
				$record['averageOfScore'] = $record['sumOfScore'] / $record['numberOfScore'];
				//$averageOfScore[$student_id] = $record['averageOfScore'];
				//$record['averageOfScore'] = sprintf('%0.1f', $record['averageOfScore']);
			} else {
				/* 成績無し者除外 08.12.18 */
				$record['averageOfScore'] = null;
			}
			/* 平均点ここまで */
			/* 順位 */
			$averageOfScore[$student_id] = $record['averageOfScore'];
			$record['averageOfScore'] = sprintf('%0.1f', $record['averageOfScore']);
		}

		if (isset($averageOfScore)) {

			arsort($averageOfScore);
			$order = 1;
			$lastScore = null;
			$lastOrder = null;
			foreach ($averageOfScore as $student_id => $score) {
				/* 留学生除外 */
				if (in_array($student_id, $foreignStudents)) {
					continue;
				}
				/* 成績無し者除外 08.12.18 */
				if ($score === null) {
					$records[$student_id]['orderOfScore'] = null;
					continue;
				}
				if ($score == $lastScore) {
					$records[$student_id]['orderOfScore'] = $lastOrder;
				} else {
					$records[$student_id]['orderOfScore'] = $order;
					$lastOrder = $order;
					$lastScore = $score;
				}
				$order++;
			}
		}


		/* 順位計算ここまで */

		/* 科目毎平均 */


		foreach ($subjects as $subject) {
			$subjectHash = $this->subjectModel->GetSubjectHash($subject);

			if ($numberOfSubject[$subjectHash] > 0) {
				$records['averageOfSubject'][$subjectHash] = $sumOfSubject[$subjectHash] / $numberOfSubject[$subjectHash];
			}
		}
		/* 科目毎平均ここまで*/

		/* 学生人数 */
		//$records['numberOfStudents'] = count($students);
		//留学生除外 & 成績無し学生除外
		$records['numberOfStudents'] = count($students) - count($foreignStudents) - $numberOfNoRecordStudent;

		/* 学生人数ここまで */

		$records['averageOfAverage'] = null;

		if (count($allOfScore)) {
			$records['averageOfAverage'] = array_sum($allOfScore) / count($allOfScore);
		}

		return array(
			'students'	=>	$students,
			'subjects'	=>	$subjects,
			'records'	=>	$records
			);

	}

	public function getReportStudents($classId)
	{
		$students = $comments = array();

		$sql = 'SELECT *
				FROM students
					LEFT JOIN belong USING ( student_id )
					LEFT JOIN classes USING ( class_id )
				WHERE class_id = :class_id
					AND year_id = :year_id
				ORDER BY belong_number';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':class_id', $classId);
		$stmt->bindParam(':year_id', $this->yearId);
		$stmt->execute();
		$students = $stmt->fetchAll();

		$sql = 'SELECT *
				FROM record_comments
					LEFT JOIN students USING ( student_id )
					LEFT JOIN belong USING ( student_id, year_id )
					LEFT JOIN classes USING ( class_id )
					LEFT JOIN exams USING ( exam_id )
				WHERE class_id = :class_id
					AND year_id = :year_id
				ORDER BY belong_number';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':class_id', $classId);
		$stmt->bindParam(':year_id', $this->yearId);
		$stmt->execute();
		while ($row = $stmt->fetch()) {
			$comments[$row['student_id']][$row['exam_id']] = true;
		}

		return array(
				'students'	=>	$students,
				'comments'	=>	$comments
			);
	}

	public function addComment($examId, $teacherId, $studentId, $comment)
	{
		require_once 'Zend/Validate.php';
		require_once 'Zend/Validate/NotEmpty.php';
		require_once 'Zend/Validate/StringLength.php';

		$validatorChain = new Zend_Validate();
		$validatorChain->addValidator(new Zend_Validate_NotEmpty())
		               ->addValidator(new Zend_Validate_StringLength(0, 20000));


		$this->db->beginTransaction();

		$sql = 'REPLACE INTO record_comments
				(year_id, exam_id, teacher_id, student_id, comment)
				VALUES (:year_id, :exam_id, :teacher_id, :student_id, :comment)';
		$stmt = $this->db->prepare($sql);

		$sql = 'DELETE
				FROM record_comments
				WHERE year_id = :year_id
					AND exam_id = :exam_id
					AND student_id = :student_id';
		$del = $this->db->prepare($sql);

		if ($validatorChain->isValid($comment)) {
			$stmt->bindParam(':year_id', $this->yearId);
			$stmt->bindParam(':exam_id', $examId);
			$stmt->bindParam(':teacher_id', $teacherId);
			$stmt->bindParam(':student_id', $studentId);
			$stmt->bindParam(':comment', $comment);
			$stmt->execute();
		} elseif ($comment === '') {
			$del->bindParam(':year_id', $this->yearId);
			$del->bindParam(':exam_id', $examId);
			$del->bindParam(':student_id', $studentId);
			$del->execute();
		} else {
			throw new Exception(implode(',', $validatorChain->getMessages()));
		}

		$this->db->commit();
	}

	public function showAsterisk($class)
	{
		//本科の1～3年生のみ
		return $class['class_regadv'] == 1 && in_array($class['class_grade'], array(1, 2, 3));
	}

	public function getRecordYears($studentId)
	{
		$years = array();
		$sql = 'SELECT min(year_id)
				FROM records
				WHERE student_id = :student_id';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':student_id', $studentId);
		$stmt->execute();
		$minYearId = $stmt->fetchColumn();
		return $this->yearModel->getYears(true, null, $minYearId);
	}

	public function getStudentSubjects($studentId)
	{
		$subjects = array();

		$sql = 'SELECT *
				FROM regists
					LEFT JOIN subjects USING (subject_id)
				WHERE student_id = :student_id
					AND year_id = :year_id
					AND subject_record = 1
					';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':year_id', $this->yearId);
		$stmt->bindParam(':student_id', $studentId);
		$stmt->execute();
		while ($row = $stmt->fetch()) {
			$subjects[$row['subject_id']] = $row;
		}

		$sql = 'SELECT *
				FROM records
					LEFT JOIN subjects USING (subject_id)
				WHERE student_id = :student_id
					AND year_id = :year_id
					AND subject_record = 1
					';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':year_id', $this->yearId);
		$stmt->bindParam(':student_id', $studentId);
		$stmt->execute();
		while ($row = $stmt->fetch()) {
			if (!isset($subjects[$row['subject_id']])) {
				$subjects[$row['subject_id']] = $row;
			}
		}
		return $subjects;
	}
}
