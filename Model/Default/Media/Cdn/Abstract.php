<?php

abstract class Bbx_Model_Default_Media_Cdn_Abstract {	
	
	protected function __construct() {
	}
		
	public static function init() {
	}
	
	public function filePath($path) {
		return $this->_filePath($path);
	}

}

?>