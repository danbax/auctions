<?php
if (!defined('Access')) {
	die('Silence is gold');
}

require_once CLASSES_DIR . '/DataBaseControl.php';

class DataManager {

	private $mysqli;
	private $query;
	private $result = null;
	private $error = "";
	private $rows = 0;

	/**
	 * DataManager constructor.
	 */
	public function __construct() {

		$this->mysqli = new DataBaseControl();
	}
        
        public function query($sql){
            $this->mysqli->connect()->query($sql);
        }
        
	public function delete( $table) {

		$this->reset();
		$this->query->base = "DELETE FROM {$table}";
		$this->query->type = 'delete';

		return $this;
	}

	public function insert( $table,  $fields) {

		$this->reset();
		$binds = $this->buildBinds($fields);
		$this->query->base = "INSERT INTO {$table} (" . implode(", ", $fields) . ") VALUES ({$binds}) ";
		$this->query->type = 'insert';

		return $this;
	}

	public function update( $table,  $fields) {

		$this->reset();
		$this->query->base = "UPDATE {$table} SET " . $this->buildUpdatesBinds($fields);
		$this->query->type = 'update';

		return $this;
	}

	public function selectAll( $table,  $fields){

		$stmt = null;

		$this->reset();
		$this->query->base = "SELECT " . implode(", ", $fields) . " FROM {$table}";
		$this->query->type = 'select all';

		$mysqli = $this->mysqli->connect();

		try {
			$stmt = $mysqli->prepare( $this->query->base );

		} catch ( Exception $ex ) {
			$this->error = $ex->getMessage();
			return null;
		}

		try {
			$stmt->execute();

		} catch ( Exception $ex ) {
			$this->error = $ex->getMessage();
			return null;
		}

		$result = $stmt->get_result();
		$this->rows = $result->num_rows;

		if ( $result->num_rows !== 0 ) {

			while ( $data = $result->fetch_assoc() ) {
				$this->result[] = $data;
			}

		} else {
			$this->error = "Data no found";
		}

		if (isset($stmt)) $stmt->close(); // if not null close
		return $this->result;
	}
        
        public function executeNoParams() {
		$stmt = null;
		if ($this->query->type === 'select' || $this->query->type === 'delete' ||
		    $this->query->type === 'insert' || $this->query->type === 'update' ) {
			if (!isset($this->error)) {

				$mysqli = $this->mysqli->connect();

				try {
					$stmt = $mysqli->prepare( $this->buildQuery() );
				} catch ( Exception $ex ) {
					$this->error = $ex->getMessage();
					return null;
				}

					try {
						$stmt->execute();
					} catch ( Exception $ex ) {
						$this->error = $ex->getMessage();
						return null;
					}

					if ($this->query->type === 'delete' ) {
						$this->rows = $stmt->affected_rows;
						$this->result = $stmt->affected_rows;

					} else if ($this->query->type === 'insert') {
						$this->rows = $stmt->affected_rows;
						$this->result = $stmt->insert_id;

					} else if ($this->query->type === 'update') {
						$this->rows = $stmt->affected_rows;
						$this->result = $stmt->affected_rows;

					} else {

						$result = $stmt->get_result();

						if ( $result->num_rows !== 0 ) {

							while ( $data = $result->fetch_assoc() ) {
								$this->rows = $result->num_rows;
								$this->result[] = $data;
							}
                                                        

						} else {
							return $result->num_rows;
						}
					}

				

			} else {
				$this->result = false;
			}

		} else {
			$this->error = "Can be used only for SELECT or DELETE or INSERT or UPDATE request";
			$this->result = false;
		}

		if (isset($stmt)) $stmt->close(); // if not null close
		return $this->result;
	}

