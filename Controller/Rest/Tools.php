<?php

class Bbx_Controller_Rest_Tools extends Bbx_Controller_Rest {

	public function init() {
		$this->_helper->contextSwitch()->addActionContext('regenerate-images','json');
		$this->_helper->contextSwitch()->addActionContext('spider','json');
		parent::init();
		$this->_helper->authenticate();
	}
	
	public function regenerateImagesAction() {
		set_time_limit(7200);
		$size = $this->_getParam('size');
		$overwrite = $this->_getParam('overwrite');
		$imgs = Bbx_Model::load('Image')->findAll();
		foreach ($imgs as $img) {
			Bbx_Log::debug("regenerating sized media for image " . $img->id);
			$img->regenerateSizedMedia($size, $overwrite);
		}
		// TODO send a JSON response
		// Also we should probably do each resize as a separate request so as to avoid the segfault problem
		$this->getResponse()->sendResponse();
		exit();
	}
	
	public function spiderAction() {
		$reset = ($this->_getParam('reset') === 'true');
		set_time_limit(7200);
		$spider = new Bbx_Search_Spider;
		$spider->start('/', $reset);
		$this->getResponse()->sendResponse();
		exit();
	}

}

?>