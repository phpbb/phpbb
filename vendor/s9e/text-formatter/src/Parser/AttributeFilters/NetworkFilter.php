<?php

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Parser\AttributeFilters;
class NetworkFilter
{
	public static function filterIp($attrValue)
	{
		return \filter_var($attrValue, \FILTER_VALIDATE_IP);
	}
	public static function filterIpport($attrValue)
	{
		if (\preg_match('/^\\[([^\\]]+)(\\]:[1-9][0-9]*)$/D', $attrValue, $m))
		{
			$ip = self::filterIpv6($m[1]);
			if ($ip === \false)
				return \false;
			return '[' . $ip . $m[2];
		}
		if (\preg_match('/^([^:]+)(:[1-9][0-9]*)$/D', $attrValue, $m))
		{
			$ip = self::filterIpv4($m[1]);
			if ($ip === \false)
				return \false;
			return $ip . $m[2];
		}
		return \false;
	}
	public static function filterIpv4($attrValue)
	{
		return \filter_var($attrValue, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4);
	}
	public static function filterIpv6($attrValue)
	{
		return \filter_var($attrValue, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV6);
	}
}