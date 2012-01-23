<?php

class Bbx_Model_Default_Media_Cdn_S3 extends Bbx_Model_Default_Media_Cdn_Abstract {
	
	private $_service;
	private $_bucket;
	private $_endpoint;
	const LOG = 'cdn_sync_log';
	
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
		$cdn = Zend_Registry::get('cdn');
		return $cdn;
	}

	private function _checkBuckets() {
	  $buckets = $this->_service->getBuckets();
	  Bbx_Log::write("Found buckets:", null, self::LOG);
	  foreach($buckets as $b) {
	    Bbx_Log::write($b, null, self::LOG);
	  }
	}
	
	public function sync($start) {
	  	  $this->_checkBuckets();
		$root = realpath(APPLICATION_PATH . '/..');
		$root_length = strlen($root);
		$remote_path = $this->_streamPath();
		Bbx_Log::write("Starting sync at " . $root . $start, null, self::LOG);
		Bbx_Log::write("Syncing to " . $remote_path, null, self::LOG);
		$stack = array();
		array_push($stack, $root . $start);
		while(!empty($stack)) {
			$node = array_pop($stack);
			if (is_dir($node)) {
				$rel_path = substr($node, $root_length);
				Bbx_Log::write("found dir " . $node, null, self::LOG);
				Bbx_Log::write("checking remote dir " . $remote_path . $rel_path, null, self::LOG);
				if (!is_resource(opendir($remote_path . $$rel_path))) {
					Bbx_Log::write($remote_path . $rel_path . ' does not exist, creating', null, self::LOG);
					if (!mkdir($remote_path . $rel_path)) {
						Bbx_Log::write('Unable to create directory, skipping', null, self::LOG);
						continue;
					}
				}
				Bbx_Log::write("dir OK", null, self::LOG);
				$dir_res = opendir($node);
				while(false !== ($dir_contents = readdir($dir_res))) {
					if (substr($dir_contents, 0, 1) == '.') {
						continue;
					}
					$path = $node . '/' . $dir_contents;
					Bbx_Log::write("adding " . $path . " to stack", null, self::LOG);
					array_push($stack, $path);
				}
			}
			else if (is_file($node)) {
				$rel_path = substr($node, $root_length);
				Bbx_Log::write("copying " . $node . " to " . $remote_path . $rel_path, null, self::LOG);
				if (!copy($node, $remote_path . $rel_path)) {
					Bbx_Log::write("unable to copy " . $node . " to " . $remote_path . $rel_path, null, self::LOG);
				}
				else {
					Bbx_Log::write("copy OK", null, self::LOG);
					if (!unlink($node)) {
						Bbx_Log::write("unable to delete " . $node, null, self::LOG);
					}
					else {
						Bbx_Log::write("deleted " . $node, null, self::LOG);
					}
				}
			}
		}
		$this->_syncing = false;
	}
	
}

?>