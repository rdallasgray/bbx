<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



abstract class Bbx_Media_Image_Processor_Abstract {

	protected $_resource;
	protected $_sourcePath;
	protected $_width;
	protected $_height;
	protected $_resolution;


 	public function load($sourcePath) {
		$this->_sourcePath = $sourcePath;
	}
	
	protected function _resource() {
		return isset($this->_resource) ? $this->_resource : $this->_getResource();
	}
	
	protected function _getResource() {
		
	}

	public function crop($width,$height,$framing='L') {
		switch ($framing) {
			case 'L':
			$xOffset = null;
			$yOffset = null;
			break;
			case 'C':
			$xOffset = ($this->getWidth()-$width)/2;
			$yOffset = ($this->getHeight()-$height)/2;
			break;
		}
		$this->_crop($width,$height,$xOffset,$yOffset);
		$this->_width = $width;
		$this->_height = $height;
		return $this;
	}
	
	public function setResolution($res) {
		if ($res !== $this->getResolution()) {
			$this->_resolution = $this->_setResolution($res);
		}
		return $this;
	}
	
	public function getResolution() {
		if (isset($this->_resolution)) {
			return $this->_resolution;
		}
		$this->_resolution = $this->_getResolution();
		return $this->_resolution;
	}

	public function resize($reqWidth, $reqHeight, $upSize = false) {

		$origWidth = $this->getWidth();
		$origHeight = $this->getHeight();

		$newWidth = $reqWidth;
		$newHeight = $reqHeight;
		
		if ($reqWidth == 0 || (($reqWidth/$reqHeight) > ($origWidth/$origHeight))) {
			$newHeight = $reqHeight;
			$newWidth = floor($origWidth * ($reqHeight/$origHeight));
		}
		else if ($reqHeight == 0 || (($reqWidth/$reqHeight) <= ($origWidth/$origHeight))) {
			$newWidth = $reqWidth;
			$newHeight = floor($origHeight * ($reqWidth/$origWidth));
		}

		if (($newWidth > $origWidth || $newHeight > $origHeight) && $upSize === false) {
			return null;
		}

		$this->_resize($newWidth,$newHeight);
		
		$this->_width = $newWidth;
		$this->_height = $newHeight;
			
		return $this;
	}

	
	public function save($writePath) {
		$this->_save($writePath);
	}
	
	public function sanitize() {
		$this->_sanitize();
		return $this;
	}
	
	public function getHeight() {
		if (isset($this->_height)) {
			return $this->_height;
		}
		list($width,$height) = getimagesize($this->_sourcePath);
		$this->_height = $height;
		$this->_width = $width;
		return $this->_height;
	}

	public function getWidth() {
		if (isset($this->_width)) {
			return $this->_width;
		}
		list($width,$height) = getimagesize($this->_sourcePath);
		$this->_height = $height;
		$this->_width = $width;
		return $this->_width;
	}

	public function getSize() {
		return getimagesize($this->_sourcePath);
	}

}

?>