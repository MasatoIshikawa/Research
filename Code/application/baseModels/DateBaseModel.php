<?php
/**
 * 期間の管理
 */
class DateBaseModel extends Kyomu_AbstractBaseModel{
	 /**
     * 各期間を識別する記号．
     */

	const DATE_REGIST 			= 'regist';
	const DATE_REGIST_ADV 		= 'regist_adv';
	const DATE_RECORD 			= 'record';
	const DATE_RECORD_5 		= 'record_5';
	const DATE_RECORD_ADV 		= 'record_adv';
	const DATE_REPORT 			= 'report';
	const DATE_JUGYO_STUDENT 	= 'jugyo_student';
	const DATE_JUGYO_TEACHER 	= 'jugyo_teacher';
	const DATE_RECORD_OPEN		= 'record_open';
	const DATE_RECORD_OPEN_5	= 'record_open_5';
	const DATE_RECORD_OPEN_ADV	= 'record_open_ADV';

    /**
     * 各期間の名称．
     *
     * @var array
     */
	protected $_names = array(
		self::DATE_REGIST			=> '履修登録（本科）',
		self::DATE_REGIST_ADV		=> '履修登録（専攻科）',
		self::DATE_RECORD 			=> '成績入力（１～４年）',
		self::DATE_RECORD_5			=> '成績入力（５年）',
		self::DATE_RECORD_ADV		=> '成績入力（専攻科）',
		self::DATE_REPORT			=> '教育通知票',
		self::DATE_JUGYO_STUDENT	=> '授業評価（学生）',
		self::DATE_JUGYO_TEACHER 	=> '授業評価（教員）',
		self::DATE_RECORD_OPEN		=> '成績表示（１～４年）',
		self::DATE_RECORD_OPEN_5 	=> '成績表示（５年）',
		self::DATE_RECORD_OPEN_ADV 	=> '成績表示（専攻科）',
	);

    /**
     * 現在が期間内であるかを判定．
     *
     * @param string $dateName - 対象項目
     * @param int $examId （オプション）- 試験ID
     * @param int $yearId （オプション）- 年度ID
     * @return boolean
     */
	public function inDate($dateName, $examId = false)
	{
		$sql = 'SELECT *
				FROM dates
				WHERE year_id = :year_id
					AND date_begin <= DATE(NOW())
					AND date_end >= DATE(NOW())
					AND date_name = :date_name';
		if ($examId) $sql .= ' AND exam_id = :exam_id';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':year_id', $this->yearId);
		$stmt->bindParam(':date_name', $dateName);
		if ($examId) $stmt->bindParam(':exam_id', $examId);
		$stmt->execute();
		return $stmt->rowCount() > 0;
	}

    /**
     * 期間を1件だけ取得
     *
     * @param string $dateName - 対象項目
     * @param int $examId - 試験ID
     * @return array 期間1件
     */
	public function getDate($dateName, $examId)
	{
		$sql = 'SELECT *
				FROM dates
					LEFT JOIN years USING ( year_id )
					LEFT JOIN exams USING ( exam_id )
				WHERE year_id = :year_id
					AND exam_id = :exam_id
					AND date_name = :date_name';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':year_id', $this->yearId);
		$stmt->bindParam(':campus_id', $this ->user ->campus);
		$stmt->bindParam(':exam_id', $examId);
		$stmt->bindParam(':date_name', $dateName);
		$stmt->execute();
		$row = $stmt->fetch();
		/*
		$date = new Zend_Date($row['date_begin']);
		$row['begin'] = $date->toString('YYYY年M月d日（EE）');
		$date = new Zend_Date($row['date_end']);
		$row['end'] = $date->toString('YYYY年M月d日（EE）');
		*/
		$row['begin'] = date('Y年n月j日', strtotime($row['date_begin']));
		$row['end'] = date('Y年n月j日', strtotime($row['date_end']));

		return $row;
	}

    /**
     * 期間を全件取得
     *
     * @param string $dateName (オプション) - 対象項目
     * @return array 期間の配列
     */
	public function getDates($name = false)
	{
		$rows = array();
		$sql = 'SELECT *
				FROM dates
					LEFT JOIN years USING ( year_id )
					LEFT JOIN exams USING ( exam_id )
				WHERE year_id = :year_id AND campus_id = :campus_id ';
		if (is_string($name)) $sql.= "AND date_name = '" . $name . "' ";
		if (is_array($name)) $sql.= "AND date_name in ( '" . implode("','", $name) . "' )";
		$sql.= 'ORDER BY exam_id, FIELD(date_name, \'' . implode("','", array_keys($this->_names)) . '\')';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':year_id', $this->yearId);
		$stmt->bindParam(':campus_id', $this ->user ->campus);
		$stmt->execute();

		while ($row = $stmt->fetch()) {
			$row['begin'] = date('Y年n月j日', strtotime($row['date_begin']));
			$row['end'] = date('Y年n月j日', strtotime($row['date_end']));

			switch ($row['exam_id']) {
				case 1:
				case 2:
					$row['exam_name_short'] = '前期';
					break;
				case 3:
				case 4:
					$row['exam_name_short'] = '後期';
					break;
			}

			$row['name'] = $this->_names[$row['date_name']];

			$rows[] = $row;
		}
		return $rows;
	}

	public function getExamIds($name) {
		$sql = 'SELECT exam_id
				FROM dates
				WHERE year_id = :year_id
					AND campus_id = :campus_id
					AND date_begin <= DATE(NOW())
					AND date_end >= DATE(NOW())
					AND date_name = :date_name';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':year_id', $this->yearId);
		$stmt->bindParam(':campus_id', $this ->user ->campus);
		$stmt->bindParam(':date_name', $name);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_COLUMN);
	}

