<?php

class Bbx_Controller_Rest_Tools extends Bbx_Controller_Rest {

  public function init() {
    $this->_helper->contextSwitch()->addActionContext('regenerate-images','json');
    $this->_helper->contextSwitch()->addActionContext('regenerate-image','json');
    $this->_helper->contextSwitch()->addActionContext('spider','json');
    $this->_helper->contextSwitch()->addActionContext('update-all','json');
    $this->_helper->contextSwitch()->addActionContext('migrate','json');
    $this->_helper->contextSwitch()->addActionContext('bootstrap','json');
    parent::init();

  }

  public function bootstrapAction() {
    $users = Bbx_Model::load('User')->findAll();
    if (count($users) === 0) {
      $roles = Bbx_Model::load('Role')->findAll();
      if (count($roles) === 0) {
	$admin = Bbx_Model::load('Role')->create(array('name' => 'admin', 'precedence' => 0));
	$staff = Bbx_Model::load('Role')->create(array('name' => 'staff', 'precedence' => 1));
      }
      $admin = Bbx_Model::load('Role')->find(array('where' => "name = 'admin'"));
      $admin->users->create(array('username' => 'default', 'name' => 'default', 'password' => 'default'));
    }
  }
	
  public function regenerateImagesAction() {
    $this->_helper->authenticate();
    set_time_limit(86400);
    $size = $this->_getParam('size');
    $overwrite = $this->_getParam('overwrite');
    $cdnType = @Bbx_Config::get()->site->cdn->type;
    $imgs = Bbx_Model::load('Image')->findAll();
    foreach ($imgs as $img) {
      Bbx_Log::debug("regenerating sized media for image " . $img->id);
      $img->regenerateSizedMedia($size, $overwrite);
    }
    if (APPLICATION_ENV == 'production' && $cdnType != null) {
      Bbx_Log::write('Doing CDN sync');
      $pid = exec('nice php ' . APPLICATION_PATH . '/../library/Bbx/bin/cdn-sync.php /www/media ' . $cdnType .  
		  ' > /dev/null 2>&1 &');
    }
    // TODO send a JSON response
    $this->getResponse()->sendResponse();
    exit();
  }
	
  public function regenerateImageAction() {
    $this->_helper->authenticate();
    set_time_limit(86400);
    $id = $this->_getParam('id');
    if (empty($id)) {
      return;
    }
    $cdnType = @Bbx_Config::get()->site->cdn->type;
    $img = Bbx_Model::load('Image')->find($id);
    $img->regenerateSizedMedia(null, true);
    if (APPLICATION_ENV == 'production' && $cdnType != null) {
      Bbx_Log::write('Doing CDN sync');
      $pid = exec('nice php ' . APPLICATION_PATH . '/../library/Bbx/bin/cdn-sync.php /www/media ' . $cdnType .  
		  ' > /dev/null 2>&1 &');
    }
    // TODO send a JSON response
    $this->getResponse()->sendResponse();
    exit();
  }
	
  public function migrateAction() {
    $users = Bbx_Model::load('User')->findAll();
    if (count($users) > 0) {
      //      $this->_helper->authenticate();
    }
    $dirname = APPLICATION_PATH . '/scripts/migrations';
    if ($handle = opendir($dirname)) {
      $db = Zend_Registry::get('db')->getConnection();
      $mignames = array();
      while (false !== ($entry = readdir($handle))) {
	if (!in_array($entry, array('.', '..'))) {
	  array_push($mignames, $entry);
	}
      }
      sort($mignames);
      foreach($mignames as $name) {
	Bbx_Log::write('Running migration ' . $name);
	$sql = file_get_contents($dirname . '/' . $name);
	if (!$db->multi_query($sql)) {
	  throw new Zend_Db_Exception('Can\'t complete migrations: ' . $db->error);
	}
	do {
	  $db->use_result();
	} while ($db->next_result());
      }
      closedir($handle);
    }
    else {
      Bbx_Log::write('Unable to find migrations directory ' . $dirname);
    }
  }
	
  public function updateAllAction() {
    $this->_helper->authenticate();
    $model = $this->_getParam('model');
    try {
      $models = Bbx_Model::load($model)->findAll();
      foreach ($models as $m) {
	$m->update(array());
      }
    }
    catch (Exception $e) {
      Bbx_Log::debug($e->getMessage());
    }
    $this->getResponse()->sendResponse();
    exit();
  }
	
  public function spiderAction() {
    $this->_helper->authenticate();
    $reset = ($this->_getParam('reset') === 'true');
    $spider = new Bbx_Search_Spider;
    $spider->start('/', $_SERVER['HTTP_HOST'], $reset);
    $this->getResponse()->sendResponse();
    exit();
  }

}

?>