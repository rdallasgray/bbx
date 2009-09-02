<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Boxes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_ControllerPlugin_NestedLayouts extends Zend_Controller_Plugin_Abstract {

	protected $_layouts = array();
	protected $_layoutsAtPostDispatch = array();

	public function addLayout($layout) {
		$this->_layouts = array_merge($this->_layouts,array_diff($layout,$this->_layouts));
	}

	protected function _renderLayouts() {
		$mvcLayout = Zend_Layout::getMvcInstance();
		$mvcContentKey = $mvcLayout->getContentKey();
		
		$this->_layoutsAtPostDispatch = array_reverse($this->_layouts);
		
		$view = $this->_cloneView();

		$layout = new Zend_Layout(array(
			'layoutPath'=>$mvcLayout->getLayoutPath(),
			'viewSuffix'=>$mvcLayout->getViewSuffix()
		));
		$layout->setView($view);

		foreach ($this->_layouts as $layoutName) {
			$layout->setLayout($layoutName);
			$layout->$mvcContentKey = $this->getResponse()->getBody();
			$body = $layout->render();
			$this->getResponse()->setBody($body);
		}
		
		$newLayouts = array_diff($this->_layouts,$this->_layoutsAtPostDispatch);
		
		if (count($newLayouts) > 0) {
			$this->_layouts = array_diff($this->_layouts,$this->_layoutsAtPostDispatch);
			$this->_renderLayouts();
		}
	}

	public function postDispatch() {
		$this->_renderLayouts();
	}
	
    protected function _cloneView() {
        $view = clone Bbx_View::get();
        $view->clearVars();
        return $view;
    }

}

?>