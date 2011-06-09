<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Model_Default_Media_Image extends Bbx_Model_Default_Media {
	
	protected $_mediaPath = '/www/media/images/:size';
	protected $_mediaUrl = '/media/images/:size';
	protected $_mimeType = 'image/jpeg';
	protected $_extension = 'jpg';
	protected $_tableName = 'images';
	protected $_sizes = array();
	protected $_size = 'original';
	
	public function width() {
		if ($this->getSize() === 'original' && !empty($this->width)) {
			return $this->width;
		}
		$dims = $this->_calculateImageGeometry($this->_sizes[$this->getSize()], $this->width, $this->height);
		return $dims[0];
	}
	
	public function height() {
		if ($this->getSize() === 'original' && !empty($this->height)) {
			return $this->height;
		}
		$dims = $this->_calculateImageGeometry($this->_sizes[$this->getSize()], $this->width, $this->height);
		return $dims[1];
	}
	
	public function getMinHeightForSize($size) {
		if (!array_key_exists($size, $this->_sizes)) {
			return 0;
		}
		list($width, $height) = explode('x', $this->_sizes[$size]);
		return $height;
	}
	
	public function getMinWidthForSize($size) {
		if (!array_key_exists($size, $this->_sizes)) {
			return 0;
		}
		list($width, $height) = explode('x', $this->_sizes[$size]);
		return $width;
	}
	
	public function setSize($size) {
		$this->_size = $size;
		return $this;
	}
	
	public function getSize() {
		return $this->_size;
	}
	
	public function getMediaPath($size = null, $forceLocal = false) {
		if ($size === null) {
			$size = $this->getSize();
		}
		$this->setSize($size);
		$path = str_replace(':size', $size, $this->_mediaPath) . '/' . $this->id . '.' . $this->_extension;
		return $this->_mediaPath($path, $forceLocal);
	}
	
	public function getMediaUrl($size = null, $absolute = false) {
		if ($size === null) {
			$size = $this->getSize();
		}
		$this->setSize($size);
		$path = str_replace(':size', $size, $this->_mediaUrl) . '/' . $this->id . '.' . $this->_extension;
		if (isset($this->_cdn) && !is_readable(APPLICATION_PATH . '/..' . $path)) {
			return $this->_cdn->url($path);
		}
		$absolutePath = $absolute ? 'http://'.$_SERVER['SERVER_NAME'] : '';
		return $absolutePath . $path;
	}
	
	public function attachMedia($filePath, $overwrite = true) {
		Bbx_Log::debug("trying to attach media to Image " . $this->id);
		$this->setSize('original');
		
		try {
			$img = Bbx_Media_Image::load($filePath);
		}
		catch (Exception $e) {
			throw new Bbx_Model_Exception("Couldn't load image ".$filePath);
			Bbx_Log::write(print_r($e, true));
		}
		try {
			if (file_exists($this->getMediaPath('original')) && !$overwrite) {
				Bbx_Log::debug('original exists (' . $this->getMediaPath('original') . ')');
				return $this->_createSizedMedia(null, $overwrite);
			}
			$img->setResolution(300)->save($this->getMediaPath(null, true));

			$this->width = $img->width();
			$this->height = $img->height();
			$this->save();
			
			$this->_createSizedMedia();
		}
		catch (Exception $e) {
			Bbx_Log::debug($e->getMessage());
			throw new Bbx_Model_Exception('Unable to create sized media for image '.$filePath);
		}
	}
	
	public function deleteMedia() {
		
		$sizes = array_keys($this->_sizes);
		$sizes[] = 'original';
		
		foreach ($sizes as $size) {
			if (!@unlink($this->getMediaPath($size))) {
				Bbx_Log::debug('Unable to delete media ' . $this->getMediaPath($size));
			}
		}
	}
	
	public function regenerateSizedMedia($size = null, $overwrite = true) {
		$this->_createSizedMedia($size, $overwrite);
	}
	
	protected function _createSizedMedia($size = null, $overwrite = true) {

		if ($size !== null) {
			if (!array_key_exists($size, $this->_sizes)) {
				return Bbx_Log::debug("Image size '".$size."' does not exist");
			}
			return $this->_resizeImage($size, $overwrite);
		}
	
		foreach(array_keys($this->_sizes) as $size) {
			$this->_resizeImage($size, $overwrite);
		}
	}
	
	protected function _resizeImage($size, $overwrite) {

		if (file_exists($this->getMediaPath($size)) && !$overwrite) {
			Bbx_Log::debug($size . ' exists (' . $this->getMediaPath($size) . ')');
			return;
		}
		
		$img = Bbx_Media_Image::load($this->getMediaPath('original'));
		list($width, $height) = $this->_calculateImageGeometry($this->_sizes[$size], $this->width, $this->height);
		
		try {
			$img->resize((int) $width, (int) $height)->save($this->getMediaPath($size, true));
		}
		catch(Exception $e) {
			Bbx_Log::debug($e->getMessage());
			throw new Bbx_Model_Exception("Couldn't resize image ".$this->id." to path ".$this->getMediaPath($size, true));
		}
	}
	
	protected function _calculateImageGeometry($geom, $origWidth, $origHeight) {
		preg_match('/(?<width>^\d*)x(?<height>\d*)(?<modifier>\^{0,1})/', $geom, $matches);
		$reqWidth = (int) $matches['width'];
		$reqHeight = (int) $matches['height'];
		$modifier = $matches['modifier'];
		$minSize = ($modifier == '^');
		
		$newWidth = $reqWidth;
		$newHeight = $reqHeight;
				
		if ($reqWidth === 0 || ($reqHeight > 0 && (($reqWidth/$reqHeight) > ($origWidth/$origHeight)))) {
			// width is not specified, or required width/height ratio is greater than original width/height ratio --
			// height should be newHeight, and width is scaled to proportion.
			$newWidth = floor($origWidth * ($reqHeight/$origHeight));
		}
		else {
			// width is specified, or required width/height ratio is less than original width/height ratio --
			// width should be newWidth, and height is scaled to proportion.
			$newHeight = floor($origHeight * ($reqWidth/$origWidth));
		}
		if ($minSize) {
			if ($newWidth < $reqWidth) {
				$newWidth = $reqWidth;
				$newHeight = floor($origHeight * ($reqWidth/$origWidth));
			}
			else if ($newHeight < $reqHeight) {
				$newHeight = $reqHeight;
				$newWidth = floor($origWidth * ($reqHeight/$origHeight));
			}
		}
		return array($newWidth, $newHeight);
	}

}

?>