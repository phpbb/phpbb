<?php

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Parser\AttributeFilters;
class MapFilter
{
	public static function filter($attrValue, array $map)
	{
		foreach ($map as $pair)
			if (\preg_match($pair[0], $attrValue))
				return $pair[1];
		return $attrValue;
	}
}