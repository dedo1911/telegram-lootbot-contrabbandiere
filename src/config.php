<?php

$GLOBALS['config'] = array (
    'formattazione_predefinita' => 'HTML',
    'formattazione_messaggi_globali' => 'HTML',
    'nascondi_anteprima_link' => true,
    'tastiera_predefinita' => 'inline',
    'funziona_nei_canali' => false,
    'funziona_messaggi_modificati' => false,
    'funziona_messaggi_modificati_canali' => false,

    'tg_token' => 'botXXXXXXXXX',  // Telegram Bot API Token
    'userbot' => 'userbot',   // Telegram Bot Username
    'admin' => '1234567890',  // Telegram ID of bot owner
    
    // false to restrict group usage to groups list
    'allow_all_groups' => false,
    // Groups where the bot is enabled
    'groups' => array(
    ),

    // List of Admin TG IDs
    'admins' => array(
    ),

    // Database
    'db' => array(
        'host' => 'localhost',
        'name' => 'dbname',
        'user' => 'username',
        'pass' => 'password',
    ),

    // Lootbot Token
    'lootbot_api_token' => 'yourtoken',
    'lootbot_tgid' => 171514820
);
