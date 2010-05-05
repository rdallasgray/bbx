<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Media_Image_Processor_GD extends Bbx_Media_Image_Processor_Abstract {
	
	protected $_newImage;
	
	public function __construct() {
	}

	protected function _crop($width,$height,$xoffset = null,$yoffset = null) {
		$newWidth = $width == 0 ? $this->getWidth() : $width;
		$newHeight = $height == 0 ? $this->getHeight() : $height;
		$xOffset = $xoffset == 0 ? 0 : $xoffset;
		$yOffset = $yoffset == 0 ? 0 : $yoffset;
		$newImage = imagecreatetruecolor($newWidth,$newHeight);
		imagecopy($newImage,$this->_resource(),0,0,$xOffset,$yOffset,$newWidth,$newHeight);
		$this->_setResource($newImage);
	}
	
	protected function _getResource() {
		try {
			$this->_resource = imagecreatefromjpeg($this->_sourcePath);
		}
		catch (Exception $e) {
			throw new Bbx_Media_Image_Processor_Exception('Failed to load image '.$this->_sourcePath);
		}
		return $this->_resource;
	}
	
	protected function _setResource($r) {
		$this->_resource = $r;
	}

	protected function _resize($newWidth,$newHeight) {
		$width = $this->getWidth();
		$height = $this->getHeight();
		$newImage = imagecreatetruecolor($newWidth,$newHeight);
		imagecopyresampled($newImage,$this->_resource(),0,0,0,0,$newWidth,$newHeight,$width,$height);
		$this->_setResource($newImage);
	}
	
	protected function _canSetResolution() {
		return false;
	}

	protected function _setResolution($resolution) {
	}

	protected function _getResolution() {
		return 72;
	}

	protected function _save($savePath) {
		if (!imagejpeg($this->_resource(),$savePath,88)) {
			throw new Bbx_Media_Image_Processor_Exception('Couldn\'t write image to '.$savePath);
		}
	}

	public function __destruct() {
		if (isset($this->_resource)) {
			imagedestroy($this->_resource);
			$this->_resource = null;
		}
	}

}

?>