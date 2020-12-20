<?php
header('Content-type:application/json;charset=utf-8');
include_once('includes/config.php');
include_once('classes/DataManagerPDO.php');

/*************************
 * check permissions
 *************************/
$pdo = new DataManagerPDO();

try {
    if (
        !isset($_FILES['file']['error']) ||
        is_array($_FILES['file']['error'])
    ) {
        throw new RuntimeException('Invalid parameters.');
        exit();
    }

    switch ($_FILES['file']['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            throw new RuntimeException('לא נשלח קובץ.');
            exit();
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            throw new RuntimeException('הקובץ גדול מדי.');
            exit();
        default:
            throw new RuntimeException('שגיאה לא ידועה.');
            exit();
    }

    $id = FILTER_INPUT(INPUT_POST,"tenderId",FILTER_VALIDATE_INT);

    $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    $originalFileName = str_replace(".".$ext,"",$_FILES['file']['name']);
    $size=$_FILES['file']['size'];
    $filepath = sprintf('files/%s_%s', uniqid(), $_FILES['file']['name']);
    $filepath = sprintf('files/%s_%s.%s', $id,uniqid(),$ext);

    if (!move_uploaded_file(
        $_FILES['file']['tmp_name'],
        $filepath
    )) {
        throw new RuntimeException('שגיאה בהעלאת הקובץ.');
        exit();
    }

    // All good, send the response
    echo json_encode([
        'status' => 'ok',
        'path' => $filepath
    ]);

    $date = date('Y-m-d H:i:s');
    $pdo->insert("tbl_files",
        ["st_file_name","st_file_original_name","date_uploaded","i_tender_id","fl_size","st_ext"],
        [$filepath,$originalFileName,$date,$id,$size,$ext])->execute();

} catch (RuntimeException $e) {
    // Something went wrong, send the err message as JSON
    http_response_code(400);

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
