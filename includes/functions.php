<?php
require_once CLASSES_DIR . '/DataManagerPDO.php';
if (!defined('Access')) {
	die('Silence is gold');
}

define('APPLE_API_KEY','sdgf');
define('GOOGLE_API_KEY','AAAAexGucR8:APA91bGOY1HddSFvlz0LC7AgYai1UQ0eXymvqbVSFl3t6LNjjl3S8bqQxP2t4snUOkAYMqjI46cHqcZxppC7I-fJh9Mn-AB7hSmzeSj-iB5eVquwPK0ixRqIOeEi260oqT8baZl_Ct1m');



function resizeImageToSixHundred($file){
    list($width, $height) = getimagesize($file);
    if($width<$height){
        // width = 600
        resize_image($file,600,$height);
    }else{
        // height = 600
        resize_image($file,$width,600);
    }
}

function resize_image($file, $w, $h, $crop=FALSE) {
    list($width, $height) = getimagesize($file);
    $r = $width / $height;
    if ($crop) {
        if ($width > $height) {
            $width = ceil($width-($width*abs($r-$w/$h)));
        } else {
            $height = ceil($height-($height*abs($r-$w/$h)));
        }
        $newwidth = $w;
        $newheight = $h;
    } else {
        if ($w/$h > $r) {
            $newwidth = $h*$r;
            $newheight = $h;
        } else {
            $newheight = $w/$r;
            $newwidth = $w;
        }
    }
    $src = imagecreatefromjpeg($file);
    $dst = imagecreatetruecolor($newwidth, $newheight);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

    return $dst;
}

function updateNumberOfTenderGroupUsers($groupId){
    $pdo = new DataManagerPDO();
    $count = $pdo->select("tbl_group_users",["count(i_group_id) as count"])
        ->where("i_group_id","=",$groupId,'AND')
        ->whereConstant("i_type","=",2)
        ->fetch();
    if(!$count){
        $count = 0;
    }
    $countOfUsersMustComment = $count[0]['count'];

    // update tenders
    $pdo->update("tbl_tenders",["i_amount_must_comment"],[$countOfUsersMustComment])
        ->where("i_group_id","=",$groupId)
        ->execute();
}

/*
 * checks if all parameters in $parameters array are valid
 */
function isParametersSet($parameters){
    foreach($parameters as $parameter){
        if(!isset($parameter)){
            return false;
        }
        if(is_null($parameter)){
            return false;
        }
        if(!$parameter){
            return false;
        }
    }
    return true;
}



/**
 * @return mixed|string Return real user ip with redirection and proxy
 */
