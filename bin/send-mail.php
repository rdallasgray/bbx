<?php

require('init.php');

$mail = Bbx_Mail::instance();

$mail
	->setFrom($argv[1])
	->setBodyText($argv[3])
	->setSubject($argv[4]);
	
foreach(explode(',', $argv[2]) as $to) {
	$mail->addTo($to);
}

try {
	$mail->send();
}
catch(Exception $e) {
	Bbx_Log::write("Couldn't send mail: " . $e->getMessage());
}

?>