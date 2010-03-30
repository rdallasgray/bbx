<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_ControllerPlugin_NestedLayouts extends Zend_Controller_Plugin_Abstract {

	protected $_layouts = array();
	protected $_layoutsAtShutdown = array();
	protected $_errorDetected = false;

	public function addLayout($layout) {
		$this->_layouts = array_merge($this->_layouts,array_diff($layout,$this->_layouts));
	}
	
	public function setErrorDetected($bool = true) {
		$this->_errorDetected = $bool;
	}

	protected function _renderLayouts() {
		$mvcLayout = Zend_Layout::getMvcInstance();
		$mvcContentKey = $mvcLayout->getContentKey();
		$content = $mvcLayout->$mvcContentKey;
		
		$view = $this->_cloneView();

		$layout = new Zend_Layout(array(
			'layoutPath'=>$mvcLayout->getLayoutPath(),
			'viewSuffix'=>$mvcLayout->getViewSuffix()
		));
		$layout->setView($view);
		$layout->$mvcContentKey = $content;

		foreach ($this->_layouts as $layoutName) {
			$layout->setLayout($layoutName);
			$layout->$mvcContentKey = $content;
			$content = $layout->render();
		}
		$mvcLayout->content = $content;
		$this->getResponse()->setBody($mvcLayout->render());
	}

	public function dispatchLoopShutdown() {
		$this->_renderLayouts();
	}
	
    protected function _cloneView() {
      	$view = clone Zend_Registry::get('view');
		if ($this->_errorDetected) {
      		$view->clearVars();
		}
        return $view;
    }

}

?>