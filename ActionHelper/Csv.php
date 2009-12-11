<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.

Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/

class Bbx_ActionHelper_Csv extends Zend_Controller_Action_Helper_Abstract {

	public function initContext() {
		$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
		$view = $viewRenderer->view;
		if ($view instanceof Zend_View_Interface) {
			$viewRenderer->setNoRender(true);
		}
	}

	public function postContext() {
		$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
		$view = $viewRenderer->view;
		if ($view instanceof Zend_View_Interface) {
			if(method_exists($view, 'getVars')) {
				$vars = Bbx_Format_Csv::encode($view->getVars());
				$this->getResponse()->setBody($vars);
				$this->getResponse()->setHeader('Content-disposition','attachment; filename='
					.Bbx_ActionHelper_Filename::fromUrl($this->getRequest()->getRequestUri()).'.csv', true);
			} 
			else {
				throw new Zend_Controller_Action_Exception('View does not implement the getVars() method needed to encode the view into CSV');
			}
		}
	}

}

?>