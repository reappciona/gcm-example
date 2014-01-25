<?php
/**
 * @project		appkr/gcm-example
 * @file		/api/index.php
 * @author		Juwon Kim <juwonkim@me.com>
 * @brief		api endpoint
 */

extract($_POST);

try{
	require(__DIR__.'/../config.php');
	require(__DIR__.'/../functions/commons.php');

	$l = cv($l) ? $l : 'en';
	$v = cv($v) ? $v : '1';

	require(__DIR__.'/../res/lang/strings_'.$l.'.php');
	require(__DIR__.'/../functions/Database.php');
	require(__DIR__.'/../functions/Gcm.php');

	$gcm = new Gcm($db_config, $table);

	if (!cv($package)) {
		throw new Exception ($strings[505], 505);
	}

	switch ($query) {
		case 'register' :
			if (stristr($_SERVER['HTTP_USER_AGENT'], 'gcm_example') === false) {
				//remove this lines at distribution version
				throw new Exception ($strings[506], 506);
			}

			if (!cv($gcmID) || !cv($uuid)) {
				throw new Exception ($strings[505], 505);
			}
			$ret = $gcm->register($uuid, $package, $gcmID);
			break;

		case 'unregister' :
			if (stristr($_SERVER['HTTP_USER_AGENT'], 'gcm_example') === false) {
				//remove this lines at distribution version
				throw new Exception ($strings[506], 506);
			}

			if (!cv($gcmID) || !cv($uuid)) {
				throw new Exception ($strings[505], 505);
			}
			$ret = $gcm->unregister($uuid, $package, $gcmID);
			break;

		case 'send' :
			if(cv($dryrun))
				$dry_run = true;

			$message = array (
				'ticker' => $ticker,
				'title' => $title,
				'message' => $message
			);

			$conditions = array(
				array('package', '=', $package)
			);

			if (cv($uuid)) {
				$conditions[] = array('uuid', '=', $uuid);
			}

			if (cv($condition)) {
				$element = trim(explode(',', $condition));
				$conditions[] = array(implode(',', $element));
			}

			$ret = $gcm->send($conditions, $message);
			break;

		default :
			$ret = array(
				'code'		=> 505,
				'message'	=> $strings[505]
			);
			break;
	}
} catch (Exception $e) {
	$ret = array(
		'code'		=> $e->getCode(),
		'message'	=> $e->getMessage()
	);
}

// save application log
if ($log_config['turn']) {
	$data = array (
  		'query' => $query,
  		'uuid'	=> $uuid,
  		'ret'	=> $ret
	);

	$gcm->_log_table = $log_table;

	if ($log_config['level'] == 2 ) {
		if ($gcm->log($data) === false) {
			$gcm->mail($data);
		}
	} else if ($log_config['level'] == 1 && ceil($ret['code']/100) == 5) {
		if ($gcm->log($data) === false) {
			$gcm->mail($data);
		}
	} else {
		//do nothing
	}
}

//print response in json format
header('Content-type: application/json; charset=utf-8');
echo json_encode($ret);
exit();
?>