	public function execute($pattern,  &$var1, &...$_) {

		$stmt = null;

		if ($this->query->type === 'select' || $this->query->type === 'delete' ||
		    $this->query->type === 'insert' || $this->query->type === 'update' ) {
                    
                
		if (!isset($this->error)) {
                $mysqli = $this->mysqli->connect();

                if ($mysqli != null) {

                    try {
                        $stmt = $mysqli->prepare($this->buildQuery());
                    } catch (Exception $ex) {
                        $this->error = $ex->getMessage();
                        return null;
                    }

                } else {
                    $this->error = $mysqli->error;
                    return null;
                }

				if ( $stmt->bind_param( $pattern, $var1, ...$_ ) ) {

					try {
						$stmt->execute();
					} catch ( Exception $ex ) {
						$this->error = 'Exception: '.$ex->getMessage();
						return null;
					}

					if ($this->query->type === 'delete' ) {
						$this->rows = $stmt->affected_rows;
						$this->result = $stmt->affected_rows;

					} else if ($this->query->type === 'insert') {
						$this->rows = $stmt->affected_rows;
						$this->result = $stmt->insert_id;

					} else if ($this->query->type === 'update') {

						$this->rows   = $stmt->affected_rows;

						if ($stmt->affected_rows !== 0) {
							$this->result = $stmt->affected_rows;

						} else {
							$this->result = 1;
						}

					} else {

						$result = $stmt->get_result();

						if ( $result->num_rows !== 0 ) {

							while ( $data = $result->fetch_assoc() ) {
								$this->rows = $result->num_rows;
								$this->result[] = $data;
							}

						} else {
							return $result->num_rows;
						}
					}

				} else {
					$this->error = "Error on binding";
					$this->result = false;
				}

			} else {
				$this->error = "Error without reason.. weird (error that occured before.";
				$this->result = false;
			}

		} else {
			$this->error = "Can be used only for SELECT or DELETE or INSERT or UPDATE request";
			$this->result = false;
		}

		if (isset($stmt)) $stmt->close(); // if not null close
		return $this->result;
	}

	/**
	 * @param string $table
	 * @param array $fields
	 *
	 * @return $this
	 */
	public function select( $table,  $fields) {

		$this->reset();
		$this->query->base = "SELECT " . implode(", ", $fields) . " FROM {$table}";
		$this->query->type = 'select';

		return $this;
	}

	/**
	 * Adding WHERE.
	 *
	 * @param string $field
	 * @param string $operator
         * @param string $innerJoinWhereClause : used to join tables in where clause like $field = $innerJoinWhereClause
         * for example: $field = tbl_one.tbl_two_id = tbl_two.id = $innerJoinWhereClause
         * 
	 *
	 * @return DataManager
	 */
	public function where( $field, $operator = '=',$innerJoinWhereClause = '')
	{
		if (!in_array($this->query->type, ['select', 'update', 'delete'])) {
			$this->error = "WHERE can only be added to SELECT OR UPDATE OR DELETE";
		}
                
                if($innerJoinWhereClause == ''){
                    $this->query->where[] = "{$field} {$operator} ? ";
                }
                else{
                    $this->query->where[] = "{$field} {$operator} {$innerJoinWhereClause}";
                }
                

		return $this;
	}
        
        public function whereFreeSqlAdd($sql)
	{
		if (!in_array($this->query->type, ['select', 'update', 'delete'])) {
			$this->error = "WHERE can only be added to SELECT OR UPDATE OR DELETE";
		}
                
                    $this->query->where[] = $sql;
                

		return $this;
	}

	/**
	 * Adding ORDER BY.
	 *
	 * @param array $fields
	 * @param $type
	 *
	 * @return DataManager
	 */
	public function orderBy( $fields,  $type)
	{
		if (!in_array($this->query->type, ['select'])) {
			$this->error = "ORDER BY can only be added to SELECT";
		}

		$this->query->orderBy = implode(", ", $fields) . " " . $type;

		return $this;
	}

        public function groupBy( $fields)
	{
		if (!in_array($this->query->type, ['select'])) {
			$this->error = "GROUP BY can only be added to SELECT";
		}

		$this->query->orderBy = " GROUP BY" . implode(", ", $fields);

		return $this;
	}

	/**
	 * Adding LIMIT.
	 *
	 * @param int $start
	 * @param int $offset
	 *
	 * @return DataManager
	 */
	public function limit(int $start, $offset = null) {

		if (!in_array($this->query->type, ['select'])) {
			$this->error = "LIMIT can only be added to SELECT";
		}

		if (isset($offset)) {
			$this->query->limit = " LIMIT {$start}, {$offset}";

		} else {
			$this->query->limit = " LIMIT {$start}";
		}

		return $this;
	}

	/**
	 * Print error msg function
	 * @return string error
	 */
	public function getError(){
		return $this->error;
	}

	/**
	 * Build Query from params
	 */
	private function buildQuery() {

		$query = $this->query;
		$sql = $query->base;

		if (!empty($query->where)) {
			$sql .= " WHERE " . implode(' AND ', $query->where);
		}
                
                if (!empty($query->orderBy)) {
			$sql .= " ORDER BY $query->orderBy";
		}

		if (isset($query->limit)) {
			$sql .= $query->limit;
		}

		$sql .= ";";

		return $sql;
	}

	private function reset() {
		$this->query = new stdClass;
		$this->result = [];
		$this->error = null;
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

	public function getResult() {
	    return $this->result;
    }

}






