<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Admin_AuthController extends Bbx_Controller_Rest {

	protected $_user;

	public function init() {
		$this->_helper->contextSwitch()->addActionContext('error','json');
		$this->_helper->contextSwitch()->addActionContext('login','json');
		$this->_helper->contextSwitch()->addActionContext('logout','json');
		$this->_helper->contextSwitch()->initContext();
	}

	public function loginAction() {
		$this->_authenticate();
		//		$this->_user = $this->_resolver->getUser();
		//		$this->_checkLastLogin();
		//		$this->_loginUser();
	}

	public function logoutAction() {
		$this->_authenticate();
		$this->_user = $this->_resolver->getUser();
		$session = $this->_user->current_admin_session;
		if (!empty($session)) {
			$session->close();
		}
	}

	protected function _loginUser() {
		$session = $this->_user->admin_sessions->create();
		// doesn't work -- findCurrentAdminSession?
		$session = $this->_user->current_admin_session;
	}

	protected function _checkLastLogin() {
		$lastSession = $this->_user->last_admin_session;
		if (!empty($lastSession)) {
			if ($lastSession->timeout == '0000-00-00 00:00:00') {
				$lastSession->close();
			}
		}
	}

}

?>