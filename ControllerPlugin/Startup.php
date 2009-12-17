<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_ControllerPlugin_Startup extends Zend_Controller_Plugin_Abstract {

	public function routeShutdown(Zend_Controller_Request_Abstract $request) {

		$contextHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('contextSwitch');
		
		$autoContextHelper = new Bbx_ActionHelper_AutoContext;
		Zend_Controller_Action_HelperBroker::addHelper($autoContextHelper);
		
		$csvHelper = new Bbx_ActionHelper_Csv;
		Zend_Controller_Action_HelperBroker::addHelper($csvHelper);
		
		$viewRenderer = Zend_Controller_Action_HelperBroker::getExistingHelper('viewRenderer');
		
		$module = $request->getModuleName();
		
		$path = SITE_ROOT.'/application/modules/'.MODULE_NAME.'/views';

		switch ($module) {
			case 'admin':
			break;

			default:
			$view = Bbx_View::get();
			
			$view->setUseStreamWrapper(true);

			$modelHelper = new Bbx_ActionHelper_Model;
			$urlHelper = new Zend_Controller_Action_Helper_Url;

			Zend_Controller_Action_HelperBroker::addHelper($modelHelper);
			Zend_Controller_Action_HelperBroker::addHelper($urlHelper);
			
			$contextHelper->addContexts(
				array(
					'html' => array(),
					'csv'  => array(
	                    'suffix'    => 'csv',
	                    'headers'   => array('Content-Type' => 'application/csv; charset=iso-8859-1'),
	                    'callbacks' => array(
	                        'init' => array($csvHelper,'initContext'),
	                        'post' => array($csvHelper,'postContext'),
						)
					)
				)
			);

			$view->addHelperPath(SITE_ROOT.'/library/Bbx/View/Helper','Bbx_View_Helper');
			$view->addHelperPath($path.'/helpers','ViewHelper');

			if (file_exists($path.'/layouts')) {
				Zend_Layout::startMvc(array(
					'layout'=>'layout',
					'viewSuffix'=>'phtml',
					'layoutPath'=>$path.'/layouts'
				));
			}

			$view->addScriptPath($path.'/partials');
			$view->addScriptPath($path.'/scripts');

			$viewRenderer->setView($view);
		}
	}

}

?>