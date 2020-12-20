<?php
/****
 * send message to winner and losers for recently closed tenders
 */
include_once('../includes/config.php');
include_once('../includes/functions.php');
include_once('../classes/DataManagerPDO.php');


$pdo = new DataManagerPDO();
/** @var  $tenderNames - array of tenders with TenderID as the key and the tenderName as the value */
$tenderNames = [];
/** @var  $tenderHighest - array of tenders with TenderID as the key and the tender highest suggestion as the value */
$tenderHighest = [];

$users = $pdo->select("tbl_users",["id","st_phone","st_email"])->fetch();

$twoHours = date("Y-m-d H:i:s", strtotime('+2 hours'));
$fifteenMinutes = date("Y-m-d H:i:s", strtotime('+15 minutes'));
$today = date('Y-m-d');

try {

// 2 hours
    $tenders = $pdo->select("tbl_tenders", ["id", "st_name", "date_start_date"])
        ->where("date_start_date", "<=", $twoHours, 'AND')
        ->whereConstant("bool_opened_mail", "=", 0,'and')
        ->whereConstant("bool_closed", "=", 0)->fetch();


    foreach ($tenders as $tender) {
        $userPhones = array();
        $url = SITE_URL . "tender.php?tenderId=" . $tender["id"];
        $fullUrl  =$url;
        if($shortUrl = getShortUrl($url)){
            $url = $shortUrl;
            $shortUrl = urlencode($shortUrl);
            $pdo->update("tbl_tenders",["st_short_url"],[$shortUrl])->where("id","=",$tender["id"])->execute();
        }
        $smsSubject = "";
        foreach ($users as $user) {
            $subject = "מכרז חדש נפתח.יתחיל בשעה " . date('H:i', strtotime($tender["date_start_date"]));
            sendMail($user["st_email"], $subject, $subject, $fullUrl,"עבור למכרז ".$tender["st_name"],$tender["id"]);
            $smsSubject = $subject;
            $smsSubject .= ' '.$url;
            if($user["st_phone"])
                array_push($userPhones,$user["st_phone"]);
        }
        $sms = sendSms($userPhones, $smsSubject,$tender["id"]);

    }

    $tenders = $pdo->update("tbl_tenders", ["bool_opened_mail"], [1])
        ->where("date_start_date", "<=", $twoHours, 'AND')
        ->where("bool_opened_mail", "=", 0)->execute();



}catch (Exception $ex){
    var_dump($ex);
}

// send email and sms to user
function sendMessageToUser($userId,$subject,$message,$url=""){
    $pdo = new DataManagerPDO();
    $userData = $pdo->select("tbl_users",["id","st_phone as phone","st_email as email"])->where("id","=",$userId)->fetch();
    if(!$userData){
        return false;
    }
    $phone = $userData[0]["phone"];
    $email = $userData[0]["email"];
    if($url){
        sendMail($email,$subject,$message,$url);
        $message .= ' '.$url;
        //sendSms($phone,$message);
    }
    else{
        sendMail($email,$subject,$message);
        //sendSms($phone,$message);
    }
    return true;
}
