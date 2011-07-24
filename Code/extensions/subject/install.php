<?php
/**
 * Description of install
 *
 * @author Yoshihide
 */
class SubjectInstaller extends Extension_AbstractInstaller {

	public function init() {
		$this ->name_ja = '授業情報管理';
		$this ->summary = '授業情報を管理するためのExtensionです。<br />※belong Extensionが必要です。';
	}

	public function initViewSettings() {
		$this ->view_settings ->addAction('index', Extension_ViewDefiner::NO_SIDEMENU)
							  ->addMainContentPath('index', 'index.tpl')
							  ->addTitle('index', '授業情報管理トップ');
		$this ->view_settings ->addAction('edit', Extension_ViewDefiner::NO_SIDEMENU)
							  ->addMainContentPath('edit', 'edit.tpl')
							  ->addTitle('edit', '授業情報 - 編集');
		$this ->view_settings ->addAction('detail', Extension_ViewDefiner::NO_SIDEMENU)
							  ->addMainContentPath('detail', 'detail.tpl')
							  ->addTitle('detail', '授業情報 - 詳細');
		$this ->view_settings ->addPlugingInterface('科目情報管理', 'index');
	}

	public function install(){
		$this ->controller_path = '/SubjectController.php';
		$this ->model_path = '/SubjectModel.php';

	}

	public function uninstall(){


	}
}
