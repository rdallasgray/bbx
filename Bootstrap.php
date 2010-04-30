<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/

class Bbx_Bootstrap extends Zend_Application_Bootstrap_Bootstrap {
	
	protected function _initLoader() {
		$ldr = Zend_Loader_Autoloader::getInstance();
		$bbxLdr = new Bbx_Autoloader;
		$ldr->unshiftAutoloader($bbxLdr);
	}
	
	protected function _initLib() {
		require(APPLICATION_PATH.'/../library/Bbx/Common/lib.php');
	}
	
	protected function _initLocale() {
		if (function_exists('mb_internal_encoding')) {
			mb_internal_encoding(Bbx_Config::get()->locale->charset);
		}
	}

	protected function _initHelperPrefix() {
		Zend_Controller_Action_HelperBroker::addPrefix('Bbx_ActionHelper');
	}
	
	protected function _initPlugins() {
		$this->bootstrap('FrontController');
		$front = $this->getResource('FrontController');
		$contextDependenciesPlugin = new Bbx_ControllerPlugin_ContextDependencies;
		$front->registerPlugin($contextDependenciesPlugin);
	}
	
	protected function _initDbUtf8() {
		$this->bootstrap('Db');
		$db = $this->getResource('Db');
		$db->query('SET NAMES utf8');
	}
	
	protected function _initRegistry() {
		Zend_Registry::set('db',$this->getResource('Db'));
	}

}

?>