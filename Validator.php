<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Boxes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/

 

class Bbx_Validator {
	
	protected $_fields = array();
	protected $_failureResponse = 'Failed';
	protected $_successResponse = 'Success';
	protected $_isBuilt = false;
	protected $_hasRun = false;
	protected $_messages = array();
	protected $_errors = array();

	public function __construct() {
	}
	
	public function setMessage($validation,$message) {
		$this->_messages[$validation] = $message;
	}
	
	public function getMessage($error,$field) {
		if (isset(Site_Config::$lang['multi'])) {
			$field = Bbx_View::get()->translate->_($field);
		}
		return str_replace('%field%',$field,$this->_messages[$error]);
	}
	
	public function getMessages($field) {
		if (!$this->_hasRun) {
			$this->_runValidation();
		}
		$messages = array();
		if (isset($this->_errors[$field])) {
			foreach ($this->_errors[$field] as $error) {
				if (!in_array($this->getMessage($error,$field),$messages)) {
					$messages[] = isset($this->_messages[$error]) ? $this->getMessage($error,$field) : $error;
				}
			}
		}
		return $messages;
	}
	
	public function getAllMessages() {
		$messages = array();
		foreach (array_keys($this->_fields) as $field) {
			$fieldMessages = $this->getMessages($field);
			if (!empty($fieldMessages)) {
				$messages[$field] = $fieldMessages;
			}
		}
		return $messages;
	}
	
	public function getValidFields($includeAll = true) {
		$fields = array();
		foreach ($this->_fields as $field=>$values) {
			if (!isset($this->_errors[$field])) {
				$fields[$field] = $values['value'];
			}
			else if ($includeAll) {
				$fields[$field] = '';
			}
		}
		return $fields;
	}

	public function addField($field,$value) {
		$this->_fields[$field] = array('value'=>$value,'validations'=>array());
	}

	public function addFields(array $fields) {
		foreach ($fields as $name=>$value) {
			$this->addField($name,$value);
		}
	}
	
	public function addValidation($validation,$fields,$params = null) {
		if (!is_array($fields)) {
			$fields = array($fields);
		}
		foreach ($fields as $field) {
			if (!isset($this->_fields[$field])) {
				throw new Zend_Exception('No such field: '.$field);
			}
			if (isset($params)) {
				if (!is_array($params)) {
					$params = array($params);
				}
			}
			else {
				$params = array();
			}
			$this->_fields[$field]['validations'][$validation] = $params;
		}
	}

	public function setSuccessResponse($response) {
		$this->_successResponse = $response;
	}

	public function setFailureResponse($response) {
		$this->_failureResponse = $response;
	}
	
	protected function _buildValidators() {
		if ($this->_isBuilt == true) {
			return;
		}
		foreach ($this->_fields as $field=>$values) {
			$this->_fields[$field]['validator'] = new Zend_Validate;
			if (empty($values['validations'])) {
				continue;
			}
			foreach ($values['validations'] as $type=>$params) {
				$class = 'Zend_Validate_'.$type;
				try {
					if (class_exists($class)) {
						try {
							if (class_exists('ReflectionClass')) {
								$ref = new ReflectionClass($class);
								$validation = $ref->newInstanceArgs($params);
							}
						}
						catch (Exception $e) {
							eval('$comp = new '.$class.'('.implode(',',array_values($params)).');');
							$validation = $comp;
						}
					}
				}
				catch (Exception $e) {
					echo $e->getMessage();
				}
				$this->_fields[$field]['validator']->addValidator($validation);
			}
			
		}
		$this->_isBuilt = true;
	}
	
	protected function _runValidation() {
		if (!$this->_isBuilt) {
			$this->_buildValidators();
		}
		foreach ($this->_fields as $field=>$values) {
			if (empty($values['validations'])) {
				continue;
			}
			$valid = $values['validator']->isValid($values['value']);
			if (!$valid) {
				$this->_errors[$field] = $values['validator']->getErrors();
			}
		}
		$this->_hasRun = true;
	}
	
	public function isValid() {
		if (!$this->_hasRun) {
			$this->_runValidation();
		}
		return empty($this->_errors);
	}
}

?> 