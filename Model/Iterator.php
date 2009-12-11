<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Model_Iterator implements Iterator {
	
	protected $_model;
	protected $_cols;
	
	public function __construct(Bbx_Model $model) {
		$this->_model = $model;
		$this->_cols = $model->columns();
	}
	
	public function rewind() {
		$col = reset($this->_cols);
		return $this->_model->$col;
	}

	public function current() {
		$col = current($this->_cols);
		return $this->_model->$col;
	}

	public function key() {
		return current($this->_cols);
	}
	
	public function next() {
		if ($col = next($this->_cols)) {
			return $this->_model->$col;
		}
		return null;
	}
	
	public function valid() {
		return !!current($this->_cols);
	}
	
	public function __destruct() {
		unset($this->_model);
	}

}

?>