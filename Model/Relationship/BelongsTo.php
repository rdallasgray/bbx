<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Boxes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Model_Relationship_BelongsTo extends Bbx_Model_Relationship_Abstract {
	
	protected $_foreignKey;
	
	protected function _initialise() {
		$this->_foreignKey = isset($this->_polymorphicKey) ? $this->_polymorphicKey : Inflector::singularize($this->_childName).'_id';
	}

	protected function _findCollection(Bbx_Model $parentModel) {
		$polyType = isset($this->_polymorphicType) ? $parentModel->{$this->_polymorphicType} : null;
		$this->_collections[$parentModel->id] = $this->_model($polyType)->findAll($parentModel->{$this->_foreignKey});
	}
	
	public function create(Bbx_Model $parentModel, $attributes = array()) {
		if ($this->_polymorphic) {
			$attributes[$this->_polymorphicType] = Inflector::lowercase($this->_parentClassName);
			$attributes[$this->_polymorphicKey] = $parentModel->id;
		}
		$model = Bbx_Model::load(Inflector::classify($this->_childName))->create($attributes);
		unset($this->_collections[$parentModel->id]);
		return $model;
	}
	
	public function delete(Bbx_Model $parentModel, $id) {
		unset($this->_collections[$parentModel->id]);
		$model = Bbx_Model::load(Inflector::classify($this->_childName))->find($id)->delete();
	}

}

?>