<?php
/**
 * Description of install
 *
 * @author Yoshihide
 */
class TeacherInstaller extends Extension_AbstractInstaller {

	public function init() {
		$this ->name_ja = 'Teacher管理';
		$this ->summary = 'Teacher情報を管理するためのExtensionです。';
	}

	public function initViewSettings() {
		$this ->view_settings ->addAction('index', Extension_ViewDefiner::NO_SIDEMENU)
							  ->addMainContentPath('index', 'index.tpl')
							  ->addTitle('index', '');
		$this ->view_settings ->addAction('edit', Extension_ViewDefiner::NO_SIDEMENU)
							  ->addMainContentPath('edit', 'edit.tpl')
							  ->addTitle('edit', '');
		$this ->view_settings ->addAction('detail', Extension_ViewDefiner::NO_SIDEMENU)
							  ->addMainContentPath('detail', 'detail.tpl')
							  ->addTitle('detail', '');
		$this ->view_settings ->addPlugingInterface('教員情報管理', 'index');
	}

	public function install(){
		$this ->controller_path = '/TeacherController.php';
		$this ->model_path = '/TeacherModel.php';

	}

	public function uninstall(){


	}
}
