<?php
session_start();

setlocale(LC_MONETARY, 'en_US');

define('Access', true);
define('DEV_MODE', false);
define('SECURE_AJAX', false);
define('DBHOST', 'localhost');
define('CLASSES_DIR', str_replace("includes", "classes", __DIR__));
define('INCLUDES_DIR', __DIR__);
define('RESULT_ERROR', 'ERROR');
define('RESULT_SUCCESS', 'OK');
define("SITE_URL","");
define("SMS_API_URL","");
define("SMS_REST_API_URL","");

date_default_timezone_set ( 'Asia/Jerusalem' );

if (DEV_MODE === true) {

    ini_set ('display_errors', 'on');
    ini_set ('log_errors', 'on');
    ini_set ('display_startup_errors', 'on');
    ini_set ('error_reporting', E_ALL);

    //db details
    define('DBUSER', 'root');
    define('DBPASS', '');
    define('DBNAME', 'tenders');

} else {

    ini_set ('display_errors', 'off');
    ini_set ('log_errors', 'on');
    ini_set ('display_startup_errors', 'off');
    ini_set ('error_reporting', E_ALL);

    //db details
    define('DBUSER', 'root');
    define('DBPASS', '');
    define('DBNAME', 'tenders');

    if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "off") {

        $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: ' . $redirect);
        exit;
    }
}


define("LOG_EMAIL",1);
define("LOG_SMS",2);
