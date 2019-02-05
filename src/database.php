<?php
if (!isset($GLOBALS['config'])) die('db config missing');

$GLOBALS['db'] = new PDO(
    'mysql:host=' . $GLOBALS['config']['db']['host'] . ';dbname=' . $GLOBALS['config']['db']['name'],
    $GLOBALS['config']['db']['user'],
    $GLOBALS['config']['db']['pass']
);
