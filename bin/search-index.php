<?php

require('init.php');

set_time_limit(7200);
$host = $argv[1];
$spider = new Bbx_Search_Spider;
$spider->start('/', $host);

?>