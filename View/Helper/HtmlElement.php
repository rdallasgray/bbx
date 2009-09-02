<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Boxes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_View_Helper_HtmlElement {
	
	public $view;
	protected $_attributes;
	protected $_tagname;
	
	public function __construct($tagname = null) {
		if (isset($tagname)) {
			$this->_tagname = $tagname;
		}
		$this->_attributes = new stdClass;
	}
	
	public function htmlElement() {
		return $this;
	}

	public function setView(Zend_View_Interface $view) {
		$this->view = $view;
	}
	
	public function __get($attname) {
		return $this->_attributes->$attname;
	}
	
	public function __set($attname,$value) {
		$this->_attributes->$attname = (string) $value;
	}
	
	public function __call($method,$args) {
		$this->_attributes->$method = $args[0];
		return $this;
	}
	
	public function __toString() {
		return $this->_build();
	}
	
	protected function _build() {
		$str = implode('',array(
			$this->_open(),
			$this->_buildAttributes(),
			$this->_close()
		));
			
		return $str;
	}
	
	protected function _buildAttributes() {
		$atts = array();
		foreach ($this->_attributes as $att=>$val) {
			$atts[] = implode(array(' ',$att,'=','"',$val,'"'));
		}
		return implode('',$atts);
	}
	
	protected function _open() {
		return '<'.$this->_tagname;
	}
	
	protected function _close() {
		return '/>';
	}
	
}

?>