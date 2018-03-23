<?php

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Parser\AttributeFilters;
use s9e\TextFormatter\Parser\Logger;
class NumericFilter
{
	public static function filterFloat($attrValue)
	{
		return \filter_var($attrValue, \FILTER_VALIDATE_FLOAT);
	}
	public static function filterInt($attrValue)
	{
		return \filter_var($attrValue, \FILTER_VALIDATE_INT);
	}
	public static function filterRange($attrValue, $min, $max, Logger $logger = \null)
	{
		$attrValue = \filter_var($attrValue, \FILTER_VALIDATE_INT);
		if ($attrValue === \false)
			return \false;
		if ($attrValue < $min)
		{
			if (isset($logger))
				$logger->warn(
					'Value outside of range, adjusted up to min value',
					[
						'attrValue' => $attrValue,
						'min'       => $min,
						'max'       => $max
					]
				);
			return $min;
		}
		if ($attrValue > $max)
		{
			if (isset($logger))
				$logger->warn(
					'Value outside of range, adjusted down to max value',
					[
						'attrValue' => $attrValue,
						'min'       => $min,
						'max'       => $max
					]
				);
			return $max;
		}
		return $attrValue;
	}
	public static function filterUint($attrValue)
	{
		return \filter_var($attrValue, \FILTER_VALIDATE_INT, [
			'options' => ['min_range' => 0]
		]);
	}
}