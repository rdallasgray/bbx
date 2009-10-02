<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Model_Relationship_HasAndBelongsToMany extends Bbx_Model_Relationship_Abstract {
	
	protected $_joinTable;
	protected $_childClassName;
	protected $_childRefColumn;
	
	protected function _initialise() {
		$this->_childClassName = Inflector::classify($this->_childName);
		$this->_childRefColumn = Inflector::singularize(Inflector::tableize($this->_childName)).'_id';
		
		$this->_joinTable()->setReferences(array(
			$this->_parentClassName => array(
				'columns' => array($this->_parentRefColumn),
				'refTableClass' => 'Bbx_Db_Table',
				'refColumns' => 'id'
			),
			$this->_childClassName => array(
				'columns' => array($this->_childRefColumn),
				'refTableClass' => 'Bbx_Db_Table',
				'refColumns' => 'id'
			),
		));
	}
	
	protected function _findCollection(Bbx_Model $parentModel) {
		$rowset = $parentModel->getRowData()->findManyToManyRowset(
			$this->_model()->getTable(),
			$this->_joinTable(),
			$this->_parentClassName,
			$this->_childClassName,
			$this->_select()
		);
		$this->_collections[$parentModel->id] = new Bbx_Model_Collection($parentModel,$rowset,$this);
	}
	
	protected function _joinTable() {
		if (isset($this->_joinTable)) {
			return $this->_joinTable;
		}
		$this->_joinTable = Bbx_Model_JoinTable::find($this->_parentName,$this->_childName);
		return $this->_joinTable;
	}
	
	public function create(Bbx_Model $parentModel, $attributes = array()) {
		$model = Bbx_Model::load($this->_childClassName)->create($attributes);
		$this->_joinTable()->insert(array($this->_parentRefColumn => $parentModel->id, $this->_childRefColumn => $model->id));
		unset($this->_collections[$parentModel->id]);
		return $model;
	}
	
	public function delete(Bbx_Model $parentModel, $id) {
		unset($this->_collections[$id]);
		return $this->_joinTable()->delete(array($this->_parentRefColumn.' = '.$parentModel->id, $this->_childRefColumn.' = '.$id));
	}
	
	public function append(Bbx_Model $parentModel, Bbx_Model $childModel) {
		unset($this->_collections[$parentModel->id]);
		echo "Append: Inserting ".$this->_parentRefColumn.": ".$parentModel->id.", ".$this->_childRefColumn.": ".$childModel->id."\n";
		return $this->_joinTable()->insert(array($this->_parentRefColumn => $parentModel->id, $this->_childRefColumn => $childModel->id));
	}

}

?>