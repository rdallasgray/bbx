<?php

$router->addRoute('index',new Zend_Controller_Router_Route
	(':controller',array('module'=>'default','controller'=>'index','action'=>'index'))
);

$router->addRoute('show',new Zend_Controller_Router_Route
	(':controller/:id',array('module'=>'default','action'=>'show'))
);

$router->addRoute('index_rel',new Zend_Controller_Router_Route
	(':controller/:id/:rel',array('module'=>'default','action'=>'index'))
);

$router->addRoute('show_rel',new Zend_Controller_Router_Route
	(':controller/:id/:rel/:rel_id',array('module'=>'default','action'=>'show'))
);

$router->addRoute('new',new Zend_Controller_Router_Route
	(':controller/new',array('module'=>'default','action'=>'new'))
);

$router->addRoute('metadata',new Zend_Controller_Router_Route
	('metadata/:action',array('module'=>'default','controller'=>'metadata'))
);

$router->addRoute('admin',new Zend_Controller_Router_Route
	('admin/:action',array('module'=>'default','controller'=>'admin'))
);

?>