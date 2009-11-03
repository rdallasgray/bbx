<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Media_Image extends Bbx_Media_Abstract {
	
	protected $_processor;
	
	protected function __construct($sourcePath) {
		parent::__construct($sourcePath);
		$this->_processor()->load($sourcePath);
	}
	
	public static function load($sourcePath) {
		return new Bbx_Media_Image($sourcePath);
	}
	
	public function save($savePath) {
		$this->_processor()->save($savePath);
		return $this;
	}
	
	protected function _processor() {
		if (isset($this->_processor)) {
			return $this->_processor;
		}
		if (Zend_Registry::isRegistered('ImageProcessor')) {
			$procName = Zend_Registry::get('ImageProcessor');
			$this->_processor = new $procName();
			return $this->_processor;
		}

		if (@class_exists('Imagick')) {
			$this->_processor = new Bbx_Media_Image_Processor_Imagick;
			Zend_Registry::set('ImageProcessor','Bbx_Media_Image_Processor_Imagick');
		}
		else if (@function_exists('NewMagickWand')) {
			$this->_processor = new Bbx_Media_Image_Processor_Magickwand;
			Zend_Registry::set('ImageProcessor','Bbx_Media_Image_Processor_Magickwand');
		}
		else {
			if (!is_array(gd_info())) {
				throw new Bbx_Media_Image_Processor_Exception('No image processor found');
			}
			$this->_processor = new Bbx_Media_Image_Processor_Gd;
			Zend_Registry::set('ImageProcessor','Bbx_Media_Image_Processor_Gd');
		}
		return $this->_processor;
	}
	
	public function resize($width,$height,$upsize = false) {
		$this->_processor()->resize($width,$height,$upsize);
		return $this;
	}
	
	public function setResolution($r) {
		$this->_processor()->setResolution($r);
		return $this;
	}
	
	public function width() {
		return $this->_processor()->getWidth();
	}
	
	public function height() {
		return $this->_processor()->getHeight();
	}
	
	public function download() {
		
	}
	
}

?>