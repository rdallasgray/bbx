<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Media_Image_Processor_Imagick extends Bbx_Media_Image_Processor_Abstract {

	public function __construct() {
	}
	
	public function load($sourcePath) {
		parent::load($sourcePath);

		$this->_getResource();

		try {
			$this->_resource->readImage($sourcePath);
		}
		catch (Exception $e) {
			throw new Bbx_Media_Image_Processor_Exception('Failed to load image '.$sourcePath);
		}
	}
	
	protected function _getResource() {
		if (isset($this->_resource)) {
			$this->_resource->destroy();
		}
		$this->_resource = new Imagick();
	}
		
	protected function _canSetResolution() {
		return true;
	}

	protected function _setResolution($resolution) {
		$this->_resource->setImageResolution($resolution,$resolution);
	}
	
	protected function _getResolution() {
		$res = $this->_resource->getImageResolution();
		return $res['x'];
	}

	protected function _crop($width,$height,$xoffset = null,$yoffset = null) {
		$newWidth = $width == 0 ? $this->getWidth() : $width;
		$newHeight = $height == 0 ? $this->getHeight() : $height;
		$xOffset = $xoffset == 0 ? 0 : $xoffset;
		$yOffset = $yoffset == 0 ? 0 : $yoffset;
		$this->_resource->cropImage($newWidth,$newHeight,$xOffset,$yOffset);
	}

	protected function _resize($newWidth,$newHeight) {
    	$this->_resource->resizeImage($newWidth, $newHeight, Imagick::FILTER_LANCZOS, 0.95);
		$this->_resource->adaptiveSharpenImage(2,1);
	}
	
	protected function _sanitizeImage() {
		$this->_resource->stripImage();
	}
	
	protected function _save($writePath) {
		$this->_resource->setImageCompressionQuality(90);
		try {
			$this->_resource->writeImage($writePath);
		}
		catch (Exception $e) {
			throw new Bbx_Media_Image_Processor_Exception('Couldn\'t write image to '.$writePath);
		}
	}

	public function __destruct() {
		if (isset($this->_resource)) {
			$this->_resource->destroy();
		}
	}
}

?>