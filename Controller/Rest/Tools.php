<?php

class Bbx_Controller_Rest_Tools extends Bbx_Controller_Rest {

	public function init() {
		$this->contexts['regenerate-images'] = array('json');
		parent::init();
		$this->_authenticate();
		Bbx_Log::write($this->getContext());
	}
	
	public function regenerateImagesAction() {
		set_time_limit(7200);
		$size = $this->_getParam('size');
		$imgs = Bbx_Model::load('Image')->findAll();
		foreach ($imgs as $img) {
			Bbx_Log::write("trying to regenerate image id ".$img->id);
			Bbx_Log::write(memory_get_usage());
			$img->regenerateSizedMedia($size);
			Bbx_Log::write("image ".$img->id." done");
			Bbx_Log::write(memory_get_usage());
		}
	}

}

?>