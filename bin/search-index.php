<?php

require('init.php');

$sentinel_file = APPLICATION_PATH . '/modules/' . MODULE_NAME . '/search/indexing';

if (file_exists($sentinel_file)) {
	Bbx_Log::write('Spider is already indexing');
	exit();
}

touch($sentinel_file);

set_time_limit(21600);
$host = $argv[1];
Bbx_Search_Spider::start('/', $host);

unlink($sentinel_file);

?>