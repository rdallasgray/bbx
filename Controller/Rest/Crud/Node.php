<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Boxes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Controller_Rest_Crud_Node extends Bbx_Controller_Rest_Crud {

	protected $_nodalColumn = "";
	
	public function init() {
		parent::init();
		$this->model = $this->_helper->Model->getModel();
		if (isset($this->collection) && $this->collection instanceof Bbx_Model) {
			$this->model = $this->collection;
		}
		else {
			$this->model = $this->_createModel();
		}
	}

	protected function _createModel() {
	}

	public function indexAction() {
		$this->showAction();
	}

	public function showAction() {
		$this->view->text = $this->model->text;
		$this->_setEtag($this->model->etag());
	}

	protected function _post() {
		$e = new Bbx_Controller_Rest_Exception(null,405,array('allowed_methods'=>'PUT'));
		throw $e;
	}

	protected function _put() {
		$this->model->$_nodalColumn = $this->_getBodyData()->$_nodalColumn;
		$this->model->save();
	}

	protected function _delete() {
		$e = new Bbx_Controller_Rest_Exception(null,405,array('allowed_methods'=>'PUT'));
		throw $e;
	}
	
	protected function _options() {
//TODO set header for allowed methods etc.
	}

}

?>