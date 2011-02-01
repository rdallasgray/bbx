<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Model_Relationship_HasMany extends Bbx_Model_Relationship_Abstract {
	
	protected function _findRowset(Bbx_Model $parentModel) {

		$this->_model()->getTable()->setReferences(array(
			$this->_parentModelName => array(
				'columns' => array($this->_parentRefColumn),
				'refTableClass' => 'Bbx_Db_Table',
				'refColumns' => 'id'
			)
		));

		return $parentModel->getRowData()->findDependentRowset(
			$this->_model()->getTable(),
			$this->_parentModelName,
			$this->_select()
		);
	}
	
	public function create(Bbx_Model $parentModel, $attributes = array(), $useId = false) {
		$attributes[$this->_parentRefColumn] = $parentModel->id;
		if ($this->_polymorphic) {
			$attributes[$this->_polymorphicType] = Inflector::underscore($this->_parentModelName);
		}
		$model = Bbx_Model::load($this->_childModelName)->create($attributes, $useId);
		unset($this->_collections[$parentModel->id]);
		return $model;
	}
	
	public function delete(Bbx_Model $parentModel, $id) {
		unset($this->_collections[$parentModel->id]);
		return Bbx_Model::load($this->_childModelName)->find($id)->delete();
	}
	
	public static function getExternalConditions($select,$parentModel,$childName,$attributes) {
		
		$parentModelName = get_class($parentModel);
		$parentTableName = $parentModel->getTableName(); // exhibitions
		
		$childName = array_key_exists('source',$attributes) ? $attributes['source'] : $childName;
		$childModelName = Inflector::classify($childName);
		$childTableName = Bbx_Model::load($childModelName)->getTableName(); // images
		
		if (!array_key_exists($childTableName,$select->getPart('from'))) {
			$select->from($childTableName,array()); // images
		}

		if (array_key_exists('as',$attributes)) {
			$refColumn = $attributes['as'].'_id';
			$polyType = $attributes['as'].'_type';
		}
		else {
			$refColumn = Inflector::singularize($parentTableName).'_id';
		}
		
		try {
			$parentModel->getRowData();
			$select->where("`".$childTableName."`.`".$refColumn."` = ".$parentModel->id); 
		}
		catch (Exception $e) {
			$select->where("`".$childTableName."`.`".$refColumn."` = `".$parentTableName."`.`id`"); 
		}
		
		if (isset($polyType)) {
			$select->where("`".$childTableName."`.`".$polyType."` = '".Inflector::underscore($parentModelName)."'");
		}
		return $select;
	}

}

?>