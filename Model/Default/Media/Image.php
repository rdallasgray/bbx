<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Boxes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Model_Default_Media_Image extends Bbx_Model_Default_Media {
	
	protected $_mediaPath = '/www/media/images/:size';
	protected $_mimeType = 'image/jpeg';
	protected $_extension = 'jpg';
	protected $_tableName = 'images';
	protected $_sizes = array();
	protected $_size = 'original';
	
	public function width() {
		if ($this->getSize() === 'original' && !empty($this->width)) {
			return $this->width;
		}
		return Bbx_Media_Image::load($this->getMediaPath())->width();
	}
	
	public function height() {
		if ($this->getSize() === 'original' && !empty($this->height)) {
			return $this->height;
		}
		return Bbx_Media_Image::load($this->getMediaPath())->height();
	}
	
	public function setSize($size) {
		$this->_size = $size;
	}
	
	public function getSize() {
		return $this->_size;
	}
	
	public function getMediaPath($size = null) {
		if ($size === null) {
			$size = $this->getSize();
		}
		return SITE_ROOT.str_replace(':size',$size,$this->_mediaPath).'/'.$this->id.'.'.$this->_extension;
	}
	
	public function attachMedia($file) {

		$this->setSize('original');
		
		try {
			$img = Bbx_Media_Image::load($file);
			$img->setResolution(300)->save($this->getMediaPath());
			
			$this->width = $img->width();
			$this->height = $img->height();
			$this->save();
			
			$this->_createSizedMedia($img);
			
		}
		catch (Exception $e) {
			throw new Bbx_Model_Exception('There was a problem attaching media to '.get_class($this));
		}
	}
	
	protected function _createSizedMedia(Bbx_Media_Image $img) {
		
		Bbx_Log::write("Creating sized media");
		
			foreach($this->_sizes as $size => $values) {
				list($width,$height) = explode('x',$values);
				$img->resize($width,$height)->save($this->getMediaPath($size));
			}
	}
	
	public function deleteMedia() {
		
		try {		
			foreach($this->_sizes as $size => $values) {
				unlink($this->_getMediaPath($size));
			}
			
		}
		catch (Exception $e) {
			throw new Bbx_Model_Exception('There was a problem deleting media from '.get_class($this).' '.$this->id);
		}
	}
	
}

?>