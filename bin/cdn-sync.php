<?php

require('init.php');

$sentinel_file = APPLICATION_PATH . '/../www/media/cdn_syncing';

if (file_exists($sentinel_file)) {
	Bbx_Log::write('CDN is already syncing');
	exit();
}

touch($sentinel_file);

set_time_limit(7200);
$start = $argv[1];

$cdnType = $argv[2];

switch($cdnType) {
	case 's3':
	$cdn = Bbx_Model_Default_Media_Cdn_S3::init();
}

$cdn->sync($start);

unlink($sentinel_file);

?>