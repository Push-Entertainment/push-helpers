<?php

namespace Push\Functions;

use Push\Functions\Traits\Error;

/**
 * Class Validation
 *
 * @package Push\Functions
 */
class Validation {

	use Error;


	/**
	 * @param mixed $status_code
	 *
	 * @return bool
	 */
	public static function is200( mixed $status_code ): bool {
		return !is_null( $status_code) && (int) $status_code === 200;
	}


	/**
	 * Validate if true
	 * @param null|int|string|bool $string
	 *
	 * @return bool
	 */
	public static function isTrue( null|int|string|bool $string ): bool {
		return ( !is_null($string) && (bool) $string === true );
	}
}