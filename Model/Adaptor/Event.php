<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Model_Adaptor_Event extends Bbx_Model_Adaptor_Abstract {

	protected $_startName;
	protected $_endName;

	public function __construct($params) {
		$this->_startName = $params['start'];
		$this->_endName = $params['end'];
	}
	
	public function start() {
		return $this->_model->{$this->_startName};
	}
	
	public function end() {
		return $this->_model->{$this->_endName};		
	}
	
	public function year() {
		return Bbx_Date::date($this->start(), 'Y');
	}
}

?>