/* 履修登録期間 */
	public function inRegistDate($regadv)
	{
		switch ($regadv) {
			case 1:
				return $this->inDate(self::DATE_REGIST);
				break;
			case 2:
				return $this->inDate(self::DATE_REGIST_ADV);
				break;
		}
		return false;
	}

	public function getRegistDates($student)
	{
		$const = $this->getConstant('DATE_REGIST', $student, true);
		return $this->getDates($const);
	}


	public function getRegistExamId($student)
	{
		$const = $this->getConstant('DATE_REGIST', $student, true);
		$ids = $this->getExamIds($const);
		return $ids[0];
	}

/* 成績入力期間 */
	public function inRecordDate($examId = false)
	{
		return $this->inDate(self::DATE_RECORD, $examId);
	}

	public function getRecordDates()
	{
		return $this->getDates(array(self::DATE_RECORD,
				  					 self::DATE_RECORD_5,
				  					 self::DATE_RECORD_ADV));
	}

	public function getRecordDate($examId)
	{
		return $this->getDate(self::DATE_RECORD, $examId);
	}

	public function addRecordDate($dates)
	{
		$this->addDate(self::DATE_RECORD, $dates);
	}


/* 担任通信欄入力期間 */
	public function inReportDate($examId = false)
	{
		return $this->inDate(self::DATE_REPORT, $examId);
	}

	public function getReportDates()
	{
		return $this->getDates(self::DATE_REPORT);
	}

	public function getReportDate($examId)
	{
		return $this->getDate(self::DATE_REPORT, $examId);
	}

	public function addReportDate($dates)
	{
		$this->addDate(self::DATE_REPORT, $dates);
	}

/* 授業評価学生評価登録期間 */
	public function inJugyoStudentDate($examId = false)
	{
		return $this->inDate(self::DATE_JUGYO_STUDENT, $examId);
	}

	public function getJugyoStudentDates()
	{
		return $this->getDates(self::DATE_JUGYO_STUDENT);
	}

	public function getJugyoStudentDate($examId)
	{
		return $this->getDate(self::DATE_JUGYO_STUDENT, $examId);
	}

	public function addJugyoStudentDate($dates)
	{
		$this->addDate(self::DATE_JUGYO_STUDENT, $dates);
	}

