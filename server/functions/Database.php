<?php
/**
 * @project		appkr/utility
 * @author		Juwon Kim <juwonkim@me.com>
 * @file		/functions/Database.php
 */

class Database
{
	protected $_conn;
	protected $_query;
	protected $_where;
	protected $_and_where;
	protected $_or_where;
	protected $_paramTypeList;
	protected $_order;
	public $pattern = '/^[a-zA-Z_]+\.[a-zA-Z_]+/';


	/**
	 * mysqli connect to the database
	 * @param array $c array('host'=>'localhost', 'user'=>'username', 'pass'=>'passphrase', 'db'=>'dbname');
	 */
	public function __construct($c){
		$this->_conn = new mysqli($c['host'], $c['user'], $c['pass'], $c['db'])
			or die('There was a problem connecting to the database : ' . htmlspecialchars($mysqli->connect_error));
		$this->_conn->set_charset('utf8');
	}

	public function rawQuery ($query)
	{
		$this->_query = $query;
		$results = $this->_conn->query($this->_query);
		return ($results) ? $results : false;
	}

	/**
	 * natural query to the table
	 * @param string $query full SQL query
	 * @return array of query result on success otherwise boolean false
	 */
	public function queryTable($query)
	{
		//if a query like SELECT * FROM table WHERE a = 'abc' is given, sanitize function escapes single quotation
		//$this->_query = self::_sanitizeQuery($query);
		$this->_query = $query;
		$stmt = $this->_prepare();
		$stmt->execute();

		$results = $this->_bindResult($stmt);

		return ($results) ? $results : false;
	}

	public function count($table)
	{
		$this->_query = "SELECT count(*) AS cnt FROM {$table} {$this->_where} {$this->_and_where} {$this->_or_where}";

		$results = self::queryTable($this->_query);
		return ($results)
			? $results[0]['cnt']
			: 0;
	}

	public function max($table, $field)
	{
		$this->_query = "SELECT max({$field}) AS mx FROM {$table} {$this->_where} {$this->_and_where} {$this->_or_where}";
		$results = self::queryTable($this->_query);
		return ($results)
			? $results[0]['mx']
			: 0;
	}

	/**
	 * SELECT query
	 * @param string $table table name
	 * @param int $numRows LIMIT
	 * @return array of query result on success otherwise boolean false
	 */
	public function get($table, $numRows = null)
	{

		$this->_query = "SELECT * FROM {$table} ";
		$stmt = $this->_buildQuery($numRows);
		$stmt->execute();

		$results = $this->_bindResult($stmt);
		return ($results) ? $results : false;
	}

	/**
	 * Helper function to retrive just one row
	 */
	public function first($table)
	{
		$return = self::get($table, 1);
		return ($return !== false)
			? $return[0]
			: false;
	}

	/**
	 * INSERT query
	 * @param string $table table name
	 * @param array $data key=>value array of data to insert
	 * @return array of last insert ID on success otherwise boolean false
	 */
	public function insert($table, $data)
	{
		$this->_query = "INSERT INTO {$table} ";
		$stmt = $this->_buildQuery(null, $data);
		$stmt->execute();

		$results = self::queryTable('SELECT LAST_INSERT_ID() AS id');

		return (isset($results) && !empty($results)) ? $results[0]['id'] : false;

		//return ($stmt->affected_rows) ? true : false;
	}

	/**
	 * UPDATE query
	 * @param string $table table name
	 * @param array $data key=>value data to update
	 * @return boolean true on success otherwise false.
	 */
	public function update($table, $data)
	{
		$this->_query = "UPDATE {$table} SET ";
		$stmt = $this->_buildQuery(null, $data);
		$stmt->execute();

		return ($stmt->affected_rows) ? true : false;
	}

	/**
	 * DELETE query
	 * @param string $table table name
	 * @return boolean true on success otherwise false.
	 */
	public function delete($table)
	{
		$this->_query = "DELETE FROM {$table} ";
		$stmt = $this->_buildQuery();
		$stmt->execute();

		return ($stmt->affected_rows) ? true : false;
	}

	/**
	 * initialize Database object
	 * MUST USE IT BEFORE DOING A SECOND QUERY TO CLEAN $_where, $_query, _paramTypeList.
	 */
	public function clean()
	{
		$this->_query = null;
		$this->_where = null;
		$this->_and_where = null;
		$this->_or_where = null;
		$this->_order = null;
		$this->_paramTypeList = null;
	}

	/**
	 * WHERE clause
	 * @param string $key field name
	 * @param mixed $value value for field name
	 */
	public function where($key, $operator, $value)
	{
		$this->_where = "WHERE {$key} {$operator} ";
//		$this->_where .= (is_numeric($value) || preg_match($this->pattern, $value))
		$this->_where .= is_numeric($value)
				? "{$value}"
				: "'{$value}'";
	}

	public function and_where($key, $operator, $value)
	{
		if($this->_where == null || $this->_or_where != null)
			return false;

		$this->_and_where .= " AND {$key} {$operator} ";
//		$this->_and_where .= (is_numeric($value) || preg_match($this->pattern, $value))
		$this->_and_where .= is_numeric($value)
				? "{$value}"
				: "'{$value}'";
	}

	public function or_where($key, $operator, $value)
	{
		if($this->_where == null || $this->_and_where != null)
			return false;

		$this->_or_where .= " OR {$key} {$operator}";
//		$this->_or_where .= (is_numeric($value) || preg_match($this->pattern, $value))
		$this->_or_where .= is_numeric($value)
				? "{$value}"
				: "'{$value}'";
	}

