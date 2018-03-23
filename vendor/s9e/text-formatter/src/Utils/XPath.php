<?php

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Utils;
use InvalidArgumentException;
abstract class XPath
{
	public static function export($value)
	{
		if (!\is_scalar($value))
			throw new InvalidArgumentException(__METHOD__ . '() cannot export non-scalar values');
		if (\is_int($value))
			return (string) $value;
		if (\is_float($value))
			return \preg_replace('(\\.?0+$)', '', \sprintf('%F', $value));
		return self::exportString((string) $value);
	}
	protected static function exportString($str)
	{
		if (\strpos($str, "'") === \false)
			return "'" . $str . "'";
		if (\strpos($str, '"') === \false)
			return '"' . $str . '"';
		$toks = [];
		$c    = '"';
		$pos  = 0;
		while ($pos < \strlen($str))
		{
			$spn = \strcspn($str, $c, $pos);
			if ($spn)
			{
				$toks[] = $c . \substr($str, $pos, $spn) . $c;
				$pos   += $spn;
			}
			$c = ($c === '"') ? "'" : '"';
		}
		return 'concat(' . \implode(',', $toks) . ')';
	}
}