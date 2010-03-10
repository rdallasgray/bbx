<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_ControllerPlugin_ActionHelpers extends Zend_Controller_Plugin_Abstract {

	public function routeShutdown(Zend_Controller_Request_Abstract $request) {
		$moduleName = $request->getModuleName();
		if ($moduleName === 'admin') {
			return;
		}
		
		$modelHelper = new Bbx_ActionHelper_Model;
		$csvHelper = new Bbx_ActionHelper_Csv;
		$urlHelper = new Zend_Controller_Action_Helper_Url;

		Zend_Controller_Action_HelperBroker::addHelper($modelHelper);
		Zend_Controller_Action_HelperBroker::addHelper($csvHelper);
		Zend_Controller_Action_HelperBroker::addHelper($urlHelper);

		$contextHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('contextSwitch');
		
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
	}

}

?>