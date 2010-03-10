<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_ControllerPlugin_View extends Zend_Controller_Plugin_Abstract {

	public function routeShutdown(Zend_Controller_Request_Abstract $request) {
		
		$moduleName = $request->getModuleName();
		
		if ($moduleName === 'admin') {
			return;
		}
		
		$path = APPLICATION_PATH.'/modules/'.$moduleName.'/views';
		$view = new Zend_View;
		$view->setUseStreamWrapper(true);
		$view->setEncoding('UTF-8');
		$view->addScriptPath($path.'/partials');
		$view->addScriptPath($path.'/scripts');
		$view->addHelperPath(APPLICATION_PATH.'/../library/Bbx/View/Helper','Bbx_View_Helper');
		$view->addHelperPath($path.'/helpers','ViewHelper');
		$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
		$viewRenderer->setView($view);
	}

}

?>