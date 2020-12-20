<?php

use PhpOffice\PhpSpreadsheet\IOFactory;

require __DIR__ . '/../Header.php';

$fileName = filter_input(INPUT_POST,"fileName",FILTER_SANITIZE_STRING);

$inputFileName = __DIR__ . '/../../../../../files/'.$fileName;
$helper->log('Loading file ' . pathinfo($inputFileName, PATHINFO_BASENAME) . ' using IOFactory to identify the format');
$spreadsheet = IOFactory::load($inputFileName);
$sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);


$tendersArray = array();
foreach($sheetData as $dataLine){
    if($dataLine["A"] != NULL){
        $projectName = filter_var($dataLine["A"],FILTER_SANITIZE_STRING);
        $projectDescription = filter_var($dataLine["B"],FILTER_SANITIZE_STRING);
        $group = filter_var($dataLine["C"],FILTER_SANITIZE_STRING);
        $field = filter_var($dataLine["D"],FILTER_SANITIZE_STRING);
        $dateDeadline = filter_var($dataLine["E"],FILTER_SANITIZE_STRING);
        $dateDeadline = filter_var($dataLine["E"],FILTER_SANITIZE_STRING);
        $dateDeadline = filter_var($dataLine["E"],FILTER_SANITIZE_STRING);
    }
}
