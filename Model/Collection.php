<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Model_Collection implements IteratorAggregate,Countable {
	
	protected $_parentModel;
	protected $_rowset;
	protected $_models = array();
	protected $_table;
	protected $_primary;
	protected $_string;
	protected $_iterator;

	public function __construct(Bbx_Model $parentModel, Bbx_Db_Table_Rowset $rowset, $relationship = null, $childModelName = null) {
		$this->_parentModel = $parentModel;
		$this->_childModelName = $childModelName ? $childModelName : get_class($parentModel);
		$this->_rowset = $rowset;
		$this->_relationship = $relationship;
		$this->_table = $this->_rowset->getTable();
		$info = $this->_table->info();
		$this->_primary = implode('-',$info['primary']);
	}
	
	protected function _table() {
		return $this->_rowset->getTable();
	}
	
	public function getPrimary() {
		return $this->_primary;
	}
	
	public function getModelName() {
		return $this->_childModelName;
	}
	
	public function getRowset() {
		return $this->_rowset;
	}
	
	public function getCurrentRowModel() {
		return $this->_rowset->current() ? $this->_instantiateRowModel($this->_rowset->current()) : null;
	}
	
	protected function _instantiateRowModel(Zend_Db_Table_Row $row) {
		if (array_key_exists($row->{$this->_primary},$this->_models)) {
			return $this->_models[$row->{$this->_primary}];
		}
		$model = Bbx_Model::load($this->getModelName());
		$model->setRowData($row);
		$this->_models[$row->{$this->_primary}] = $model;
		return $model;
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
		while ($this->getIterator()->next()) {
			$i = $this->getIterator()->next();
		}
		return $i;
	}
		
	public function getIterator() {
		if (!isset($this->_iterator)) {
			$this->_iterator = new Bbx_Model_Collection_Iterator($this);
		}
		return $this->_iterator;
	}

	public function __toString() {
		return $this->_stringifier();
	}

	protected function _stringifier() {
		$a = array();
		foreach ($this as $model) {
			$a[] = $model->__toString();
		}
 		return implode(', ',$a);
	}
		
	public function count() {
		return $this->_rowset->count();
	}
	
	public function toArray($options = array()) {
		
		$a = array();
		
		foreach ($this as $model) {
			if (isset($options['deep']) && $options['deep'] === true) {
				$a[] = $model->toArray($options);
			}
			else {
				$a[] = $model;
			}
		}
		
		return $a;
	}
	
	protected function _toRowArray() {
		$array = array();
		foreach ($this->_rowset as &$row) {
			$array[] = $row;
		}
		return $array;
	}
	
	public function random($num = 1) {
		if (count($this->_rowset) == 0) {
			return array();
		}
		$array = $this->_toRowArray();
		$keys = (array)array_rand($array,$num);
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
	
	public function create($attributes = array()) {
		if (isset($this->_relationship)) {
			Bbx_Log::debug('$this->_relationship is set, creating new and reloading');
			$model = $this->_relationship->create($this->_parentModel,$attributes);
		}
		else {
			Bbx_Log::debug('$this->_relationship is NOT set, creating new and reloading');
			$model = $this->_parentModel->create($attributes);
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
	
	public function append(Bbx_Model $model) {
		if (isset($this->_relationship)) {
			$this->_relationship->append($this->_parentModel,$model);
			$this->_reload($model);
			return $this;
		}
		throw new Bbx_Model_Exception("No relationship loaded for ".$this->_table->info('name')."-".$model->getTable()->info('name'));
	}
	
	protected function _reload(Bbx_Model $model) {
		$this->_models = array();
		if (isset($this->_relationship)) {
			$c = $this->_relationship->getCollection($model,true,true);
			$this->_rowset = $c->getRowset();
		}
	}

	public function etag($extra = null) {
		
		$data = $this->getRowset()->getRawData();
		
		return md5(serialize($data).$extra);
	}
	
	public function __destruct() {
		unset($this->_iterator);
	}

}

?>