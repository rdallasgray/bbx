<?php

/*

  Copyright (c) 2009 Robert Johnston

  This file is part of Backbox.

  Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.

  Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

  You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/

class Bbx_ActionHelper_Media extends Zend_Controller_Action_Helper_Abstract {


    public function handleUpload($collection) {
        $request = $this->getRequest();
        $response = $this->getResponse();
        if ($request->getHeader("Content-Length") == 0) {
            $response->setHttpResponseCode(204)->sendResponse();
            exit();
        }

        $mimeType = Bbx_Model::load($collection->getModelName())->getMimeType();

        $max_size = ini_get('upload_max_filesize');
        $post_max = ini_get('post_max_size');
        if ($post_max < $max_size) {
            $max_size = $post_max;
        }

        $upload = new Zend_File_Transfer_Adapter_Http();
        // TODO throws error 'Magicfile can not be set. There is no finfo extension installed' (Zend_Validate_File_MimeType 193)		
        //		$upload->addValidator('MimeType', false, $mimeType);
        $upload->getValidator('Zend_Validate_File_Upload')
            ->setMessage('File is too large - max size ' . $max_size, Zend_Validate_File_Upload::INI_SIZE);

        if ($upload->receive()) {

            Bbx_Log::debug("upload received");

            $files = $upload->getFileInfo();
            $media = $files['file_data'];

            Bbx_Log::debug(print_r($media,true));

            $new_model = $collection->create();
            try {
                $new_model->attachMedia($media['tmp_name']);
                $new_model->save();
            }
            catch (Exception $e) {
                $new_model->delete();
                throw $e;
            }
            return $new_model;
        }
        else {
            $this->_throwUploadException($upload);
        }
    }

    protected function _throwUploadException($upload) {
        $msgs = $upload->getMessages();
        if (empty($msgs)) {
            $msgs = array('Unknown failure - please contact support.');
        }
        Bbx_Log::debug('Upload failed: '.implode($msgs));
        throw new Bbx_Controller_Rest_Exception('Upload failed: '.implode($msgs),500);
    }

}

?>