<?php
if (!defined('Access')) {
	die('Silence is gold');
}

require_once CLASSES_DIR . '/DataBaseControl.php';

class DataManagerPDO {
	/**
	 * @var PDO|null
	 */
	public $pdo = null;

	private $query;
	private $result = null;
	private $error = "";
	private $rows = 0;

	/**
	 * DataManager constructor.
	 */
	public function __construct($host=null,$dbname=null,$dbuser=null,$dbpass=null) {
	    $conn = new DataBaseControl();
            if($host && $dbname && $dbuser && $dbpass){
                $this->pdo = $conn->PDOconnect($host,$dbname,$dbuser,$dbpass);
            }
            else{
                $this->pdo = $conn->PDOconnect();
            }
		return $this->pdo;
	}

	/**
	 * @param $table
	 * @param $fields
	 *
	 * @return $this
	 */
	public function select( $table, $fields) {
		$this->reset();
		$this->query->from = $table;

		$select = (is_array($fields) ? implode(', ', $fields) : $fields);
		$this->query->select = $select;

		return $this;
	}

	public function update(  $table, $fields, $data,  $expressions = null ) {
		$this->reset();
                $fields = (is_array($fields) ? $fields : explode(', ',$fields));
                $data = (is_array($data) ? $data : explode(', ',$data));
		$this->query->from = $table;

		if($fields && $data){
                    $this->query->update = "UPDATE {$table} SET " . $this->buildUpdatesBinds($fields);
                }
                if($expressions){
                    $this->query->update.= ', '.implode(", ",$expressions);
                }
		$this->query->values = $data;

		return $this;
	}



	public function updateExpressions(  $table,  $expressions ,$key,$value ) {

                $expressionsString = implode(',',$expressions);
		$sql = "UPDATE $table SET $expressionsString WHERE $key=:key";
                $result = $this->pdo->prepare($sql)->execute(array(
                    ':key' => $value
                ));
		return $result;
	}


	public function delete( $table) {

		$this->reset();
		$this->query->delete = "DELETE FROM {$table}";

		return $this;
	}

	public function insert( $table, $fields, $data ) {
		$this->reset();

        $fields = (is_array($fields) ? $fields : explode(', ',$fields));
        $data = (is_array($data) ? $data : explode(', ',$data));
		$binds = $this->buildBinds($fields);
		$this->query->insert = "INSERT INTO {$table} (" . implode(", ", $fields) . ") VALUES ({$binds}) ";
		$this->query->values = $data;

		return $this;
	}

	public function whereNULL($where, $notIs = 'NOT', $andOr = null) {
        $notIs = $notIs == 'NOT' ? $notIs : '';

	    if (!isset($andOr)) {
            $this->query->where[]  = "{$where} IS {$notIs} NULL ";

        } else {
            $this->query->where[]  = "{$where} IS {$notIs} NULL {$andOr}";
        }

        return $this;
    }
	/**
	 * @param        $where
	 * @param string $operator
	 * @param null $type
	 * @param null $val
	 * @param string $andOr
	 *
	 * @return $this
	 */
	public function where($where, $operator = '=', $val = null, $andOr = null) {

		if (!isset($andOr)) {
			$this->query->where[]  = "{$where} {$operator} ? ";
			$this->query->values[] = $val;

		} else {
			$this->query->where[] = "{$where} {$operator} ? {$andOr} ";
			$this->query->values[] = $val;
		}

		return $this;
	}

        /*
         * compare to constant, ex. : where "amount > 5";
         */
        public function whereConstant($where, $operator = '=', $val, $andOr = null) {
		if (!isset($andOr)) {
			$this->query->where[]  = "{$where} {$operator} $val ";

		} else {
			$this->query->where[] = "{$where} {$operator} $val {$andOr} ";
		}

		return $this;
	}

