<?php

class Bbx_Controller_Rest_Crud_Users extends Bbx_Controller_Rest_Crud {
	
	protected function _closeSession() {
		$this->_user = $this->_helper->Authenticate->getUser();
		$session = $this->_user->current_admin_session;
		try {
			$session->close();
		}
		catch (Exception $e) {
			Bbx_Log::debug("Unable to close session: ".$e->getMessage());
		}
	}
	
	protected function _get() {
		$this->_helper->authenticate();
		parent::_get();
	}
	
	protected function _put() {
		$data = $this->_getBodyData();
		if ($data['password'] === '') {
			if (!($this->_userIsSelf($data['id']) && $this->_helper->Authenticate->getUser()->username != $data['username'])) {
				unset($data['password']);
			}
		}
		if (!$this->_userIsAdmin()) {
			parent::_put($this->_sanitizePut());
		}
		else {
			parent::_put($data);
		}
		if ($this->_userIsSelf($data['id'])) {
			$this->_closeSession();
		}
	}
	
	protected function _post() {
		$this->_userIsAdmin();
		parent::_post();
	}
	
	protected function _delete() {
		$this->_userIsAdmin();
		if ($this->_userIsSelf($this->_getParam('id'))) {
			$this->_closeSession();
		}
		parent::_delete();
	}
	
	protected function _sanitizePut() {
		$data = $this->_getBodyData();
		if ($data['password'] === '') {
			unset($data['password']);
		}
		unset($data['is_admin']);
		return $data;
	}
	
	protected function _getIndexData() {
		$data = $this->_helper->Model->getCollection();
		foreach ($data as $model) {
			$model->password = '';
		}
		return $data;
	}
	
	protected function _getShowData() {
		$model = $this->_helper->Model->getModel();
		$model->password = '';
		return $model;
	}
	
	protected function _userIsSelf($id) {
		return $this->_helper->Authenticate->getUser()->id == $id;
	}
	
	protected function _userIsAdmin() {
		$userRequesting = $this->_helper->Authenticate->getUser();
		if ($userRequesting->is_admin) {
			return true; //admins can do anything
		}
		// user isn't an admin
		$request = $this->getRequest();
		if ($request->isPost() || $request->isDelete()) {
			//non-admins can't post or delete
			throw new Bbx_Controller_Rest_Exception(null,401); 
		}
		if ($request->isPut()) {
			$data = $this->_getBodyData();
			if ($userRequesting->id != $data['id']) {
				//non-admins can only update themselves
				throw new Bbx_Controller_Rest_Exception(null,401); 
			}
		}
		return false;
	}

}

?>