<?php

class Bbx_View_Helper_BasicNavigation extends Zend_View_Helper_Abstract {
	
	protected $_navigation;
	
	public function setView(Zend_View_Interface $view) {
		$this->view = $view;
	}
	
	public function setNavigation($id, $model) {
		$this->_navigation = Zend_Registry::get('Zend_Navigation')->findOneById($id);
		$this->findAssociations($model);
		return $this->view->navigation()->menu($this->_navigation)->setRenderInvisible(true);
	}
	
	public function findAssociations($model) {
		$pages = array();		
		$this->_navigation->addPages($pages);
	}
	
}

?>