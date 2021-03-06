<?php

class Bbx_Date {
	
	const YEAR_PATTERN = '[1-2][0-9][0-9][0-9]';
	const MONTH_PATTERN = '[0-1][0-9]';
	const DAY_PATTERN = '[0-3][0-9]';
	const HOUR_PATTERN = '[0-2][0-9]';
	const MINUTE_PATTERN = '[0-5][0-9]';
	const SECOND_PATTERN = '[0-5][0-9]';
	
	public static function getDateParts($str) {
		$dtParts = self::_getDateTimeParts($str);
		$parts = explode('-',$dtParts[0]);
		$parts = array_pad($parts, 3, "00");
		
		if (preg_match('/^' . self::YEAR_PATTERN . '$/',$parts[0]) === 0) {
			$parts[0] = "0000";
		}
		if (preg_match('/^' . self::MONTH_PATTERN . '$/',$parts[1]) === 0) {
			$parts[1] = "00";
		}
		if (preg_match('/^' . self::DAY_PATTERN . '$/',$parts[2]) === 0) {
			$parts[2] = "00";
		}
		return $parts;
	}
	
	public static function getTimeParts($str) {
		$dtParts = self::_getDateTimeParts($str);
		$parts = explode(':', $dtParts[1]);
		$parts = array_pad($parts,3,"00");
		
		if (preg_match('/^' . self::HOUR_PATTERN . '$/',$parts[0]) === 0) {
			$parts[0] = "00";
		}
		if (preg_match('/^' . self::MINUTE_PATTERN . '$/',$parts[1]) === 0) {
			$parts[1] = "00";
		}
		if (preg_match('/^' . self::SECOND_PATTERN . '$/',$parts[2]) === 0) {
			$parts[2] = "00";
		}
		return $parts;
	}
	
	public static function fixDateFormat($str) {
		$parts = self::getDateParts($str);
		return implode('-',$parts);
	}
	
	public static function fixTimeFormat($str) {
		$parts = self::getTimeParts($str);
		return implode(':',$parts);
	}
	
	public static function fixDateTimeFormat($str) {
		$parts = self::_getDateTimeParts($str);
		$datePart = $parts[0];
		$timePart = $parts[1];
		
		$date = self::fixDateFormat($datePart);
		$time = self::fixTimeFormat($timePart);
		
		return $date.' '.$time;
	}
	
	public static function timestamp($date = null) {
		if ($date == null) {
			return time();
		}
		$n = self::_normalizeDateTime($date);
		return self::_timestamp(array_values($n));
	}
	
	public static function date($date = null, $format = null) {
		if ($date === null) {
			$timestamp = time();
			$date = date('Y-m-d');
		}
		else {
			$timestamp = self::timestamp($date);
		}
		return self::_trim(self::_getFinalDate($timestamp,self::_getValidDateParts($date),$format));
	}
	
	public static function time($time = null, $format = null) {
		if ($time === null) {
			$time = date('G:i:s');
			$timestamp = time();
		}
		else {
			$ta = explode(' ',$time);
			$time = isset($ta[1]) ? $ta[1] : $ta[0];
			$timestamp = self::timestamp($time);
		}
		return self::_trim(self::_getFinalDate($timestamp,self::_getValidTimeParts($time),$format));
	}
	
	public static function dateTime($dateTime = null, $format = null) {
		if ($dateTime === null) {
			$dateTime = date('Y-m-d G:i:s');
			$timestamp = time();
		}
		else {
			$timestamp = self::timestamp($dateTime);
		}
		return self::_trim(self::_getFinalDate($timestamp,self::_getValidDateTimeParts($dateTime),$format));
	}
	
