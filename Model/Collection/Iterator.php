<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Boxes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Model_Collection_Iterator implements SeekableIterator {
	
	protected $_collection;
	
	public function __construct(Bbx_Model_Collection $collection) {
		$this->_collection = $collection;
	}
	
	public function rewind() {
		$this->_collection->getRowset()->rewind();
		if ($this->_collection->getRowset()->valid()) {
			return $this->_collection->getCurrentRowModel();
		}
		return null;
	}

	public function current() {
//		print_r($this->_collection->getRowset());
		echo $this->_collection->getRowset()->valid() === false;
		if ($this->_collection->getRowset()->valid()) {
			return $this->_collection->getCurrentRowModel();
		}
		return null;
	}

	public function key() {
		$primary = $this->_collection->getPrimary();
		return $this->_collection->getRowset()->current()->$primary;
	}
	
	public function next() {
		$this->_collection->getRowset()->next();
		if ($this->_collection->getRowset()->valid()) {
			return $this->_collection->getCurrentRowModel();
		}
		return null;
	}
	
	public function valid() {
		return $this->_collection->getRowset()->valid();
	}
	
	public function seek($position) {
		$this->_collection->getRowset()->seek($position);
		return $this->_collection->getCurrentRowModel();
	}
	
	public function __destruct() {
		unset($this->_collection);
	}

}

?>