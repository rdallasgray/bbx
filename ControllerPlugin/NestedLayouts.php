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
	protected $_errorDetected = false;

	public function addLayout($layout) {
		$this->_layouts = array_merge($this->_layouts,$layout);
	}

	public function setLayout($layout) {
	  $this->_layouts = array($layout);
	}
	
	public function setErrorDetected($bool = true) {
		$this->_errorDetected = $bool;
		$this->getResponse()->clearBody();
		$this->_clearLayouts();
	}

	public function postDispatch() {
		$layouts = array_reverse($this->_layouts);
		$mvcLayout = Zend_Layout::getMvcInstance();
		$mvcContentKey = $mvcLayout->getContentKey();
		$content = $this->getResponse()->getBody();
		
		$view = $this->_cloneView();

		$layout = new Zend_Layout(array(
			'layoutPath'=>$mvcLayout->getLayoutPath(),
			'viewSuffix'=>$mvcLayout->getViewSuffix()
		));
		$layout->setView($view);

		foreach ($layouts as $layoutName) {
			$layout->setLayout($layoutName);
			$layout->$mvcContentKey = $this->getResponse()->getBody();
			$this->getResponse()->setBody($layout->render());
		}
	}
	
    protected function _cloneView() {
      	$view = clone Zend_Registry::get('view');
		if ($this->_errorDetected) {
      		$view->clearVars();
		}
        return $view;
    }

	protected function _clearLayouts() {
		$this->_layouts = array();
	}

}

?>