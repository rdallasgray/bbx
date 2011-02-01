<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Model_Relationship_Abstract {
	
	protected $_childName;
	protected $_childModelName;
	protected $_childTableName;
	protected $_parentModelName;
	protected $_parentRefColumn;
	protected $_throughModelName;
	protected $_throughName;
	protected $_throughModel;
	protected $_throughTableName;
	protected $_polymorphic;
	protected $_polymorphicKey;
	protected $_polymorphicType;
	protected $_collections = array();
	protected $_models = array();
	protected $_type;
	protected $_originalSelect = array();
	protected $_select = array();
	protected $_isInitialised = false;
	protected $_parentRow;
	protected $_parentTableName;
	protected $_finders = array();
	
	public function __construct(Bbx_Model $parentModel,$childName,array $attributes) {
		$this->_childName = $childName;
		
		extract($attributes);
		
		$this->_parentTableName = $parentModel->getTable()->info('name');
		$this->_parentModelName = get_class($parentModel);
		$this->_type = Inflector::variablize(substr(get_class($this),22));

		if (isset($select)) {
			$this->_originalSelect = $select;
			$this->_select = $select;
		}
		if (isset($polymorphic)) {
			// belongsTo only
			$this->_polymorphic = true;
			$this->_polymorphicKey = Inflector::singularize($childName).'_id';
			$this->_polymorphicType = Inflector::singularize($childName).'_type';
			$this->_childModelName = Inflector::classify($parentModel->{$this->_polymorphicType});
		}
		else {
			// could do this out of registry
			$this->_childName = isset($source) ? $source : $childName;
			$this->_childModelName = Inflector::classify($this->_childName);
		}
		$this->_childTableName = Bbx_Model::load($this->_childModelName)->getTableName();

		if (isset($through)) {
			$this->_throughName = $through;
			$this->_throughModelName = Inflector::classify($through);
			$this->_throughModel = Bbx_Model::load($this->_throughModelName,true);
			$this->_throughTableName = $this->_throughModel->getTableName();
		}
		if (isset($as)) {
			// hasMany(Through) only
			$this->_polymorphic = true;
			$this->_polymorphicKey = $as.'_id';
			$this->_polymorphicType = $as.'_type';
			$polymorphicTable = isset($this->_throughTableName) ? $this->_throughTableName : $this->_childTableName;
			
			$this->_parentRefColumn = $as.'_id';
			$type = Inflector::singularize(Inflector::underscore($this->_parentTableName));
			$typeSelect = array('where'=>array("`".$polymorphicTable."`.`".$as."_type` = '".$type."'"));
			$this->_originalSelect = array_merge_recursive((array)$this->_originalSelect,$typeSelect);
		}
		else {
			$this->_parentRefColumn = Inflector::singularize($this->_parentTableName).'_id';
		}
		$this->_models[$this->_childModelName] = Bbx_Model::load($this->_childModelName);
		$this->_originalSelect = $this->_mergeSelects(
			$this->_convertForSelect($this->_models[$this->_childModelName]->getDefaultParams()),
			$this->_originalSelect
		);
		$this->_select = $this->_originalSelect;
	}
	
	protected function _model($childModelName = null) {
		$childModelName = $childModelName ? $childModelName : $this->_childModelName;
		$this->_childModelName = $childModelName;
		if (!isset($this->_models[$childModelName])) {
			$this->_models[$childModelName] = Bbx_Model::load($childModelName);
		}
		return $this->_models[$childModelName];
	}
	
	protected function _initialise() {
	}

	protected function _initCollection(Bbx_Model $parentModel) {
		$this->_clearCollection($parentModel->id);
		$this->_findCollection($parentModel);
	}
	
	protected function _findCollection(Bbx_Model $parentModel) {
		$this->_collections[$parentModel->id] = new Bbx_Model_Collection(
			$parentModel,
			$this->_findRowset($parentModel),
			$this,
			$this->_childModelName
		);
	}
	
	protected function _findRowset(Bbx_Model $parentModel) {
	}
	
	protected function _mergeSelects() {
		$selects = func_get_args();
		$merged = array();
		foreach ($selects as $s) {
			foreach ($s as $keyword => $value) {
				if ($keyword === 'order') {
					$merged['order'] = $value;
				}
				else {
					if (!array_key_exists($keyword,$merged)) {
						$merged[$keyword] = array();
					}
					if (!is_array($value)) {
						$merged[$keyword][] = $value;
					}
					else {
						$merged[$keyword] = array_merge_recursive($merged[$keyword],$value);
					}
				}
			}
		}
		return $merged;
	}
	
	protected function _convertForSelect($params) {
		$select = array();
		
		foreach($params as $key=>$value) {
			$select[$key] = $value;
		}
		return $select;
	}
	
	public function select($conditions) {
		$this->_select = array_merge_recursive($this->_select,$this->_convertForSelect($conditions));
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
				foreach ($condition as $c) {
					$select->$keyword($c);
				}
			}
		}
		return $select;
	}
	
	protected function _selectConditions($conditions) {
		$this->_select = $this->_originalSelect;
		$this->_select = array_merge_recursive($this->_select,$conditions);
	}

	public function getCollection(Bbx_Model $parentModel, $forceReload = false, $forceCollection = false) {
		if ($forceReload === true) {
			$this->_clearCollection($parentModel->id);
		}
		if (!array_key_exists($parentModel->id,$this->_collections)) {
			$this->_initCollection($parentModel);
		}
		
		$this->_clearSelect();
		
		if ($this->_type == 'belongsto' || $this->_type == 'hasone' || $this->_type == 'hasonethrough') {
			if ($this->_collections[$parentModel->id]->current() instanceof Bbx_Model) {
				if (!$forceCollection) {
					return $this->_collections[$parentModel->id]->current();
				}
			}
		}
		
		return $this->_collections[$parentModel->id];
	}
	
	public function destroyCollectionsFor($id) {
		unset($this->_collections[$id]);
		unset($this->_finders[$id]);
	}
	
	public function getFinder(Bbx_Model $parentModel) {
		if (!array_key_exists($parentModel->id, $this->_finders)) {
			$this->_finders[$parentModel->id] = new Bbx_Model_Relationship_Finder($this, $parentModel);
		}

		return $this->_finders[$parentModel->id];
	}
	
	public function setFindParams($params) {
		
		$conditions = array();
		
		if (is_array($params)) {
			$parsedParams = $this->_model()->parseParams($params);
			if (empty($parsedParams)) {
				return;
			}
			foreach($parsedParams as $key=>$val) {
				if (strpos($key,'`') === false) {
					$key = '`'.$key.'`';
				}
				if (strpos($key, ':value') === false) {
					$conditions['where'][] = Zend_Registry::get('db')->quoteInto($key.' = ?',$val);
				}
				else {
					$conditions['where'][] = str_replace(':value', $val, $key);
				}
			}
		}
		else {
			$conditions['where'] = $params;
		}
		$this->_selectConditions($conditions);
	}
	
	protected function _clearCollection($id) {
		unset($this->_collections[$id]);
	}
	
	protected function _clearSelect() {
		$this->_select = $this->_originalSelect;
	}
	
	public function isActiveFor(Zend_Db_Table_Row $parentRow) {
		$this->_selectConditions(array('limit'=>'1'));
		$c = $this->getCollection($parentRow);
		$this->_clearCollection($parentRow->id);
		$this->_clearSelect();
		return ($c instanceof Bbx_Model || $c->count() > 0);
	}
	
	public function create(Bbx_Model $parentModel, $attributes) {	
	}
	
	public function delete(Bbx_Model $parentModel, $id) {
		// is_dependent?
	}
	
	public function __destruct() {
		unset($this->_throughModel);
		unset($this->_models);
		unset($this->_finders);
		unset($this->_originalSelect);
		unset($this->_rowData);
		unset($this->_select);
		unset($this->_parentRow);
	}

}

?>