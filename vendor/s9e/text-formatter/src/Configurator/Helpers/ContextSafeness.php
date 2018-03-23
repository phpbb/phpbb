<?php

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\Helpers;
abstract class ContextSafeness
{
	public static function getDisallowedCharactersAsURL()
	{
		return [':'];
	}
	public static function getDisallowedCharactersInCSS()
	{
		return ['(', ')', ':', '\\', '"', "'", ';', '{', '}'];
	}
	public static function getDisallowedCharactersInJS()
	{
		return ['(', ')', '"', "'", '\\', "\r", "\n", "\xE2\x80\xA8", "\xE2\x80\xA9", ':', '%', '='];
	}
}