<?php

class Bbx_Controller_Rest_Tools extends Bbx_Controller_Rest {

	public function init() {
		$this->_helper->contextSwitch()->addActionContext('regenerate-images','json');
		$this->_helper->contextSwitch()->addActionContext('spider','json');
		$this->_helper->contextSwitch()->addActionContext('update-all','json');
		parent::init();
		$this->_helper->authenticate();
	}
	
	public function regenerateImagesAction() {
		set_time_limit(86400);
		$size = $this->_getParam('size');
		$overwrite = $this->_getParam('overwrite');
		$cdnType = Bbx_Config::get()->site->cdn->type;
		$imgs = Bbx_Model::load('Image')->findAll();
		foreach ($imgs as $img) {
			Bbx_Log::debug("regenerating sized media for image " . $img->id);
			$img->regenerateSizedMedia($size, $overwrite);
			if (APPLICATION_ENV == 'production' && $cdnType != '') {
				Bbx_Log::write('Doing CDN sync');
				$pid = exec('nice php ' . APPLICATION_PATH . '/../library/Bbx/bin/cdn-sync.php /www/media ' . $cdnType .  
					' > /dev/null 2>&1 &');
			}
		}
		// TODO send a JSON response
		$this->getResponse()->sendResponse();
		exit();
	}
	
	public function updateAllAction() {
		$model = $this->_getParam('model');
		try {
			$models = Bbx_Model::load($model)->findAll();
			foreach ($models as $m) {
				$m->update(array());
			}
		}
		catch (Exception $e) {
			Bbx_Log::debug($e->getMessage());
		}
		$this->getResponse()->sendResponse();
		exit();
	}
	
	public function spiderAction() {
		$reset = ($this->_getParam('reset') === 'true');
		$spider = new Bbx_Search_Spider;
		$spider->start('/', $_SERVER['HTTP_HOST'], $reset);
		$this->getResponse()->sendResponse();
		exit();
	}

}

?>