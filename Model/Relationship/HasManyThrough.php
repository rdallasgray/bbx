<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Model_Relationship_HasManyThrough extends Bbx_Model_Relationship_Abstract {

	protected $_throughName;

	protected function _findCollection(Bbx_Model $parentModel) {
		$select = isset($this->_select) ? $this->_select() : $this->_model()->getTable()->select();
		
		$stmt = $this->_hasManyRowset($select,$parentModel)->query();

		$config = array(
            'table'    => $this->_model()->getTable(),
            'data'     => $stmt->fetchAll(Zend_Db::FETCH_ASSOC),
            'readOnly' => false,
            'stored'   => true
		);
		$rowset = new Bbx_Db_Table_Rowset($config);
				
		$this->_collections[$parentModel->id] = new Bbx_Model_Collection($parentModel,$rowset,$this,$this->_childModelName);
	}
	
	protected function _hasManyRowset($select,$parentModel) {
		$select
			->from($this->_childName)
			->from($this->_throughName,array())
			->where("`".$this->_throughName."`.`".$this->_parentRefColumn."` = ".$parentModel->id)
			->where("`".$this->_throughName."`.`".Inflector::singularize($this->_childName)."_id` = `".$this->_childName."`.`id`")
			->setIntegrityCheck(false);
		return $select;
	}
	
	public function create(Bbx_Model $parentModel, $attributes = array()) {
		$child = Bbx_Model::load($this->_childName)->create($attributes);
		$throughAttributes = array(
			Inflector::singularize($this->_childName).'_id' => $child->id,
			$this->_polymorphicType = $this->_parentName,
			$this->_polymorphicKey = $parentModel->id
		);
		$through = Bbx_Model::load($this->_throughName)->create($throughAttributes);
		return $child;
	}
	
	public function delete(Bbx_Model $parentModel, $id) {
		$throughConditions = array(
			Inflector::singularize($this->_childName).'_id' => $id,
			$this->_polymorphicType = $this->_parentName,
			$this->_polymorphicKey = $parentModel->id
		);
		return Bbx_Model::load($this->_throughName)->find($throughConditions)->delete();
	}

}

?>