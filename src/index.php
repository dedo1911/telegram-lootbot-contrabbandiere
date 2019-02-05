<?php

require 'class-http-request.php';


$content = file_get_contents("php://input");
$update = json_decode($content, true);

var_dump($content);


require 'config.php';
require 'database.php';

$api = $GLOBALS['config']['api'];
$idadmin = $GLOBALS['config']['admin'];
$adminID = $idadmin;
$userbot = $GLOBALS['config']['userbot'];

require 'functions.php';
require 'comandi.php';

$file = "input.json";
$f2 = fopen($file, 'w');
fwrite($f2, $content);
fclose($f2);
