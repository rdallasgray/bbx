<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/

class Bbx_ActionHelper_Model extends Zend_Controller_Action_Helper_Abstract {
	
	private $_privateParams = array('rel','rel_id','controller','id','parentModel','module','action','format','initialRequest');
		
	public function parseParams($allParams) {
		
		$userParams = array();
		
		foreach($allParams as $key=>$val) {
			if (!in_array($key,$this->_privateParams)) {
				$userParams[$key] = $val;
			}
		}
		
		return $userParams;
	}

	public function getModel() {
		$controller = $this->getActionController();
		$request = $this->getRequest();
		$controllerName = $request->getControllerName();
		
		if ($controllerName == 'error') {
			return;
		}

		if ($parentModel = $request->getParam('parentModel')) {
			$params = $this->parseParams($request->getUserParams());
			if (method_exists($parentModel->$controllerName, 'findAll') && !empty($params)) {
				$model = $parentModel->$controllerName->findAll($params);
			}
			else {
				$model = $parentModel->$controllerName;
			}
		}
		
		else {
			try {
				$model = Bbx_Model::load($controllerName);
			}
			catch (Exception $e) {
				throw new Bbx_Controller_Rest_Exception(null,404);
			}

			if ($id = $request->getParam('id')) {
				$model = $model->find((int)$id);
				if (!$model instanceof Bbx_Model) {
					throw new Bbx_Controller_Rest_Exception(null,404);
				}
			}
		}
		
		if ($request->getParam('list') === 'true') {
			$model->renderAsList();
		}

		return $model;
	}
	
	public function getCollection() {
		$params = $this->parseParams($this->getRequest()->getParams());
		$collection = $this->getModel();

		if (!$collection instanceof Bbx_Model_Collection) {
			$collection = $collection->findAll($params);
		}

		if (@$params['list'] === 'true') {
			$collection->renderAsList();
		}

		return $collection;
	}

}

?>