<?php
/**
 * Description of install
 *
 * @author Yoshihide
 */
class TanninInstaller extends Extension_AbstractInstaller {

	public function init() {
		$this ->name_ja = 'Tannin管理';
		$this ->summary = 'Tannin情報を管理するためのExtensionです。';
	}

	public function initViewSettings() {
		$this ->view_settings ->addAction('index', Extension_ViewDefiner::NO_SIDEMENU)
							  ->addMainContentPath('index', 'index.tpl')
							  ->addTitle('index', '');
		$this ->view_settings ->addAction('edit', Extension_ViewDefiner::NO_SIDEMENU)
							  ->addMainContentPath('edit', 'edit.tpl')
							  ->addTitle('edit', '');
		$this ->view_settings ->addPlugingInterface('担任情報管理', 'index');
	}

	public function install(){
		$this ->controller_path = '/TanninController.php';
		$this ->model_path = '/TanninModel.php';

	}

	public function uninstall(){


	}
}
