<?php

require('init.php');

$u = Bbx_Config::get()->resources->db->params->username;
$p = Bbx_Config::get()->resources->db->params->password;
$d = Bbx_Config::get()->resources->db->params->dbname;
$path = APPLICATION_PATH . '/scripts/migrations';

$cmd = "mysqldump --user={$u} --password='{$p}' --no-data --skip-add-drop-table --databases {$d} | sed 's/CREATE TABLE/CREATE TABLE IF NOT EXISTS/g' > {$path}/schema.sql";

exec($cmd);

?>