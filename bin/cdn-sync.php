<?php

require('init.php');

Bbx_Log::write('Starting CDN Sync');

$sentinel_file = APPLICATION_PATH . '/../www/media/.cdn_syncing';

if (file_exists($sentinel_file)) {
	Bbx_Log::write('CDN is already syncing');
	exit();
}

if (!touch($sentinel_file)) {
  Bbx_Log::write('Unable to create sentinel file for CDN sync');
}

set_time_limit(7200);
$start = (string) $argv[1];

$cdnType = (string) $argv[2];

Bbx_Log::write('CDN syncing at ' . $start . ', CDN type is ' . $cdnType);

switch($cdnType) {
	case 's3':
	$cdn = Bbx_Model_Default_Media_Cdn_S3::init();
}

$cdn->sync($start);

if (!unlink($sentinel_file)) {
  Bbx_Log::write('Unable to delete sentinel file for CDN sync');
}

?>