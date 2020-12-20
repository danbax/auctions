<?php
require_once 'includes/config.php';
require_once INCLUDES_DIR . '/functions.php';



define("NO_DATE","0000-00-00 00:00:00");


if (!isset($_SESSION['user']['id'])) {
    header('Location: index.php');
}
else{
    $currentUserId = $_SESSION['user']['id'];
}

$isShowingHistory = false;
if(isset($_GET['showHistory'])){
    $isShowingHistory = true;
}

require_once CLASSES_DIR . '/DataManagerPDO.php';
require_once CLASSES_DIR .  '/Permission.php';


// Initialization //////////////////////////////////////////////////////////////////////////////////////////////////////
$data = new DataManager();
$roles = new Permission();

$statuses = array();
$statuses[0] = "ממתין לתשובה";
$statuses[1] = "מאושר";
$statuses[2] = "נדחה";


$tenderId = filter_input(INPUT_GET, 'tenderId', FILTER_VALIDATE_INT);


// get tenders
$pdo = new DataManagerPDO();
$tenders = array();

$isAllowed = $pdo->select("tbl_users",["bool_is_admin"])
    ->where("id","=",$_SESSION['user']['id'])
    ->fetch();


$isAdmin = false;
if($isAllowed[0]["bool_is_admin"]) {
    $isAdmin = true;
}
try {
    $table = "tbl_tenders";
    $fields = ["tbl_tenders.bool_canceled","tbl_models.st_name as modelName", "tbl_finishing.st_name as finishingName", "i_winner_user_id","tbl_users.st_username as username","tbl_users.st_friendly_name as friendlyName",
        "st_production_year", "st_licensing", "i_classification", "i_min_increase", "i_finishing_id",
        "tbl_tenders.id", "tbl_tenders.st_name", "st_description", "date_created", "date_last_update",
        "st_comments", "st_sub_name", "fl_starting_amount", "fl_highest_amount", "date_start_date", "date_end_date", "st_sub_title", "tbl_tenders.i_model_id"];

    $now = date('Y-m-d H:i:s');

    if ($isShowingHistory) {
        $tenders = $pdo->select($table, $fields)
            ->leftJoin("tbl_models", "tbl_models.id", "=", "tbl_tenders.i_model_id")
            ->leftJoin("tbl_finishing", "tbl_finishing.id", "=", "tbl_tenders.i_finishing_id")
            ->leftJoin("tbl_users", "tbl_users.id", "=", "tbl_tenders.i_winner_user_id")
            ->where("date_end_date", "<", $now)
            ->orderBy($table . ".id", "desc")
            ->fetch();


    } else {
        $pdo->select($table, $fields)
            ->leftJoin("tbl_models", "tbl_models.id", "=", "tbl_tenders.i_model_id")
            ->leftJoin("tbl_users", "tbl_users.id", "=", "tbl_tenders.i_winner_user_id")
            ->leftJoin("tbl_finishing", "tbl_finishing.id", "=", "tbl_tenders.i_finishing_id");

        if ($isAdmin) {
            $pdo->where("date_end_date", ">=", $now);
        } else {
            $pdo->where("date_end_date", ">=", $now);
            //->where("i_winner_user_id","=",$_SESSION['user']['id']);
        }

        $tenders = $pdo->orderBy($table . ".id", "desc")->fetch();
    }



// create response

    $response = new stdClass();
    $response->data = [];

    if ($tenders) {
        foreach ($tenders as $tender) {
            $tenderObject = new stdClass();
            if($tender["bool_canceled"]){
                $tenderObject->url = '<p class="alert alert-danger">המכרז בוטל</p>';
            }else {
                $tenderObject->url = '<a href="tender.php?tenderId=' . $tender["id"] . '">עבור למכרז</a>';
            }
            $tenderObject->id = strval($tender["id"]);
            $tenderObject->name = $tender["st_name"];
            $tenderObject->subName = $tender["st_sub_name"];
            $tenderObject->modelName = $tender["modelName"];
            $tenderObject->finishingName = $tender["finishingName"];
            $tenderObject->productionYear = strval($tender["st_production_year"]);
            $tenderObject->classification = "";
            if (isset($classification[$tender["i_classification"]])) {
                $tenderObject->classification = $classification[$tender["i_classification"]];
            }
            $tenderObject->startDate = date('d/m/Y', strtotime($tender['date_start_date']));

            $end = strtotime($tender['date_end_date']); // or your date as well
            $start = strtotime($tender['date_start_date']);
            $datediff = $end - $start;
            $daysBetween = round($datediff / (60 * 60 * 24));

            $tenderObject->daysBetween = $daysBetween;
            $tenderObject->winner = $tender["i_winner_user_id"];
            $tenderObject->endDate = date('d/m/Y', strtotime($tender['date_end_date']));
            $tenderObject->highestAmount = getPrice($tender["fl_highest_amount"]);
            $tenderObject->empty = "";

            if (strtotime($tender['date_start_date']) > strtotime('now')) {
                $tenderObject->admin = "true";
            } else {
                $tenderObject->admin = "false";
            }

            if ($tenderObject->winner == $_SESSION['user']['id']) {
                $tenderObject->won = "true";
            } else {
                $tenderObject->won = "false";
            }

            $tenderObject->startingAmount = getPrice($tender["fl_starting_amount"]);

            if($tender["friendlyName"]){
                $tenderObject->winnerName = $tender["friendlyName"];
            }else{
                $tenderObject->winnerName = $tender["username"];
            }



            $response->data[] = $tenderObject;

        }
    }

    echo json_encode($response);

}
catch (Exception $ex){
    var_dump($ex);
}
