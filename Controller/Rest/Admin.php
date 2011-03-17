<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Controller_Rest_Admin extends Bbx_Controller_Rest {

	protected $_user;

	public function init() {
		$this->_helper->contextSwitch()->addActionContext('error','json');
		$this->_helper->contextSwitch()->addActionContext('login','json');
		$this->_helper->contextSwitch()->addActionContext('logout','json');
		$viewRenderer = Zend_Controller_Action_HelperBroker::getExistingHelper('viewRenderer');
		$viewRenderer->setNoRender();
		parent::init();
	}

	public function indexAction() {
		$this->_helper->getHelper('Redirector')->gotoUrl('/Bxs/app/xul/main.xul');
	}

	public function loginAction() {
		$this->_helper->authenticate();
		$this->_user = $this->_helper->Authenticate->getUser();
		$this->_checkLastLogin();
		$this->_loginUser();
		$this->view->assign(array('id' => $this->_user->id));
	}

	public function logoutAction() {
		$this->_helper->authenticate();
		$this->_user = $this->_helper->Authenticate->getUser();
		$session = $this->_user->current_admin_session;
		try {
			$session->close();
		}
		catch (Exception $e) {
			Bbx_Log::debug("Unable to close session: ".$e->getMessage());
		}
		$this->_doSearchIndex();
	}
	
	protected function _doSearchIndex() {
		try {
			$report = Bbx_Model::load('SearchIndexReport');
			$time = $report->timeSinceLastIndex();
			Bbx_Log::write('Last search-index was ' . $time . 's ago');
			if ($time > 86400) {
				Bbx_Log::write('> 24h elapsed, running index');
				$pid = exec('nice php ' . APPLICATION_PATH . 
					'/../library/Bbx/bin/search-index.php ' . $_SERVER['HTTP_HOST'] . 
					' > /dev/null 2>&1 &');
			}
		}
		catch (Exception $e) {
			Bbx_Log::write('Unable to load SearchIndexReport model');
		}
	}

	protected function _loginUser() {
		$session = $this->_user->admin_sessions->create();
	}

	protected function _checkLastLogin() {
		$session = $this->_user->last_admin_session;
		if (!$session instanceof Bbx_Model) {
			return;
		}
		try {
			if ($session->logged_out_at == '0000-00-00 00:00:00') {
				$session->close();
			}
		}
		catch (Exception $e) {
			Bbx_Log::debug("Unable to close session: ".$e->getMessage());
		}
	}

}

?>