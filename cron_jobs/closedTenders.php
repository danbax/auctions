<?php
/****
 * send message to winner and losers for recently closed tenders
 */
include_once('../includes/config.php');
include_once('../includes/functions.php');
include_once('../classes/DataManagerPDO.php');

$pdo = new DataManagerPDO();

/**
 * select all user bids for tenders that should be closed and have not been closed yet
 * select distinct users for each tender
 * @date_end_date = the date the tender should be closed at
 * @bool_closed = is the tender closed
 */
$table = "tbl_user_bids";
$fields = ["tbl_user_bids.i_user_id", "tbl_user_bids.i_tender_id", "tbl_user_bids.fl_bid",
    "tbl_tenders.st_name as tenderName","tbl_tenders.fl_highest_amount","tbl_tenders.i_winner_user_id"];


try {
    $userBids = $pdo->select("tbl_user_bids", $fields)
        ->whereConstant("tbl_tenders.date_end_date", "<=", 'now()', 'AND')
        ->whereConstant("bool_closed", "=", 0)
        ->leftJoin("tbl_tenders","tbl_tenders.id","=","tbl_user_bids.i_tender_id")
        ->orderBy("i_tender_id", "desc")
        ->groupBy("tbl_user_bids.i_user_id,tbl_user_bids.i_tender_id")
        ->fetch();
}catch (Exception $ex){
    /** if exception have been thrown by the PDO object show the Exception */
    echo $ex->getMessage();
}

if($userBids) {
    foreach ($userBids as $userBid) {
        $userId = $userBid["i_user_id"];
        $url = SITE_URL . "tender.php?tenderId=" . $userBid["i_tender_id"];

        if ($userBid["i_user_id"] == $userBid["i_winner_user_id"]) {
            // This user won the tender
            $message = "נצחת במכרז " . $userBid["tenderName"];
        } else {
            // This user lost the tender
            $message = "תודה על השתתפותך במכרז - הודעת זכיה הועברה לגורם אשר הצעתו הטובה ביותר";
        }

        // send message to user
        $messageSent = sendMessageToUser($userId, $message, $message, $url, $userBid["i_tender_id"]);
        if(!$messageSent){
            echo "Message was not sent for user #".$userId;
        }
    }
}

/*** close tenders */
$pdo->update("tbl_tenders",["bool_closed"],[1])
    ->whereConstant("tbl_tenders.date_end_date", "<=", 'now()')
    ->execute();


/**
 * Sends email and sms to user
 * @param $userId - the unique user id the message should be sent to
 * @param $subject - the subject of the mail
 * @param $message - the message
 * @param string $url - url we should link to (will be included in the mail as a button)
 * @param int $tenderId - the tender id that message associated with (for the log)
 * @return bool
 */
function sendMessageToUser($userId,$subject,$message,$url="",$tenderId=0){
    $pdo = new DataManagerPDO();

    // get user phone & mail
    $userData = $pdo->select("tbl_users",["id","st_phone as phone","st_email as email"])->where("id","=",$userId)->groupBy("id")->fetch();

    // if there is a problem return false
    if(!$userData){
        return false;
    }
    $phone = $userData[0]["phone"];
    $email = $userData[0]["email"];
    if($url){
        sendMail($email,$subject,$message,$url,"עבור למכרז",$tenderId);
    }
    else{
        sendMail($email,$subject,$message,"עבור למכרז",$tenderId);
    }

    sendSms($phone,$message);

    return true;
}