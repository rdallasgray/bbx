<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



abstract class Bbx_Model_Registry_Abstract {

	protected $_data = array();
	protected $_tempEntry = array();
	
	public function __construct() {
	}

	public function register($modelName,$key) {
		$this->_createTempEntryFor($modelName,$key);
		return $this;
	}
	
	public function endCurrentRegistration() {
		if (empty($this->_tempEntry)) {
			return;
		}
		$currentEntry = $this->_tempEntry;
		if (!$this->_isRegistered($currentEntry['modelName'],$currentEntry['key'])) {
			$this->_registerCurrentEntry();
		}
		$this->_tempEntry = array();
	}
		
	public function key($keyName) {
		if (!$this->_keyExists($keyName)) {
			$this->_tempEntry['data'][$keyName] = array();
			$this->_currentKey = &$this->_tempEntry['data'][$keyName];
		}
		return $this;
	}
	
	protected function _keyExists($keyName) {
		return array_key_exists($keyName,$this->_tempEntry['data']);
	}
	
	protected function _createTempEntryFor($modelName,$key) {
		$this->endCurrentRegistration();
		$this->_tempEntry = array(
			'modelName' => $modelName,
			'key' => $key,
			'data' => array()
		);
		$this->_currentKey = &$this->_tempEntry['data'];
	}
	
	protected function _createStubs() {
		if (!array_key_exists($this->_tempEntry['modelName'],$this->_data)) {
			$this->_data[$this->_tempEntry['modelName']] = array();
		}
		if (!array_key_exists($this->_tempEntry['key'],$this->_data[$this->_tempEntry['modelName']])) {
			$this->_data[$this->_tempEntry['modelName']][$this->_tempEntry['key']] = array();
		}
	}
	
	protected function _registerCurrentEntry() {
		$this->_createStubs();
		$this->_data[$this->_tempEntry['modelName']][$this->_tempEntry['key']] = $this->_tempEntry['data'];
	}
	
	protected function _isRegistered($modelName,$key = null) {
		$modelRegistered = array_key_exists($modelName,$this->_data);
		if (!$key) {
			return $modelRegistered;
		}
		return $modelRegistered && array_key_exists($key,$this->_data[$modelName]);
	}
		
	public function getData($modelName) {
		$this->endCurrentRegistration();
		if (!$this->_isRegistered($modelName)) {
			throw new Zend_Exception('No data registered for '.$modelName);
		}
		return $this->_data[$modelName];
	}

	public function __call($method,$arguments) {
		if (empty($this->_tempEntry)) {
			throw new Zend_Exception('No current entry is set: '.$method.' '.implode(',',$arguments));
		}
		if (count($arguments) == 1 && is_numeric(reset(array_keys($arguments)))) {
			$arguments = $arguments[0];
		}
		$this->_currentKey[$method] = $arguments;
		return $this;
	}

}

?>