/* 授業評価教員コメント登録期間 */
	public function inJugyoTeacherDate($examId)
	{
		return $this->inDate(self::DATE_JUGYO_TEACHER, $examId);
	}

	public function getJugyoTeacherDates()
	{
		return $this->getDates(self::DATE_JUGYO_TEACHER);
	}

	public function getJugyoTeacherDate($examId)
	{
		return $this->getDate(self::DATE_JUGYO_TEACHER, $examId);
	}

	public function addJugyoTeacherDate($dates)
	{
		$this->addDate(self::DATE_JUGYO_TEACHER, $dates);
	}

/* 学生成績表示可能期間 */
	public function inRecordOpenDate($student)
	{
		$const = $this->getConstant('DATE_RECORD_OPEN', $student);
		return $this->inDate($const);
	}

	public function getRecordOpenExamIds($student)
	{
		$const = $this->getConstant('DATE_RECORD_OPEN', $student);
		return $this->getExamIds($const);
	}

	public function getRecordOpenDates($student)
	{
		$const = $this->getConstant('DATE_RECORD_OPEN', $student);
		return $this->getDates($const);
	}

/* クラス定数の動的取得 */
	public function getConstant($name, $student, $common = false) {
		switch ($student['student_regadv']) {
			case 1:
				if ($common) {

				} else {
					switch ($student['student_grade']) {
						case 1:	case 2:	case 3:	case 4:
							break;
						case 5:
							$name .= '_5';
							break;
						default:
					}
				}
				break;
			case 2:
				$name .= '_ADV';
				break;
			default:
		}
		return constant('self::' . $name);
	}

	public function getNames()
	{
		return $this->_names;
	}

	public function hasDate($post)
	{
		$sql = 'SELECT *
				FROM dates
				WHERE year_id = :year_id
					AND campus_id = :campus_id
					AND date_name = :date_name
					AND exam_id = :exam_id';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':year_id', $this->yearId);
		$stmt->bindParam(':campus_id', $this ->user ->campus);
		$stmt->bindParam(':date_name', $post['name']);
		$stmt->bindParam(':exam_id', $post['examId']);
		$stmt->execute();

		return $stmt->rowCount() > 0;
	}

	public function addDate($post)
	{
		$date_begin = implode('-', $post['begin']);
		$date_end = implode('-', $post['end']);

		$data = array(
			'year_id'	=>	$this->yearId,
			'campus_id'	=>	$this ->user ->campus,
			'date_name'	=>	$post['name'],
			'exam_id'	=>	$post['examId'],
			'date_begin'=>	$date_begin,
			'date_end'	=>	$date_end
		);

		$this->db->insert('dates', $data);

	}

	public function editDate($post)
	{
		$date_begin = implode('-', $post['begin']);
		$date_end = implode('-', $post['end']);

		$this->db->beginTransaction();

		$sql = 'UPDATE dates
				SET date_begin = :date_begin,
					date_end = :date_end
				WHERE date_id = :date_id';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':date_id', $post['d']);
		$stmt->bindParam(':date_begin', $date_begin);
		$stmt->bindParam(':date_end', $date_end);
		$stmt->execute();

		$this->db->commit();
	}

	public function deleteDate($dateId)
	{
		$this->db->beginTransaction();

		$sql = 'DELETE FROM dates
				WHERE date_id = :date_id';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':date_id', $dateId);
		$stmt->execute();

		$this->db->commit();
	}


	/**
	 *
	 * @param Zend_Session_Namespace $session
	 * @param bool $timestamp
	 * @return string|integer
	 */
	public function formDate($session, $timestamp = false)
	{
		if($timestamp){
			if ($session->mode == 2) {
				$year = $session->date['Date_Year'];
				$month = (int)$session->date['Date_Month'];
				$day = (int)$session->date['Date_Day'];
				$date = strtotime($year.'/'.$month.'/'.$day);
			} else {
				$date = time();
			}
		}
		else{
			if ($session->mode == 2) {
				$year = $session->date['Date_Year'];
				$month = (int)$session->date['Date_Month'];
				$day = (int)$session->date['Date_Day'];
				$date = "{$year}年 {$month}月 {$day}日";
			} else {
				$date = date('Y年 n月 j日');
			}
		}
		return $date;
	}
}
