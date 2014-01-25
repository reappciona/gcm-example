<?php
/**
 * @project		appkr/gcm-example
 * @file		Gcm.php
 * @author		Juwon Kim <juwonkim@me.com>
 */

class Gcm extends Database {

	private $_table;
	private $_log_table;

	/**
	 * Gcm Object constructor
	 * @param array $c database connection configuration array
	 * @param string $table $users table on which gcmIDs will be written
	 */
	public function __construct($c, $table = 'users')
	{
		parent::__construct($c);
		$this->_table = $table;
	}

	/**
	 * gcmID register (insert operation)
	 * @param type $uuid
	 * @param type $package
	 * @param type $gcmID(=registration_id if i borrows google jargon) the 162byte random & unique string passed by googld server
	 * @return int number of affected rows (or record id that was successfully inserted), otherwise false.
	 * @throws Exception 501
	 */
	public function register($uuid, $package, $gcmID)
	{
		$res = self::_getRecord($uuid, $package);

		foreach ($res as $gg) {
			if ($gg['gcmID'] == $gcmID) {
				//when duplicate gcmID was found
				throw new Exception ($GLOBALS['strings'][501], 501);
			}
		}

		parent::clean();

		if ($res === false) {
			//insert new record when there is any matching existing record
			return (parent::insert($this->_table, array(
				'uuid'		=> $uuid,
				'package'	=> $package,
				'gcmID'		=> $gcmID
			))) !== false
				? array(
					'code'		=> 201,
					'message'	=> sprintf($GLOBALS['strings'][201], 1)
				)
				: array(
					'code'		=> 504,
					'message'	=> sprintf($GLOBALS['strings'][504], $this->error)
				);
		} else {
			//a record found corresponding to package & uuid, but gcmID is different
			parent::where('uuid', '=', $uuid);
			parent::and_where('package', '=', $package);

			return ($res = parent::update($this->_table, array(
				'gcmID'	=> $gcmID
			))) !== false
				? array(
					'code'		=> 202,
					'message'	=> sprintf($GLOBALS['strings'][202], $res)
				)
				: array(
					'code'		=> 504,
					'message'	=> sprintf($GLOBALS['strings'][504], $this->error)
				);
		}
	}

	/**
	 * gcmID unregister (update operation)
	 * @param type $uuid
	 * @param type $package
	 * @param type $gcmID
	 * @return int number of affected rows, otherwise false.
	 * @throws Exception 502
	 */
	public function unregister($uuid, $package, $gcmID)
	{
		$res = self::_getRecord($uuid, $package);
		$flag = false;

		foreach ($res as $gg) {
			if ($gg['gcmID'] == $gcmID) {
				$flag = true;
			}
		}

		if ($flag === false) {
			//when matching gcmID was not found
			throw new Exception ($GLOBALS['strings'][502], 502);
		}

		parent::clean();
		parent::where('uuid', '=', $uuid);
		parent::and_where('package', '=', $package);
		parent::and_where('gcmID', '=', $gcmID);

		return ($res = parent::update($this->_table, array(
			'gcmID'	=> null
		))) !== false
			? array(
				'code'		=> 202,
				'message'	=> sprintf($GLOBALS['strings'][202], $res)
			)
			: array(
				'code'		=> 504,
				'message'	=> sprintf($GLOBALS['strings'][504], $this->error)
			);
	}

