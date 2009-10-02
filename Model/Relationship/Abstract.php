<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Model_Relationship_Abstract {
	
	protected $_parentModel;
	protected $_childName;
	protected $_parentClassName;
	protected $_parentRefColumn;
	protected $_throughName;
	protected $_polymorphic;
	protected $_polymorphicKey;
	protected $_polymorphicType;
	protected $_collections = array();
	protected $_models = array();
	protected $_type;
	protected $_originalSelect;
	protected $_select = array();
	protected $_isInitialised = false;
	protected $_parentRow;
	protected $_parentName;
	
	public function __construct(Bbx_Model $parentModel,$childName,array $attributes) {
		$this->_parentModel = $parentModel;
		
		extract($attributes);
		
		$this->_parentName = $this->_parentModel->getTable()->info('name');
		$this->_parentClassName = get_class($this->_parentModel);
		$this->_type = Inflector::variablize(substr(get_class($this),22));

		if (isset($through)) {
			$this->_throughName = $through;
		}
		if (isset($select)) {
			$this->_originalSelect = $select;
			$this->_select = $select;
		}
		if (isset($polymorphic)) {
			$this->_polymorphic = true;
			$this->_polymorphicKey = Inflector::singularize($childName).'_id';
			$this->_polymorphicType = Inflector::singularize($childName).'_type';
			$this->_childName = $this->_parentModel->{$this->_polymorphicType};
		}
		else {
			// could do this out of registry
			$this->_childName = isset($source) ? $source : $childName;
		}
		
		if (isset($as)) {
			$this->_polymorphic = true;
			$this->_polymorphicKey = Inflector::singularize($this->_childName).'_id';
			$this->_polymorphicType = Inflector::singularize($as).'_type';
			
			$this->_parentRefColumn = $as.'_id';
			$type = isset($this->_throughName) ? Inflector::singularize($this->_throughName) : Inflector::singularize($this->_parentName);
			$typeSelect = array('where'=>array("`".Inflector::tableize($this->_childName)."`.`".$as."_type` = '".$type."'"));
			$this->_select = array_merge_recursive((array)$this->_select,$typeSelect);
		}
		else {
			$this->_parentRefColumn = Inflector::singularize($this->_parentName).'_id';
		}
		$this->_models[$this->_childName] = Bbx_Model::load($this->_childName);
	}
	
	protected function _model($childName = null) {
		$childName = $childName ? $childName : $this->_childName;
		if (!isset($this->_models[$childName])) {
			$this->_models[$childName] = Bbx_Model::load($childName);
		}
		return $this->_models[$childName];
	}
	
	protected function _initialise() {
	}

	protected function _initCollection(Bbx_Model $parentModel) {
		$this->_collections[$parentModel->id] = null;
		$this->_findCollection($parentModel);
	}
	
	protected function _findCollection(Bbx_Model $parentModel) {
	}
	
	protected function _select() {
		if (empty($this->_select)) {
			return null;
		}
		$select = $this->_model()->getTable()->select();
		foreach ($this->_select as $keyword=>$condition) {
			if (!is_array($condition)) {
				$select->$keyword($condition);
			}
			else {
				if (array_key_exists('args',$condition)) {
					call_user_func_array(array($select,$keyword),$condition['args']);
				}
				else {
					foreach ($condition as $c) {
						if (is_array($c) && array_key_exists('args',$c)) {
							call_user_func_array(array($select,$keyword),$c['args']);
						}
						else {
							$select->$keyword($c);
						}
					}
				}
			}
		}
		return $select;
	}
	
	protected function _selectConditions($conditions) {
		$this->_select = array_merge_recursive($this->_select,$conditions);
	}

	public function getCollection(Bbx_Model $parentModel, $forceReload = false, $forceCollection = false) {
		if (!$this->_isInitialised) {
			$this->_initialise();
		}
		if ((!array_key_exists($parentModel->id,$this->_collections)) || ($forceReload === true)) {
			$this->_initCollection($parentModel);
		}
		if ($this->_type == 'belongsto' || $this->_type == 'hasone') {
			if (($this->_collections[$parentModel->id]->current() instanceof Bbx_Model)) {
				if (!$forceCollection) {
					return $this->_collections[$parentModel->id]->current();
				}
			}
			else if ($this->_type == 'hasone') {
				Bbx_Log::write("creating new model");
				$current = $this->_collections[$parentModel->id]->create();
				if (!$forceCollection) {
					return $current;
				}
			}
		}
		return $this->_collections[$parentModel->id];
	}
	
	public function getFinder(Bbx_Model $parentModel) {
		return new Bbx_Model_Relationship_Finder($this,$parentModel);
	}
	
	public function setFindParams($params) {
		if ($params instanceof Zend_Db_Table_Select) {
			// can it be?
		}
		
		$conditions = array();
		
		if (is_array($params)) {
			$parsedParams = $this->_model()->parseParams($params);
			if (empty($parsedParams)) {
				return;
			}
			foreach($parsedParams as $key=>$val) {
				$conditions['where'][] = array('args'=>array($key.' = ?',$val));
			}
		}
		else {
			$conditions['where'] = $params;
		}

		$this->_selectConditions($conditions);
	}
	
	protected function _clear() {
		$this->_select = $this->_originalSelect;
		$this->_collections = array();
	}
	
	public function isActiveFor(Zend_Db_Table_Row $parentRow) {
		$this->_selectConditions(array('limit'=>'1'));
		$c = $this->getCollection($parentRow);
		$this->_clear();
		return ($c instanceof Bbx_Model || $c->count() > 0);
	}
	
	public function create(Bbx_Model $parentModel, $attributes) {	
	}
	
	public function delete(Bbx_Model $parentModel, $id) {
		// is_dependent?
	}

}

?>