	public static function dateRange($start = null, $end = null, $format = null, $separator = '–') {

		$startDate = self::date($start, $format);
		$endDate = self::date($end, $format);

		if ($startDate === '') {
			return '';
		}
		if ($endDate === '' || $endDate === $startDate) {
			return $startDate;
		}
		
		$startParts = self::_getValidDateParts($start);
		$endParts = self::_getValidDateParts($end);
		$startTimestamp = self::_timestamp(self::_normalizeDate($start));
		$endTimestamp = self::_timestamp(self::_normalizeDate($end));
		
		if ($startParts['YEAR'] == $endParts['YEAR']) {
			unset($startParts['YEAR']);
		}
		
		$startDate = self::_getFinalDate($startTimestamp, $startParts, $format);
		$endDate = self::_getFinalDate($endTimestamp, $endParts, $format);
		
		return $startDate === $endDate 
			? self::_trim($startDate) : self::_trim($startDate) . $separator . self::_trim($endDate);
	}
	
	public static function timeRange($start = null, $end = null, $format = null, $separator = '–') {
		$startTime = self::time($start, $format);
		$endTime = self::time($end, $format);
		
		return $startTime === $endTime ? self::_trim($startTime) : self::_trim($startTime) . $separator . self::_trim($endTime);
	}
	
	public static function dateTimeRange($start = null, $end = null, $format = null, $separator = '–') {
		$range = self::dateRange($start, $end);
		if ($range != self::date($start)) {
			return $range;
		}
		return self::dateRange($start, $end) . ', ' . self::timeRange($start, $end);
	}
	
	public static function isValidDate($date) {
		return self::date($date) !== '';
	}
	
	public static function isValidDateTime($dateTime) {
		return self::dateTime($date) !== '';
	}
	
	public static function isFuture($date) {
		if (!self::isValidDate($date)) {
			return false;
		}
		$normalized = self::_normalizeDateTime($date);
		$timestamp = self::_timestamp(array_values($normalized));
		return $timestamp > time();
	}
	
	public static function isPast($date) {
		if (!self::isValidDate($date)) {
			return false;
		}
		$normalized = self::_normalizeDateTime($date);
		$timestamp = self::_timestamp(array_values($normalized));
		return time() > $timestamp;
	}
	
	protected static function _getFinalDate($timestamp,$parts,$format) {
		if ($format === null) {
			$format = Bbx_Config::get()->locale->datetime_format;
		}
		$formatArray = self::_parseFormat($format);
		$finalFormatArray = array();
		foreach($formatArray as $key => $value) {
			if (array_key_exists($key,$parts)) {
				$finalFormatArray[$key] = $formatArray[$key];
			}
		}
		$format = implode('',$finalFormatArray);
		return date($format,$timestamp);
	}
	
	protected static function _normalize($parts) {
		$normalized = array('HOUR' => '00','MINUTE' => '00','SECOND' => '00','MONTH' => '01','DAY' => '01','YEAR' => date('Y'));
		
		foreach($normalized as $key => $value) {
			if (array_key_exists($key,$parts)) {
				$normalized[$key] = $parts[$key];
			}
		}
		return $normalized;
	}
	
	protected static function _normalizeDate($date) {
		$parts = self::_getValidDateParts($date);
		return self::_normalize($parts);
	}
	
	protected static function _normalizeTime($time) {
		$parts = self::_getValidTimeParts($time);
		
		return self::_normalize($parts);
	}
	
	protected static function _normalizeDateTime($dateTime) {
		$parts = self::_getValidDateTimeParts($dateTime);
		
		return self::_normalize($parts);
	}
	
	protected static function _timestamp($normalized) {
		$ts = call_user_func_array('mktime',$normalized);
		return $ts;
	}
	
	protected static function _parseFormat($format) {
		$fArray = array();
		$order = array();
		
		$regExp = array(
			'HOUR' => "/[gGhH]+[\s:\.aA,]*/",
			'MINUTE' => "/[i]+[\s:\.aA,]*/",
			'SECOND' => "/[s]+[\s:\.aA,]*/",
			'MONTH' => "/[FmMn]+[\s:\.\/,]*/",
			'DAY' => "/[dDjlNS\s]+[\s:\.\/,]*/",
			'YEAR' => "/[oYy]+[\s:\.\/,]*/",
		);
		
		foreach($regExp as $key => $value) {
			if (preg_match_all($value,$format,$matches,PREG_OFFSET_CAPTURE) !== 0) {
				foreach ($matches as $match) {
					if (!array_key_exists($key,$fArray)) {
						$fArray[$key] = '';
					}
					$fArray[$key] .= $match[0][0];
					$order[$match[0][1]] = $key;
				}
			}
		}
		
		ksort($order);
		$sortedFArray = array();
		
		foreach(array_values($order) as $key) {
			$sortedFArray[$key] = $fArray[$key];
		}
		return $sortedFArray;
	}
	
