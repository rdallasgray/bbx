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
		$this->_helper->contextSwitch()->initContext();
		$front = Zend_Controller_Front::getInstance();
		if ($plugin = Zend_Controller_Front::getInstance()->getPlugin('Bbx_ControllerPlugin_NestedLayouts')) {
			$plugin->clearLayouts();
			$plugin->setErrorDetected(true);
		}
	}

	public function errorAction() {

		$this->_error = $this->_getParam('error_handler');

		switch ($this->_error->type) {
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
			$this->getResponse()->setHttpResponseCode(404);
			$this->view->error = 'Not Found';
			$url = $this->getRequest()->getRequestUri();
			$this->view->errorVars = array('url'=>$url);
			break;

			default:
			if ($this->_error['exception'] instanceof Bbx_Controller_Rest_Exception) {
				$this->getResponse()->setHttpResponseCode($this->_error['exception']->getCode());
			}
			else {
				$this->getResponse()->setHttpResponseCode(500);
			}
			if ($this->getResponse()->getHttpResponseCode() == 500) {
				$this->_notify();
			}
			else {
				$this->_log();
			}
			
			$this->view->error = $this->_error['exception']->getMessage();
		
			if (isset($this->_error['exception']->errorVars)) {
				$this->view->errorVars = $this->_error['exception']->errorVars;
			}
			break;
		}
		
		if ($this->_helper->contextSwitch()->getCurrentContext() === 'json') {
			$vars = isset($this->view->errorVars) ? $this->view->errorVars : array();
			$this->getResponse()->setBody($this->view->error."\n\n".implode("\n",$vars));
			$this->view->clearVars();
			$this->getResponse()->sendResponse();
			exit();
		}
	}

	protected function _log() {
		Bbx_Log::write($this->_error->exception->getMessage()."::".$this->_error->request->getRequestUri());
	}

	protected function _notify() {
		Bbx_Log::debug(print_r($this->_error,true));
/*		if (isset(Bbx_Config::get()->site->mail->support_address)) {
			try {
				$mail = Bbx_Mail::instance();
				$mail->setFrom('error@'.Bbx_Config::get()->site->location,Bbx_Config::get()->site->location);
				$mail->setBodyText(print_r($this->_error,true));
				$mail->addTo(Bbx_Config::get()->site->mail->support_address);
				$mail->setSubject('Error at '.Bbx_Config::get()->site->location);
				$mail->send();
			}
			catch (Exception $e) {
			}
		}*/
	}
}

?>