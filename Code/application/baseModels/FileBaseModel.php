<?php
require_once APPLICATION_PATH.'/baseModels/SubjectBaseModel.php';

class FileBaseModel extends Kyomu_AbstractBaseModel
{
	private $subjectModel;
	private $save_to;

	public function _initBaseModelLoader(){
		$this ->subjectModel = new SubjectBaseModel();
		$this ->save_to = APPLICATION_PATH.'/../cache/temp/';

		CEZend_Directory::checkAndCreateDirectory($this ->save_to);

		set_include_path(get_include_path() . PATH_SEPARATOR . realpath(APPLICATION_PATH.'/../library/PHPExcel/'));

		error_reporting(E_ERROR);
	}

	public function getExcel($year, $exam, $class, $students, $subjects, $records, $showAsterisk)
	{
		$filename = $year['year_name'] . $exam['exam_name'] . $class['class_name']. '成績一覧表.xls';
		$filename = mb_convert_encoding($filename, 'sjis-win');
		
		require_once 'PHPExcel.php';
		require_once 'PHPExcel/Writer/Excel5.php';
		require_once 'PHPExcel/Reader/Excel5.php';
		
		$objPHPExcel = new PHPExcel();
		
		$objPHPExcel->setActiveSheetIndex(0);
		$sheet= $objPHPExcel->getActiveSheet();
		
		$col = 0;
		
		$sheet->setCellValueByColumnAndRow(0, 1, '授業科目名');
		$sheet->setCellValueByColumnAndRow(0, 2, '単位数');
		$sheet->setCellValueByColumnAndRow(0, 3, '担当教員');
		
		$col += 3 + count($subjects);
		
		$colStr = PHPExcel_Cell::stringFromColumnIndex($col);
		$optStart = $colStr;
		
		$sheet->mergeCells("{$colStr}1:{$colStr}3");
		$sheet->setCellValueByColumnAndRow($col++, 1, '60点未満の科目数');
		$colStr = PHPExcel_Cell::stringFromColumnIndex($col);
		$sheet->mergeCells("{$colStr}1:{$colStr}3");
		$sheet->setCellValueByColumnAndRow($col++, 1, '不合格単位合計');
		$colStr = PHPExcel_Cell::stringFromColumnIndex($col);
		$sheet->mergeCells("{$colStr}1:{$colStr}3");
		$sheet->setCellValueByColumnAndRow($col++, 1, '合計');
		$colStr = PHPExcel_Cell::stringFromColumnIndex($col);
		$sheet->mergeCells("{$colStr}1:{$colStr}3");
		$sheet->setCellValueByColumnAndRow($col++, 1, '順位');
		$colStr = PHPExcel_Cell::stringFromColumnIndex($col);
		$sheet->mergeCells("{$colStr}1:{$colStr}3");
		$sheet->setCellValueByColumnAndRow($col++, 1, '平均点');
		$colStr = PHPExcel_Cell::stringFromColumnIndex($col);
		$sheet->mergeCells("{$colStr}1:{$colStr}3");
		$sheet->setCellValueByColumnAndRow($col++, 1, '要出席日数');
		$colStr = PHPExcel_Cell::stringFromColumnIndex($col);
		$sheet->mergeCells("{$colStr}1:{$colStr}3");
		$sheet->setCellValueByColumnAndRow($col++, 1, '総欠席日数');
		
		$optEnd = $colStr;
		
		

		$sheet->mergeCells('A1:C1');
		$sheet->mergeCells('A2:C2');
		$sheet->mergeCells('A3:C3');

		
		$subCol = 3;
		foreach ($subjects as $subject) {
			$sheet->setCellValueByColumnAndRow($subCol, 1, $subject['subject_name']);
			$sheet->setCellValueByColumnAndRow($subCol, 2, $subject['subject_credit']);
			$sheet->setCellValueByColumnAndRow($subCol, 3, $subject['subject_teacher']);
			$subCol++;
		}

		$stuRow = 4;
		foreach ($students as $student) {
			$stuCol = 0;
			
			$sheet->setCellValueByColumnAndRow($stuCol++, $stuRow, $student['belong_number']);
			$sheet->setCellValueByColumnAndRow($stuCol++, $stuRow, $student['student_name']);
			$sheet->setCellValueByColumnAndRow($stuCol++, $stuRow, $student['student_course']);
		
			foreach ($subjects as $subject) {
				$hash = $this->subjectModel->GetSubjectHash($subject);
				if (isset($records[$student['student_id']][$hash])) {
					$score = $records[$student['student_id']][$hash];
					if ($score < 60) {
						$score .= '*';
					} else {
						$score .= ' ';
					}
				} else {
					$score = '- ';
				}
				$sheet->setCellValueByColumnAndRow($stuCol++, $stuRow, $score);	
			}
			
			$failedSubjects = $records[$student['student_id']]['failedSubjects'];
			if ($failedSubjects >=4 && $showAsterisk) {
				$failedSubjects .= '*';
			} else {
				$failedSubjects .= ' ';
			}
			$failedCredits = $records[$student['student_id']]['failedCredits'];
			if ($failedCredits >=7 && $showAsterisk) {
				$failedCredits = number_format($failedCredits, 1) . '*';
			} else {
				$failedCredits = number_format($failedCredits, 1) . ' ';
			}
			
			$sheet->setCellValueByColumnAndRow($stuCol++, $stuRow, $failedSubjects);
			$sheet->setCellValueByColumnAndRow($stuCol++, $stuRow, $failedCredits);
			$sheet->setCellValueByColumnAndRow($stuCol++, $stuRow, $records[$student['student_id']]['sumOfScore']);
			$sheet->setCellValueByColumnAndRow($stuCol++, $stuRow, $records[$student['student_id']]['orderOfScore']);
			$sheet->setCellValueByColumnAndRow($stuCol++, $stuRow, number_format($records[$student['student_id']]['averageOfScore'], 1));
			$sheet->setCellValueByColumnAndRow($stuCol++, $stuRow, $records[$student['student_id']]['reqAttendanceDays']);
			$sheet->setCellValueByColumnAndRow($stuCol++, $stuRow, $records[$student['student_id']]['absencedDays']);
			
			$stuRow++;
		}
		
		$stuCol = 0;
		$sheet->setCellValueByColumnAndRow($stuCol++, $stuRow, '平均点');
		$sheet->mergeCells("A{$stuRow}:C{$stuRow}");
		$rowEnd = $stuRow;

		
		$subCol = 3;
		foreach ($subjects as $subject) {
			$hash = $this->subjectModel->GetSubjectHash($subject);
			if (isset($records['averageOfSubject'][$hash])) {
				$sheet->setCellValueByColumnAndRow($subCol, $stuRow, number_format($records['averageOfSubject'][$hash], 1));
			}
			$subCol++;
		}
		$sheet->setCellValueByColumnAndRow($subCol + 4, $stuRow, number_format($records['averageOfAverage'], 1));

		//一番上行
		$objPHPExcel->getActiveSheet()->duplicateStyleArray(
			array(
				'borders' => array(
	 				'top'     => array(
	 					'style' => PHPExcel_Style_Border::BORDER_THIN
	 				),
					'bottom'     => array(
	 					'style' => PHPExcel_Style_Border::BORDER_THIN
	 				),
				),
				//'font'    => array(
				//	'bold'      => true
				//),

			),
			"A1:{$optEnd}1"
		);

		//二番目行
		$objPHPExcel->getActiveSheet()->duplicateStyleArray(
			array(
				'borders' => array(
	 				'bottom'     => array(
	 					'style' => PHPExcel_Style_Border::BORDER_THIN
	 				),
				),
			),
			"A2:{$optEnd}2"
		);
		
		//一番下の平均点
		$objPHPExcel->getActiveSheet()->duplicateStyleArray(
			array(
				'borders' => array(
	 				'bottom'     => array(
	 					'style' => PHPExcel_Style_Border::BORDER_THIN
	 				),
	 	 			'top'     => array(
	 					'style' => PHPExcel_Style_Border::BORDER_THIN
	 				),
				),
			),
			"A{$rowEnd}:{$optEnd}{$rowEnd}"
		);
	
	//オプションの項目
	for($i=$optStart; $i<=$optEnd; $i++) {	
		$objPHPExcel->getActiveSheet()->duplicateStyleArray(
			array(
				'borders' => array(
	 				'left'     => array(
	 					'style' => PHPExcel_Style_Border::BORDER_THIN
	 				),
		 			'right'     => array(
	 					'style' => PHPExcel_Style_Border::BORDER_THIN
	 				),
				),
			),
			"{$i}1:{$i}{$rowEnd}"
		);
	}

		//左の線
		$objPHPExcel->getActiveSheet()->duplicateStyleArray(
			array(
				'borders' => array(
	 				'left'     => array(
	 					'style' => PHPExcel_Style_Border::BORDER_THIN
	 				),
	 	 			'right'     => array(
	 					'style' => PHPExcel_Style_Border::BORDER_THIN
	 				),
				),
			),
			"A1:C{$rowEnd}"
		);
		//中の線	

		//数値
		$objPHPExcel->getActiveSheet()->duplicateStyleArray(
			array(
				'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
				),
			),
			"D3:{$optEnd}{$rowEnd}"
		);
			
	

		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);

		$objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
		$objWriter->save($this ->save_to . $filename);
		
		readfile($this ->save_to . $filename);
		
		PHPExcel_Writer_Excel5_Writer::send($filename);

	}
	
	
	public function getCSV($year, $exam, $subject, $students, $records)
	{

		$filename = $year['year_name'] . $exam['exam_name'] . $subject['subject_abbr'] . $subject['subject_name'] . '成績入力.xls';
		$filename = mb_convert_encoding($filename, 'sjis-win');
		
		require_once 'PHPExcel.php';
		require_once 'PHPExcel/Writer/Excel5.php';
		require_once 'PHPExcel/Reader/Excel5.php';
		
		$objPHPExcel = new PHPExcel();
		
		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setCellValue('A1', '学籍番号');
		$objPHPExcel->getActiveSheet()->setCellValue('B1', '氏名');
		$objPHPExcel->getActiveSheet()->setCellValue('C1', '評価');

		$x = 2;		
		foreach ($students as $student) {
			$studentId =  str_replace(array('s', 'a'), '', (string)$student['student_id']);
			$studentName = mb_convert_encoding($student['student_name'], 'sjis-win');
			$score = isset($records[$student['student_id']]) ? $records[$student['student_id']] : '-';
			
			$objPHPExcel->getActiveSheet()->setCellValueExplicit('A'. $x, $studentId, PHPExcel_Cell_DataType::TYPE_STRING);
			$objPHPExcel->getActiveSheet()->setCellValue('B'. $x, $student['student_name']);
			$objPHPExcel->getActiveSheet()->setCellValue('C'. $x, $score);
			$objPHPExcel->getActiveSheet()->getStyle('A'. $x)->getNumberFormat()->setFormatCode('0000');
			$x++;
		}
	
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getFill()->getStartColor()->setARGB('FFe0e0e0');
		$objPHPExcel->getActiveSheet()->getStyle('B1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$objPHPExcel->getActiveSheet()->getStyle('B1')->getFill()->getStartColor()->setARGB('FFe0e0e0');
		$objPHPExcel->getActiveSheet()->getStyle('C1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$objPHPExcel->getActiveSheet()->getStyle('C1')->getFill()->getStartColor()->setARGB('FFe0e0e0');

		$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);

		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);

		//v($objPHPExcel);

	
		$objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
		$objWriter->save($this ->save_to . $filename);
		
		readfile($this ->save_to . $filename);
		
		PHPExcel_Writer_Excel5_Writer::send($filename);
		//header( "Content-Type: application/octet-stream" );
		//header( "Content-disposition: attachment; filename=$filename");

	}
	
	public function loadExcel()
	{
		require_once 'PHPExcel.php';
		require_once 'PHPExcel/Writer/Excel5.php';
		require_once 'PHPExcel/Reader/Excel5.php';
	
		$objReader = new PHPExcel_Reader_Excel5;
		$objPHPExcel = $objReader->load($_FILES['file']['tmp_name']);

		$sheet = $objPHPExcel->getSheet(0);

		// Get cell collection
		$cellCollection = $sheet->getCellCollection();
		
		// Get column count
		$colCount = PHPExcel_Cell::columnIndexFromString($sheet->getHighestColumn());
		
		// Loop trough cells
		$currentRow = -1;
		$rows = array();
		$rowData = array();
		foreach ($cellCollection as $cell) {					
			if ($currentRow != $cell->getRow()) {
				// End previous row?
				if ($currentRow != -1) {
				//	$this->_writeLine($fileHandle, $rowData);
					$rows[] = $rowData;
				}

				// Set current row
				$currentRow = $cell->getRow();
			
				// Start a new row
				$rowData = array();
				for ($i = 0; $i < $colCount; $i++) {
					$rowData[$i] = '';
				}
			}
					
			// Copy cell
			$column = PHPExcel_Cell::columnIndexFromString($cell->getColumn()) - 1;
			if ($cell->getValue() instanceof PHPExcel_RichText) {
				$rowData[$column] = $cell->getValue()->getPlainText();
			} else {
//				if ($this->_preCalculateFormulas) {
//					$rowData[$column] = PHPExcel_Style_NumberFormat::toFormattedString(
//						$cell->getCalculatedValue(),
//						$sheet->getstyle( $cell->getCoordinate() )->getNumberFormat()->getFormatCode()
//					);
//				} else {
					$rowData[$column] = PHPExcel_Style_NumberFormat::toFormattedString(
						$cell->getValue(),
						$sheet->getstyle( $cell->getCoordinate() )->getNumberFormat()->getFormatCode()
					);
//				}
			}
			
		}
			// End last row?
		if ($currentRow != -1) {
			//	$this->_writeLine($fileHandle, $rowData);
			$rows[] = $rowData;
		}
		return $rows;
	}

	//☆修正2010.3.25☆
	public function loadCsv($filename){
		$rows = array();
		$fp = fopen($filename, 'r');
		while ($row = fgets($fp)) {
			$rows[] =  explode(',', rtrim($row));
			//$rows[] = array_map('mb_convert_encoding', $row, array('UTF-8', 'Shift-JIS'));
		}
		return $rows;
	}
	
	public function load($type)
	{
		$rows = array();
		switch ($type) {
			case 'excel':
				$rows = $this->loadExcel();
				break;
			case 'csv':
				$rows = $this->loadCsv();
				break;
		}
		return $rows;
	}
	
	public function putCSV($subjectId)
	{
		$rows = $this->load('excel');
	
		foreach ($rows as $row) {
			if (is_numeric($row[0])){
				$id = $row[0];
				$data[$id] = $row[2];
			}
		}

		$session = new Zend_Session_Namespace('record');
		$session->$subjectId = $data;
	}
	
	public function clearCSV($subjectId)
	{
		$session = new Zend_Session_Namespace('record');
		if (isset($session->$subjectId)) {
			$session->$subjectId = null;
		}
	}

	public function checkIfUploadedFileIsCSV(){
		if(strtolower($_FILES['file']['type']) != 'text/csv'){
			if(strtolower($_FILES['file']['type']) != 'application/octet-stream'){
				if(strtolower(substr($_FILES['file']['name'], strpos($_FILES['file']['name'], '.') + 1)) != 'csv'){
					return FALSE;
				}
			}
			else{
				return FALSE;
			}
		}
		return TRUE;
	}
}
