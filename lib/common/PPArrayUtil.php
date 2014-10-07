<?php

class PPArrayUtil {

	/**
	 *
	 * @param array $arr
	 * @return true if $arr is an associative array
	 */
	public static function isAssocArray(array $arr) {
	    if(empty($arr)) {
	        return false;
	    }
	
	    foreach($arr as $k => $v) {
	        if(is_int($k)) {
	            return false;
	        }
	    }
	    return true;
	}
}
