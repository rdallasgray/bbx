<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_View_Helper_AddLayout {
	
	protected $_plugin;
	public $view;
	
	public function __construct() {
		$this->_getPlugin();
	}
	
	protected function _getPlugin() {
		$front = Zend_Controller_Front::getInstance();
		$pluginClass = 'Bbx_ControllerPlugin_NestedLayouts';
		if ($plugin = $front->getPlugin($pluginClass)) {
			$this->_plugin = $plugin;
			return;
		}
		$this->_plugin = new $pluginClass;
		Zend_Controller_Front::getInstance()->registerPlugin($this->_plugin);		
	}

	public function setView(Zend_View_Interface $view) {
		$this->view = $view;
	}

	public function addLayout() {
		$layout = func_get_args();
		$this->_plugin->addLayout($layout);
	}

	public function setLayout($layout) {
	  $this->_plugin->setLayout($layout);
	}
}

?>