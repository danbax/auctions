<?php
require_once 'includes/config.php';
require_once INCLUDES_DIR . '/functions.php';
require_once CLASSES_DIR . '/DataManager.php';
require_once CLASSES_DIR . '/DataManagerPDO.php';
require_once CLASSES_DIR . '/User.php';
require_once INCLUDES_DIR . '/statuses.php';


$action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING);

// initialize response json class
$response = new stdClass();
$response->data = array();
$response->timestamp = strtotime("now");
$response->message = "";

// init related entities for log
$myUserId = 0;
if(isset($_SESSION['user'])){
    $myUserId = $_SESSION['user']['id'];
}

// related entities object saves all the data we should save in log
$relatedEntities = new stdClass();


if (!isset($action)) {
        $response->status = RESULT_ERROR;
        $response->errorCode  = 2;
        $response->error = $errors[$response->errorCode];
	    sendResponse($response);
}
else{
    $pdo = new DataManagerPDO();
}


switch ($action) {
    /*
     * login to back office action
     * @email - email of the user
     * @password - password of the user
     */
    case 'login' :
        /*
         * input: email, password, uuid
         */
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = filter_input(INPUT_POST, 'password', FILTER_DEFAULT);
        $uuid = filter_input(INPUT_SERVER, 'HTTP_USER_AGENT',FILTER_DEFAULT);
        
        // check if the parameters are valid
        $parameters = array();
        array_push($parameters,$email,$password);
        
        if (!(isParametersSet($parameters)))  {
                $response->status = RESULT_ERROR;
                $response->errorCode  = 3;
                $response->message = $errors[$response->errorCode];
        } else {

                $user = new User();
                if ( $user->login( $email, $password) ) {
                    $user = $user->getUser();
                        $response->status = RESULT_SUCCESS;
                        $response->message = 'succefully logged in';
                        
                            /*
                             * update date last login
                             */

                            $currentDate = date('Y-m-d H:i:s');
                            $pdo = new DataManagerPDO();
                            $table = "tbl_users";
                            $fields = ['date_last_login'];
                            $data = [$currentDate];

                            $update = $pdo->update($table, $fields, $data)->where('id', '=', $user["id"])->execute();
                            
                            $response->backOffficeAllowed  = true;
                            $response->clientType  = 1; // admin
                            $myUserId = $user["id"];
                           // $_SESSION['user']['credentialsToken'] = $user["token"];
                            $_SESSION['user']['name'] = $user["st_username"];

                            $response->url = "tenders.php";
                            if(isset($_SESSION['lastTenderId']) && $_SESSION['lastTenderId'] && is_numeric($_SESSION['lastTenderId'])){
                                $response->url = "tender.php?tenderId=".$_SESSION['lastTenderId'];
                            }


                } else {
                        
                        $response->status = RESULT_ERROR;
                        $response->errorCode  = 6;
                        $response->message  = $user->getError();
                }
        }

        sendResponse($response);
        break;
    /*
     * delete user by id
     * @id - id of the user
     */
    case 'deleteUser':
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if (!isset($id)) {
            $response->status = RESULT_ERROR;
            $response->errorCode  = 3;
            $response->message = $errors[$response->errorCode];
        } else {
            $pdo = new DataManagerPDO();
            $notActive=0;

            $table = "tbl_users";


            $update = $pdo->delete($table)->where('id', '=', $id)->execute();

            if(!$update){
                $response->status = RESULT_ERROR;
                $response->errorCode  = 6;
                if($pdo->getError()){
                    $response->message = $pdo->getError();
                }
                else{
                    $response->message = "Database problem";
                }
            }
            else{
                $response->status = RESULT_SUCCESS;
            }
        }
        sendResponse($response);
        break;
    case 'deleteFile':
        $id = filter_input(INPUT_POST, 'fileId', FILTER_VALIDATE_INT);
        if (!isset($id)) {
            $response->status = RESULT_ERROR;
            $response->errorCode  = 3;
            $response->message = $errors[$response->errorCode];
        } else {
            $table = "tbl_files";

            $delete = $pdo->delete($table)->where('id', '=', $id)->execute();

            if(!$delete){
                $response->status = RESULT_ERROR;
                $response->errorCode  = 6;
                if($pdo->getError()){
                    $response->message = $pdo->getError();
                }
                else{
                    $response->message = "Database problem";
                }
            }
            else{
                $response->status = RESULT_SUCCESS;
            }
        }
        sendResponse($response);
        break;
    /*
     * create new user
     */
    case 'addUser' :
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
        $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
        $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
        $isAdmin = filter_input(INPUT_POST, 'isAdmin', FILTER_VALIDATE_INT);
        $friendlyName = filter_input(INPUT_POST, 'friendlyName', FILTER_SANITIZE_STRING);

        $password = hashPass($password);


        if (!(isset($email) && isset($username)&& isset($phone) && isset($isAdmin)&& isset($password))) {
            $response->status = RESULT_ERROR;
			$response->error = 'Insert all valid required fields.';
		} else {

		    $table = "tbl_users";
            $fields = ["st_username","st_email","st_phone","bool_is_admin","st_password","st_friendly_name"];
            $data = [$username,$email,$phone,$isAdmin,$password,$friendlyName];

		    if($id = $pdo->insert($table,$fields,$data)->execute()){
				$response->status = RESULT_SUCCESS;
				$response->error  = '';

				$user = $pdo->select($table,$fields)->where("id","=",$id)->fetch();

				$response->data = $user;

			} else {
				$response->status = RESULT_ERROR;
				$response->error  = $pdo->getError();
			}
		}

		sendResponse($response);
		break;
    /*
     * update existing user
     */
    case 'updateUser' :
		// TODO Add permission to add user

		$id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
        $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
        $isAdmin = filter_input(INPUT_POST, 'isAdmin', FILTER_VALIDATE_INT);
        $friendlyName = filter_input(INPUT_POST, 'friendlyName', FILTER_SANITIZE_STRING);



        if (!(isset($email) && isset($username)&& isset($id)&& isset($phone) && isset($isAdmin))) {
			$response->status = RESULT_ERROR;
            $response->errorCode  = 3;
            $response->error = $errors[$response->errorCode];

		} else {

            $table = "tbl_users";
            $fields = ["st_username","st_email","st_phone","bool_is_admin","st_friendly_name"];
            $data = [$username,$email,$phone,$isAdmin,$friendlyName];


			if ( $update = $pdo->update($table,$fields,$data)->where("id","=",$id)->execute()){

				$response->status = RESULT_SUCCESS;
				$response->error  = '';

                $user = $pdo->select($table,$fields)->where("id","=",$id)->fetch();

				$response->data = $user;

			} else {
				$response->status = RESULT_ERROR;
				$response->error  = $pdo->getError();
			}
		}

		sendResponse($response);
		break;
    /*
     * get user
     */
    case 'getUser' :
        // TODO Add permission to get a user
        $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);

        if (!isset($id)) {
            $response->status = RESULT_ERROR;
            $response->errorCode  = 3;
            $response->error = $errors[$response->errorCode];

        } else {

            $table = "tbl_users";
            $fields = ["id","st_username","st_email","st_phone","datetime_last_login","bool_is_admin","st_friendly_name"];

            if ($user = $pdo->select($table,$fields)->where("id","=",$id)->fetch()) {

                $response->status = RESULT_SUCCESS;
                $response->error  = '';
                $response->data = $user[0];

            } else {
                $response->status = RESULT_ERROR;
                $response->error  = $pdo->getError();
            }
        }

        sendResponse($response);
        break;



    case 'getTender':
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if (!isset($id)) {
            $response->status = RESULT_ERROR;
            $response->errorCode  = 3;
            $response->message = $errors[$response->errorCode];
        } else {
            $pdo = new DataManagerPDO();
            $notActive=0;

            $table = "tbl_tenders";
            $fields = ["tbl_tenders.id","tbl_tenders.st_name as tenderName","tbl_tenders.st_description","tbl_tenderers.st_name as st_publisher",
                "tbl_tenders.date_last_update","tbl_tenders.date_deadline","tbl_tenders.i_group_id",
                "tbl_tenders.i_status","tbl_tenders.i_user_id","tbl_tenders.st_comments",
                "tbl_users.st_username as userNickname","tbl_tenders.st_name as tenderName","tbl_tenders.i_status as tenderStatus"];

            $tenders = $pdo->select($table, $fields)
                ->where('tbl_tenders.id',"=",$id)
                ->leftJoin("tbl_users","tbl_users.id","=","tbl_tenders.i_user_id")
                ->leftJoin("tbl_groups","tbl_groups.id","=","tbl_tenders.i_group_id")
                ->leftJoin("tbl_tenderers","tbl_tenderers.id","=","tbl_tenders.i_tender_id")
                ->fetch();

            // get files
            $table = "tbl_files";
            $fields = ["st_file_name","st_file_original_name","date_uploaded","i_tender_id"];
            $files = $pdo->select($table, $fields)->where("i_tender_id","=",$id)->fetch();

            // get comments
            $table = "tbl_tenders_comments,tbl_users";
            $fields = ["tbl_tenders_comments.id","st_comment","tbl_tenders_comments.date_created","tbl_users.st_username as nickname"];
            $comments = $pdo->select($table, $fields)->where("i_tender_id","=",$id,'AND')
                ->whereConstant("tbl_users.id","=","tbl_tenders_comments.i_user_id")->fetch();

            // get group users
            $table = "tbl_groups_users,tbl_users";
            $fields = ["tbl_groups_users.i_type as type","tbl_users.id","tbl_users.st_username as nickname"];
            $groupUsers = $pdo->select($table, $fields)->where("i_group_id","=",$tenders[0]["i_group_id"],"AND")
                ->whereConstant("tbl_groups_users.i_user_id","=","tbl_users.id")
                ->orderBy("i_type","desc")->fetch();

            $tenders[0]["isMustApprove"] = false;
            if($groupUsers){
                foreach($groupUsers as $groupUser){
                    if($groupUser["id"] == $_SESSION['user']['id']){
                        $tenders[0]["isMustApprove"] = true;
                    }
                }
            }


            if(!$tenders){
                $response->status = RESULT_ERROR;
                $response->errorCode  = 6;
                if($pdo->getError()){
                    $response->message = $pdo->getError();
                }
                else{
                    $response->message = "Database problem";
                }
            }
            else{
                $response->status = RESULT_SUCCESS;
                $tender = $tenders[0];
                $tender["date_last_update"] = date('H:i:s d/m/Y',strtotime($tender["date_last_update"]));
                $tender["date_deadline"] = date('H:i:s d/m/Y',strtotime($tender["date_deadline"]));
                $tender["groupUsers"] = $groupUsers;
                $response->tender = $tender;
                $response->comments = $comments;

                // get files
                $response->files = array();
                if($files){
                    $response->files = $files;
                }
            }
        }
        sendResponse($response);
        break;
    case 'addTender' :

        $userId = $_SESSION['user']['id'];

        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $subName = filter_input(INPUT_POST, 'subName', FILTER_SANITIZE_STRING);
        $startingAmount = filter_input(INPUT_POST, 'startingAmount', FILTER_VALIDATE_FLOAT);

        $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
        $comments = filter_input(INPUT_POST, 'comments', FILTER_SANITIZE_STRING);

        $dateStart = str_replace("/","-",$_POST['dateStart']);
        $dateStart = date('Y-m-d H:i:s',strtotime($dateStart));

        $dateEnd = str_replace("/","-",$_POST['dateEnd']);
        $dateEnd = date('Y-m-d H:i:s',strtotime($dateEnd));

        $minIncrease = filter_input(INPUT_POST, 'minIncrease', FILTER_VALIDATE_INT);
        $classafication = filter_input(INPUT_POST, 'classafication', FILTER_VALIDATE_INT);
        $licensing = filter_input(INPUT_POST, 'licensing', FILTER_SANITIZE_STRING);
        $productionDate = filter_input(INPUT_POST, 'productionDate', FILTER_VALIDATE_INT);
        $model = filter_input(INPUT_POST, 'model', FILTER_VALIDATE_INT);

        $finishing = filter_input(INPUT_POST, 'finishing', FILTER_VALIDATE_INT);

        if(strtotime($dateStart) < strtotime('now')){
            $response->status = RESULT_ERROR;
            $response->error = 'תאריך ההתחלה עבר כבר';
        }else {

            if (strtotime($dateEnd) < strtotime($dateStart)) {
                $response->status = RESULT_ERROR;
                $response->error = 'תאריך ההתחלה לא יכול להיות גדול מתאריך הסיום';
            } else {
                if (!($finishing && $name  && $startingAmount && $dateStart && $dateEnd
                    && $minIncrease && $classafication && $licensing && $productionDate && $model)) {
                    $response->status = RESULT_ERROR;
                    $response->error = 'Insert all valid required fields.';
                } else {

                    // add tender
                    $table = "tbl_tenders";
                    $fields = ["st_name", "st_description", "date_created", "date_last_update", "st_comments", "st_sub_name", "fl_starting_amount",
                        "fl_highest_amount", "date_start_date", "date_end_date",
                        "st_production_year", "i_model_id", "i_finishing_id", "st_licensing", "i_classification", "i_min_increase"];

                    $now = date('Y-m-d H:i:s');

                    $data = [$name, $description, $now, $now, $comments, $subName, $startingAmount, $startingAmount, $dateStart, $dateEnd,
                        $productionDate, $model, $finishing, $licensing, $classafication, $minIncrease];

                    $id = $pdo->insert($table, $fields, $data)->execute();

                    if ($id) {
                        $response->status = RESULT_SUCCESS;
                        $response->error = '';
                        $response->tenderId = $id;

                    } else {
                        $response->status = RESULT_ERROR;
                        $response->error = $pdo->getError();
                    }
                }

            }
        }
        sendResponse($response);
        break;
    case 'addComment' :

        $userId = $_SESSION['user']['id'];
        $comment = filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_STRING);
        $tenderId = filter_input(INPUT_POST, 'tenderId', FILTER_VALIDATE_INT);
        $createdDate = date('Y-m-d H:i:s');

        if (!($comment  && $createdDate && $userId && $tenderId )) {
            $response->status = RESULT_ERROR;
            $response->error = 'Insert all valid required fields.';
        } else {

            $table = "tbl_tenders_comments";
            $fields = ["i_tender_id","i_user_id","st_comment","date_created"];
            $data = [$tenderId,$userId,$comment,$createdDate];

            if($id = $pdo->insert($table,$fields,$data)->execute()){
                $response->status = RESULT_SUCCESS;
                $response->error  = '';

                // get comments
                $table = "tbl_tenders_comments,tbl_users";
                $fields = ["tbl_tenders_comments.id","st_comment","tbl_tenders_comments.date_created","tbl_users.st_username as nickname"];
                $comment = $pdo->select($table, $fields)
                    ->where("tbl_tenders_comments.id","=",$id,'AND')
                    ->whereConstant("tbl_users.id","=","tbl_tenders_comments.i_user_id")->fetch();


                $response->comment = $comment;

            } else {
                $response->status = RESULT_ERROR;
                $response->error  = $pdo->getError();
            }
        }

        sendResponse($response);
        break;
    case 'updateTender' :

        $userId = $_SESSION['user']['id'];
        $tenderId = filter_input(INPUT_POST, 'tenderId', FILTER_VALIDATE_INT);

        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $subName = filter_input(INPUT_POST, 'subName', FILTER_SANITIZE_STRING);
        $startingAmount = filter_input(INPUT_POST, 'startingAmount', FILTER_VALIDATE_FLOAT);

        $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
        $comments = filter_input(INPUT_POST, 'comments', FILTER_SANITIZE_STRING);

        $dateStart = str_replace("/","-",$_POST['dateStart']);
        $dateStart = date('Y-m-d H:i:s',strtotime($dateStart));

        $dateEnd = str_replace("/","-",$_POST['dateEnd']);
        $dateEnd = date('Y-m-d H:i:s',strtotime($dateEnd));

        $minIncrease = filter_input(INPUT_POST, 'minIncrease', FILTER_VALIDATE_INT);
        $classafication = filter_input(INPUT_POST, 'classafication', FILTER_VALIDATE_INT);
        $licensing = filter_input(INPUT_POST, 'licensing', FILTER_SANITIZE_STRING);
        $productionDate = filter_input(INPUT_POST, 'productionDate', FILTER_VALIDATE_INT);
        $model = filter_input(INPUT_POST, 'model', FILTER_VALIDATE_INT);
        $finishing = filter_input(INPUT_POST, 'finishing', FILTER_VALIDATE_INT);

        if (!($tenderId && $name && $startingAmount &&$dateStart &&$dateEnd
            && $minIncrease && $classafication &&$licensing &&$productionDate &&$model)) {
            $response->status = RESULT_ERROR;
            $response->error = 'Insert all valid required fields.';
        } else {

            // add tender
            $table = "tbl_tenders";
            $fields = ["st_name","st_description","date_created","date_last_update","st_comments","st_sub_name","fl_starting_amount",
                "date_start_date","date_end_date",
                "st_production_year","i_model_id","i_finishing_id","st_licensing","i_classification","i_min_increase"];

            $now = date('Y-m-d H:i:s');

            $data = [$name,$description,$now,$now,$comments,$subName,$startingAmount,$dateStart,$dateEnd,
                $productionDate,$model,$finishing,$licensing,$classafication,$minIncrease];



            $id = $pdo->update($table,$fields,$data)
                ->where("id","=",$tenderId)
                ->execute();

            if(strtotime($dateStart) > strtotime('now')){
                // update highest suggestion to start price
                $pdo->update("tbl_tenders",["fl_highest_amount"],$startingAmount)->where("id","=",$tenderId)->execute();
            }

            if($id){
                $response->status = RESULT_SUCCESS;
                $response->error  = '';
                $response->tenderId = $id;

            } else {
                $response->status = RESULT_ERROR;
                $response->error  = $pdo->getError();
            }

        }

        sendResponse($response);
        break;
    case 'updatePassword' :

        $userId = filter_input(INPUT_POST, 'userId', FILTER_VALIDATE_INT);
        $password = hashPass(filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING));

        if (!($userId && $password)) {
            $response->status = RESULT_ERROR;
            $response->error = 'Insert all valid required fields.';
        } else {

            $table = "tbl_users";
            $fields = ["st_password"];
            $data = [$password];

            if($pdo->update($table,$fields,$data)->where("id","=",$userId)->execute()){
                $response->status = RESULT_SUCCESS;
                $response->error  = '';

            } else {
                $response->status = RESULT_ERROR;
                $response->error  = $pdo->getError();
            }
        }

        sendResponse($response);
        break;
    case 'cancelTender' :

        $tenderId = filter_input(INPUT_POST, 'tenderId', FILTER_VALIDATE_INT);


        if (!($tenderId)) {
            $response->status = RESULT_ERROR;
            $response->error = 'Insert all valid required fields.';
        } else {


            $tenderData = $pdo->select("tbl_tenders",["date_start_date","st_name"])->where("id","=",$tenderId)->fetch();

            if(!$tenderData){
                $response->status = RESULT_ERROR;
                $response->error = 'המכרז לא קיים.';
            }else{
                $diff_time=(strtotime($tenderData[0]["date_start_date"]) - strtotime(date("Y/m/d H:i:s")))/60;
                if($diff_time < 15){
                    $response->status = RESULT_ERROR;
                    $response->error = 'לא ניתן לבטל את המכרז כיוון שנשארו פחות מרבע שעה עד שהוא יתקיים';
                }else{
                    $table = "tbl_tenders";
                    $fields = ["bool_canceled"];
                    $data = [1];

                    if($pdo->update($table,$fields,$data)->where("id","=",$tenderId)->execute()){
                        $response->status = RESULT_SUCCESS;
                        $response->error  = '';

                        /**
                         * send sms to all users
                         */

                        $users = $pdo->select("tbl_users",["id","st_phone","st_email"])->fetch();

                        try {
                            $userPhones = array();
                            $url = SITE_URL . "tender.php?tenderId=" . $tenderId;
                            $fullUrl  =$url;
                            $smsSubject = "";
                            foreach ($users as $user) {
                                $subject = "מכרז ".$tenderData[0]["st_name"].' בוטל.';
                                sendMail($user["st_email"], $subject, $subject, $fullUrl,"עבור למכרז ".$tenderData[0]["st_name"],$tenderId);
                                $smsSubject = $subject;
                                if($user["st_phone"])
                                    array_push($userPhones,$user["st_phone"]);
                            }
                            $sms = sendSms($userPhones, $smsSubject,$tenderId);



                        }catch (Exception $ex){
                            var_dump($ex);
                        }

                    } else {
                        $response->status = RESULT_ERROR;
                        $response->error  = $pdo->getError();
                    }
                }
            }
        }

        sendResponse($response);
        break;

    case 'deleteModel':
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if (!isset($id)) {
            $response->status = RESULT_ERROR;
            $response->errorCode  = 3;
            $response->message = $errors[$response->errorCode];
        } else {
            $pdo = new DataManagerPDO();
            $notActive=0;

            $table = "tbl_models";
            $update = $pdo->update($table,["i_is_active"],[0])->where('id', '=', $id)->execute();

            if(!$update){
                $response->status = RESULT_ERROR;
                $response->errorCode  = 6;
                if($pdo->getError()){
                    $response->message = $pdo->getError();
                }
                else{
                    $response->message = "Database problem";
                }
            }
            else{
                $response->status = RESULT_SUCCESS;
            }
        }
        sendResponse($response);
        break;
    case 'addModel' :
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);

        if (!(isset($name))) {
            $response->status = RESULT_ERROR;
            $response->error = 'Insert all valid required fields.';
        } else {

            $table = "tbl_models";
            $fields = ["st_name","i_is_active"];
            $data = [$name,1];

            try {
                $pdo->insert($table, $fields, $data)->execute();
                $response->status = RESULT_SUCCESS;
                $response->error = '';
            }
            catch(Exception $ex){
                $response->status = RESULT_ERROR;
                $response->error  = $ex->getMessage();
            }
        }

        sendResponse($response);
        break;
    case 'updateModel' :
        // TODO Add permission to add user

        $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);


        if (!(isset($id) && isset($name))) {
            $response->status = RESULT_ERROR;
            $response->errorCode  = 3;
            $response->error = $errors[$response->errorCode];

        } else {

            $table = "tbl_models";
            $fields = ["st_name"];
            $data = [$name];


            if ( $update = $pdo->update($table,$fields,$data)->where("id","=",$id)->execute()){

                $response->status = RESULT_SUCCESS;
                $response->error  = '';

                $user = $pdo->select($table,$fields)->where("id","=",$id)->fetch();

                $response->data = $user;

            } else {
                $response->status = RESULT_ERROR;
                $response->error  = $pdo->getError();
            }
        }

        sendResponse($response);
        break;
    case 'getModel' :
        // TODO Add permission to get a user
        $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);

        if (!isset($id)) {
            $response->status = RESULT_ERROR;
            $response->errorCode  = 3;
            $response->error = $errors[$response->errorCode];

        } else {

            $table = "tbl_models";
            $fields = ["st_name","i_is_active"];

            if ($user = $pdo->select($table,$fields)->where("id","=",$id)->fetch()) {

                $response->status = RESULT_SUCCESS;
                $response->error  = '';
                $response->data = $user[0];

            } else {
                $response->status = RESULT_ERROR;
                $response->error  = $pdo->getError();
            }
        }

        sendResponse($response);
        break;


    case 'deleteFinishing':
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if (!isset($id)) {
            $response->status = RESULT_ERROR;
            $response->errorCode  = 3;
            $response->message = $errors[$response->errorCode];
        } else {
            $pdo = new DataManagerPDO();
            $notActive=0;

            $table = "tbl_finishing";
            $update = $pdo->update($table,["i_is_active"],[0])->where('id', '=', $id)->execute();

            if(!$update){
                $response->status = RESULT_ERROR;
                $response->errorCode  = 6;
                if($pdo->getError()){
                    $response->message = $pdo->getError();
                }
                else{
                    $response->message = "Database problem";
                }
            }
            else{
                $response->status = RESULT_SUCCESS;
            }
        }
        sendResponse($response);
        break;
    case 'addFinishing' :
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $modelId = filter_input(INPUT_POST, 'modelId', FILTER_VALIDATE_INT);


        if (!(isset($name) && isset($modelId))) {
            $response->status = RESULT_ERROR;
            $response->error = 'Insert all valid required fields.';
        } else {

            $table = "tbl_finishing";
            $fields = ["st_name","i_is_active","i_model_id"];
            $data = [$name,1,$modelId];

            try {
                $pdo->insert($table, $fields, $data)->execute();
                $response->status = RESULT_SUCCESS;
                $response->error = '';
            }
            catch(Exception $ex){
                $response->status = RESULT_ERROR;
                $response->error  = $ex->getMessage();
            }
        }

        sendResponse($response);
        break;
    case 'updateFinishing' :
        // TODO Add permission to add user

        $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $modelId = filter_input(INPUT_POST, 'modelId', FILTER_SANITIZE_NUMBER_INT);


        if (!(isset($id) && isset($name)&& isset($modelId))) {
            $response->status = RESULT_ERROR;
            $response->errorCode  = 3;
            $response->error = $errors[$response->errorCode];

        } else {

            $table = "tbl_finishing";
            $fields = ["st_name","i_model_id"];
            $data = [$name,$modelId];


            if ( $update = $pdo->update($table,$fields,$data)->where("id","=",$id)->execute()){

                $response->status = RESULT_SUCCESS;
                $response->error  = '';

                $user = $pdo->select($table,$fields)->where("id","=",$id)->fetch();

                $response->data = $user;

            } else {
                $response->status = RESULT_ERROR;
                $response->error  = $pdo->getError();
            }
        }

        sendResponse($response);
        break;
    case 'getFinishing' :
        // TODO Add permission to get a user
        $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);

        if (!isset($id)) {
            $response->status = RESULT_ERROR;
            $response->errorCode  = 3;
            $response->error = $errors[$response->errorCode];

        } else {

            $table = "tbl_finishing";
            $fields = ["st_name","i_is_active","i_model_id"];

            if ($user = $pdo->select($table,$fields)->where("id","=",$id)->fetch()) {

                $response->status = RESULT_SUCCESS;
                $response->error  = '';
                $response->data = $user[0];

            } else {
                $response->status = RESULT_ERROR;
                $response->error  = $pdo->getError();
            }
        }

        sendResponse($response);
        break;


    case 'suggestBid' :

        $userId = $_SESSION['user']['id'];
        $bidAmount = filter_input(INPUT_POST, 'bidAmount', FILTER_VALIDATE_FLOAT);
        $tenderId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $now = date('Y-m-d H:i:s');


        if (!($bidAmount  && $tenderId && $userId )) {
            $response->status = RESULT_ERROR;
            $response->error = 'הכנס את כל השדות הדרושים.';
        } else {

            $tender = $pdo->select("tbl_tenders",["fl_highest_amount","i_min_increase","st_name","date_end_date"])
                ->where("id","=",$tenderId)
                ->fetch();

            $min = $tender[0]["fl_highest_amount"]+$tender[0]["i_min_increase"];
            $tenderName = $tender[0]["st_name"];
            $endDate = $tender[0]["date_end_date"];

            if(strtotime($endDate) < strtotime($now)){
                $response->status = RESULT_ERROR;
                $response->error = 'הסכום קטן מהמינימום הדרוש';

            }

            if($bidAmount < $min) {
                $response->status = RESULT_ERROR;
                $response->error = 'הסכום קטן מהמינימום הדרוש';
            }
            else {

                $previousUserId = 0;
                //if($userId != $previousUserId){
                $previousUser = $pdo->select("tbl_user_bids",["i_user_id"])->where("i_tender_id","=",$tenderId)
                    ->orderBy("fl_bid","desc")->limit(1)->fetch();

                if($previousUser){
                    $previousUserId = $previousUser[0]["i_user_id"];
                }

                $userEmail = "";
                $userPhone = "";
                $user = $pdo->select("tbl_users",["st_email","st_phone"])->where("id","=",$previousUserId)->fetch();
                if($user){
                    $userEmail = $user[0]["st_email"];
                    $userPhone = $user[0]["st_phone"];
                }

                $insert = $pdo->insert("tbl_user_bids",["i_user_id","i_tender_id","fl_bid","date_created"],
                    [$userId,$tenderId,$bidAmount,$now])->execute();
                $update = $pdo->update("tbl_tenders",["fl_highest_amount","i_winner_user_id"],[$bidAmount,$userId])
                    ->where("id","=",$tenderId)
                    ->execute();

                $response->status = RESULT_SUCCESS;
                $response->error  = '';

                if($userId != $previousUserId) {
                    $url = SITE_URL."tender.php?tenderId=".$tenderId;
                    $subject = "הציעו הצעה גבוה משלך עבור המכרז" . " " . $tender[0]["st_name"];
                    $text =  "משתמש אחר הציע הצעה בגובה " . $bidAmount . ' על המכרז '.$tenderName.' שהובלת בו ';
                    if($userEmail){
                        sendMail($userEmail,"הצעה גבוה משלך על מכרז",$text,$url,"עבור למכרז",$tenderId);
                    }
                    if($userPhone){
                        sendSms($userPhone,$subject,$tenderId);
                    }
                }
            }
        }

        sendResponse($response);
        break;


    case 'sendSms' :
        $smsMessage = filter_input(INPUT_POST, 'smsMessage', FILTER_SANITIZE_STRING);
        $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);


        if (!($smsMessage  && $phone )) {
            $response->status = RESULT_ERROR;
            $response->error = 'הכנס את כל השדות הדרושים.';
        } else {
            $resSms = sendSms($phone,$smsMessage);
            if(strpos($resSms,"Successfully") != false){
                $response->status = RESULT_SUCCESS;
            }
            else{
                $response->status = RESULT_ERROR;
                $response->error = $resSms;
            }
        }

        sendResponse($response);
        break;


    case 'sendEmail' :
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_STRING);
        $subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING);
        $emailMessage = filter_input(INPUT_POST, 'emailMessage', FILTER_SANITIZE_STRING);

        if (!($emailMessage  && $email  && $subject )) {
            $response->status = RESULT_ERROR;
            $response->error = 'הכנס את כל השדות הדרושים.';
        } else {
            sendMail($email,$subject,$emailMessage);
            $response->status = RESULT_SUCCESS;
        }

        sendResponse($response);
        break;



	default:
		$response->status  = RESULT_ERROR;
		$response->error = 'Calling unknown function';
		sendResponse($response);
		break;
}

/**
 * Password hash function
 * @param $pass
 * @return string $password Hashed password.
 */
function hashPass($pass) {
	return password_hash($pass, PASSWORD_DEFAULT);
}

function sendResponse($response) {
	header("Content-type:application/json");
	echo json_encode($response, JSON_UNESCAPED_UNICODE);
	die;
}




// send email and sms to user
function sendMessageToUser($userId,$subject,$message,$url=""){
    $pdo = new DataManagerPDO();
    $userData = $pdo->select("tbl_users",["id","st_phone as phone","st_email as email"])->where("id","=",$userId)->fetch();
    if(!$userData){
        return false;
    }
    $phone = $userData["phone"];
    $email = $userData["email"];
    if($url){
        sendMail($email,$subject,$message,$url);
    }
    else{
        sendMail($email,$subject,$message);

    }
    sendSms($phone,$message);
    return true;
}