	public function order($order, $asc = 'ASC')
	{
		$this->_order = "ORDER BY {$order} " . strtoupper($asc);
	}

	public function add_order($order, $asc = 'ASC')
	{
		$this->_order .= ", {$order} " . strtoupper($asc);
	}

	/**
	 * build SQL query depending on $this->_query
	 * @param int $numRows LIMIT clause
	 * @param array $data data to be updated or inserted
	 * @return mysqli statement object on success otherwise boolean false
	 */
	protected function _buildQuery($numRows = null, $data = false)
	{
		$hasData = null;
		$this->_paramTypeList = null;

		if(gettype($data) === 'array')
			$hasData = true;

		if(!empty($this->_where)) {

			if($hasData && strpos($this->_query, 'UPDATE') !== false) {
				//update query
				$i = 1;
				foreach($data as $property => $value) {
					$this->_paramTypeList .= $this->_determineType($value);

					if($i === count($data)){
						$this->_query .= "{$property} = ? {$this->_where} {$this->_and_where} {$this->_or_where}";
					} else {
						$this->_query .= "{$property} = ?, ";
					}
					$i++;
				}
			} else {
				//no table data was passed. might be a SELECT/DELETE statement
				$this->_query .= "  {$this->_where} {$this->_and_where} {$this->_or_where}";
			}
		} else {
			//todo What about DELETE query?
			if ($hasData && strpos($this->_query, 'INSERT') !== false) {
				//insert query
				$num = count($data);
				foreach($data as $k => $v) {
					$v = self::_sanitizeQuery($v);
					$this->_paramTypeList .= $this->_determineType($v);
					$field[] = $k;
				}

				$field = implode(', ', $field);
				$this->_query .= "({$field}) VALUES(";
				while($num !== 0) {
					$this->_query .= ($num !== 1) ? '?, ' : '?)';
					$num--;
				}
			}
		}

		if($this->_order != null)
			$this->_query .= " {$this->_order}";

		//did the user set a limit?
		if(isset($numRows)) {
			$this->_query .= is_array($numRows)
					? " LIMIT " . implode(',', $numRows)
					: " LIMIT " . (int)$numRows;
		}

		$stmt = $this->_prepare();

		if($hasData) {
			$args = array();
			$args[] = $this->_paramTypeList;

			foreach($data as $k => $v) {
				$args[] = &$data[$k];
			}

			call_user_func_array(array($stmt, 'bind_param'), $args);

		} /*else if($this->_where) {
			$stmt->bind_param($this->_paramTypeList, $whereValue);
		}*/

		return ($stmt) ? $stmt : false;
	}

	/**
	 * determine type of value
	 * @param mixed $item
	 * @return string s,i,b,d
	 */
	protected function _determineType($item)
	{
		switch(gettype($item)) {
			case 'string' :
				$paramType = 's';
				break;
			case 'integer' :
				$paramType = 'i';
				break;
			case 'blob' :
				$paramType = 'b';
				break;
			case 'double' :
				$paramType = 'd';
				break;
			default:
				$paramType = 's';
		}
		return $paramType;
	}

	/**
	 * prepare SQL query
	 * @return mysqli statement object on success otherwise boolean false
	 */
	protected function _prepare()
	{
		$stmt = $this->_conn->prepare($this->_query)
			or die('Problem preparing query : ' . htmlspecialchars($this->_conn->error));
		return ($stmt) ? $stmt : false;
	}

	/**
	 * bind result and make result array
	 * @param object $stmt
	 * @return array of query result otherwise boolean false
	 */
	protected function _bindResult($stmt)
	{
		$parameters = array();
		$results = array();
		$meta = $stmt->result_metadata();

		while ($field = $meta->fetch_field()) {
			$parameters[] = &$row[$field->name];
		}

		call_user_func_array(array($stmt, 'bind_result'), $parameters);

		while ($stmt->fetch()){
			$x = array();

			foreach ($row as $key => $value){
				$x[$key] = $value;
			}

			$results[] = $x;
		}

		return ($results) ? $results : false;
	}

	/**
	 * sanitize string to a SQL-friendly
	 * @param string $value value to sanitize
	 * @return string sanitized string on success otherwise boolean false
	 */
	private function _sanitizeQuery($value)
	{
		$value = filter_var($value, FILTER_SANITIZE_STRING);
		$value = filter_var($value, FILTER_SANITIZE_MAGIC_QUOTES);
		return ($value) ? $value : false;
	}

	public function __get($name) {
		if ( property_exists($this, $name) ) {
			$refprop = new ReflectionProperty($this, $name);
			$refprop->setAccessible($name);
			return $refprop->getValue($this);
		}

		$method_name = "get_{$name}";

		if ( method_exists($this, $method_name) ) {
			return $this->{$method_name}();
		}

		trigger_error("Undefined property $name or method $method_name", E_USER_ERROR);
	}

	public function __set($name, $value) {
		if ( property_exists($this, $name) ) {
			$refprop = new ReflectionProperty($this, $name);
			$refprop->setAccessible($name);
			$refprop->setValue($this, $value);
			return;
		}

		$method_name = "set_{$name}";

		if ( method_exists($this, $method_name) ) {
			$this->{$method_name}($value);
			return;
		}

		trigger_error("Undefined property $name or method $method_name", E_USER_ERROR);
	}

	/**
	 * close mysqli connection
	 */
	public function __destruct()
	{
		$this->_conn->close();
	}

}
?>
