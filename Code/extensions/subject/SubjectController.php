<?php
class Extension_SubjectController extends  Extension_AbstractController
{	
	
	public function indexAction()
	{
		$sort = $this->getParam('sort');
		$order = $this->getParam('order');
		$this->view->abbrs = $this->Models->getAbbrs();
		$this->view->subjects = $this->Models->getSubjects(false, true, $sort, $order);
		$this->view->columns = $this->getSchema('subjects');
	}
	
	public function detailAction()
	{
		$this->reqParams('s');
		$subjectId = $this->getParam('s');
		$this->view->columns = $this->getSchema('subjects');
		
		$this->view->subject = $this->Models->getSubject($subjectId);
		$this->view->teachers = $this->Models ->ChargeModel->getChargeTeachers($subjectId);
		$this->view->classes = $this->Models ->OpenModel->getOpenClasses($subjectId);
		$this->view->entriedYears = $this->Models->JugyoModel->getEntriedYears($subjectId);

		$this ->view ->TeacherDetailUrl = $this ->ExtensionCall('teacher', 'urldetail');
		$this ->view ->ClassDetailUrl = $this ->ExtensionCall('class', 'urldetail');
		$this ->view ->JugyoDisplayUrl = $this ->ExtensionCall('jugyo', 'urldisplay');
	}
	
	public function editAction()
	{
		$this->reqParams('s');
		$subjectId = $this->getParam('s');
		
		$this->view->columns = $this->getSchema('subjects');
		$this->view->subject = $this->Models->getSubject($subjectId);
	}

	public function addAction()
	{
		$this->setNoRender();
		$this->reqParams('subject');
		$post = $this->getParam('subject');

		$this->Models->insert($post);
	
		$this->setMessage('新規登録しました： ' . print_r($post, true));
		$this->forward('', 'subject');
	}

	public function updateAction()
	{
		$this->setNoRender();
		$this->reqParams(array('s', 'subject'));
		$post = $this->getParam('subject');
		$subjectId = $this->getParam('s');

		$this->Models->update($post, $subjectId);
	
		$this->setMessage('変更を保存しました： ' . print_r($post, true));
		$this->forward('', 'subject');
	}
	
	public function deleteAction()
	{
		$this->setNoRender();
		$this->reqParams('s');
		$subjectId = $this->getParam('s');

		$this->Models->delete($subjectId);
	
		$this->setMessage('削除しました: ' . $subjectId);
		$this->forward('', 'subject');
	}

	public function teacherNameAction()
	{
		$this->setNoRender();

		$this->Models->teacherName();
	
		$this->setMessage('実行しました．');
		$this->forward('', 'subject');
	}

}