	public function whereIn(array $arrParams, $whereInKey) {
		if (isset($arrParams)) {
            $this->query->whereIn = $arrParams;
            $this->query->whereInKey = $whereInKey;
		}

		return $this;
	}

	public function whereCustom( $whereRequest, $values) {
        $values = (is_array($values) ? implode(', ', $values) : $values);

        $this->query->where[] = $whereRequest . " ?";
        $this->query->values[] = $values;


        return $this;
    }

    /**
     * @param        $table
     * @param null $field1
     * @param string $operator
     * @param null $field2
     * @param string $type
     * @return $this
     */
    public function join($table, $field1 = null, $operator = '', $field2 = null, $type = '')  {
        $on = $field1;

        if (!is_null($operator)) {
            $on = $field1 . ' ' . $operator . ' ' . $field2;
        }

        $this->query->join[] =  ' ' . $type . 'JOIN' . ' ' . $table . ' ON ' . $on;


        return $this;
    }

    /**
     * @param        $table
     * @param        $field1
     * @param string $operator
     * @param string $field2
     *
     * @return $this
     */
    public function innerJoin($table, $field1, $operator = '', $field2 = '') {
        $this->join($table, $field1, $operator, $field2, 'INNER ');

        return $this;
    }

    /**
     * @param        $table
     * @param        $field1
     * @param string $operator
     * @param string $field2
     * @return $this
     */
    public function leftJoin($table, $field1, $operator = '', $field2 = '') {
        $this->join($table, $field1, $operator, $field2, 'LEFT ');

        return $this;
    }

	public function buildStatement() {

		$query = $this->query;
		$sql = '';

		if (isset($query->select)) {
			$sql = "SELECT {$query->select} FROM {$query->from} ";
		}

		if (isset($query->update)) {
			$sql = $query->update;
		}

		if (isset($query->delete)) {
			$sql = $query->delete;
		}

		if (isset($query->insert)) {
			$sql = $query->insert;
		}

                if (isset($query->join)) {
                    $sql .= implode(' ', $query->join);
                }


		if (isset($query->where)) {
			$where = implode(' ', $query->where);
		}

		if (!empty($where)) {
			$sql .= " WHERE $where";
		}


        if (isset($query->whereIn)) {
            $clause = implode(',', array_fill(0, count($query->whereIn), '?'));
            $sql .=  "  $query->whereInKey IN ({$clause})";
        }

		if (isset($query->groupBy)) {
			$sql .= " GROUP BY {$query->groupBy}";
		}

		if (isset($query->orderBy)) {
			$sql .= " ORDER BY {$query->orderBy}";
		}

		if (isset($query->limit)) {
			$sql .= " LIMIT {$query->limit}";
		}

		$sql .= ";";


		return $sql;
	}


    public function getQuery(){
        return $this->buildStatement();
    }

	public function execute() {
		$stmt = null;


		try {
			$stmt = $this->pdo->prepare( $this->buildStatement() );

		} catch (PDOException $e) {
			$this->error = $e->getMessage();
			return null;
		}

		if (isset($this->query->whereIn)) {
		    if(isset($this->query->values) && isset($this->query->whereIn)) {
                $stmt->execute(array_merge($this->query->values, $this->query->whereIn));
            }
		    else{
                $stmt->execute(array_merge($this->query->whereIn));
            }

		} else {

			if (!empty($this->query->values)) {
				$stmt->execute( $this->query->values );

			} else {
				$stmt->execute();
			}
		}


		if (isset($this->query->insert)) {
			return $this->getLastInsertedId();
		}

        if (isset($this->query->delete)) {
            return $stmt->rowCount();
        }

		return $stmt;
	}


