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
		return $this->_endpoint . '/' . $this->_bucket . '/www' . $path;
	}
	
	public static function init() {
		if (!Zend_Registry::isRegistered('cdn')) {
			Zend_Registry::set('cdn', new Bbx_Model_Default_Media_Cdn_S3());
		}
		return Zend_Registry::get('cdn');
	}
	
	public function sync($start) {
		$root = realpath(APPLICATION_PATH . '/..');
		$root_length = strlen($root);
		$remote_path = $this->_streamPath();
		Bbx_Log::write("Starting sync at " . $root . $start, null, 'cdn_sync_log');
		Bbx_Log::write("Syncing to " . $remote_path, null, 'cdn_sync_log');
		$stack = array();
		array_push($stack, $root . $start);
		while(!empty($stack)) {
			$node = array_pop($stack);
			if (is_dir($node)) {
				$rel_path = substr($node, $root_length);
				Bbx_Log::write("found dir " . $node, null, 'cdn_sync_log');
				Bbx_Log::write("checking remote dir " . $remote_path . $rel_path, null, 'cdn_sync_log');
				if (!is_resource(opendir($remote_path . $$rel_path))) {
					Bbx_Log::write($remote_path . $rel_path . ' does not exist, creating', null, 'cdn_sync_log');
//					mkdir($remote_path . $rel_path);
				}
				$dir_res = opendir($node);
				while(false !== ($dir_contents = readdir($dir_res))) {
					if (substr($dir_contents, 0, 1) == '.') {
						continue;
					}
					$path = $node . '/' . $dir_contents;
					Bbx_Log::write("adding " . $path . " to stack", null, 'cdn_sync_log');
					array_push($stack, $path);
				}
			}
			else if (is_file($node)) {
				$rel_path = substr($node, $root_length);
				Bbx_Log::write("copying " . $node . " to " . $remote_path . $rel_path, null, 'cdn_sync_log');
//				if (copy($node, $remote_path . $rel_path)) {
//					Bbx_Log::write("deleting " . $node . " from local fs");
//					unlink($node);
//				}
			}
		}
	}
	
}

?>