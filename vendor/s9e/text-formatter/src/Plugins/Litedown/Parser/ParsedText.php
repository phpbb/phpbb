<?php

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Plugins\Litedown\Parser;
class ParsedText
{
	public $decodeHtmlEntities = \false;
	protected $hasEscapedChars = \false;
	public $hasReferences = \false;
	public $linkReferences = [];
	protected $text;
	public function __construct($text)
	{
		if (\strpos($text, '\\') !== \false && \preg_match('/\\\\[!"\'()*[\\\\\\]^_`~]/', $text))
		{
			$this->hasEscapedChars = \true;
			$text = \strtr(
				$text,
				[
					'\\!' => "\x1B0", '\\"' => "\x1B1", "\\'" => "\x1B2", '\\('  => "\x1B3",
					'\\)' => "\x1B4", '\\*' => "\x1B5", '\\[' => "\x1B6", '\\\\' => "\x1B7",
					'\\]' => "\x1B8", '\\^' => "\x1B9", '\\_' => "\x1BA", '\\`'  => "\x1BB",
					'\\~' => "\x1BC"
				]
			);
		}
		$this->text = $text . "\n\n\x17";
	}
	public function __toString()
	{
		return $this->text;
	}
	public function charAt($pos)
	{
		return $this->text[$pos];
	}
	public function decode($str)
	{
		if ($this->decodeHtmlEntities && \strpos($str, '&') !== \false)
			$str = \html_entity_decode($str, \ENT_QUOTES, 'UTF-8');
		$str = \str_replace("\x1A", '', $str);
		if ($this->hasEscapedChars)
			$str = \strtr(
				$str,
				[
					"\x1B0" => '!', "\x1B1" => '"', "\x1B2" => "'", "\x1B3" => '(',
					"\x1B4" => ')', "\x1B5" => '*', "\x1B6" => '[', "\x1B7" => '\\',
					"\x1B8" => ']', "\x1B9" => '^', "\x1BA" => '_', "\x1BB" => '`',
					"\x1BC" => '~'
				]
			);
		return $str;
	}
	public function indexOf($str, $pos = 0)
	{
		return \strpos($this->text, $str, $pos);
	}
	public function isAfterWhitespace($pos)
	{
		return ($pos > 0 && $this->isWhitespace($this->text[$pos - 1]));
	}
	public function isAlnum($chr)
	{
		return (\strpos(' abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', $chr) > 0);
	}
	public function isBeforeWhitespace($pos)
	{
		return $this->isWhitespace($this->text[$pos + 1]);
	}
	public function isSurroundedByAlnum($pos, $len)
	{
		return ($pos > 0 && $this->isAlnum($this->text[$pos - 1]) && $this->isAlnum($this->text[$pos + $len]));
	}
	public function isWhitespace($chr)
	{
		return (\strpos(" \n\t", $chr) !== \false);
	}
	public function markBoundary($pos)
	{
		$this->text[$pos] = "\x17";
	}
	public function overwrite($pos, $len)
	{
		if ($len > 0)
			$this->text = \substr($this->text, 0, $pos) . \str_repeat("\x1A", $len) . \substr($this->text, $pos + $len);
	}
}