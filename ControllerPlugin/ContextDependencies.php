<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_ControllerPlugin_ContextDependencies extends Zend_Controller_Plugin_Abstract {
	
	protected $_request;
	
	public function routeShutdown(Zend_Controller_Request_Abstract $request) {
		
		$this->_request = $request;
		
		if ($accept = $request->getHeader('Accept')) {
			$types = explode(',',$accept);
			$mainType = trim($types[0]);
			$mime = explode('/',$mainType);
		
			if ($mime[1] !== '') {
				try {
					if ($request->getParam('format') == '') {
						$request->setParam('format',$mime[1]);
					}
				}
				catch (Exception $e) {
				}
			}
		}
		
		$context = $request->format;

		$contextHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('contextSwitch');
		
		$contextHelper->addContext('html',array())
			          ->setDefaultContext('html');
		
		$this->_initDependencies($context);
	}
	
	protected function _initDependencies($context) {
		switch ($context) {
			case 'html':
			$this->_initView();
			$this->_initLayout();
			$this->_initHelpers();
			break;
			
			case 'json':
			case 'csv':
			$this->_initHelpers();
			break;

			default:
			$this->_initView();
			$this->_initLayout();
			$this->_initHelpers();
		}
	}
	
	protected function _initView() {
		$path = APPLICATION_PATH.'/modules/'.MODULE_NAME.'/views';
		$view = new Zend_View;
		$view->setUseStreamWrapper(true);
		$view->setEncoding('UTF-8');
		$view->addScriptPath($path.'/partials');
		$view->addScriptPath($path.'/scripts');
		$view->addHelperPath(APPLICATION_PATH.'/../library/Bbx/View/Helper','Bbx_View_Helper');
		$view->addHelperPath($path.'/helpers','ViewHelper');
		$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
		$viewRenderer->setView($view);
		Zend_Registry::set('view',$view);
	}
	
	protected function _initLayout() {
		$path = APPLICATION_PATH.'/modules/'.MODULE_NAME.'/views';
		Zend_Layout::startMvc(array(
			'layout'=>'layout',
			'viewSuffix'=>'phtml',
			'layoutPath'=>$path.'/layouts'
		));
	}
	
	protected function _initHelpers() {
		
//		switch ($context) {
			
//			case 'csv':
			$csvHelper = new Bbx_ActionHelper_Csv;
			Zend_Controller_Action_HelperBroker::addHelper($csvHelper);
			Zend_Controller_Action_HelperBroker::getStaticHelper('contextSwitch')->addContext(
				'csv',
				array(
                    'suffix'    => 'csv',
                    'headers'   => array('Content-Type' => 'application/csv; charset=iso-8859-1'),
                    'callbacks' => array(
                        'init' => array($csvHelper,'initContext'),
                        'post' => array($csvHelper,'postContext'),
					)
				)
			);
//			break;
			
//			default:
//		}
	}

}

?>