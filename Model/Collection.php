<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Model_Collection implements IteratorAggregate, Countable, ArrayAccess {
	
	protected $_parentModel;
	protected $_backing;
	protected $_models = array();
	protected $_table;
	protected $_primary;
	protected $_string;
	protected $_iterator;
	protected $_renderAsList = false;

	public function __construct(Bbx_Model $parentModel, $backing, $relationship = null, $childModelName = null) {
		$this->_parentModel = $parentModel;
		$this->_childModelName = $childModelName ? $childModelName : get_class($parentModel);
		$this->_backing = $backing;
		$this->_relationship = $relationship;
		$this->_table = Bbx_Model::load($this->_childModelName)->getTable();
		$this->_primary = implode('-', $this->_table->info('primary'));
	}
	
	public function getPrimary() {
		return $this->_primary;
	}
	
	public function getModelName() {
		return $this->_childModelName;
	}
	
	public function getTableName() {
		return $this->_table->info('name');
	}
	
	public function getBacking() {
		return $this->_backing;
	}
	
	public function getCurrentRowModel() {
		$current = $this->_backing instanceof Bbx_Db_Table_Rowset ? $this->_backing->current() : current($this->_backing);
		return $current ? $this->_instantiateRowModel($current) : null;
	}
	
	protected function _instantiateRowModel($row) {
		if ($row instanceof Bbx_Model) {
			$model = $row;
		}
		else {
			$model = Bbx_Model::load($this->getModelName());
			$model->setRowData($row);
		}
		return $this->_renderAsList ? array('id' => $model->id, 'label' => $model->__toString()) : $model;
	}
	
	public function first() {
		return $this->getIterator()->rewind();
	}
	
	public function current() {
		return $this->getIterator()->current();
	}
	
	public function next() {
		return $this->getIterator()->next();
	}
	
	public function last() {
		if ($this->_backing instanceof Bbx_Db_Table_Rowset) {
			while ($this->getIterator()->next()) {
				$i = $this->getIterator()->next();
			}
			return $i;
		}
		return end($this->_backing);
	}
		
	public function getIterator() {
		if (!isset($this->_iterator)) {
			$this->_iterator = new Bbx_Model_Collection_Iterator($this);
		}
		return $this->_iterator;
	}
	
	public function offsetExists($offset) {
		$this->getIterator()->seek($position);
		return $this->getIterator()->valid();
	}
	
	public function offsetGet($offset) {
		return $this->getIterator()->seek($offset);
	}
	
	public function offsetSet($offset, $value) {
		// reqd by ArrayAccess
	}
	
	public function offsetUnset($offset) {
		// reqd by ArrayAccess
	}

	public function __toString() {
		$a = array();
		foreach ($this as $model) {
			$a[] = $model->__toString();
		}
 		return (string)implode(', ',$a);
	}
		
	public function count() {
		return count($this->_backing);
	}
	
	public function toArray($options = array()) {
		
		$a = array();
		
		foreach ($this as $model) {
			if (isset($options['deep']) && $options['deep'] === true) {
				$a[] = $model instanceof Bbx_Model ? $model->toArray() : $model;
			}
			else {
				$a[] = $model;
			}
		}
		
		return $a;
	}
	
	public function renderAsList($option = true) {
		$this->_renderAsList = $option;
	}
	
	protected function _toRowArray() {
		if (is_array($this->_backing)) {
			return $this->_backing;
		}
		$array = array();
		foreach ($this->_backing as $row) {
			$array[] = $row;
		}
		return $array;
	}
	
	public function random($num = 1, $limit = 0) {
		if (count($this->_backing) === 0) {
			return array();
		}
		$array = $this->_toRowArray();
		if ($limit > 0) {
			$array = array_slice($array, 0, $limit);
		}
		$num = $num > count($array) ? count($array) : $num;
		$keys = (array)array_rand($array, $num);
		$rand = array_values(array_intersect_key($array,array_flip($keys)));
		if ($num == 1) {
			return $this->_instantiateRowModel($rand[0]);
		}
		$models = array();
		foreach ($rand as $row) {
			$models[] = $this->_instantiateRowModel($row);
		}
		return $models;
	}
	
	public function create($attributes = array(), $useId = false) {
		if (isset($this->_relationship)) {
			Bbx_Log::debug('$this->_relationship is set, creating new and reloading');
			$model = $this->_relationship->create($this->_parentModel, $attributes, $useId);
		}
		else {
			Bbx_Log::debug('$this->_relationship is NOT set, creating new and reloading');
			$model = $this->_parentModel->create($attributes, $useId);
		}
		$this->_reload($this->_parentModel);
		return $model;
	}
	
	public function delete($id) {
		if (isset($this->_relationship)) {
			$this->_relationship->delete($this->_parentModel,$id);
			$this->_reload($this->_parentModel);
		}
		else {
			$this->_parentModel->delete($id);
			$this->_reload($this->_parentModel);
		}
		return $this;
	}
	
	protected function _reload(Bbx_Model $model) {
		$this->_models = array();
		if (isset($this->_relationship)) {
			$c = $this->_relationship->getCollection($model,true,true);
			$this->_backing = $c->getBacking();
		}
	}

	public function etag($extra = null) {
		$data = ($this->_backing instanceof Zend_Db_Table_Rowset) ? $this->_backing->getRawData() : $this->_backing;
		return md5(serialize($data).$extra);
	}
	
	public function url($absolute = false) {
		$url = $this->_parentModel->url($absolute);
		if ($this->_childModelName === get_class($this->_parentModel)) {
			return $url;
		}
		return $url.'/'.Inflector::pluralize(Inflector::interscore($this->_childModelName));
	}

	public function __destruct() {		
		unset($this->_relationship);
		unset($this->_parentModel);
		unset($this->_backing);
		unset($this->_models);
		unset($this->_iterator);
		unset($this->_table);
	}

}

?>