<?php

/**
 * @project		appkr/utility
 * @file		commons.php
 * @author		Juwon Kim <juwonkim@me.com>
 */

/**
  * get CODEBLOCK's execution time
  * float get_execition_time(void)
  * @section <EXAMPLE>
  * get_execution_time();
  * {CODE BLOCK}
  * echo get_execution_time();
  */
function get_execution_time() {
	static $microtime_start = null;
	return $microtime_start === null ? $microtime_start = microtime(true) : microtime(true) - $microtime_start;
}

/**
 * pretty print for debug message
 * @param type $val
 */
function pp($val) {
	echo "<div style='word-break:break-all;'>\n";
	echo "<pre>\n";
	print_r($val);
	echo PHP_EOL;
	echo "</pre>\n";
	echo "</div>\n";
}

/**
 * pretty print for debug message
 * @param type $val
 */
function dd($val) {
	var_dump($val);
	echo PHP_EOL;
}

/**
 * check variable
 * @param {mixed} $var
 * @return {boolean} true if $var is set and not empty otherwise false
 */
function cv($var) {
	return (isset($var) && !empty($var));
}

/**
  * cut string (support 2byte languages)
  * string bite_str(string $string, int $start, int $len[, int $byte])
  * @param $start start index
  * @param $length length of string to cut
  * @param $byte charset type. utf-8:$byte=3 | gb2312:$byte=2 | big5:$byte=2
  * @section EXAMPLE
  * bite_str($str,$i,80)
  */
function bite_str($string, $start, $len, $byte=3)
{
    $str     = "";
    $count   = 0;
    $str_len = strlen($string);

    for ($i=0; $i<$str_len; $i++) {
        if (($count+1-$start)>$len) {
            $str  .= "...";
            break;
        } elseif ((ord(substr($string,$i,1)) <= 128) && ($count < $start)) {
            $count++;
        } elseif ((ord(substr($string,$i,1)) > 128) && ($count < $start)) {
            $count = $count+2;
            $i     = $i+$byte-1;
        } elseif ((ord(substr($string,$i,1)) <= 128) && ($count >= $start)) {
            $str  .= substr($string,$i,1);
            $count++;
        } elseif ((ord(substr($string,$i,1)) > 128) && ($count >= $start)) {
            $str  .= substr($string,$i,$byte);
            $count = $count+2;
            $i     = $i+$byte-1;
        }
    }

    return $str;
}

/**
  * string format_size(int $filesize);
  * @param $filesize  byte value of file size
  * @return return more legible file size string on success other wise 'NaN (Not a Number'
  */
function format_size($filesize){

	if(is_numeric($filesize)){

		$decr = 1024; $step = 0;
		$suffix = array('Byte','KB','MB','GB','TB','PB');

		while(($filesize / $decr) > 0.9){
			$filesize = $filesize / $decr;
			$step++;
		}

		return round($filesize,2).' '.$suffix[$step];

	} else {
		return 'NaN';
	}
}

/**
 * sanitize input string
 * string sanitize_string (string $value)
 */
function sanitize_string($string) {
	$string = filter_var($string, FILTER_SANITIZE_STRING);
	$string = filter_var($string, FILTER_SANITIZE_MAGIC_QUOTES);

	return $string;
}

/**
  * get formated datetime from a timestamp
  * string time2date(int $time)
  * @param $time unix timestamp
  */
function time2date($time) {
	$date = date("Y-m-d H:i:s", $time);
	return $date;
}

 /**
  * get timestamp from datetime
  * int date2time(string $date)
  * @param $date ISO date string
  */
function date2time($date) {
	$arg = explode(' ', $date);
	$ymd = explode('-', $arg[0]);
	$hms = (isset($arg[1])) ? explode(':', $arg[1]) : array(0,0,0);

	$time=mktime($hms[0],$hms[1],$hms[2],$ymd[1],$ymd[2],$ymd[0]);
	return $time;
}

?>