function getRealUserIp() {

    if ( array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ) {

        if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') > 0) {
            $addr = explode(",",$_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($addr[0]);

        } else {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }

    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

/**
 * Ajax secure block
 */

/**
 * @return string
 * @throws Exception
 */
function setupAjaxToken() {
	$token = bin2hex(random_bytes(8));
	$_SESSION['ajax_token'] = $token;

	return $token;
}


/**
 * send styled mail
 */
function sendMail($emails,$subject,$msg,$url=SITE_URL,$urlText = "",$tenderId=0){
    if(!is_array($emails)){
        $emails = array($emails);
    }
    foreach($emails as $email) {
        $to = $email;

        $subject = "מערכת מכרזים - ".$subject;

        $headers = "From: $serverMail" . "\r\n";
        $headers .= "Reply-To:  $serverMail" . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";


        $message = <<<MAIL
<!doctype html>
<html dir="rtl">
  <head>
    <meta name="viewport" content="width=device-width">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Simple Transactional Email</title>
    <style>
    /* -------------------------------------
        INLINED WITH htmlemail.io/inline
    ------------------------------------- */
    /* -------------------------------------
        RESPONSIVE AND MOBILE FRIENDLY STYLES
    ------------------------------------- */
    @media only screen and (max-width: 620px) {
      table[class=body] h1 {
        font-size: 28px !important;
        margin-bottom: 10px !important;
      }
      table[class=body] p,
            table[class=body] ul,
            table[class=body] ol,
            table[class=body] td,
            table[class=body] span,
            table[class=body] a {
        font-size: 16px !important;
      }
      table[class=body] .wrapper,
            table[class=body] .article {
        padding: 10px !important;
      }
      table[class=body] .content {
        padding: 0 !important;
      }
      table[class=body] .container {
        padding: 0 !important;
        width: 100% !important;
      }
      table[class=body] .main {
        border-left-width: 0 !important;
        border-radius: 0 !important;
        border-right-width: 0 !important;
      }
      table[class=body] .btn table {
        width: 100% !important;
      }
      table[class=body] .btn a {
        width: 100% !important;
      }
      table[class=body] .img-responsive {
        height: auto !important;
        max-width: 100% !important;
        width: auto !important;
      }
    }

    /* -------------------------------------
        PRESERVE THESE STYLES IN THE HEAD
    ------------------------------------- */
    @media all {
      .ExternalClass {
        width: 100%;
      }
      .ExternalClass,
            .ExternalClass p,
            .ExternalClass span,
            .ExternalClass font,
            .ExternalClass td,
            .ExternalClass div {
        line-height: 100%;
      }
      .apple-link a {
        color: inherit !important;
        font-family: inherit !important;
        font-size: inherit !important;
        font-weight: inherit !important;
        line-height: inherit !important;
        text-decoration: none !important;
      }
      #MessageViewBody a {
        color: inherit;
        text-decoration: none;
        font-size: inherit;
        font-family: inherit;
        font-weight: inherit;
        line-height: inherit;
      }
      .btn-primary table td:hover {
        background-color: #34495e !important;
      }
      .btn-primary a:hover {
        background-color: #34495e !important;
        border-color: #34495e !important;
      }
    }
    </style>
  </head>
  <body class="" style="text-align:center;background-color: #f6f6f6; font-family: sans-serif; -webkit-font-smoothing: antialiased; font-size: 14px; line-height: 1.4; margin: 0; padding: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;">
    <center>
    <table border="0" cellpadding="0" cellspacing="0" class="body" style="test-align:center; border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; background-color: #f6f6f6;">
      <tr>
        <td style="font-family: sans-serif; font-size: 14px; vertical-align: top;">&nbsp;</td>
        <td class="container" style="font-family: sans-serif; font-size: 14px; vertical-align: top; display: block; Margin: 0 auto; max-width: 580px; padding: 10px; width: 580px;">
          <div class="content" style="box-sizing: border-box; display: block; Margin: 0 auto; max-width: 580px; padding: 10px;">

            <!-- START CENTERED WHITE CONTAINER -->
            <span class="preheader" style="color: transparent; display: none; height: 0; max-height: 0; max-width: 0; opacity: 0; overflow: hidden; mso-hide: all; visibility: hidden; width: 0;">מערכת מכרזים.</span>
            <table class="main" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; background: #ffffff; border-radius: 3px;">

              <!-- START MAIN CONTENT AREA -->
              <tr>
                <td class="wrapper" style="font-family: sans-serif; font-size: 14px; vertical-align: top; box-sizing: border-box; padding: 20px;">
                  <table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;">
                    <tr> 
                        <td align="center">
                            <img src="https://i.pinimg.com/originals/13/ab/5f/13ab5fe6c536ff7125432ece35b8b217.png  " style="width:100px; height:100px;"/>
                        </td>
                    </tr>
                    <tr style="text-align: center">
                      <td align="center" dir="rtl" style="font-family: sans-serif; font-size: 14px; vertical-align: top;">
                        $msg
                        <table  border="0" cellpadding="0" cellspacing="0" class="btn btn-primary" style="margin-right:auto; margin-left:auto; border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; box-sizing: border-box;">
                          <tbody>
                            <tr>
                              <td align="right" dir="rtl" style="font-family: sans-serif; font-size: 14px; vertical-align: top; padding-bottom: 15px;">
                              <br>
                                <table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: auto;">
                                  <tbody>
                                    
                                  </tbody>
                                </table>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </td>
                    </tr>
                    <tr align="center" style="text-align: center; margin-right:auto; margin-left:auto; cursor:pointer; " onclick="window.location.href='$url'">
                              <td align="center"  style=" width:auto; margin-right:auto; margin-left:auto; font-family: sans-serif; font-size: 14px; vertical-align: top; background-color: #ce152c; border-radius: 5px; text-align: center;"> <a align="center" href="$url" target="_blank" style="display: inline-block; color: #ffffff; background-color: #ce152c; border: solid 1px #ce152c; border-radius: 5px; box-sizing: border-box; cursor: pointer; text-decoration: none; font-size: 14px; font-weight: bold; margin: 0; padding: 12px 25px; text-transform: capitalize; border-color: #ce152c;">עבור למכרז</a> </td>
                            </tr>
                  </table>
                </td>
              </tr>

            <!-- END MAIN CONTENT AREA -->
            </table>

            <!-- START FOOTER -->
            <div class="footer" style="clear: both; Margin-top: 10px; text-align: center; width: 100%;">
              <table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;">
                <tr>
                  <td class="content-block powered-by" style="font-family: sans-serif; vertical-align: top; padding-bottom: 10px; padding-top: 10px; font-size: 12px; color: #999999; text-align: center;">
                  מערכת המכרזים - יוניו מוטורס. המערכת נבנתה על ידי פי.די.איי.סי טכנולוגיות בע"מ  <br>
  
                  </td>
                </tr>
              </table>
            </div>
            <!-- END FOOTER -->

          <!-- END CENTERED WHITE CONTAINER -->
          </div>
        </td>
        <td style="font-family: sans-serif; font-size: 14px; vertical-align: top;">&nbsp;</td>
      </tr>
    </table>
    </center>
  </body>
</html>
MAIL;

        mail($to, $subject, $message, $headers);


        // log
        $pdo = new DataManagerPDO();
        $now = date('Y-m-d H:i:s');
        $pdo->insert("tbl_log",
            ["i_type","st_subject","st_message","st_receiver","i_status","date_created","i_tender_id"],
            [LOG_EMAIL,$subject,$msg,$to,1,$now,$tenderId])
            ->execute();
    }
}

/**
 * returns response object
 * response->status : true/false
 * response->balance : balance of the account
 * @return mixed|stdClass
 * @throws SoapFault
 */
function checkSmsBalance(){
    $client = new SoapClient(SMS_API_URL);

    // Set request params
    $params = array(
        "strUserName" => SMS_API_USERNAME,
        "password" => SMS_API_PASSWORD
    );


    // Invoke WS method (Function1) with the request params
    $response = $client->__soapCall("CheckCredit", array($params));
    // Print WS response
    $data = $response->CheckCreditResult->any;
    $xml = simplexml_load_string($data);
    $data = $xml->CheckCreditResult;


    $response = new stdClass();
    if($data < 0){
        $response->status = RESULT_ERROR;
        $response->message = "";
        switch($data){
            case -1;
                $response->message = "שגיאה בפעולה";
                break;
            case -2;
                $response->message = "שגיאה בהתחברות לשרת Soprano";
                break;
            case -3;
                $response->message = "שגיאה בפרטי הגישה";
                break;
            case -4;
                $response->message = "שגיאה כללית";
                break;
        }
    }
    else{
        $response->status = RESULT_SUCCESS;
        $response->balance = $data;
    }

    return $response;
}

/**
 * send sms with rest api
 * @param $toNumber
 * @param $content
 * @param int $tenderId
 * @return bool|mixed|string
 */
function sendSms($toNumber,$content,$tenderId=0){
    if(DEV_MODE){
        return true;
    }
    
    $now = date('Y-m-d H:i:s');
    $pdo = new DataManagerPDO();

    if(!is_array($toNumber)){
        $numbers = explode(",",$toNumber);
    }else{
        $numbers = $toNumber;
    }


    $data = new stdClass();
    $data->UserName = SMS_API_USERNAME;
    $data->Password = SMS_API_PASSWORD;
    $data->SenderName = "Tenders";
    $data->BodyMessage = $content;
    $data->Recipents = array();
    for($i=0; $i<sizeof($numbers); $i++){
        $recipient = new stdClass();
        $recipient->Cellphone = strval($numbers[$i]);
        $recipient->Reference = $i;
        $recipient->URID = strval($i);
        $recipient->TemplateId = "";

        array_push($data->Recipents,$recipient);
    }
    $data->Relative = 0;
    $data->RootReference = 0;
    $data->Ist2s = false;

    $data = json_encode($data);

    $ch = curl_init(SMS_REST_API_URL);

    $payload = $data;

// Attach encoded JSON string to the POST fields
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

// Set the content type to application/json
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));

