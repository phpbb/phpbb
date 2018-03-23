<?php

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Plugins\Litedown\Parser\Passes;
abstract class AbstractScript extends AbstractPass
{
	protected $longRegexp;
	protected $shortRegexp;
	protected $syntaxChar;
	
	protected $tagName;
	protected function parseAbstractScript($tagName, $syntaxChar, $shortRegexp, $longRegexp)
	{
		$this->tagName     = $tagName;
		$this->syntaxChar  = $syntaxChar;
		$this->shortRegexp = $shortRegexp;
		$this->longRegexp  = $longRegexp;
		$pos = $this->text->indexOf($this->syntaxChar);
		if ($pos === \false)
			return;
		$this->parseShortForm($pos);
		$this->parseLongForm($pos);
	}
	protected function parseLongForm($pos)
	{
		$pos = $this->text->indexOf($this->syntaxChar . '(', $pos);
		if ($pos === \false)
			return;
		\preg_match_all($this->longRegexp, $this->text, $matches, \PREG_OFFSET_CAPTURE, $pos);
		foreach ($matches[0] as $_4b034d25)
		{
			list($match, $matchPos) = $_4b034d25;
			$matchLen = \strlen($match);
			$this->parser->addTagPair($this->tagName, $matchPos, 2, $matchPos + $matchLen - 1, 1);
			$this->text->overwrite($matchPos, $matchLen);
		}
		if (!empty($matches[0]))
			$this->parseLongForm($pos);
	}
	protected function parseShortForm($pos)
	{
		\preg_match_all($this->shortRegexp, $this->text, $matches, \PREG_OFFSET_CAPTURE, $pos);
		foreach ($matches[0] as $_4b034d25)
		{
			list($match, $matchPos) = $_4b034d25;
			$matchLen = \strlen($match);
			$startPos = $matchPos;
			$endLen   = (\substr($match, -1) === $this->syntaxChar) ? 1 : 0;
			$endPos   = $matchPos + $matchLen - $endLen;
			$this->parser->addTagPair($this->tagName, $startPos, 1, $endPos, $endLen);
		}
	}
}