<?php

require 'config.php';
require 'database.php';

require 'Telegram.php';
$GLOBALS['telegram'] = new Telegram($GLOBALS['config']['tg_token']);

// Store data from php://input for future uses
$GLOBALS['telegram']->setData($GLOBALS['telegram']->getData());

file_put_contents('input_debug.json', json_encode($GLOBALS['telegram']->getData(), JSON_PRETTY_PRINT));

require 'functions.php';
require 'messages.php';
require 'menus.php';
require 'commands.php';
require 'actions.php';

// TODO: Invoke stuff

require 'cleanup.php';
