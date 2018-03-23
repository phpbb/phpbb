<?php

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter;
use DOMDocument;
use DOMXPath;
abstract class Utils
{
	public static function getAttributeValues($xml, $tagName, $attrName)
	{
		$values = [];
		if (\strpos($xml, '<' . $tagName) !== \false)
		{
			$regexp = '(<' . \preg_quote($tagName) . '(?= )[^>]*? ' . \preg_quote($attrName) . '="([^"]*+))';
			\preg_match_all($regexp, $xml, $matches);
			foreach ($matches[1] as $value)
				$values[] = \html_entity_decode($value, \ENT_QUOTES, 'UTF-8');
		}
		return $values;
	}
	public static function encodeUnicodeSupplementaryCharacters($str)
	{
		return \preg_replace_callback(
			'([\\xF0-\\xF4]...)S',
			__CLASS__ . '::encodeUnicodeSupplementaryCharactersCallback',
			$str
		);
	}
	public static function removeFormatting($xml)
	{
		$dom   = self::loadXML($xml);
		$xpath = new DOMXPath($dom);
		foreach ($xpath->query('//e | //s') as $node)
			$node->parentNode->removeChild($node);
		return $dom->documentElement->textContent;
	}
	public static function removeTag($xml, $tagName, $nestingLevel = 0)
	{
		if (\strpos($xml, '<' . $tagName) === \false)
			return $xml;
		$dom   = self::loadXML($xml);
		$xpath = new DOMXPath($dom);
		$query = '//' . $tagName . '[count(ancestor::' . $tagName . ') >= ' . $nestingLevel . ']';
		$nodes = $xpath->query($query);
		foreach ($nodes as $node)
			$node->parentNode->removeChild($node);
		return self::saveXML($dom);
	}
	public static function replaceAttributes($xml, $tagName, callable $callback)
	{
		if (\strpos($xml, '<' . $tagName) === \false)
			return $xml;
		return \preg_replace_callback(
			'((<' . \preg_quote($tagName) . ')(?=[ />])[^>]*?(/?>))',
			function ($m) use ($callback)
			{
				return $m[1] . self::serializeAttributes($callback(self::parseAttributes($m[0]))) . $m[2];
			},
			$xml
		);
	}
	protected static function encodeUnicodeSupplementaryCharactersCallback(array $m)
	{
		$utf8 = $m[0];
		$cp   = (\ord($utf8[0]) << 18) + (\ord($utf8[1]) << 12) + (\ord($utf8[2]) << 6) + \ord($utf8[3]) - 0x3C82080;
		return '&#' . $cp . ';';
	}
	protected static function loadXML($xml)
	{
		$flags = (\LIBXML_VERSION >= 20700) ? \LIBXML_COMPACT | \LIBXML_PARSEHUGE : 0;
		$dom = new DOMDocument;
		$dom->loadXML($xml, $flags);
		return $dom;
	}
	protected static function parseAttributes($xml)
	{
		$attributes = [];
		if (\strpos($xml, '="') !== \false)
		{
			\preg_match_all('(([^ =]++)="([^"]*))S', $xml, $matches);
			foreach ($matches[1] as $i => $attrName)
				$attributes[$attrName] = \html_entity_decode($matches[2][$i], \ENT_QUOTES, 'UTF-8');
		}
		return $attributes;
	}
	protected static function saveXML(DOMDocument $dom)
	{
		return self::encodeUnicodeSupplementaryCharacters($dom->saveXML($dom->documentElement));
	}
	protected static function serializeAttributes(array $attributes)
	{
		$xml = '';
		\ksort($attributes);
		foreach ($attributes as $attrName => $attrValue)
			$xml .= ' ' . \htmlspecialchars($attrName, \ENT_QUOTES) . '="' . \htmlspecialchars($attrValue, \ENT_COMPAT) . '"';
		$xml = \preg_replace('/\\r\\n?/', "\n", $xml);
		$xml = \preg_replace('/[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F]+/S', '', $xml);
		$xml = \str_replace("\n", '&#10;', $xml);
		return self::encodeUnicodeSupplementaryCharacters($xml);
	}
}