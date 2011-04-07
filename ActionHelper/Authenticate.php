<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/

class Bbx_ActionHelper_Authenticate extends Zend_Controller_Action_Helper_Abstract {
	
	protected $_user;
	protected $_resolver;
	protected $_redirect;
	
	public function direct() {	
		if (!$this->asPrivilege('staff')) {
			throw new Bbx_Controller_Rest_Exception(null, 401);
		}
	}
	
	public function setRedirect($redirect) {
		$this->_redirect = $redirect;
		return $this;
	}
	
	public function asRole($name) {
		return $this->getUser()->role->name == $name;
	}
	
	public function asPrivilege($roleName) {
		return $this->getUser()->hasPrivilege($roleName);
	}
	
	protected function _httpAuth() {
		$config = array(
			'accept_schemes' => 'digest',
			'realm'          => Bbx_Config::get()->site->location,
			'digest_domains' => '/',
			'nonce_timeout'  => 3600,
		);
		$adaptor = new Zend_Auth_Adapter_Http($config);
		$this->_resolver = new Bbx_Auth_Resolver_Db;
		$adaptor
			->setDigestResolver($this->_resolver)
			->setRequest($this->getRequest())
			->setResponse($this->getResponse());
		
		try {
			$auth = $adaptor->authenticate();
			if (!$auth->isValid()) {
				if (isset($this->_redirect)) {
					$body = $this->_getRedirectBody();
				}
				else {
					$body = $this->_get403Body();
				}
				$this->getResponse()->setHttpResponseCode(401)->setBody($body)->sendResponse();
				exit();
			}
			return $auth->isValid();
		}
		catch (Exception $e) {
			Bbx_Log::write('Exception during authentication: ' . $e->getMessage());
			throw new Bbx_Controller_Rest_Exception(null, 401);
		}
	}
	
	protected function _getRedirectBody() {
		return '<html><meta http-equiv="refresh" content="1;url=' . $this->_redirect . '"></html>';
	}
	
	protected function _get403Body() {
		$file = APPLICATION_PATH . '/../www/static/html/403.html';
		if (file_exists($file)) {
			return file_get_contents($file);
		}
		return '403: Forbidden';
	}
	
	public function getUser() {
		if (!isset($this->_user)) {
			if (!$this->_httpAuth()) {
				if (isset($this->_redirect)) {
					Zend_Controller_Action_HelperBroker::getStaticHelper('redirector')->gotoUrl($this->_redirect);
				}
				else {
					throw new Bbx_Controller_Rest_Exception(null, 403);
				}
			}
			$this->_user = $this->_resolver->getUser();
		}
		return $this->_user;
	}

}

?>