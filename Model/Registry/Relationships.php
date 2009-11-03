<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Model_Registry_Relationships extends Bbx_Model_Registry_Abstract {

	public function getRelationship(Bbx_Model $parentModel,$childName) {
		$parentModelName = get_class($parentModel);
		$this->endCurrentRegistration();

		if (!$this->_isRegistered($parentModelName,$childName)) {
			throw new Zend_Exception('No relationship registered for '.$parentModelName.'-'.$childName);
		}
		if (!$this->_isInstantiated($parentModelName,$childName)) {
			$this->_instantiate($parentModel,$childName);
		}

		return $this->_data[$parentModelName][$childName]['model']->getFinder($parentModel);
	}
	
	public function getActiveRelationshipsFor(Bbx_Model $parentModel,Zend_Db_Table_Row $parentRow) {
		$parentModelName = get_class($parentModel);
		$self = $this;
		if (!isset($self->_data[$parentModelName])) {
			throw new Zend_Exception($parentModelName." is not registered");
		}
		$active = array();
		foreach (array_keys($self->_data[$parentModelName]) as $childName) {
			if (!$self->_isInstantiated($parentModelName,$childName)) {
				$self->_instantiate($parentModel,$childName);
			}
			if ($self->_data[$parentModelName][$childName]['model']->isActiveFor($parentModel)) {
				$active[] = $childName;
			}
		}
		return $active;
	}
	
	public function getRelationshipDataFor($parentModelName,$childName = null) {
		$self = $this;

		if (isset($self->_data[$parentModelName])) {
			if ($childName == null) {
				return $self->_data[$parentModelName];
			}
		}
		if (isset($self->_data[$parentModelName][$childName])) {
			return $self->_data[$parentModelName][$childName];
		}
		if (isset($self->_data[$parentModelName][Inflector::singularize($childName)])) {
			return $self->_data[$parentModelName][Inflector::singularize($childName)];
		}
		throw new Zend_Exception('No relationship registered for '.$parentModelName.'-'.$childName);
	}
	
	protected function _instantiate(Bbx_Model $parentModel,$childName) {
		$parentModelName = get_class($parentModel);
		$type = reset(array_keys($this->_data[$parentModelName][$childName]));
		$class = 'Bbx_Model_Relationship_'.ucwords($type);
		if (array_key_exists('through',$this->_data[$parentModelName][$childName][$type])) {
			$class .= 'Through';
		}
		$rel = new $class($parentModel,$childName,$this->_data[$parentModelName][$childName][$type]);
		$this->_data[$parentModelName][$childName]['model'] = $rel;
	}
	
	protected function _isInstantiated($parentModelName,$childName) {
		return array_key_exists('model',$this->_data[$parentModelName][$childName]);
	}

}

?>