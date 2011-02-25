<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Model_Relationship_Finder implements IteratorAggregate, Countable, ArrayAccess {
	
	protected $_relationship;
	protected $_parentModel;
	protected $_collection = null;
	protected $_clearFlag = false;
	
	public function __construct(Bbx_Model_Relationship_Abstract $relationship, Bbx_Model $parentModel) {
		$this->_relationship = $relationship;
		$this->_parentModel = $parentModel;
	}
	
	protected function _collection() {
		if ($this->_collection === null || $this->_clearFlag) {
			$this->_collection = $this->_relationship->getCollection($this->_parentModel,$this->_clearFlag);
		}
		$this->_setClearFlag(false);
		return $this->_collection;
	}
	
	protected function _setClearFlag($bool) {
		$this->_clearFlag = $bool;
	}
	
	public function getIterator() {
		return new Bbx_Model_Collection_Iterator($this->_collection());
	}
	
	public function count() {
		return $this->_collection()->count();
	}
		
	public function offsetExists($offset) {
		return $this->_collection()->offsetExists($offset);
	}
	
	public function offsetGet($offset) {
		return $this->_collection()->offsetGet($offset);
	}
	
	public function offsetSet($offset, $value) {
		// reqd by ArrayAccess
	}
	
	public function offsetUnset($offset) {
		// reqd by ArrayAccess
	}

	public function __get($key) {
		return $this->_collection()->$key;
	}
	
	public function __set($key,$val) {
		return $this->_collection()->$key = $val;
	}
	
	public function __isset($key) {
		return isset($this->_collection()->$key);
	}
	
	public function __call($method,$arguments) {
		return call_user_func_array(array($this->_collection(),$method),$arguments);
	}
	
	public function findAll($params = null) {
		if ($params !== 'all' && !empty($params)) {
			$this->_relationship->setFindParams($params);
		}
		$this->_setClearFlag(true);
		$c = $this->_collection();
		$this->_setClearFlag(true);
		return $c;
	}
	
	public function find($params) {
		return $this->findAll($params)->current();
	}
	
	public function select($conditions) {
		$this->_relationship->select($conditions);
		$this->_setClearFlag(true);
		$c = $this->_collection();
		$this->_setClearFlag(true);
		return $c;
	}
	
	public function __toString() {
		return (string) $this->_collection()->__toString();
	}
	
	public function __destruct() {
		unset($this->_relationship);
		unset($this->_parentModel);
		unset($this->_collection);
	}

}

?>