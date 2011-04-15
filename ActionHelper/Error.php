<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/

class Bbx_ActionHelper_Error extends Zend_Controller_Action_Helper_Abstract {

	public function log(Exception $e) {
		Bbx_Log::debug($e->getMessage(). "::" . $this->getRequest()->getRequestUri());
	}

	public function notify(Exception $e) {
		if (isset(Bbx_Config::get()->site->support_address) && APPLICATION_ENV == 'production') {
			try {
				$mail = Bbx_Mail::instance();
				$mail->setFrom('error@'.Bbx_Config::get()->site->location,Bbx_Config::get()->site->location);
				$mail->setBodyText($this->getRequest()->getRequestUri() . "\n\n" . print_r($e, true));
				$mail->addTo(Bbx_Config::get()->site->support_address);
				$mail->setSubject('Error at '.Bbx_Config::get()->site->location);
				$mail->send();
			}
			catch (Exception $exc) {
				Bbx_Log::debug(print_r($e, true));
				Bbx_Log::debug("Couldn't send mail: " . $exc->getMessage());
			}
		}
		else {
			Bbx_Log::debug(print_r($e, true));
		}
	}
}

?>