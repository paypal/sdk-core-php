<?php

class PPArrayUtil {

	/**
	 *
	 * @param array $arr
	 * @return true if $arr is an associative array
	 */
	public static function isAssocArray(array $arr) {
		foreach($arr as $k => $v)
			return !is_int($k);
		return false;
	}
}