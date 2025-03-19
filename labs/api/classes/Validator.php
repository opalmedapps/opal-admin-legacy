<?php

// SPDX-FileCopyrightText: Copyright (C) 2020 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Opal\Labs\Classes;

use Exception;
use DateTime;

class Validator
{

	static $errors = true;

	private $params;

	function __construct($parameters)
	{
		$this->params = $parameters;
	}
	public function getParameters(){
		return $this->params;
	}

	public function exists(string $key): bool {
        return isset($this->params[$key]);
    }
    /**
     * @param $arr
     * @throws Exception if property not found, an exception is thrown
     */
    public function required($arr)
	{
		foreach ($arr as $value) {
			if (!isset($this->params[$value])) {
				self::throwError(
					'Required value: \'' . $value . '\' is missing in request parameters',
					900
				);
			}
		}
	}

	 function int($key)
	{
		$val = filter_var($this->params[$key], FILTER_VALIDATE_INT);
		if ($val === false) {
			self::throwError('Invalid Integer', 901);
		}
        $this->params[$key] = $val;
	}

	static function str($val)
	{
		if (!is_string($val)) {
			self::throwError('Invalid String', 902);
		}
		$val = trim(htmlspecialchars($val));
		return $val;
	}

	static function bool($val)
	{
		$val = filter_var($val, FILTER_VALIDATE_BOOLEAN);
		return $val;
	}

	function email(string $key)
	{
		$val = filter_var($this->params[$key], FILTER_VALIDATE_EMAIL);
		if ($val === false) {
			self::throwError('Invalid Email', 903);
		}
		return $val;
	}
	public function isDate(string $key)
	{
		try {
			new DateTime($this->params[$key]);
		} catch (Exception $e) {
			self::throwError("Parameter $key is an invalid date, please provide a date with the right format", 904);
		}
	}

    /**
     * @param string $key
     * @throws Exception
     */
    public function toDate(string $key): void{
        try {
            $this->params[$key] = new DateTime($this->params[$key]);
        } catch (Exception $e) {
            self::throwError("Parameter $key is an invalid date, please provide a date with the right format", 904);
        }
    }
	static function url($val)
	{
		$val = filter_var($val, FILTER_VALIDATE_URL);
		if ($val === false) {
			self::throwError('Invalid URL', 904);
		}
		return $val;
	}

	static function throwError($error = 'Error In Processing', $errorCode = 0)
	{
		if (self::$errors === true) {
			throw new Exception($error, $errorCode);
		}
	}
}
