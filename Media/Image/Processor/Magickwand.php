<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Media_Image_Processor_Magickwand extends Bbx_Media_Image_Processor_Abstract {

	public function __construct() {
	}
	
	public function load($sourcePath) {
		parent::load($sourcePath);

		$this->_getResource();

		try {
			MagickReadImage($this->_resource,$sourcePath);
		}
		catch (Exception $e) {
			throw new Bbx_Media_Image_Processor_Exception('Failed to load image '.$sourcePath);
		}
	}
	
	protected function _getResource() {
		$this->_resource = NewMagickWand();
	}
		
	protected function _canSetResolution() {
		return true;
	}

	protected function _setResolution($resolution) {
		MagickSetImageResolution($this->_resource,$resolution,$resolution);
	}
	
	protected function _getResolution() {
		$res = MagickGetImageResolution($this->_resource);
		return $res[0];
	}

	protected function _crop($width,$height,$xoffset = null,$yoffset = null) {
		$newWidth = $width == 0 ? $this->getWidth() : $width;
		$newHeight = $height == 0 ? $this->getHeight() : $height;
		$xOffset = $xoffset == 0 ? 0 : $xoffset;
		$yOffset = $yoffset == 0 ? 0 : $yoffset;
		MagickCropImage($this->_resource,$newWidth,$newHeight,$xOffset,$yOffset);
	}

	protected function _resize($newWidth,$newHeight) {
		MagickScaleImage($this->_resource,$newWidth,$newHeight);
		MagickUnsharpMaskImage($this->_resource,0.5,0.25,1.2,0.05);
	}
	
	protected function _sanitizeImage() {
		MagickStripImage($this->_resource);
	}
	
	protected function _save($writePath) {
		try {
			MagickSetImageCompressionQuality($this->_resource,85);
			MagickWriteImage($this->_resource,$writePath);
		}
		catch (Exception $e) {
			throw new Bbx_Media_Image_Processor_Exception('Couldn\'t write image to '.$writePath);
		}
	}

	public function __destruct() {
		if (isset($this->_resource)) {
			DestroyMagickWand($this->_resource);
		}
	}
}

?>