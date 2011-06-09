<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Model_Default_Media extends Bbx_Model {
	
	protected $_mimeType;
	protected $_mediaPath;
	protected $_extension;
	protected $_cdnType;
	protected $_cdn;

	public function __construct() {
		parent::__construct();
		if (isset($this->_cdnType)) {
			$this->_initCdn();
		}
	}
	
	protected function _initCdn() {
		switch($this->_cdnType) {
			case 's3':
			$this->_cdn = Bbx_Model_Default_Media_Cdn_S3::init();
			break;
		}
	}
	
	public function getMimeType() {
		return $this->_mimeType;
	}
	
	public function getMediaPath($forceLocal = false) {
		$path = $this->_mediaPath . $this->id . '.' . $this->_extension;
		return $this->_mediaPath($path, $forceLocal);
	}
	
	protected function _mediaPath($path, $forceLocal = false) {
		if (!$forceLocal && isset($this->_cdn) && !is_readable(APPLICATION_PATH . '/..' . $path)) {
			return $this->_cdn->filePath($path);
		}
		return APPLICATION_PATH . '/..' . $path;
	}
		
	public function getMediaUrl($absolute = false) {
		$path = $this->_mediaUrl . $this->id . '.' . $this->_extension;
		$absolutePath = $absolute ? 'http://'.$_SERVER['SERVER_NAME'] : '';
		return $absolutePath . $$path;
	}
	
	public function getExtension() {
		return $this->_extension;
	}
	
	public function attachMedia($filePath, $overwrite = true) {
		Bbx_Log::debug("trying to attach media to " . get_class($this) . ' ' . $this->id);
		
		if (!copy($filePath, $this->getMediaPath(true))) {
			throw new Bbx_Model_Exception('Unable to save media ' . $filePath . ' to ' . $this->getMediaPath(true));
		}
	}
	
	public function deleteMedia() {
		try {
			unlink($this->getMediaPath());
		}
		catch (Exception $e) {
			Bbx_Log::debug('Unable to delete media '.$this->_getMediaPath());
		}
	}

	public function delete() {
		try {
			$this->deleteMedia();
		}
		catch (Exception $e) {
			Bbx_Log::debug('Unable to delete media '.get_class($this).' id '.$this->id.' - '.$e->getMessage());
		}
		parent::delete();
	}
	
}

?>