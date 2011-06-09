<?php

class Bbx_Model_Default_Media_Cdn_S3 extends Bbx_Model_Default_Media_Cdn_Abstract {
	
	private $_service;
	private $_bucket;
	private $_endpoint;
	
	protected function __construct() {
		$this->_endpoint = Bbx_Config::get()->site->cdn->endpoint;
		$accessKey = Bbx_Config::get()->site->cdn->accessKey;
		$secretKey = Bbx_Config::get()->site->cdn->secretKey;
		$this->_service = new Zend_Service_Amazon_S3($accessKey, $secretKey);
		$this->_service->registerStreamWrapper('s3');
		$this->_service->setEndpoint($this->_endpoint);
		$this->_bucket = Bbx_Config::get()->site->cdn->bucket;
	}
	
	protected function _streamPath() {
		return 's3://' . $this->_bucket;
	}
	
	protected function _filePath($path) {
		return $this->_streamPath() . $path;
	}
	
	public function url($path) {
		return $this->_endpoint . '/' . $this->_bucket . '/' . $path;
	}
	
	public static function init() {
		if (!Zend_Registry::isRegistered('cdn')) {
			Zend_Registry::set('cdn', new Bbx_Model_Default_Media_Cdn_S3());
		}
		return Zend_Registry::get('cdn');
	}
	
	public static function sync() {
		$root = APPLICATION_PATH . '/../www/media';
	}
	
}

?>