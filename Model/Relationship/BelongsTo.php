<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Model_Relationship_BelongsTo extends Bbx_Model_Relationship_Abstract {
	
	protected $_foreignKey;
	
	protected function _initialise() {
		$this->_foreignKey = isset($this->_polymorphicKey) ? $this->_polymorphicKey : Inflector::singularize($this->_childTableName).'_id';
	}

	protected function _findCollection(Bbx_Model $parentModel) {
		$polyType = isset($this->_polymorphicType) ? $parentModel->{$this->_polymorphicType} : null;
		$this->_collections[$parentModel->id] = $this->_model($polyType)->findAll($parentModel->{$this->_foreignKey});
	}
	
	public static function getExternalConditions($select,$parentModel,$childName,$attributes) {

		$parentModelName = get_class($parentModel);
		$parentTableName = $parentModel->getTableName();
		
		$childName = array_key_exists('source',$attributes) ? attributes('source') : $childName;
		$childModelName = Inflector::classify($childName);
		$childTableName = Bbx_Model::load($childModelName)->getTableName();
		
		$refColumn = Inflector::singularize($childTableName).'_id';
		
		if (!array_key_exists($childTableName,$select->getPart('from'))) {
			$select->from($childTableName,array());
		}
		
		$select->where("`".$parentTableName."`.`".$refColumn."` = `".$childTableName."`.`id`");
		
		return $select;
	}
	
/* SHOULD NOT BE CREATING OR DELETING BELONGS_TO MODELS	
	public function create(Bbx_Model $parentModel, $attributes = array()) {
		if ($this->_polymorphic) {
			$attributes[$this->_polymorphicType] = Inflector::underscore($this->_parentModelName);
			$attributes[$this->_polymorphicKey] = $parentModel->id;
		}
		$model = Bbx_Model::load($this->_childModelName)->create($attributes);
		unset($this->_collections[$parentModel->id]);
		return $model;
	}
	
	public function delete(Bbx_Model $parentModel, $id) {
		unset($this->_collections[$parentModel->id]);
		$model = Bbx_Model::load($this->_childModelName)->find($id)->delete();
	}
*/
}

?>