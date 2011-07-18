<?php

class Bbx_Controller_Rest_Crud_Downloadable extends Bbx_Controller_Rest_Crud {
	
	public function downloadAction() {
		$auth = $this->_helper->Authenticate;
		if ($auth->asPrivilege('staff')) {
			if ($this->getRequest()->isHead()) {
				$this->_helper->Download->sendHeadResponse();
			}
			$model = $this->_helper->Model->getModel('id');
			$this->_helper->Download->media($model);
		}
	}


}

?>