// Return response instead of outputting
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Execute the POST request
    $result = curl_exec($ch);

    curl_close($ch);
    $result = json_decode($result);
    if (json_last_error() === JSON_ERROR_NONE) {
    if($result->StatusCode == 0){
        $references = $result->References; // array of phones
        $phones = array();
        foreach($references as $reference){
            $phone = $reference->Cellphone;
            array_push($phones,$phone);
        }

        foreach($numbers as $number){
            $isExist = false;
            foreach($phones as $phone){
                if($phone == $number){
                    $isExist = true;
                }
            }

            $jeepEncodedContent = jeepEmojiEncode($content);
            if($isExist == false){
                $pdo->insert("tbl_log",["i_type","st_subject","st_message","st_receiver","i_status","date_created","i_tender_id"],[LOG_SMS,"שגיאה בשליחת SMS",$jeepEncodedContent,$number,0,$now,$tenderId])->execute();
            }
            else{
                $pdo->insert("tbl_log",["i_type","st_subject","st_message","st_receiver","i_status","date_created","i_tender_id"],[LOG_SMS,"SMS נשלח בהצלחה",$jeepEncodedContent,$number,1,$now,$tenderId])->execute();
            }
        }

    }else{
        $jeepEncodedContent = jeepEmojiEncode($content);
        foreach($numbers as $number) {
            $pdo->insert("tbl_log",["i_type","st_subject","st_message","st_receiver","i_status","date_created","i_tender_id"],[LOG_SMS,$result->Description,$jeepEncodedContent,$number,0,$now,$tenderId])->execute();
        }
    }


    }else{
        $jeepEncodedContent = jeepEmojiEncode($content);
        foreach($numbers as $number) {
            $pdo->insert("tbl_log",["i_type","st_subject","st_message","st_receiver","i_status","date_created","i_tender_id"],[LOG_SMS,"הוחזר מידע לא תקין משירות הSMSים",$jeepEncodedContent,$number,0,$now,$tenderId])->execute();
        }
    }


    return $result;

}


