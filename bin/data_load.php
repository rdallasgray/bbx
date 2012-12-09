<?php

require('init.php');

$u = Bbx_Config::get()->resources->db->params->username;
$p = Bbx_Config::get()->resources->db->params->password;
$d = Bbx_Config::get()->resources->db->params->dbname;
$path = APPLICATION_PATH . '/scripts/migrations';

$cmd = "mysql --user={$u} --password='{$p}' < {$path}/data.sql";

exec($cmd);

?>