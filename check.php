<?php
require_once 'includes/config.php';
require_once INCLUDES_DIR . '/functions.php';
require_once CLASSES_DIR . '/DataManagerPDO.php';

phpinfo();

if (!isset($_SESSION['user']['id'])) {
    header('Location: index.php');
}
$number = "0503434677";
$content = "ניסיון https://🚙.st/api.php";
$now = date('Y-m-d H:i:s');
$tenderId = 1;

$pdo = new DataManagerPDO();
$pdo->insert("tbl_log",["i_type","st_subject","st_message","st_receiver","i_status","date_created","i_tender_id"],[LOG_SMS,"SMS נשלח בהצלחה",$content,$number,1,$now,$tenderId])->execute();