<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/

class Bbx_View_Helper_Model extends Zend_View_Helper_Abstract {
	
	protected $_model;
	protected $_modelName;
	protected $_text = '';
	
	public function setView(Zend_View_Interface $view) {
		$this->view = $view;
	}
	
	public function __call($method, $arguments) {
		
		$this->_modelName = $method;
		
		$m = (count($arguments) > 0) ? $arguments[0] : null;
		
		if ($m === null) {
			$this->_model = $this->view->{$this->_modelName};
		}
		else {
			$this->_model = $m;
		}
		
		return $this;		
	}
	
	public function setModel($m) {
		$this->_model = $m;
		return $this;
	}
	
	public function link($cond = true) {
		if ($cond && $this->_model->isLinkable()) {
			$this->_text = '<a href="' . $this->_model->url() . '">' . $this->_text . '</a>';
		}
		return $this;
	}
	
	public function wrapLink() {
		$this->_text = '<a href="' . $this->_model->url() . '">' . $this->_text . '</a>';
		return $this;
	}
	
	public function __toString() {
		$text = $this->_text;
		$this->_text = '';
		return $text;
	}
	
}

?>