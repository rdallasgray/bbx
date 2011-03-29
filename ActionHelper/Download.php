<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/

class Bbx_ActionHelper_Download extends Zend_Controller_Action_Helper_Abstract {
	
	public function direct($model) {
		$extension = ($model instanceof Bbx_Model_Default_Media) ? 
			$model->getExtension() :  $this->getRequest()->getParam('format');
		$filename = $this->_getFilename($model);
		$this->getResponse()->setHeader('Content-disposition','attachment; filename=' . $filename . '.' . $extension, true);

		if ($model instanceof Bbx_Model_Default_Media) {
			return $this->_doMediaDownload($model);
		}
		
	}
	
	private function _doMediaDownload($model) {
		Zend_Controller_Action_HelperBroker::getExistingHelper('viewRenderer')->setNoRender(true);
		try {
			$this->getResponse()->setHeader('Content-Type', $model->getMimeType());
			if ($this->getRequest()->isHead()) {
				$this->getResponse()->setHeader('Content-Length', 0)->sendResponse();
				exit(); // nb file corrupts if no exit
			}
			else {
				$this->getResponse()
					->setHeader('Content-Length', filesize($model->getMediaPath()))
					->setBody(readfile($model->getMediaPath()))->sendResponse();
				exit(); // nb file corrupts if no exit
			}
		}
		catch (Exception $e) {
			throw new Bbx_Controller_Rest_Exception("Couldn't read file information for download: "
				. $this->getRequest()->getRequestUri(), 500);
		}
	}
	
	private function _getFilename($model) {
		$filename = 'file';
		if ($model instanceof Bbx_Model) {
			$filename = Bbx_ActionHelper_Filename::fromModel($model);
		}
		else {
			$filename = Bbx_ActionHelper_Filename::fromUrl($this->getRequest()->getRequestUri());
		}
		return $filename;
	}

}

?>