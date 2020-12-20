<?php
if (!defined('Access')) {
	die('Silence is gold');
}

require_once CLASSES_DIR . '/DataBaseControl.php';
require_once CLASSES_DIR . '/MailBuilder.php';
require_once CLASSES_DIR . '/DataManager.php';
require_once CLASSES_DIR . '/DataManagerPDO.php';

class User {

	private $mysqli;
	/** @var object of the logged in user */
	private $user;
	/** @var string error msg */
	private $error;
	/** @var int number of permitted wrong login attemps */
	private $permitedAttemps = 5;
	/** @var DataBaseControl */
	private $conn;

	private $pdo;

	public function __construct() {
		$this->conn = new DataBaseControl();
		$this->pdo = new DataManagerPDO();
	}
        
        public function getUser(){
            return $this->user;
        }
        
        /*
         * returns list of user push notification tokens
         * @userId = return token of user with id number
         * @companyId = return tokens of users from company id with id number
         * @platform = Android/IOS 
         */
        public function getUsersTokens($userId=0,$companyId=0,$platform=""){
                    $table = "tbl_users";
                    $fields = ['distinct st_push_notification_token','st_first_name','st_last_name'];

                    $pdo = new DataManagerPDO();
                    $pdo->select($table, $fields);
                    $pdo->whereConstant('bool_push_advertisement','=',1,'AND');
                    if($userId){
                        $pdo->where('id','=',$userId,'AND');
                    }else {
                        if ($companyId) {
                            $pdo->where('i_company_id', '=', $companyId,'AND');
                        } else {
                            if ($platform) {
                                $pdo->where('st_platform', '=', $platform,'AND');
                            }
                        }
                    }
                    $pdo->whereConstant(1,'=',1); // TO complete the sql.sql statement
                            
                    $records = $pdo->fetch();
                    $response = new stdClass();
                    if(!$records){
                        $response->status = RESULT_ERROR;
                        $response->error = $pdo->getError();
                    }
                    else{
                        $response->status = RESULT_SUCCESS;
                        $response->data = $records;
                    }
                    return $response;
        }
        
        /*
         * checks if user token is valid
         */
        public function isUserTokenValid($credentialsToken, $clientId){
             $userData = $this->getUserDataById($clientId); // get user data
                
             if($userData["credentialsToken"] == $credentialsToken)
                 return true;
             return false;
        }

	/**
	 * Login function
	 * @param string $email User email.
	 * @param string $password User password.
	 *
	 * @return bool Returns login success.
	 */
	public function login($email, $password) {
		$mysqli = $this->conn->connect();

		if (is_null($mysqli)) {
			$this->error = 'Connection did not work out!';
			$this->error = $this->conn->getError();

			return false;

		} else {

                        $userIp = getRealUserIp();
                        $date = date('Y-m-d H:i:s', time());
                        $userAgent = $_SERVER['HTTP_USER_AGENT'];

			$stmt = $mysqli->prepare("SELECT id,st_username,st_password,st_email,datetime_last_login,st_phone,
			st_last_login_ip,bool_is_admin from tbl_users"
                                . " WHERE st_email = ? || st_username = ?");

			$stmt->bind_param("ss", $email,$email);
			$stmt->execute();
			$result = $stmt->get_result();
			$user = $result->fetch_assoc();
			if ($user && password_verify($password, $user['st_password'])) {
					session_regenerate_id();
					$_SESSION['user']['id'] = $user['id'];

					$isSuccess = true;
					$this->user = $user;

					// update ip &  last login date
                    $lastLoginDate = date('Y-m-d H:i:s');
                    $ip = $_SERVER['REMOTE_ADDR'];

                    $dm = new DataManager();
                    if ( !$dm->update( "tbl_users", ["datetime_last_login","st_last_login_ip"])
                        ->where("id", "=")
                        ->execute( "ssi", $lastLoginDate, $ip,$user['id']) ) {
                        $isSuccess = false;
                    }


			} else {
				$this->error = 'פרטי התחברות שגויים';
				$isSuccess = false;
			}
		}

		$stmt->close();
		return $isSuccess;
	}
        

	/**
	 * Register a new user account function
	 * @param string $email User email.
	 * @param string $fname User first name.
	 * @param string $lname User last name.
	 * @param string $pass User password.
	 * @return boolean of success.
	 */
	public function registration($email, $fname, $lname, $pass) {
		$mysqli = $this->conn->connect();

		if ($this->checkEmail($email)) {
			$this->error = 'This email is already taken.';

			return false;
		}


		$guestPermissionId = 2;
		$pass = $this->hashPass($pass);
		$confCode = $this->hashPass(date('H:i:s') . $email);
		$stmt = $mysqli->prepare('INSERT INTO tbl_users (st_fname, st_lname, st_email, st_password, st_confirm_code, i_user_role) VALUES (?, ?, ?, ?, ?, ?)');
		$stmt->bind_param("sssssi", $fname, $lname, $email, $pass, $confCode, $guestPermissionId);

		if ($stmt->execute()) {

			if ($this->sendConfirmationEmail($email)) {
				$isSuccess = true;

			} else {
				$this->error = 'confirmation email sending has failed.';
				$isSuccess = false;
			}

		} else {
			$this->error = 'Inesrting a new user failed.';
			$isSuccess = false;
		}

		$stmt->close();
		return $isSuccess;
	}

