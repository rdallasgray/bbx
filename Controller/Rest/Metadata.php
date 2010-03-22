<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Controller_Rest_Metadata extends Bbx_Controller_Rest {
	
	public function init() {
		$this->_helper->contextSwitch()->addActionContext('error','json');
		$this->_helper->contextSwitch()->addActionContext('null','json');
		$this->_initContext();
	}
	
	public function preDispatch() {
		
		if ($this->_getParam('final')) {
			return;
		}
		
		$modelName = $this->getRequest()->getActionName();

		if ($modelName === 'null') {
			return;
		}
		try {
			$model = Bbx_Model::load($modelName);
		}
		catch (Exception $e) {
			$this->_forward('error',null,null,array('format' => 'json','modelName' => $modelName,'final' => true));
			return;
		}
		
		$this->_sendMetadata($model);
	}
	
	protected function _sendMetadata($model) {
		$url = $model->url(true);
		$name = Inflector::interscore(Inflector::pluralize(get_class($model)));
		$metadata = array('url' => $url,'name' => $name);
		$this->view->assign($metadata);
		$this->_forward('null',null,null,array('format' => 'json','final' => true));
	}
	
	public function errorAction() {
		$modelName = $this->_getParam('modelName');
		throw new Bbx_Controller_Rest_Exception('Unable to load model '.$modelName,500);
	}
	
	public function nullAction() {
	}

}

?>