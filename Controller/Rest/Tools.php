<?php

class Bbx_Controller_Rest_Tools extends Bbx_Controller_Rest {

	public function init() {
		$this->_helper->contextSwitch()->addActionContext('regenerate-images','json');
		parent::init();
		$this->_authenticate();
	}
	
	public function regenerateImagesAction() {
		set_time_limit(7200);
		$size = $this->_getParam('size');
		$imgs = Bbx_Model::load('Image')->findAll();
		foreach ($imgs as $img) {
			$img->regenerateSizedMedia($size);
		}
	}

}

?>