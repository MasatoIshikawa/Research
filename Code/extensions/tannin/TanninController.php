<?php
class Extension_TanninController extends Extension_AbstractController
{	
	
	public function indexAction()
	{
		$this->view->tannins = $this->Models->getTannins();
		$this->view->classes = $this->Models->ClassModel->getClasses(true);
		$this->view->teachers = $this->Models->TeacherModel->getTeachers(true);
		$this->view->columns = $this->getSchema('tannin');
	}
	
	public function setAction()
	{
		$this->setNoRender();
		$this->reqParams('t');
		$tanninId = $this->getParam('t');
		
		$this->Models->ConfigModel->set('tannin_id', $tanninId);
		
		$this->setMessage('設定しました： ' . print_r($post, true));
		$this->forward('', 'tannnin');
	}
	
	public function editAction()
	{
		$this->reqParams('t');
		$tanninId = $this->getParam('t');
		
		$this->view->columns = $this->getSchema('tannin');
		$this->view->tannin = $this->Models->getTannin($tanninId);
		$this->view->classes = $this->Models->ClassModel->getClasses(true);
		$this->view->teachers = $this->Models->TeacherModel->getTeachers(true);
	}

	public function addAction()
	{
		$this->setNoRender();
		$this->reqParams('tannin');
		$post = $this->getParam('tannin');


		$this->Models->insert($post);
	
		$this->setMessage('新規登録しました： ' . print_r($post, true));
		$this->forward('', 'tannin');
	}

	public function updateAction()
	{
		$this->setNoRender();
		$this->reqParams(array('t', 'tannin'));
		$post = $this->getParam('tannin');
		$tanninId = $this->getParam('t');

		$this->Models->update($post, $tanninId);
	
		$this->setMessage('変更を保存しました： ' . print_r($post, true));
		$this->forward('', 'tannin');
	}
	
	public function deleteAction()
	{
		$this->setNoRender();
		$this->reqParams('t');
		$tanninId = $this->getParam('t');

		$this->Models->delete($tanninId);
	
		$this->setMessage('削除しました: ' . $tanninId);
		$this->forward('', 'tannin');
	}

}