<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Controller_Rest_Error extends Bbx_Controller_Rest {
	
	protected $_error;
	
	public function init() {
		$this->_helper->contextSwitch()->addActionContext('error','json');
		$this->_initContext();
		if ($plugin = Zend_Controller_Front::getInstance()->getPlugin('Bbx_ControllerPlugin_NestedLayouts')) {
			$plugin->setErrorDetected(true);
		}
		// TODO the below does nothing at present
		$this->_helper->getHelper('StaticCache')->cancel();
	}

	public function errorAction() {
		$this->_error = $this->_getParam('error_handler');
		$response = $this->getResponse();

		switch ($this->_error->type) {
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
			$this->_set404();
			break;

			default:
			if ($this->_error['exception'] instanceof Zend_View_Exception
				|| $this->_error['exception'] instanceof Bbx_Model_Exception_NotFound) {
				$this->_set404();
				break;
			}
			
			if ($this->_error['exception'] instanceof Bbx_Controller_Rest_Exception) {
				if ($this->_error['exception']->getCode() == 401) {
					// send the auth request immediately
					$response->sendResponse();
					exit();
				}
				$method = '_set' . $this->_error['exception']->getCode();
				if (!method_exists($this, $method)) {
					$method = '_set500';
				}
				$this->$method();
			}
			else {
				$this->_set500();
			}
		}
			
		if ($this->_helper->contextSwitch()->getCurrentContext() === 'json') {
			$vars = isset($this->view->errorVars) ? $this->view->errorVars : array();
			$response->setBody($this->view->error."\n\n".implode("\n", $vars));
			$this->view->clearVars();
			$response->sendResponse();
			exit();
		}
	}
	
	protected function _set404() {
		$this->getResponse()->setHttpResponseCode(404);
		$this->view->errorType = 'Not Found';
		$this->view->error = 'Not Found';
		$this->view->responseCode = '404';
		$url = $this->getRequest()->getRequestUri();
		$this->view->errorVars = array('url'=>$url);
		$this->_log();
	}
	
	protected function _set405() {
		$this->getResponse()->setHttpResponseCode(405);
		$this->view->errorType = 'Method Not Allowed';
		$this->view->error = 'Method Not Allowed';
		$this->view->responseCode = '405';
		$url = $this->getRequest()->getRequestUri();
		$this->view->errorVars = array('url'=>$url);
		$this->_log();
	}
	
	protected function _set403() {
		$this->getResponse()->setHttpResponseCode(403);
		$this->view->errorType = 'Forbidden';
		$this->view->error = 'Forbidden';
		$this->view->responseCode = '403';
		$url = $this->getRequest()->getRequestUri();
		$this->view->errorVars = array('url'=>$url);
		$this->_log();
	}
	
	protected function _set401() {
		$this->getResponse()->setHttpResponseCode(401);
		$this->view->errorType = 'Authorization Required';
		$this->view->error = 'Authorization Required';
		$this->view->responseCode = '401';
		$url = $this->getRequest()->getRequestUri();
		$this->view->errorVars = array('url'=>$url);
		$this->_log();
	}
	
	protected function _set500() {		
		Bbx_Log::write(print_r($this->_error, true));
		$this->getResponse()->setHttpResponseCode(500);
		$this->view->errorType = 'Server Error';
		$this->view->error = $this->_error['exception']->getMessage();;
		$this->view->responseCode = '500';
		$url = $this->getRequest()->getRequestUri();
		$this->view->errorVars = array('url'=>$url);
		$this->_notify();
	}

	protected function _log() {
		$this->_helper->Error->log($this->_error['exception']);
	}

	protected function _notify() {
		$this->_helper->Error->notify($this->_error['exception']);
	}
}

?>