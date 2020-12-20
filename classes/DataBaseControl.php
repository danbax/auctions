<?php
if (!defined('Access')) {
	die('Silence is gold');
}

require_once INCLUDES_DIR . '/config.php';

class DataBaseControl {

	/** @var mysqli reference to mysqli object */
	private $connection;
	private $error;

	/**
	 * DataBaseControl constructor.
	 */
	public function __construct() {
		$this->connection = null;
	}

	/**
	 * Connection init function
    */
	public function connect() {

		if ($this->connection === null) {
			// Creating new safe connection to data base
			mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

			try {
				$this->connection = new mysqli("localhost", DBUSER, DBPASS, DBNAME);
				$this->connection->set_charset("utf8");

			} catch(Exception $e) {
				$this->error = "Error in data base connection";
                                $this->error = $e;
				return $this->connection = null;
			}
		}

		return $this->connection;
	}
        
    public function PDOconnect($host=null,$dbname=null,$dbuser=null,$dbpass=null){

        if ($this->connection === null) {

            try {
                if($host && $dbname && $dbuser && $dbpass){
                    $this->connection = new PDO('mysql:host=' . $host . '; dbname=' . $dbname, $dbuser, $dbpass);
                }else{
                    $this->connection = new PDO('mysql:host=' . DBHOST . '; dbname=' . DBNAME, DBUSER, DBPASS);
                }
                $this->connection->exec("SET CHARACTER SET utf8");
                $this->connection->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
                $this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES  , false);
                $this->connection->setAttribute(PDO::ATTR_PERSISTENT, false);

            } catch(PDOException $e) {
                $this->error =  $e->getMessage();
                return $this->connection = null;
            }
        }

        return $this->connection;
    }
    
    public function getError(){
        return $this->error;
    }
}










