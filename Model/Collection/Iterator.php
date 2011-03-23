<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Model_Collection_Iterator implements SeekableIterator {
	
	protected $_collection;
	
	public function __construct(Bbx_Model_Collection $collection) {
		$this->_collection = $collection;
		$this->_backing = $this->_collection->getBacking();
	}
	
	public function rewind() {
		if ($this->_backing instanceof Zend_Db_Table_Rowset) {
			$this->_backing->rewind();
			if ($this->_backing->valid()) {
				return $this->_collection->getCurrentRowModel();
			}
			return null;
		}
		else return reset($this->_backing);
	}

	public function current() {
		if ($this->_backing instanceof Zend_Db_Table_Rowset) {
			if ($this->_backing->valid()) {
				return $this->_collection->getCurrentRowModel();
			}
			return null;
		}
		return current($this->_backing);
	}

	public function key() {
		$primary = $this->_collection->getPrimary();
		return $this->current()->$primary;
	}
	
	public function next() {
		if ($this->_backing instanceof Zend_Db_Table_Rowset) {
			$this->_backing->next();
			if ($this->_backing->valid()) {
				return $this->_collection->getCurrentRowModel();
			}
			return null;
		}
		return next($this->_backing);
	}
	
	public function valid() {
		if ($this->_backing instanceof Zend_Db_Table_Rowset) {
			return $this->_backing->valid();
		}
		return !!current($this->_backing);
	}
	
	public function seek($position) {
		if ($this->_backing instanceof Zend_Db_Table_Rowset) {
			$this->_backing->seek($position);
			return $this->_collection->getCurrentRowModel();
		}
		$backing = $this->_backing;
		return $backing[$position];
	}
	
	public function __destruct() {
		$this->_collection = null;
	}

}

?>