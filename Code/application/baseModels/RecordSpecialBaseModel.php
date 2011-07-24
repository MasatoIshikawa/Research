<?php
require_once APPLICATION_PATH.'/baseModels/SubjectBaseModel.php';

/**
 * Description of RecordSpecialBaseModel
 *
 * @author Yoshihide
 */
class RecordSpecialBaseModel extends Kyomu_AbstractBaseModel{

	public $subjectModel;

	public function _initModels(){
		$this ->subjectModel = new SubjectBaseModel();
	}

	/**
     * 学年末成績を上書きする特別試験成績の取得
     *
     * @param int $classId - クラスID
     * @return array
     */
	public function getSpecialRecords($classId)
	{
		$record_specials = array();
		$sql = 'SELECT *
				FROM record_specials
					LEFT JOIN subjects USING (subject_id)
					LEFT JOIN belong USING (student_id, year_id)
				WHERE year_id = :year_id
					AND class_id = :class_id';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':year_id', $this->yearId);
		$stmt->bindParam(':class_id', $classId);
		$stmt->execute();
		while ($row = $stmt->fetch()) {
			$subjectHash = $this->subjectModel->getSubjectHash($row);
			$record_specials[$row['student_id']][$subjectHash] = $row;
		}
		return $record_specials;
	}
}