	/**
	 * gcm message send
	 * @staticvar int $rr Retry counter
	 * @param array $condition query condition who will receive the given gcm message
	 * 	array(
	 * 		array('a','=','b'),
	 *		array('c','=','d')
	 *	)
	 * @param array $gcmMessage
	 *	array (
	 * 		'ticker' => '',
	 *		'title' => '',
	 *		'message' => ''
	 *	);
	 * @param array $gcmQueue self recursion list of gcmIDs, when it was not successful at first gcm message send attempt
	 * @return object json object on success, otherwise null.
	 * @throws Exception 503 when HTTP socket falis
	 */
	public function send($condition, $gcmMessage, $gcmQueue=array())
	{
		if (empty($gcmQueue)) {
			$list = self::_makeList($condition);
			static $rr = 0;//Retry counter
		} else {
			$list = $gcmQueue;//replace $list to $gcmQueue which was generated and passed from Retry logic
			$gcmQueue = array();//deliberate init to prevent growing infinitely when retrying
		}

		if(!empty($list)) {
			$data = array(
				'registration_ids' => $list,
				'collapse_key' => $package,
				'delay_while_idle' => true,
				'data' => $gcmMessage
			);

			//use this when do not send gcm to clients actually
			//but want to receive response from google server
			if ($GLOBALS['dry_run'] === true) {
				$data['dry_run'] = true;
			}

			$headers = array(
				'Authorization: key=' . $GLOBALS['google_api_key'],
				"Referer: {$_SERVER['HTTP_HOST']}"
			);

			$res = self::_httpPost($GLOBALS['google_gcm_url'], $data, $headers, 'json');

			if ($res) {
				$gcm[] = json_decode(trim($res['body']));
			} else {
				throw new Exception($GLOBALS['strings'][503], 503);
			}

			if ($gcm[$rr]->failure > 0 || $gcm[$rr]->canonical_ids > 0) {
				//the routine will not be activated when failure or canonical_ids is 0.
//				{
//					"multicast_id":5385385503171147569,
//					"success":1,
//					"failure":0,
//					"canonical_ids":0,
//					"results":[
//						{
//							"message_id":"0:1389847036861904%d308081bf9fd7ecd"
//						}
//					]
//				}

				$ii = 0;
				while ($gcm[$rr]->results[$ii]) {
					if ($gcm[$rr]->results[$ii]->registration_id) {
						//if registration_id is set, update gcmID at our server to google given registration_id
						parent::clean();
						parent::where('gcmID', '=', $list[$ii]);
						$res = parent::update($this->_table, array(
							'gcmID'	=> $gcm[$rr]->results[$ii]->registration_id
						));
						if ($res === false) {
							throw new Exception(sprintf($GLOBALS['strings'][504], $this->error), 504);
						}
					}

					if ($gcm[$rr]->result[$ii]->error == 'Unavailable') {
						//if Unavailable response was received, prepare retrying by saving gcmIDs to gcmQueue
						$gcmQueue[] = $list[$ii]; //Retry때는 새로운 Queue가 생성됨.
					}

					if ($gcm[$rr]->result[$ii]->error == 'NotRegistered') {
						//NotRegistered
						//the client was removed from a device OR broadcast receiver was not set correctly
						//go delete correspondig record from the server
						parent::clean();
						parent::where('gcmID', '=', $list[$ii]);
						$res = parent::update($this->_table, array(
							'gcmID'	=> null
						));
						if ($res === false) {
							throw new Exception(sprintf($GLOBALS['strings'][504], $this->error), 504);
						}
					}

					if ($gcm[$rr]->result[$ii]->error == 'InvalidRegistration') {
						//don't understand exactly. do your research and let me knwo.
						//InvalidRegistration, delete correspondig record from the server
						parent::clean();
						parent::where('gcmID', '=', $list[$ii]);
						$res = parent::update($this->_table, array(
							'gcmID'	=> null
						));
						if ($res === false) {
							throw new Exception(sprintf($GLOBALS['strings'][504], $this->error), 504);
						}
					}

					$ii++;
				} //end while

				if (!empty($gcmQueue) && $rr == 0) {
					while ($rr < 3) { //Retry 3 times
						sleep(1);
						$res = self::send($package, $gcmMessage, $gcmQueue);
						$gcm[0]->success += $gcm[$rr]->success;
						$gcm[0]->failure += $gcm[$rr]->failure;
						$rr++;
					}
				}

			} //end if

			return $gcm[0]; //json object
		}
	}

	/**
	 * save application-level log
	 * @param array $data
	 * array (
	 * 		'query' => 'register',
	 * 		'uuid'	=> 'xxxxxxxxxxxxxxxx',
	 * 		'ret'	=> array(
	 *			'code'		=> '501',
	 * 			'message'	=> 'Duplicate entry.'
	 * 		)
	 * );
	 */
	public function log($data)
	{
		if (is_array($data) === false) {
			return false;
		}

		parent::clean();

		return parent::insert($this->_log_table, array(
			'request_time'	=> $_SERVER['REQUEST_TIME'],
			'query'			=> cv($data['query']) ? $data['query'] : null,
			'uuid'			=> cv($data['uuid']) ? $data['uuid'] : 'airplug',
			'request'		=> $_SERVER['REQUEST_METHOD'].' Body: '.json_encode($_POST).' '.$_SERVER['SERVER_PROTOCOL'],
			'user_agent'	=> $_SERVER['HTTP_USER_AGENT'],
			'remote_addr'	=> $_SERVER['REMOTE_ADDR'],
			'response'		=> cv($data['ret']) ? json_encode($data['ret']) : null
		));
	}

