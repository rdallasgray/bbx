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
	
	public function getMimeType() {
		return $this->_mimeType;
	}
	
	public function getMediaPath() {
		return SITE_ROOT.$this->_mediaPath.'/'.$this->id.'.'.$this->_extension;
	}
	
	public function getExtension() {
		return $this->_extension;
	}
	
	public function attachMedia($filePath) {
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