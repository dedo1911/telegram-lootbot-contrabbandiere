<?php

$link = mysqli_connect($config['db_hostname'], $config['db_username'], $config['db_password']);
if ($link != FALSE) {
	mysqli_select_db($link, $config['db_name']); 
} else {
	echo('Could not connect to db');
}