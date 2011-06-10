<?php

require('init.php');


set_time_limit(7200);
$start = $argv[1];

$cdnType = $argv[2];

switch($cdnType) {
	case 's3':
	$cdn = Bbx_Model_Default_Media_Cdn_S3::init();
}

$cdn->sync($start);

?>