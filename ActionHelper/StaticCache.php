<?php

/*

Copyright (c) 2009 Robert Johnston

This file is part of Backbox.

Backbox is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software  Foundation, either version 3 of the License, or (at your option) any later version.
 
Backbox is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with Backbox. If not, see http://www.gnu.org/licenses/.

*/

class Bbx_ActionHelper_StaticCache extends Zend_Controller_Action_Helper_Abstract {
		
	public function direct($params, $format = 'html', $allowQuery = false) {
		$request = $this->getRequest();
		if ($request->getParam('format') != $format || !$request->isGet() || (!$allowQuery && $request->getQuery() != null)) {
			return;
		}
		$helper = Zend_Controller_Action_HelperBroker::getStaticHelper('cache');
		foreach($params as $action => $tags) {
			if ($action != $request->getActionName()) {
				continue;
			}
			$parsedTags = array();
			foreach($tags as $t) {
				if (strpos($t, ':') !== false) {
					$keys = preg_match('/:\w+/', $t, $matches);
					$key = substr($matches[0], 1);
					if (($param = $request->getParam($key))) {
						$parsedTags[] = str_replace(':' . $key, $param, $t);
					}
				}
				else {
					$parsedTags[] = $t;
				}
			}
			$helper->direct(array($action), $parsedTags);
		}
	}

}

?>