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
	
	protected function _findRowset(Bbx_Model $parentModel) {
		
		$this->_foreignKey = isset($this->_polymorphicKey) ? 
			$this->_polymorphicKey : Inflector::singularize($this->_childTableName).'_id';

		$polyType = isset($this->_polymorphicType) ? $parentModel->{$this->_polymorphicType} : null;
		
		return $this->_model($polyType)->getTable()->find($parentModel->{$this->_foreignKey});
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
	
}

?>