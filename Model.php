<?php

/*

  Copyright (c) 2009 Robert Johnston

  This file is part of Backbox.

  Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
  Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

  You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Model implements IteratorAggregate {

  protected $_tableName;
  protected $_table;
  protected $_rowData;
  protected $_primary;
  protected $_to_string_pattern = ':id';
  protected $_select;
  protected $_alwaysLinked = array();
  protected $_neverLinked = array();
  protected $_validations = array();
  protected $_validationsInited = false;
  protected $_isInitialised = false;
  protected $_params = array();
  protected $_derivedParams = array();
  protected $_iterator;
  protected $_defaultParams = array();
  protected $_oldData = array();
  protected $_renderAsList = false;

  public function __construct() {
    $this->_tableName = isset($this->_tableName) ? $this->_tableName : Inflector::tableize(get_class($this));
    $this->_initSelf();
  }

  public function getDefaultParams() {
    return $this->_defaultParams;
  }

  public function getIterator() {
    if (!isset($this->_iterator)) {
      $this->_iterator = new Bbx_Model_Iterator($this);
    }
    return $this->_iterator;
  }
	
  public function isEmpty() {
    try {
      $this->_rowData();
      return false;
    }
    catch (Bbx_Model_Exception $e) {
      return true;
    }
  }

  public static function load($name, $forceInit = false) {
    $class = Inflector::classify($name);
    if (@class_exists($class)) {
      $model = new $class;
    }
    if (isset($model)) {
      if ($forceInit) {
	return $model->forceInit();
      }
      return $model;
    }
    throw new Bbx_Model_Exception('No model was found named '.$class);
  }
	
  public function forceInit() {
    $this->_init();
    return $this;
  }
	
  public function columns() {
    if (!Zend_Registry::isRegistered("tables:".$this->_tableName.":columns")) {
      Zend_Registry::set("tables:".$this->_tableName.":columns",$this->_table()->info('cols'));
    }
    return Zend_Registry::get("tables:".$this->_tableName.":columns");
  }
	
  protected function _hasColumn($col) {
    $cols = $this->columns();
    return in_array($col, $cols);
  }
	
  protected function _metadata() {
    if (!Zend_Registry::isRegistered("tables:".$this->_tableName.":metadata")) {
      Zend_Registry::set("tables:".$this->_tableName.":metadata",$this->_table()->info('metadata'));
    }
    return Zend_Registry::get("tables:".$this->_tableName.":metadata");
  }

	
  protected function _initSelf() {
  }

  protected function _init() {
    $this->_initRelationships();
    Bbx_Model_Registry::get('Relationships')->endCurrentRegistration();
    $this->_initAdaptors();
    Bbx_Model_Registry::get('Adaptors')->endCurrentRegistration();
    $this->_isInitialised = true;
  }

  protected function _initRelationships(){
  }

  protected function _initValidations(){
  }

  protected function _initAdaptors(){
  }
	
  protected function _registerAdaptor($ad) {
    return Bbx_Model_Registry::get('Adaptors')->register(get_class($this), $ad);
  }
	
  public function getAdaptor($ad) {
    if (!$this->_isInitialised) {
      $this->_init();
    }
    return Bbx_Model_Registry::get('Adaptors')->getAdaptor($this, $ad)->setModel($this);
  }

  public function getTable() {
    return $this->_table();
  }

  public function getTableName() {
    return $this->_tableName;
  }

  public function getPrimary() {
    return $this->_primary();
  }

  protected function _table() {
    if (!isset($this->_table)) {
      $this->_table = new Bbx_Db_Table(array('name'=>$this->_tableName));
    }
    return $this->_table;
  }

  protected function _primary() {
    if (!isset($this->_primary)) {
      $p = $this->_table()->info('primary');
      $this->_primary = implode('-',$p);
    }
    return $this->_primary;
  }

  protected function _rowData() {
    if (!isset($this->_rowData)) {
      throw new Bbx_Model_Exception('No rowData is set for '.get_class($this));
    }
    return $this->_rowData;
  }

  public function setRowData(Zend_Db_Table_Row $row) {
    $this->_rowData = $row;
    return $this;
  }

  public function getRowData() {
    return $this->_rowData();
  }

  protected function _getRelationship($childName) {
    if (!$this->_isInitialised) {
      $this->_init();
    }
    return Bbx_Model_Registry::get('Relationships')->getRelationship($this, $childName);
  }

  public function getRelationshipData($childName = null) {
    if (!$this->_isInitialised) {
      $this->_init();
    }
    return Bbx_Model_Registry::get('Relationships')->getRelationshipDataFor(get_class($this),$childName);
  }
	
  public function getBelongsToRelationships() {
    if (!$this->_isInitialised) {
      $this->_init();
    }
    $rd = Bbx_Model_Registry::get('Relationships')->getRelationshipDataFor(get_class($this));
		
    $b = array();
		
    foreach($rd as $key => $values) {
      if (array_key_exists('belongsTo',$values)) {
	$b[] = $key;
      }
    }
		
    return $b;
  }

  public function select() {
    if (!isset($this->_select)) {
      $this->_select = $this->_table()->select()->setIntegrityCheck(false);
    }
    return $this->_select;
  }

  public function findAll() {

    $args = func_get_args();
    $select = $this->select();

    if (!empty($this->_defaultParams)) {
      foreach($this->_defaultParams as $key=>$value) {
	$select->$key($value);
      }
    }
    if (empty($args)) {
      return new Bbx_Model_Collection($this,$this->_table()->fetchAll($select));
    }
    if (is_numeric($args[0])) {
      return new Bbx_Model_Collection($this,$this->_table()->find($args[0]));
    }
    if (is_array($args[0])) {
      if (count(array_intersect(array('where', 'order', 'limit'), array_keys($args[0]))) > 0) {
	foreach ($args[0] as $key => $value) {
	  if ($key == 'order') {
	    $select->reset(Zend_Db_Select::ORDER);
	  }
	  else if ($key == 'where' && is_array($value)) {
	    foreach($value as $condition) {
	      $select->where($condition);
	    }
	  }
	  else {
	    $select->$key($value);
	  }
	}
	return new Bbx_Model_Collection($this, $this->_table()->fetchAll($select));
      }
      return $this->findWithParams($args[0],$select);
    }
    if ($args[0] instanceof Zend_Db_Table_Select) {
      return new Bbx_Model_Collection($this, $this->_table()->fetchAll($args[0]));
    }
    throw new Bbx_Model_Exception('Bbx_Model::find() requires one numeric argument, one array of params or one Zend_Db_Table_Select');
  }
	
  public function find() {
    $args = func_get_args();
    $c = call_user_func_array(array($this,'findAll'),$args);
    return $c->current();
  }
	
  public function findWithParams(array $params, $select = null) {

    $parsedParams = $this->parseParams($params);
		
    if ($select === null) {
      $select = $this->select();
    }
		
    if (empty($parsedParams)) {
      return new Bbx_Model_Collection($this, $this->_table()->fetchAll($select));
    }

    foreach($parsedParams as $key => $val) {
      if (strpos($key,'`') === false) {
	$key = '`'.$key.'`';
      }
			
      if (strpos($key, ':value') === false) {
	$select->where($key.' = ?', $val);
      }
      else {
	$q = str_replace(':value', $val, $key);
	$select->where($q);
      }

    }

    return new Bbx_Model_Collection($this, $this->_table()->fetchAll($select));
  }

  public function findLinkable() {
    $args = func_get_args();
    $found = call_user_func_array(array($this, 'findAll'), $args);
    $linkable = array();
    foreach($found as $model) {
      if ($model->isLinkable()) {
	$linkable[] = $model;
      }
    }
    return $linkable;
  }
	
  public function parseParams(array $params) {
		
    $cols = $this->columns();
		
    $parsedParams = array();
		
    foreach($params as $key => $val) {
      if (in_array($key, $cols)) {
	if (strpos($key, '`') === false) {
	  $key = '`' . $key . '`';
	}
	if (strpos($key, '`' . $this->getTableName() . '`') === false) {
	  $key = '`' . $this->getTableName() . '`.' . $key;
	}
	$parsedParams[$key] = $val;
      }
      else {
	$whereCondition = $this->_conditionFromParam($key, $val);
	if ($whereCondition) {
	  $parsedParams[$whereCondition] = $val;
	}
      }
    }

    return $parsedParams;
  }
	
  protected function _conditionFromParam($key) {
    if (!array_key_exists($key,$this->_params)) {
      return false;
    }
    return $this->_params[$key];
  }
		
  public function validates($key) {
    return Bbx_Model_Registry::get('Validations')->register(get_class($this),$key);
  }

  protected function _validate($data,$validateAll = true) {
    $validations = $this->_getValidations();
    if (empty($validations)) {
      return true;
    }
    $cols = $validateAll ? $this->columns() : array_keys($data);
    $chains = array();
    $validationErrors = array();
    foreach ($cols as $key) {
      if (!array_key_exists($key,$validations)) {
	continue;
      }
      $chains[$key] = new Zend_Validate;
      $validators = $validations[$key];
      foreach ($validators as $name => $rules) {
	$className = 'Zend_Validate_'.$name;
	$class = new ReflectionClass($className);
	if (!empty($rules)) {
	  $val = $class->newInstanceArgs($rules);
	}
	else {
	  $val = $class->newInstance();
	}
	$chains[$key]->addValidator($val);
      }
      if (!$chains[$key]->isValid($data[$key])) {
	$validationErrors[$key] = array_values($chains[$key]->getMessages());
      }
    }
    if (!empty($validationErrors)) {
      $e = new Bbx_Model_Exception('Validation error');
      foreach ($validationErrors as $key => $value) {
	$e->errorVars[] = $key.': '.implode('; ',$value);
      }
      throw $e;
    }
  }
	
  protected function _getValidations() {
    if (!$this->_validationsInited) {
      $this->_initValidations();
    }
    $this->_validationsInited = true;
    try {
      return Bbx_Model_Registry::get('Validations')->getData(get_class($this));
    }
    catch (Exception $e) {
      return null;
    }
  }
	
  public function build($attributes = array()) {
    $this->_validate($attributes);
    $attributes = $this->_dbiseAll($attributes);
		
    $cols = $this->columns();
    $date = new Zend_Date(Zend_Date::ISO_8601);
    $now = $date->get(Zend_Date::ISO_8601);
		
    if (in_array('created_at',$cols)) {
      if (!array_key_exists('created_at', $attributes) 
	  || $attributes['created_at'] == '' 
	  || $attributes['created_at'] == '0000-00-00 00:00:00') 
	{
	  $attributes['created_at'] = $now;
	}
    }
    if (in_array('updated_at',$cols)) {
      $attributes['updated_at'] = $now;
    }
		
    $this->_rowData = $this->_table()->createRow($attributes);
    return $this;
  }

  public function create($attributes = array(), $useId = false) {
    $this->_beforeCreate();
    if (!$useId) {
      unset($attributes['id']);
    }
    $this->build($attributes);
    $this->save();
    $this->_afterCreate();
    return $this;
  }
	
  protected function _beforeCreate() {
  }
	
  protected function _afterCreate() {
  }
	
  public function save() {
    $this->_beforeSave();
    $this->_rowData()->save();
    Bbx_Log::debug('saved model: ('.get_class($this).') '.print_r($this->toArray(),true));
    $this->_afterSave();
    return $this;
  }
	
  protected function _beforeSave() {
  }
	
  protected function _afterSave() {
  }

  public function update($attributes) {
    $this->_validate($attributes,false);
    $attributes = $this->_dbiseAll($attributes);

    $cols = $this->columns();

    if (in_array('updated_at',$cols)) {
      $date = new Zend_Date();
      $attributes['updated_at'] = $date->get(Zend_Date::ISO_8601);
    }

    $this->_oldData = $this->_rowData()->toArray();
    $this->_rowData()->setFromArray($attributes);
		
    return $this->save();
  }
	
  public function delete() {
    Bbx_Log::debug('deleting model: ('.get_class($this).') '.print_r($this->toArray(),true));
    $this->_rowData()->delete();
  }

  public function deleteAll($attributes) {

  }
	
  protected function _viewise($key,$value) {
    $metadata = $this->_metadata();
		
    if ($metadata[$key]['DATA_TYPE'] === 'tinyint') {
      $value = !!$value;
    }
    if ($value === null) {
      $value = '';
    }
    return array($key,$value);
  }
	
  protected function _dbise($key,$value) {
    $metadata = $this->_metadata();
    $value = trim($value);
		
    if ($metadata[$key]['DATA_TYPE'] === 'tinyint') {
      $value = (int) $value;
    }
    if ($metadata[$key]['DATA_TYPE'] === 'date') {
      $value = Bbx_Date::fixDateFormat($value);
    }
    if ($metadata[$key]['DATA_TYPE'] === 'datetime') {
      $value = Bbx_Date::fixDateTimeFormat($value);
    }
    if ($value === '') {
      $value = null;
    }
    return array($key,$value);
  }
	
  protected function _viewiseAll($attributes) {
    $new_attrs = array();
    foreach($attributes as $key => $value) {
      $viewised = $this->_viewise($key,$value);
      $new_attrs[$viewised[0]] = $viewised[1];
    }
    return $new_attrs;
  }
	
  protected function _dbiseAll($attributes) {
    $new_attrs = array();
    foreach($attributes as $key => $value) {
      $dbised = $this->_dbise($key,$value);
      $new_attrs[$dbised[0]] = $dbised[1];
    }
    return $new_attrs;
  }

  public function __set($key,$value) {
    $dbised = $this->_dbise($key,$value);
    return $this->_rowData()->$dbised[0] = $dbised[1];
  }

  public function __get($key) {
    if ($this->isEmpty()) {
      $cols = $this->columns();
      if (array_key_exists($key, $cols)) {
	return null;
      }
    }
    $derivedParam = 'dp' . ucfirst($key);
    if (isset($this->_rowData()->$key)) {
      $value = $this->_rowData()->$key;
      $viewised = $this->_viewise($key,$value);
      return $viewised[1];
    }
    else if (method_exists($this, $derivedParam)) {
      return $this->$derivedParam();
    }
    else {
      try {
	return $this->_getRelationship(Inflector::underscore($key));
      }
      catch (Exception $e) {
	Bbx_Log::debug($e->getMessage());
	throw new Bbx_Model_Exception_NotFound("Trying to get value of uninitialised variable '$key': ".get_class($this));
      }
    }
  }

  public function __isset($key) {
    try {
      $rowData = $this->_rowData();
      return isset($rowData->$key);
    }
    catch (Exception $e) {
      return false;
    }
  }

  public function __unset($key) {
    $rowData = $this->_rowData();
    if (isset($rowData->$key)) {
      unset($rowData->$key);
    }
    else {
      Bbx_Model_Registry::get('Relationships')->destroyRelationshipDataFor($this, $key);
    }
  }

  public function __call($method,$arguments) {
    if (in_array($method,array('belongsTo','hasOne','hasMany'))) {
      return Bbx_Model_Registry::get('Relationships')->register(get_class($this),$arguments[0])->key($method);
    }
    throw new Bbx_Model_Exception('Unknown method: '.$method);
  }

  public function __toString() {
    if (isset($this->_rowData)) {
      return $this->_stringifier();
    }
  }

  protected function _stringifier() {
    return trim(preg_replace_callback(
				      "/:\w+/",
				      array($this,'_stringifierCallback'),
				      $this->_to_string_pattern
				      )," \t\n\r\0\x0b,");
  }

  protected function _stringifierCallback($matches) {
    $key = substr($matches[0],1);
    $schema = $this->schema();
    if (@$schema[$key]['type'] === 'date') {
      return Bbx_Date::date($this->$key);
    }
    return $this->$key;
  }
	
  public function toArray() {
    if ($this->isEmpty()) {
      return $this->newModel();
    }

    if ($this->_renderAsList) {
      return array('id' => $this->id, 'label' => $this->__toString());
    }
		
    $a = array();
		
    foreach($this->columns() as $c) {
      $a[$c] = $this->$c;
    }
		
    return $a;
  }

  public function url($absolute = false) {
    if ($this->_hasColumn('url')) {
      if (!empty($this->url)) {
	return 'http://' . $this->url;
      }
      return '';
    }
    else {
      $url = $this->_defaultRoute();
    }
    return $absolute ? 'http://'.$_SERVER['SERVER_NAME'] . $url : $url;
  }

  public function toHtml() {
    return new Bbx_View_Helper_HtmlElement(Inflector::underscore(get_class($this)));
  }

  protected function _defaultRoute() {
    $idPart = isset($this->id) ? '/'.$this->id : '';
    return '/'.Inflector::interscore(Inflector::pluralize(get_class($this))).$idPart;
  }

  public function activeRelationships() {
    return Bbx_Model_Registry::get('Relationships')->getActiveRelationshipsFor($this,$this->_rowData());
  }

  public function activeLinks() {
    $links = array_merge($this->_alwaysLinked,array_fill_keys($this->activeRelationships(),null));
    $active = array();
    foreach ($links as $name => $link) {
      if (!in_array($name,$this->_neverLinked)) {
	$link = $link ? $link : $name;
	$active[$name] = implode('/',array($this->url(),Inflector::interscore($link)));
      }
    }
    return $active;
  }
	
  public function schema() {

    $cols = $this->columns();
    $metadata = $this->_metadata();
    $schema = array();
    foreach ($cols as $col) {
		
      switch ($metadata[$col]['DATA_TYPE']) {
      case 'int':
      case 'smallint':
      case 'mediumint':
      case 'bigint':
	$type = 'integer';
	break;
			
      case 'char':
      case 'varchar':
	$type = 'string';
	break;
			
      case 'tinyint':
	$type = 'boolean';
	break;
			
      default:
	$type = $metadata[$col]['DATA_TYPE'];
			
      }
      $schema[$col] = array(
			    'type' => $type,
			    'length' => $metadata[$col]['LENGTH'],
			    'default' => $metadata[$col]['DEFAULT'],
			    'null' => $metadata[$col]['NULLABLE']
			    );
    }
    return $schema;
  }
	
  public function newModel() {
    $cols = $this->columns();
    $new = array();
    foreach ($cols as $col) {
      $new[$col] = null;
    }
    unset($new['id']);
    return $new;
  }
	
  public function renderAsList($option = true) {
    $this->_renderAsList = $option;
  }
	
  public function isListable() {
    return true;
  }
	
  public function isLinkable() {
    return true;
  }
	
  public function getLinkable($key) {
    $method = 'getLinkable' . Inflector::camelize($key);
    if (method_exists($this, $method)) {
      return $this->$method();
    }
    $items = array();
    foreach($this->$key as $i) {
      if ($i->isLinkable()) {
	$items[] = $i;
      }
    }
    return new Bbx_Model_Collection($this, $items);
  }

  public function has($key) {
    try {
      return count($this->$key) > 0;
    }
    catch (Exception $e) {
      Bbx_Log::write('Exception checking ' . get_class($this) . '->has(' . $key . '): ' . $e->getMessage());
      return false;
    }
  }
	
  public function hasLinkable($key) {
    $method = 'hasLinkable' . Inflector::camelize($key);
    if (method_exists($this, $method)) {
      return $this->$method();
    }
    try {
      return $this->$key->isLinkable();
    }
    catch (Exception $e) {
      Bbx_Log::write('Exception when checking isLinkable (' . $key . '): ' . $e->getMessage());
      return false;
    }
  }
	
  public function hasLinkableRows() {
    $c = $this->findAll();
    foreach ($c as $row) {
      if ($row->isLinkable()) {
	return true;
      }
    }
    return false;
  }
	
  public function toStringPattern() {
    return $this->_to_string_pattern;
  }

  public function etag($extra = null) {
    if ($this->isEmpty()) {
      return md5(get_class($this) . $extra);
    }
    if (isset($this->updated_at)) {
      return md5($this->updated_at . $extra);
    }

    $cols = $this->columns();
    $etagArray = array();

    foreach($cols as $col) {
      $etagArray[] = $this->$col;
    }

    return md5(implode('-', $etagArray) . $extra); 
  }
	
  public function __destruct() {
    try {
      Bbx_Model_Registry::get('Relationships')->destroyRelationshipDataFor($this);
      Bbx_Model_Registry::get('Validations')->destroyDataFor(get_class($this));
      Bbx_Model_Registry::get('Adaptors')->destroyDataFor(get_class($this));
    }
    catch (Exception $e) {}
    unset($this->_iterator);
    unset($this->_table);
    unset($this->_rowData);
    unset($this->_validations);
    unset($this->_oldData);
  }
}

?>