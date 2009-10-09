<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/



class Bbx_Controller_Rest_Crud_Media extends Bbx_Controller_Rest_Crud {

	protected function _post() {
		
		if ($this->getRequest()->getHeader("Content-Length") == 0) {
			$this->getResponse()->setHttpResponseCode(204)->sendResponse();
			exit();
		}

		$model = isset($this->collection) ? $this->collection : $this->model;
		$mimeType = ($model instanceof Bbx_Model) ? $model->getMimeType() : Bbx_Model::load($model->getModelName())->getMimeType();		
		
		$upload = new Zend_File_Transfer_Adapter_Http();
		$upload->addValidator('Count', false, array('min' => 1, 'max' => 1))
			   ->addValidator('MimeType', false, $mimeType);
			
		if ($upload->receive()) {
			
			Bbx_Log::debug("upload received");
			
			$files = $upload->getFileInfo();
			$media = $files['file_data'];
			
			Bbx_Log::debug(print_r($media,true));

			$new_model = $model->create($this->_getBodyData());
			Bbx_Log::debug(print_r($new_model,true));
			$new_model->attachMedia($media['tmp_name']);
			$new_model->save();
					
			$this->getResponse()->setHttpResponseCode(201)->setHeader('Location',$new_model->url(true));
		
			$this->_forward('show',null,null,array('format' => 'json','id' => $new_model->id,'final' => true));
		}
		else {
			Bbx_Log::debug('Upload failed: '.implode($upload->getMessages()));
			throw new Bbx_Controller_Rest_Exception('Upload failed: '.implode($upload->getMessages()),500);
		}
		
	}

}

?>