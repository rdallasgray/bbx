<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Boxes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Model_Relationship_HasManyThrough extends Bbx_Model_Relationship_Abstract {

	protected $_throughName;

	protected function _findCollection(Bbx_Model $parentModel) {
		$r = Bbx_Model_Registry::get('Relationships')->getRelationshipDataFor(get_class($parentModel),$this->_throughName);
		$type = reset(array_keys($r));
		$select = isset($this->_select) ? $this->_select() : $this->_model()->getTable()->select();
		$func = '_'.$type.'Rowset';
		
		$stmt = $this->$func($select,$parentModel)->query();
		
		$config = array(
            'table'    => $this->_model()->getTable(),
            'data'     => $stmt->fetchAll(Zend_Db::FETCH_ASSOC),
            'readOnly' => false,
            'stored'   => true
		);
		$rowset = new Bbx_Db_Table_Rowset($config);
				
		$this->_collections[$parentModel->id] = new Bbx_Model_Collection($parentModel,$rowset,$this);
	}
	
	protected function _hasManyRowset($select,$parentModel) {
		return $select
			->from($this->_childName)
			->from($this->_throughName,array())
			->where("`".$this->_childName."`.`".$this->_parentRefColumn."` = `".$this->_throughName."`.`id`")
			->where("`".$this->_throughName."`.`".Inflector::singularize($this->_parentName)."_id` = ".$parentModel->id)
			->setIntegrityCheck(false);
	}
	
	protected function _hasAndBelongsToManyRowset($select,$parentModel) {
		$joinTable = Bbx_Model_JoinTable::findName($this->_parentName,$this->_throughName);
		
		return $select
			->from($this->_childName)
			->from($joinTable,array())
			->where("`".$this->_childName."`.`".$this->_parentRefColumn."` = `".$joinTable."`.`".Inflector::singularize($this->_throughName)."_id`")
			->where("`".$joinTable."`.`".Inflector::singularize($this->_parentName)."_id` = ".$parentModel->id)
			->setIntegrityCheck(false);
	}
	
	public function create(Bbx_Model $parentModel, $attributes = array()) {
		throw new Zend_Exception('HasManyThrough cannot create');
		// it flipping should be able to
	}
	
	public function delete(Bbx_Model $parentModel, $id) {
		throw new Zend_Exception('HasManyThrough cannot delete');
		// ditto
	}

}

?>