	public function fetch($fetchStyle = PDO::FETCH_ASSOC) {


		switch ($fetchStyle) {


			case PDO::FETCH_ASSOC :
			default:
			    try {
                    $stmt = $this->execute();
                    if($stmt){
                        if ($stmt->rowCount() == 0) {
                            return array();
                        }

                    }

                } catch (PDOException $e) {
			        $this->error = $e.$this->getError();
                }




				if (isset($stmt)) {

					while ( $row = $stmt->fetch( $fetchStyle ) ) {
						$this->result[] = $row;
					}

				} else {
					$this->result = false;
				}
				break;
		}


                $stmt = null;
		return $this->result;
	}
	/**
	 * @param      $orderBy
	 * @param null $orderDir
	 *
	 * @return $this
	 */
	public function groupBy($groupBy) {

		if (!is_null($groupBy)) {
			$this->query->groupBy = $groupBy;
		}

		return $this;
	}

	/**
	 * @param      $orderBy
	 * @param null $orderDir
	 *
	 * @return $this
	 */
	public function orderBy($orderBy, $orderDir = null) {

		if (!is_null($orderDir)) {
			$this->query->orderBy = $orderBy . ' ' . $orderDir;
		}

		return $this;
	}


	/**
	 * @param      $limit
	 * @param null $limitEnd
	 *
	 * @return $this
	 */
	public function limit($limit, $limitEnd = null)
	{
		if (!is_null($limitEnd)) {
			$this->query->limit = $limit . ', ' . $limitEnd;

		} else {
			$this->query->limit = $limit;
		}

		return $this;
	}

	public function getError() {
		return $this->error;
	}

	private function reset() {
		$this->query = new stdClass;
		$this->result = null;
		$this->error = null;
		$this->rows = 0;
	}

	private function buildUpdatesBinds($fields) {
		$binds = "";
		$count = count($fields);
		if ($count === 1) {
			return $binds = "{$fields[0]} = ?";

		} else {

			for ($i = 0; $i < $count - 1; $i++) {
				$binds .= "{$fields[$i]} = ?,";
			}
			$binds .= "{$fields[$count - 1]} = ?";
		}

		return $binds;
	}

	private function buildBinds( array $fields ) {

		if (count($fields) === 1) {
			return $binds = "?";

		} else {
			$binds = "?";

			for ($i = 1; $i < count($fields); $i++) {
				$binds .= ", ?";
			}
		}

		return $binds;
	}

        /*
         * insert multiple rows to table
         * $rows => [['field1','fields2],['field1row2','field2row2']]
         */
        public function multipleInsertLimited($table,$fields,$rows){
            $row_length = count($rows[0]);
            $nb_rows = count($rows);
            $length = $nb_rows * $row_length;
            $lastLength = $nb_rows%1000;
            //$query = "INSERT INTO $table (". implode(',',$fields).") VALUES ".$args;

            $count = 0;

            /* Fill in chunks with '?' and separate them by group of $row_length */
            $args = implode(',', array_map(
                function($el) { return '('.implode(',', $el).')'; },
                array_chunk(array_fill(0, 1000, '?'), $row_length)
            ));



            $args_last = implode(',', array_map(
                function($el) { return '('.implode(',', $el).')'; },
                array_chunk(array_fill(0, $lastLength, '?'), $row_length)
            ));



            // array of params to insert
            $params = array();
            foreach($rows as $row)
            {
                foreach($row as $value)
                {
                    $params[] = $value;
                }
            }

            foreach($rows as $row){
                if($count %1000 == 0){
                    // execute
                    $query = "INSERT INTO $table (". implode(',',$fields).") VALUES ".$args;

                    $stmt = null;

                    try {
                        $stmt = $this->pdo->prepare($query);

                    } catch (PDOException $e) {
                        $this->error = $e->getMessage();
                        var_dump($e->getMessage());
                        return null;
                    }

                    $stmt->execute(array_slice($params, $count-1000,$count));

                    return true;
                }
                if($count == $nb_rows){
                    $query = "INSERT INTO $table (". implode(',',$fields).") VALUES ".$args_last;

                    $stmt = null;

                    try {
                        $stmt = $this->pdo->prepare($query);

                    } catch (PDOException $e) {
                        $this->error = $e->getMessage();
                        var_dump($e->getMessage());
                        return null;
                    }


                    $stmt->execute(array_slice($params, $length-$lastLength,$length));

                    return true;
                }
            }

            for($i=0;$i<$nb_rows;$i+=1000){

            }

            $rows = array_slice($input, 2);
            print_r($rows);
            print_r($fields);

            $query = "INSERT INTO $table (". implode(',',$fields).") VALUES ".$args;

        }