	/**
	 * send email with given data
	 * @param array $data same as $Gcm::log
	 */
	public function mail($data)
	{
		if (is_array($data) === false) {
			return false;
		}

		$subject = "=?EUC-KR?B?".base64_encode(iconv('UTF-8','EUC-KR',$GLOBALS['project'].' api 로그 저장에 실패했습니다.')).'?=\n';
		$request = $_SERVER['REQUEST_METHOD'].' Body: '.json_encode($_POST).' '.$_SERVER['SERVER_PROTOCOL'];
		$message =<<<EOT
<pre>\n
request_time	: {$_SERVER['REQUEST_TIME']}\n
query		: {$data['query']}\n
uuid		: {$data['uuid']}\n
request		: {$request}\n
user_agent	: {$_SERVER['HTTP_USER_AGENT']}\n
remote_addr	: {$_SERVER['REMOTE_ADDR']}\n
response	: {$data['ret']['code']} {$data['ret']['message']}\n

Email generated by {$GLOBALS['project']} api\n
</pre>\n
EOT;

		$headers  = 'MIME-Version: 1.0'.PHP_EOL;
		$headers .= 'Content-type: text/html; charset=utf-8'.PHP_EOL;
		$headers .= 'From: '.$GLOBALS['email_from'].PHP_EOL;
		if ($GLOBALS['email_cc']) {
			$headers .= 'Cc: '.$GLOBALS['email_cc'].PHP_EOL;
		}

	  	return mail ($GLOBALS['email_to'], $subject, $message, $headers);
	}

	/**
	 * helper function to check the existence of record
	 * which will be helpful for exception processing at register and unregister api
	 * @param string $uuid
	 * @param string $package
	 * @return array of table query result on success, otherwise false
	 */
	private function _getRecord($uuid, $package)
	{
		parent::clean();
		parent::where('uuid', '=', $uuid);
		parent::and_where('package', '=', $package);

		return parent::get($this->_table);
	}

	/**
	 * helper function for making gcm receiver list
	 * @param type $condition
	 * @return array of table query result, otherwise false.
	 */
	private function _makeList($condition)
	{
		$ii = 0;
		parent::clean();

		foreach ($condition as $cc) {
			if ($ii == 0) {
				parent::where($cc[0], $cc[1], $cc[2]);
			} else {
				parent::and_where($cc[0], $cc[1], $cc[2]);
			}
			$ii++;
		}

		$res = parent::get($this->_table);

		if ($res) {
			foreach($res as $dd){
				$list[] = $dd['gcmID'];
			}
		} else {
			return $res;
		}

		return $list;
	}

	/**
	 * HTTP POST implementation
	 * @param string $url
	 * @param array $data array('k'=>'v')
	 * @param array $header HTTP Headers
	 * @param string $type valid types are 'json' or null
	 * @return array array('header'=>array('k'=>'v'), 'body'=>array('k'=>'v')) on success, otherwise false.
	 */
	private function _httpPost($url, $data, $header = false, $type = false) {
		//build POST body depending on type
		//valid types are 'json' or null
		$reqBody = ($type == 'json')
			? json_encode($data)
			: http_build_query($data);

		//process https
		$url = parse_url($url);
		switch ($url['scheme']) {
			case 'https':
				$scheme = 'ssl://';
				$port = 443;
				break;
			case 'http':
			default:
				$scheme = '';
				$port = 80;
		}

		$host = $url['host'];
		$path = $url['path'];

		if ($fp = fsockopen($scheme.$host, $port, $errno, $errstr, 30)) {
			$reqHeader = "POST {$path} HTTP/1.1\r\n";
			$reqHeader .= "Host: {$host}\r\n";
			if ($type == 'json') {
				$reqHeader .= "Content-type: application/json; charset=utf-8\r\n";
			} else {
				//when post type is given to null, set normal HTTP POST header
				$reqHeader .= "Content-type: application/x-www-form-urlencoded\r\n";
			}
			if ($header && is_array($header)) {
				foreach ($header as $v) {
					$reqHeader .= "{$v}\r\n";
				}
			}
			$reqHeader .= "Content-Length: " . strlen($reqBody) . "\r\n";
			$reqHeader .= "Connection: close\r\n\r\n";

			//send request
			fwrite($fp, $reqHeader);
			fwrite($fp, $reqBody);

			//get response from the server
			while(!feof($fp)) {
				$res .= fgets($fp, 1024);
			}

			fclose($fp);
		} else {
			//when socket open fails
			return false;
		}

		//separate to header and body
		$result = explode("\r\n\r\n", $res, 2);

		return array(
			'header'	=> isset($result[0])
				? $result[0]
				: null,
			'body'		=> isset($result[1])
				? $result[1]
				: null
		);
	}

}
?>
