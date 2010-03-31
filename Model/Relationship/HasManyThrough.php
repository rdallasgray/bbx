<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Model_Relationship_HasManyThrough extends Bbx_Model_Relationship_Abstract {

	protected function _findRowset(Bbx_Model $parentModel) {
		
		$select = (isset($this->_select) && !empty($this->_select)) ? $this->_select() : $this->_model()->getTable()->select();

		$this->_parentRelationship = Bbx_Model_Registry::get('Relationships')->getRelationshipDataFor(
			$this->_parentModelName,$this->_throughName);
			
		$parentType = reset(array_keys($this->_parentRelationship));		
		$parentAttributes = $this->_parentRelationship[$parentType];
		$relationshipType = 'Bbx_Model_Relationship_'.ucwords($parentType);
		if (array_key_exists('through',$parentAttributes)) {
			$relationshipType .= 'Through';
		}
		
		$select->from($this->_childTableName);
		
		$select = call_user_func_array(
			array($relationshipType,'getExternalConditions'),
			array($select,$parentModel,$this->_throughName,$parentAttributes)
		);

		$this->_throughRelationship = Bbx_Model_Registry::get('Relationships')->getRelationshipDataFor(
			$this->_throughModelName,$this->_childName);

		$throughType = reset(array_keys($this->_throughRelationship));
		$throughAttributes = $this->_throughRelationship[$throughType];
		$throughRelationshipType = 'Bbx_Model_Relationship_'.ucwords($throughType);
		
		if ($throughType == 'belongsTo') {
			$this->_childName = Inflector::singularize($this->_childName);
		}
		
		$select = call_user_func_array(
			array($throughRelationshipType,'getExternalConditions'),
			array($select,$this->_throughModel,$this->_childName,$throughAttributes)
		);

		$stmt = $select->query();

		$config = array(
            'table'    => $this->_model()->getTable(),
            'data'     => $stmt->fetchAll(Zend_Db::FETCH_ASSOC),
            'readOnly' => false,
            'stored'   => true
		);

		return new Bbx_Db_Table_Rowset($config);
	}
	
	public function create(Bbx_Model $parentModel, $attributes = array()) {
		$child = Bbx_Model::load($this->_childModelName)->create($attributes);
		try {
			$through = Bbx_Model::load($this->_throughModelName)->create($this->_getThroughConditions($parentModel, $child->id));
			return $child;
		}
		catch (Exception $e) {
			$child->delete();
			throw $e;
		}
	}
	
	public function delete(Bbx_Model $parentModel, $childId) {
		return Bbx_Model::load($this->_throughModelName)->find($this->_getThroughConditions($parentModel,$childId))->delete();
	}
	
	protected function _getThroughConditions($parentModel, $childId) {
		$throughConditions = array(
			Inflector::singularize($this->_childTableName).'_id' => $childId,
			$this->_parentRefColumn => $parentModel->id
		);
		if (isset($this->_polymorphicType)) {
			$throughConditions[$this->_polymorphicType] = Inflector::singularize(Inflector::underscore($this->_parentModelName));
		}
		
		return $throughConditions;
	}
	
	public static function getExternalConditions($select,$parentModel,$childName,$attributes) {
		
		$parentModelName = get_class($parentModel);
		$parentTableName = $parentModel->getTableName(); 

		$childName = array_key_exists('source',$attributes) ? attributes('source') : $childName;
		$childModelName = Inflector::classify($childName);
		$childTableName = Bbx_Model::load($childModelName)->getTableName(); 

		$throughName = $attributes['through']; 
		$throughModelName = Inflector::classify($throughName);
		$throughTableName = Bbx_Model::load($throughModelName)->getTableName();
		
		if (!array_key_exists($childTableName,$select->getPart('from'))) {
			$select->from($childTableName,array()); 
		}
		
		if (array_key_exists('as',$attributes)) {
			$refColumn = $attributes['as'].'_id';
			$polyType = $attributes['as'].'_type';
		}
		else {
			$refColumn = Inflector::singularize($parentTableName).'_id';
		}

		$select
			->from($throughTableName,array()) 
			->where("`".$throughTableName."`.`".$refColumn."` = ".$parentModel->id) 
			->where("`".$throughTableName."`.`".Inflector::singularize($childTableName)."_id` = `".$childTableName."`.id");

		if (array_key_exists('as',$attributes)) {
			$select
				->where("`".$throughTableName."`.`".$polyType."` = '".Inflector::singularize($parentTableName)."'");
		}

		return $select;
	}

}

?>