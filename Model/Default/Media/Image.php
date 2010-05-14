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
		try {
			return Bbx_Media_Image::load($this->getMediaPath())->width();
		}
		catch (Exception $e) {
			return 0;
		}
	}
	
	public function height() {
		if ($this->getSize() === 'original' && !empty($this->height)) {
			return $this->height;
		}
		try {
			return Bbx_Media_Image::load($this->getMediaPath())->height();
		}
		catch (Exception $e) {
			return 0;
		}
	}
	
	public function setSize($size) {
		$this->_size = $size;
		return $this;
	}
	
	public function getSize() {
		return $this->_size;
	}
	
	public function getMediaPath($size = null) {
		if ($size === null) {
			$size = $this->getSize();
		}
		$this->setSize($size);
		return SITE_ROOT.str_replace(':size',$size,$this->_mediaPath).'/'.$this->id.'.'.$this->_extension;
	}
	
	public function getMediaUrl($size = null,$absolute = false) {
		if ($size === null) {
			$size = $this->getSize();
		}
		$this->setSize($size);
		$absolutePath = $absolute ? 'http://'.$_SERVER['SERVER_NAME'] : '';
		return $absolutePath.str_replace(':size',$size,$this->_mediaUrl).'/'.$this->id.'.'.$this->_extension;
	}
	
	public function attachMedia($filePath, $overwrite = true) {
		Bbx_Log::debug("trying to attach media to image");
		$this->setSize('original');
		
		try {
			$img = Bbx_Media_Image::load($filePath);
		}
		catch (Exception $e) {
			throw new Bbx_Model_Exception("Couldn't load image ".$filePath);
		}
		try {
			if (file_exists($this->getMediaPath('original')) && !$overwrite) {
				return $this->_createSizedMedia($overwrite);
			}
			$img->setResolution(300)->save($this->getMediaPath());

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
			@unlink($this->getMediaPath($size));
		}
	}
	
	public function regenerateSizedMedia($size = null, $overwrite = true) {
		$this->_createSizedMedia($size, $overwrite);
	}
	
	protected function _createSizedMedia($size = null, $overwrite = true) {
		$img = Bbx_Media_Image::load($this->getMediaPath('original'));

		if ($size !== null) {
			if (!array_key_exists($size, $this->_sizes)) {
				return Bbx_Log::debug("Image size '".$size."' does not exist");
			}
			return $this->_resizeImage($img, $size, $overwrite);
		}
	
		foreach(array_keys($this->_sizes) as $size) {
			$this->_resizeImage($img, $size, $overwrite);
		}
	}
	
	protected function _resizeImage($img, $size, $overwrite) {

		if (file_exists($this->getMediaPath($size)) && !$overwrite) {
			continue;
		}
		
		list($width, $height) = $this->_calculateImageGeometry($this->_sizes[$size], $img);
		
		if ($width > $img->width() || $height > $img->height()) {
			$img = Bbx_Media_Image::load($this->getMediaPath('original'));
		}
		try {
			$img->resize((int) $width, (int) $height)->save($this->getMediaPath($size));
		}
		catch(Exception $e) {
			Bbx_Log::debug($e->getMessage());
			throw new Bbx_Model_Exception("Couldn't resize image ".$this->id." to path ".$this->getMediaPath($size));
		}
	}
	
	protected function _calculateImageGeometry($size, $img) {
		
		list($reqWidth, $reqHeight) = explode('x', $size);
		$reqWidth = (int) $reqWidth;
		$reqHeight = (int) $reqHeight;
		
		$newWidth = $reqWidth;
		$newHeight = $reqHeight;

		$origWidth = $img->width();
		$origHeight = $img->height();
				
		if ($reqWidth === 0 || ($reqHeight > 0 && (($reqWidth/$reqHeight) > ($origWidth/$origHeight)))) {
			$newWidth = floor($origWidth * ($reqHeight/$origHeight));
		}
		else {
			$newHeight = floor($origHeight * ($reqWidth/$origWidth));
		}
		return array($newWidth, $newHeight);
	}
		
	public function getCaption() {
		try {
			$view = Zend_Registry::get('view');
		}
		catch (Exception $e) {
			return (string) $this->subject;
		}
		$helperName = Inflector::camelize(get_class($this->subject), false);
		if (!isset($stringMethod)) {
			try {
				return $view->$helperName($this->subject)->imageCaption($this);
			}
			catch (Exception $e) {
				return (string) $this->subject;
			}
		}
	}

}

?>