	/**
	 * Email the confirmation code function
	 * @param string $email User email.
	 * @return boolean of success.
	 */
	private function sendConfirmationEmail($email){
		$mysqli = $this->conn->connect();

		$stmt = $mysqli->prepare('SELECT st_confirm_code FROM tbl_users WHERE st_email = ? limit 1');
		$stmt->bind_param("s", $email);
		$stmt->execute();
		$result = $stmt->get_result();
		$code = $result->fetch_assoc();


		$mail = new MailBuilder("test@site.com");
		$isSent = $mail->setSubject('Confirm your registration')
						->setMessage('Please confirm you registration by pasting this code in the confirmation box: http://localhost/webStarter/activate.php?t=' . $code['confirm_code'])
		                ->setBcc("bcc@mail.com")
		                ->setCc("cc@mail.com")
		                ->sendEmail($email);

		if ($isSent) {
			$isSuccess = true;

		} else {
			$isSuccess = false;
		}

		$stmt->close();
		return $isSuccess;
	}

	/**
	 * Check if email is already used function
	 * @param string $email User email.
	 * @return boolean of success.
	 */
	private function checkEmail($email) {
		$mysqli = $this->conn->connect();

		$stmt = $mysqli->prepare('SELECT id FROM tbl_users WHERE st_email = ? limit 1');
		$stmt->bind_param("s", $email);
		$stmt->execute();
		$result = $stmt->get_result();

		if ($result->num_rows > 0) {
			$isSuccess = true;

		} else {
			$isSuccess = false;
		}

		$stmt->close();
		return $isSuccess;
	}
        
        
        /*
         * get information of user by id number
         */
	public function getUserDataById($id) {
		$pdo = new DataManagerPDO();

                $table = "tbl_users";
                $fields = ["id","st_username","st_password","st_email","datetime_last_login","st_username",
                    "st_last_login_ip","bool_active","bool_allowed_create_tenders","bool_allowed_edit_users"];

                $records = $pdo->select($table, $fields)
                        ->where("id","=",$id)
                        ->fetch();

                if(!$records){
                    return false;
                }
                else{
                    return $records[0];
                }
	}
        
 
        
        
	/**
	 * Check if email is already used function
	 * @param string $email User email.
	 * @return boolean of success.
	 */
	private function getCompanyName($companyId) {
		$mysqli = $this->conn->connect();

		$stmt = $mysqli->prepare('SELECT st_name FROM tbl_companies WHERE id = ? limit 1');
		$stmt->bind_param("i", $companyId);
		$stmt->execute();
		$result = $stmt->get_result();
                $company = $result->fetch_assoc();
                
		$stmt->close();

		if($company){
                    return $company["st_name"];
                }
                
                return false;
	}

	/**
	 * Activate a login by a confirmation code and login function
	 * @param string $confCode Confirmation code.
	 * @return boolean of success.
	 */
	public function activate($confCode) {
		$mysqli = $this->conn->connect();

		$stmt = $mysqli->prepare('SELECT id, st_fname, st_lname, st_email FROM tbl_users WHERE st_confirm_code = ? AND bool_confirmed = 0');
		$stmt->bind_param("s", $confCode);
		$stmt->execute();
		$result = $stmt->get_result();

		if ($result->num_rows > 0) {
			$user = $result->fetch_assoc();

			$mail = new MailBuilder("test@site.com");
			$isSent = $mail->setSubject('Activation Completed')
			                ->setMessage('Account Activated! Congrats!')
			                ->setBcc("bcc@mail.com")
			                ->setCc("cc@mail.com")
			                ->sendEmail($user['email']);

			if ($isSent) {
				$stmt = $mysqli->prepare('UPDATE tbl_users SET bool_confirmed = 1 WHERE st_confirm_code = ?');
				$stmt->bind_param("s", $confCode);

				if ($stmt->execute()) {
					$this->user = $user;

					session_regenerate_id();

					$_SESSION['user']['id'] = $user['id'];
					$_SESSION['user']['fname'] = $user['fname'];
					$_SESSION['user']['lname'] = $user['lname'];
					$_SESSION['user']['email'] = $user['email'];

					$isSuccess = true;

				} else {
					$this->error = 'Activate Failed';
					$isSuccess = false;
				}

			} else {
				$this->error = 'Mail Sending Problem.';
				$isSuccess = false;
			}

		} else {
			$this->error = 'Token failed.';
			$isSuccess = false;
		}

		$stmt->close();
		return $isSuccess;
	}

	/**
	 * Password hash function
	 * @param $pass
	 * @return string $password Hashed password.
	 */
	private function hashPass($pass) {
		return password_hash($pass, PASSWORD_DEFAULT);
	}

	/**
	 * Print error msg function
	 * @return string error
	 */
	public function getError(){
		return $this->error;
	}
}
