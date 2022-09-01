<?php

/**
 * Name: COMMON CORE FILE by Prk PHP Framework
 * Author: Prk
 * Website: https://imprk.me/
 * Date: 2018-01-42
 * Location: Guangzhou Guangdong, People's Republic of China
 * Update: https://github.com/BiliPrk/Prk-PHP-Framework
 */

error_reporting(0);
if(defined('IN_CRONLITE')) return;

// Env
define('VERSION', '110');
define('DB_VERSION', '110');
define('IN_CRONLITE', true);
define('SYSTEM_ROOT', dirname(__FILE__) . '/');
define('ROOT', dirname(SYSTEM_ROOT) . '/');
define('TEMPLATE_ROOT', ROOT . 'template/');
date_default_timezone_set('Asia/Shanghai');
$date = date("Y-m-d H:i:s");
$time = time();

// SESSION
if(!isset($nosession) || !$nosession) session_start();

// Get Website's URL
if(!function_exists("is_https")) {
    function is_https() {
        if(isset($_SERVER['SERVER_PORT']) && 443 == $_SERVER['SERVER_PORT']) return true;
        elseif(isset($_SERVER['HTTPS']) && 'on' == (strtolower($_SERVER['HTTPS']) || '1' == $_SERVER['HTTPS'])) return true;
        elseif(isset($_SERVER['HTTP_X_CLIENT_SCHEME']) && 'https' == $_SERVER['HTTP_X_CLIENT_SCHEME']) return true;
        elseif(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && 'https' == $_SERVER['HTTP_X_FORWARDED_PROTO']) return true;
        elseif(isset($_SERVER['REQUEST_SCHEME']) && 'https' == $_SERVER['REQUEST_SCHEME']) return true;
        elseif(isset($_SERVER['HTTP_EWS_CUSTOME_SCHEME']) && 'https' == $_SERVER['HTTP_EWS_CUSTOME_SCHEME']) return true;
        else return false;
    }
}
$siteurl = (is_https() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/';

// Autoloader
include_once(SYSTEM_ROOT . "autoloader.php");
Autoloader::register();

// load functions
include 'functions.php';

// database
require ROOT . 'config.php';
define('DB_PREFIX', $db_config['prefix']);
if (!$db_config['username'] || !$db_config['password'] || !$db_config['name']) exit(base64_decode('VU5BQkxFJm5ic3A7VE8mbmJzcDtDT05ORUNUJm5ic3A7VE8mbmJzcDtUSEUmbmJzcDtEQVRBQkFTRSEmbmJzcDsoRXJyb3ImbmJzcDtjb2RlOiZuYnNwOy0xMDExKSZuYnNwOzxhJm5ic3A7aHJlZj0iaHR0cHM6Ly9naXRodWIuY29tL0JpbGlQcmsvUHJrLVBIUC1GcmFtZXdvcmsvd2lraS9EYXRhYmFzZS1FcnJvci1JbmZvI2Vycm9yLWNvZGUtLTEwMTEiJm5ic3A7dGFyZ2V0PSJfYmxhbmsiPihIZWxwKTwvYT4='));
$DB = new \lib\PdoHelper($db_config);
