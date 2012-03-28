<?php

require('init.php');

$mail = Bbx_Mail::instance();

$mail
->setFrom((string) $argv[1])
->setBodyText((String) $argv[3])
->setSubject((string) $argv[4]);
	
foreach(explode(',', (string) $argv[2]) as $to) {
	$mail->addTo($to);
}

try {
	$mail->send();
}
catch(Exception $e) {
	Bbx_Log::write("Couldn't send mail: " . $e->getMessage());
}

?>