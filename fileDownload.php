<?php
require_once 'includes/config.php';
require_once INCLUDES_DIR . '/functions.php';
require_once CLASSES_DIR . '/DataManagerPDO.php';
require_once CLASSES_DIR .  '/Permission.php';

if (!isset($_SESSION['user']['id'])) {
    exit();
}

$pdo = new DataManagerPDO();

if(isset($_GET['file'])){
    $file = filter_input(INPUT_GET,"file",FILTER_SANITIZE_STRING);

    $fileData = $pdo->select("tbl_files",["st_file_name","st_file_original_name","st_ext"])
        ->where("st_file_name","=",$file)
        ->fetch();

    if($fileData){
        $tokenFileName = $fileData[0]["st_file_name"];
        $originalFileName = $fileData[0]["st_file_original_name"];
        $ext = $fileData[0]["st_ext"];

        // headers to send your file
        header("Content-Type: application/jpeg");
        header("Content-Length: " . filesize($tokenFileName));
        header('Content-Disposition: attachment; filename="' . $originalFileName .'.'. $ext.'"');

        // upload the file to the user and quit
        readfile($tokenFileName);
        exit();

    }
}
