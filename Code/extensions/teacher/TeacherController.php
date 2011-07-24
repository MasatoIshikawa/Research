<?php
class Extension_TeacherController extends Extension_AbstractController
{	
	
	public function indexAction()
	{
		$sort = $this->getParam('sort');
		$order = $this->getParam('order');
		$this->view->teachers = $this->Models->getTeachers(false, true, $sort, $order);
		$columns = $this->getSchema('teachers');
		$columns['course_id']['options'] = $this ->Models ->CourseModel ->getCourses(true);
		$columns['course_id']['options'][0] = "非常勤";
		$this->view->columns = $columns;
	}
	
	public function detailAction()
	{
		$teacherId = $this->getParam('t');
		$this->view->teacher = $this->Models->getTeacher($teacherId);
		$this->view->subjects = $this->Models->ChargeModel->getTeacherCharges($teacherId);
		$this ->view ->SubjectDetailUrl = $this ->ExtensionCall('subject', 'urldetail');
	}

	public function editAction()
	{
		$teacherId = $this->getParam('t');
		$this->view->teacher = $this->Models->getTeacher($teacherId);
		$columns = $this->getSchema('teachers');
		$columns['course_id']['options'] = $this ->Models ->CourseModel ->getCourses(true);
		$columns['course_id']['options'][0] = "非常勤";
		$this->view->columns = $columns;
	}

	public function addAction()
	{
		$this->setNoRender();
		$this->reqParams('teacher');
		$post = $this->getParam('teacher');

		$this->Models->insert($post);
	
		$this->setMessage('新規登録しました： ' . print_r($post, true));
		$this->forward('', 'teacher');
	}

	public function updateAction()
	{
		$this->setNoRender();
		$this->reqParams(array('t', 'teacher'));
		$post = $this->getParam('teacher');
		$teacherId = $this->getParam('t');

		$this->Models->update($post, $teacherId);
	
		$this->setMessage('変更を保存しました： ' . print_r($post, true));
		$this->forward('', 'teacher');
	}
	
	public function deleteAction()
	{
		$this->setNoRender();
		$this->reqParams('t');
		$teacherId = $this->getParam('t');

		$this->Models->delete($teacherId);
	
		$this->setMessage('削除しました: ' . $teacherId);
		$this->forward('', 'teacher');
	}
}
