<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Admin_ErrorController extends Bbx_Controller_Rest {
	
	public function init() {
		$this->_helper->contextSwitch()->addActionContext('error','json');
		$this->_helper->contextSwitch()->initContext();
	}

	public function errorAction() {
		Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->setNoRender(true);
		
		$error = $this->_getParam('error_handler');

		switch ($error->type) {
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
			$this->getResponse()->setHttpResponseCode(404);
			break;

			default:
			if ($error['exception'] instanceof Bbx_Controller_Rest_Exception) {
				$this->getResponse()->setHttpResponseCode($error['exception']->getCode());
			}
			else {
				$this->getResponse()->setHttpResponseCode(500);
			}
			$this->_notify($error);
			break;
		}
		
		$this->view->error = $error['exception']->getMessage();
		
		if (isset($error['exception']->errorVars)) {
			$this->view->errorVars = $error['exception']->errorVars;
		}
		
	}

	protected function _notify($error) {
		Bbx_Log::write(print_r($error,true));
/*		if (isset(Bbx_Config::get()->site->mail->support_address)) {
			try {
				$mail = Bbx_Mail::instance();
				$mail->setFrom('error@'.Bbx_Config::get()->site->location,Bbx_Config::get()->site->location);
				$mail->setBodyText(print_r($error,true));
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