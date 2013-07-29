<?php
namespace PayPal\Core;
class PPUtils
{

	/**
	 *
	 * Convert a Name Value Pair (NVP) formatted string into
	 * an associative array taking care to urldecode array values
	 *
	 * @param string $nvpString
	 * @return array
	 */
	public static function nvpToMap($nvpString)
	{
		$ret = array();
		$params = explode("&", $nvpString);
		foreach ($params as $p) {
			list($k, $v) = explode("=", $p);
			$ret[$k] = urldecode($v);
		}
		return $ret;
	}



	/**
	 * Returns true if the array contains a key like $key
	 *
	 * @param array $map
	 * @param string $key
	 * @return bool
	 */
	public static function array_match_key($map, $key)
	{
		$replace = str_replace(array(
				'(',
				')',
				'.'
		), array(
				'\(',
				'\)',
				'\.'
		), $key);

		$pattern = "/$replace*/";

		foreach ($map as $k => $v) {
			preg_match($pattern, $k, $matches);
			if (count($matches) > 0) {
				return true;
			}
		}
		return false;
	}



	/**
	 * Get the local IP address. The client address is a required
	 * request parameter for some API calls
	 */
	public static function getLocalIPAddress()
	{
		if (array_key_exists("SERVER_ADDR", $_SERVER)) {
			// SERVER_ADDR is available only if we are running the CGI SAPI
			return $_SERVER['SERVER_ADDR'];

		} else {
			if (function_exists("gethostname")) {
				// gethostname is available only in PHP >= v5.3
				return gethostbyname(gethostname());

			} else {
				// fallback if nothing works
				return "127.0.0.1";
			}
		}
	}

	public static function xmlToArray($xmlInput)
	{
		$xml = simplexml_load_string($xmlInput);

		$ns = $xml->getNamespaces(true);
		$soap = $xml->children($ns['SOAP-ENV']);
		$getChild = $soap->Body->children();
		$array = array();
		$ret = PPUtils::convertXmlObjToArr($getChild, $array);
		return $ret;
	}



	private static function convertXmlObjToArr($obj, &$arr)
	{
		$children = $obj->children();
		foreach ($children as $elementName => $node) {
			$nextIdx = count($arr);
			$arr[$nextIdx] = array();
			$arr[$nextIdx]['name'] = strtolower((string)$elementName);
			$arr[$nextIdx]['attributes'] = array();
			$attributes = $node->attributes();
			foreach ($attributes as $attributeName => $attributeValue) {
				$attribName = strtolower(trim((string)$attributeName));
				$attribVal = trim((string)$attributeValue);
				$arr[$nextIdx]['attributes'][$attribName] = $attribVal;
			}
			$text = (string)$node;
			$text = trim($text);
			if (strlen($text) > 0) {
				$arr[$nextIdx]['text'] = $text;
			}
			$arr[$nextIdx]['children'] = array();
			PPutils::convertXmlObjToArr($node, $arr[$nextIdx]['children']);
		}
		return $arr;
	}



	/**
	 * Escapes invalid xml characters
	 *
	 * @param $textContent = xml data to be escaped
	 * @return string
	 */
	public static function escapeInvalidXmlCharsRegex($textContent)
	{
		return htmlspecialchars($textContent, (1 | 2), 'UTF-8', false);
	}



	/**
	 * @param array $map
	 * @param string $keyPrefix
	 * @return array
	 */
	public static function filterKeyPrefix(array $map, $keyPrefix)
	{
		$filtered = array();
		foreach ($map as $key => $val) {
			if (($pos = stripos($key, $keyPrefix)) !== 0) {
				continue;
			}

			$filtered[substr_replace($key, '', 0, strlen($keyPrefix))] = $val;
		}

		return $filtered;
	}



	/**
	 * @var array|ReflectionProperty[]
	 */
	private static $propertiesRefl = array();

	/**
	 * @var array|string[]
	 */
	private static $propertiesType = array();



	/**
	 * @param string $class
	 * @param string $propertyName
	 * @throws RuntimeException
	 * @return string
	 */
	public static function propertyAnnotations($class, $propertyName)
	{
		$class = is_object($class) ? get_class($class) : $class;
		if (!class_exists('ReflectionProperty')) {
			throw new \RuntimeException("Property type of " . $class . "::{$propertyName} cannot be resolved");
		}

		if ($annotations =& self::$propertiesType[$class][$propertyName]) {
			return $annotations;
		}

		if (!($refl =& self::$propertiesRefl[$class][$propertyName])) {
			$refl = new \ReflectionProperty($class, $propertyName);
		}

		// todo: smarter regexp
		if (!preg_match_all('~\@([^\s@\(]+)[\t ]*(?:\(?([^\n@]+)\)?)?~i', $refl->getDocComment(), $annots, PREG_PATTERN_ORDER)) {
			return NULL;
		}
		foreach ($annots[1] as $i => $annot) {
			$annotations[strtolower($annot)] = empty($annots[2][$i]) ? TRUE : rtrim($annots[2][$i], " \t\n\r)");
		}

		return $annotations;
	}

	/**
	 * @param string $class
	 * @param string $propertyName
	 * @return string
	 */
	public static function isAttributeProperty($class, $propertyName) {
		if (($annotations = self::propertyAnnotations($class, $propertyName))) {
			return array_key_exists('attribute', $annotations);
		}
		return FALSE;
	}

	/**
	 * @param string $class
	 * @param string $propertyName
	 * @return string
	 */
	public static function isPropertyArray($class, $propertyName) {
		if (($annotations = self::propertyAnnotations($class, $propertyName))) {
			if (isset($annotations['var']) && substr($annotations['var'], -2) === '[]') {
				return TRUE;

			} elseif (isset($annotations['array'])) {
				return TRUE;
			}
		}

		return FALSE;
	}



	/**
	 * @param string $class
	 * @param string $propertyName
	 * @throws RuntimeException
	 * @return string
	 */
	public static function propertyType($class, $propertyName)
	{
		if (($annotations = self::propertyAnnotations($class, $propertyName)) && isset($annotations['var'])) {
			if (substr($annotations['var'], -2) === '[]') {
				return substr($annotations['var'], 0, -2);
			}

			return $annotations['var'];
		}

		return 'string';
	}

	/**
	 * @param object $object
	 * @return array
	 */
	public static function objectProperties($object)
	{
		$props = array();
		foreach (get_object_vars($object) as $property => $default) {
			$annotations = self::propertyAnnotations($object, $property);
			if (isset($annotations['name'])) {
				$props[strtolower($annotations['name'])] = $property;
			}

			$props[strtolower($property)] = $property;
		}

		return $props;
	}



	/**
	 * @param array $array
	 * @return array
	 */
	public static function lowerKeys(array $array)
	{
		$ret = array();
		foreach ($array as $key => $value) {
			$ret[strtolower($key)] = $value;
		}

		return $ret;
	}

}