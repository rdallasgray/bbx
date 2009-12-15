<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/

class Bbx_Format_Csv {

	public static function encode($data) {
		if ($data instanceof Bbx_Model_Collection) {
			return self::_encodeCollection($data);
		}
		if ($data instanceof Bbx_Model) {
			return self::_encodeModel($data, true);
		}
		if (is_array($data)) {
			return self::_encodeArray($data);
		}
	}

	protected static function _process(&$string) {
		$string = "\"".str_replace("\"","\"\"",$string)."\"";
		$string = @iconv('UTF-8','latin1',$string);
	}
	
	protected static function _encodeModel(Bbx_Model $model, $useHeader = false) {
		$a = array();
		$cols = $model->columns();
		$header = '';
		
		if ($useHeader) {
			$header = self::_encodeHeader($model)."\n";
		}

		foreach ($cols as $key => $col) {
			if (@strpos($col,'_id',(strlen($col)-3)) !== false) {
				$col = substr($col,0,-3);
				$a[] = $model->$col->__toString();
			}
			else {
				$a[] = $model->$col;
			}
		}
		$model = null;
		return $header.self::_encodeArrayRow($a);
	}
	
	protected static function _encodeCollection(Bbx_Model_Collection $collection) {
		$csvArr = array();
		$headerEncoded = false;
		
		foreach ($collection as $c) {
			if (!$headerEncoded) {
				$csvArr[] = self::_encodeHeader($c);
				$headerEncoded = true;
			}
			$csvArr[] = self::_encodeModel($c);
			$c = null;
		}
		
		return implode("\n",$csvArr);
	}
	
	protected static function _encodeArrayRow(array $a) {
		array_walk($a,array('self','_process'));
		return implode(';',$a);
	}
	
	protected static function _encodeArray(array $a) {
		$csvArr = array();
		$headerEncoded = false;

		while ($row = array_shift($a)) {
			if (is_array($row)) {
				$csvArr[] = self::_encodeArrayRow($row);
			}
			else if ($row instanceof Bbx_Model) {
				if (!$headerEncoded) {
					$csvArr[] = self::_encodeHeader($row);
					$headerEncoded = true;
				}
				$csvArr[] = self::_encodeModel($row);
			}
			else {
				throw new Zend_Exception('Trying to convert unknown type to CSV');
			}
		}

		return implode("\n",$csvArr);
	}
	
	protected static function _encodeHeader(Bbx_Model $model) {
		$a = $model->columns();
		foreach ($a as $key => $val) {
			if (@strpos($val,'_id',(strlen($val)-3)) !== false) {
				$val = substr($val,0,-3);
				$a[$key] = $val;
			}
		}
		$model = null;
		return self::_encodeArrayRow($a);
	}
}

?>