	protected static function _getDateTimeParts($str) {
		$parts = array('', '');
		$matches = array();
		$datePattern =  '/(' . self::YEAR_PATTERN . ')(-' . self::MONTH_PATTERN . ')?(-' . self::DAY_PATTERN . ')?/';
		if (preg_match($datePattern, $str, $matches) > 0) {
			$parts[0] = $matches[0];
		}
		if (preg_match('/' . self::HOUR_PATTERN . ':' . self::MINUTE_PATTERN . ':' . self::SECOND_PATTERN . '/', $str, $matches) > 0) {
			$parts[1] = $matches[0];
		}
		return $parts;
	}
	
	protected static function _getValidDateTimeParts($dateTime) {
		$parts = self::_getDateTimeParts($dateTime);
		$date = $parts[0];
		$time = $parts[1];
		
		$valid_date = self::_getValidDateParts($date);
		$valid_time = array_key_exists('DAY',$valid_date) ? self::_getValidTimeParts($time) : array();

		$valid = array_merge($valid_date,$valid_time);

		return $valid;
	}
	
	protected static function _getValidDateParts($dateString) {
		$parts = self::_getDateTimeParts($dateString);
		$date = $parts[0];
		$dateParts = explode('-',$date);
		$dateParts = array_pad($dateParts,3,"00");
		$valid = array();
		
		if (preg_match('/^' . self::YEAR_PATTERN . '$/',$dateParts[0]) !== 0) {
			$valid['YEAR'] = $dateParts[0];
		}
		else {
			return $valid;
		}
		
		if (preg_match('/^' . self::MONTH_PATTERN . '$/',$dateParts[1]) !== 0) {
			if ((int)$dateParts[1] > 0 && (int)$dateParts[1] < 13) {
				$valid['MONTH'] = $dateParts[1];
			}
			else {
				return $valid;
			}
		}
		
		if (preg_match('/^' . self::DAY_PATTERN . '$/',$dateParts[2]) !== 0) {
			$day_count = 31;
			$thirty_days = array("04","06","09","11");
			
			if (in_array($dateParts[1],$thirty_days)) {
				$day_count = 30;
			}
			if ($dateParts[1] === "02") {
				$day_count = 29;
				if (date('L',strtotime($dateParts[0].'-01-01')) === 1) {
					$day_count = 29;
				}
			}
			
			if ((int)$dateParts[2] > 0 && (int)$dateParts[2] <= $day_count) {
				$valid['DAY'] = $dateParts[2];
			}
			else {
				return $valid;
			}
		}
		return $valid;
	}
	
	protected static function _getValidTimeParts($time) {
		
		if ($time === '') {
			return array();
		}
		
		$timeParts = explode(':',$time);
		$timeParts = array_pad($timeParts, 3, "00");
		
		$valid = array();
		
		if (preg_match('/^' . self::HOUR_PATTERN . '$/',$timeParts[0]) !== 0) {
			if ((int)$timeParts[0] < 24) {
				$valid['HOUR'] = $timeParts[0];
			}
			else {
				return $valid;
			}
		}
		
		if (preg_match('/^' . self::MINUTE_PATTERN . '$/',$timeParts[1]) !== 0) {
			$valid['MINUTE'] = $timeParts[1];
		}
		
		if (preg_match('/^' . self::SECOND_PATTERN . '$/',$timeParts[2]) !== 0) {
			$valid['SECOND'] = $timeParts[2];
		}
		
		return $valid;
	}
	
	protected static function _trim($str) {
		return trim($str," \t\n\r\0\x0B,:.'/");
	}
}

?>