            public function multipleInsert($table,$fields,$rows){
            $row_length = count($rows[0]);
            $nb_rows = count($rows);
            $length = $nb_rows * $row_length;

            /* Fill in chunks with '?' and separate them by group of $row_length */
            $args = implode(',', array_map(
                function($el) { return '('.implode(',', $el).')'; },
                array_chunk(array_fill(0, $length, '?'), $row_length)
            ));

            // array of params to insert
            $params = array();
            foreach($rows as $row)
            {
               foreach($row as $value)
               {
                  $params[] = $value;
               }
            }

            $query = "INSERT INTO $table (". implode(',',$fields).") VALUES ".$args;

            $stmt = null;

            try {
                    $stmt = $this->pdo->prepare($query);

            } catch (PDOException $e) {
                    $this->error = $e->getMessage();
                    var_dump($e->getMessage());
                    return null;
            }

            $stmt->execute($params);

            return true;
        }

        /*
         * multiple updates with transaction
         * $key = the key by which we update
         */
        public function multipleUpdate($table,$fields,$key,$rows){
            $fieldsEqueals = "";
            foreach($fields as $field){
                if(empty($fieldsEqueals)){
                    $fieldsEqueals = $field.' = ?';
                }
                else{
                    $fieldsEqueals .= ','.$field.' = ?';
                }
            }

            //We will need to wrap our queries inside a TRY / CATCH block.
            //That way, we can rollback the transaction if a query fails and a PDO exception occurs.
            try{

                //We start our transaction.
                $this->pdo->beginTransaction();

                foreach($rows as $row){
                    $params = array();
                    foreach($row as $item){
                        $params[] = $item;
                    }
                    
                    try {
                    $sql = "UPDATE $table SET $fieldsEqueals WHERE $key = ?";
                    $stmt = $this->pdo->prepare($sql);
                    if (!$stmt->execute($params)) { 
                        print_r($stmt->errorInfo());
                    }
                        } catch (Exception $e) {
                            echo $e->getMessage();
                        }
                }


                //We've got this far without an exception, so commit the changes.
                $this->pdo->commit();

            } 
            //Our catch block will handle any exceptions that are thrown.
            catch(Exception $e){
                //An exception has occured, which means that one of our database queries
                //failed.
                //Print out the error message.
                return 'x';
                return $e->getMessage();
                //Rollback the transaction.
                $pdo->rollBack();
            }
            
        }
        
        /*
         * insert new log entry
         * $type - for ex: user,order etc.
         * $relatedEntity - for ex: user id, order id etc.
         */
        public function insertLogEntry($logString,$relatedEntities){
            $fields = ["userId","orderId","truckPlate"];
            
            // init fields
            foreach($fields as $field){
                if(!isset($relatedEntities->$field)){
                    $relatedEntities->$field = 0;
                }
            }
            
            
            $timestamp = date('Y-m-d H:i:s');
            $sql = "INSERT INTO tbl_log (timestamp, st_log, i_user_id,i_order_id,st_truck_plate) VALUES (?,?,?,?,?)";
            $stmt= $this->pdo->prepare($sql);
            $stmt->execute([$timestamp, $logString, $relatedEntities->userId,$relatedEntities->orderId,$relatedEntities->truckPlate]);
        }
        
        
        public function getLastInsertedId(){
            return $this->pdo->lastInsertId();
        }

	public function getResult() {
		return $this->result;
	}
}