/**
 * This function gets long url and returns short url that redirects to it
 * @param $longUrl - The url which the short url will redirect to
 * @return bool
 */
function getShortUrl($longUrl){
    $post = [
        'secretKey' => SHORT_URL_API_KEY,
        'action' => 'createShortUrl',
        'longUrl'   => $longUrl,
        'username'   => SHORT_URL_API_USERNAME,
    ];



    $cURLConnection = curl_init(SHORT_URL_API_URL);
    curl_setopt($cURLConnection, CURLOPT_POSTFIELDS, $post);
    curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($cURLConnection);
    curl_close($cURLConnection);


// do anything you want with your response
    $response = json_decode($response);


    if($response->status == RESULT_SUCCESS){
        return $response->url;
    }

    $pdo = new DataManagerPDO();
    $now = date('Y-m-d H:i:s');
    $pdo->insert("tbl_log",
        ["i_type","st_subject","st_message","st_receiver","i_status","date_created","i_tender_id"],
        [LOG_EMAIL,"שגיאה בקיצור כתובת",$response->error,'',1,$now,0])
        ->execute();

    return false;
}

/**
 * Validate Token For Secure Ajax
 * POST add 't' variable for send token
 * Its close situation like checking if its our ajax from our page
 * @return bool
 * @throws Exception
 */

function getDiffTimeString($date){
    $start_date = new DateTime(date("Y-m-d H:i:s",strtotime($date)));
    $since_start = $start_date->diff(new DateTime(date("Y-m-d H:i:s")));
    $timeString = "לפני ";
    if($since_start->m > 0){
        return date('H:i:s d/m/Y',strtotime($date));
    }
    else{
        $justMinutes = true;
        $justSeconds = true;
        if($since_start->d > 0){
            $justMinutes = false;
            $justSeconds = false;
            if($since_start->d == 1){
                $timeString .= ' יום, ';
            }
            else{
                $timeString .= $since_start->d.' ימים,';
            }
        }
        if($since_start->h > 0){
            $justMinutes = false;
            $justSeconds = false;
            if($since_start->h == 1){
                $timeString .= ' שעה ';
            }
            else {
                $timeString .= ' ' . $since_start->h . ' שעות';
            }
        }
        if($since_start->i > 1){
            $justSeconds = false;
            if($justMinutes){
                $timeString .= $since_start->i.' דקות';
            }
            else{
                $timeString .= ' ו-'.$since_start->i.' דקות';
            }
        }
        if($justSeconds){
            $timeString .= $since_start->s.' שניות';
        }
        /*
        if($since_start->s > 0){
            $timeString .= $since_start->s.' שניות';
        }
        */


    }
    return $timeString;
}

function isValidToken() {

	if ( SECURE_AJAX ) {

        if ( empty( $_SESSION['ajax_token'] ) ) {
            $_SESSION['ajax_token'] = bin2hex( random_bytes( 8 ) );
        }

        $headers = apache_request_headers();
        if ( isset( $headers['T'] ) ) {

            if ( $headers['T'] === $_SESSION['ajax_token'] ) {
                return true;
            }

        } else {
            return false;
        }

        return false;

    } else {
        return true;
    }
}

/**
 *  Number formation with Shekel currency
 * @param $price - price
 * @return int|string
 */
function getPrice($price) {
    if ($price==0) return 0;
    return number_format($price).' ₪';
}

/**
 * Encode jeep emoji for mysql databases support
 */
function jeepEmojiEncode($string){
    $jeepEmoji = "🚙";
    $bbCode = "[*Jeep*]";

    return str_replace($jeepEmoji,$bbCode,$string);
}
/**
 * Decode jeep emoji for mysql databases support
 */
function jeepEmojiDecode($string){
    $jeepEmoji = "🚙";
    $bbCode = "[*Jeep*]";

    return str_replace($bbCode,$jeepEmoji,$string);
}