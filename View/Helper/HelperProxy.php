<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/

class Bbx_View_Helper_HelperProxy extends Zend_View_Helper_Abstract {
	
	protected $_items;
	protected $_helperName;
	protected $_helperMethod;
	protected $_stringMethod;
	
	public function setView(Zend_View_Interface $view) {
		$this->view = $view;
	}
	
	public function __call($method, $arguments) {
		$this->_items = $arguments[0];
		$this->_helperMethod = count($arguments) > 1 ? $arguments[1] : $method;
		$this->_helperName = get_class($arguments[0][0]);
		
		$text = $this->_getOutput();
		unset($this->_stringMethod);
		return $text;		
	}
	
	protected function _getOutput() {
		return "";
	}
	
	protected function _getItemString($item) {
		if (!isset($this->_stringMethod)) {
			$this->_setStringMethod();
		}
		return $this->{$this->_stringMethod}($item);
	}
	
	protected function _setStringMethod() {
		try {
			$h = $this->view->getHelper($this->_helperName);
			if ($h instanceof Zend_View_Helper_Abstract && method_exists($h, $this->_helperMethod)) {
				$this->_stringMethod = '_helperString';
			}
		}
		catch(Exception $e) {
			$this->_stringMethod = '_noHelperString';
		}
	}
	
	protected function _helperString($item) {
		return (string) $this->view->{$this->_helperName}($item)->{$this->_helperMethod}();
	}
	
	protected function _noHelperString($item) {
		return (string) $item;
	}
}

?>