<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Boxes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Model_Relationship_HasMany extends Bbx_Model_Relationship_Abstract {
		
	protected function _initialise() {		
		$this->_model()->getTable()->setReferences(array(
			$this->_parentClassName => array(
				'columns' => array($this->_parentRefColumn),
				'refTableClass' => 'Bbx_Db_Table',
				'refColumns' => 'id'
			)
		));
	}
	
	protected function _findCollection(Bbx_Model $parentModel) {
		$rowset = $parentModel->getRowData()->findDependentRowset(
			$this->_model()->getTable(),
			$this->_parentClassName,
			$this->_select()
		);
		$this->_collections[$parentModel->id] = new Bbx_Model_Collection($parentModel,$rowset,$this);
	}
	
	public function create(Bbx_Model $parentModel, $attributes = array()) {
		$attributes[$this->_parentRefColumn] = $parentModel->id;
		if ($this->_polymorphic) {
			$attributes[$this->_polymorphicType] = Inflector::underscore($this->_parentClassName);
		}
		$model = Bbx_Model::load(Inflector::classify($this->_childName))->create($attributes);
		unset($this->_collections[$parentModel->id]);
		return $model;
	}
	
	public function delete(Bbx_Model $parentModel, $id) {
		unset($this->_collections[$parentModel->id]);
		return Bbx_Model::load(Inflector::classify($this->_childName))->find($id)->delete();
	}

}

?>