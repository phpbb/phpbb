<?php

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Parser\AttributeFilters;
class TimestampFilter
{
	public static function filter($attrValue)
	{
		if (\preg_match('/^(?=\\d)(?:(\\d+)h)?(?:(\\d+)m)?(?:(\\d+)s)?$/D', $attrValue, $m))
		{
			$m += [0, 0, 0, 0];
			return \intval($m[1]) * 3600 + \intval($m[2]) * 60 + \intval($m[3]);
		}
		return NumericFilter::filterUint($attrValue);
	}
}