<?php

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Plugins\Litedown\Parser\Passes;
class Emphasis extends AbstractPass
{
	protected $closeEm;
	protected $closeStrong;
	protected $emPos;
	protected $emEndPos;
	protected $remaining;
	protected $strongPos;
	protected $strongEndPos;
	public function parse()
	{
		$this->parseEmphasisByCharacter('*', '/\\*+/');
		$this->parseEmphasisByCharacter('_', '/_+/');
	}
	protected function adjustEndingPositions()
	{
		if ($this->closeEm && $this->closeStrong)
			if ($this->emPos < $this->strongPos)
				$this->emEndPos += 2;
			else
				++$this->strongEndPos;
	}
	protected function adjustStartingPositions()
	{
		if (isset($this->emPos) && $this->emPos === $this->strongPos)
			if ($this->closeEm)
				$this->emPos += 2;
			else
				++$this->strongPos;
	}
	protected function closeSpans()
	{
		if ($this->closeEm)
		{
			--$this->remaining;
			$this->parser->addTagPair('EM', $this->emPos, 1, $this->emEndPos, 1);
			$this->emPos = \null;
		}
		if ($this->closeStrong)
		{
			$this->remaining -= 2;
			$this->parser->addTagPair('STRONG', $this->strongPos, 2, $this->strongEndPos, 2);
			$this->strongPos = \null;
		}
	}
	protected function parseEmphasisByCharacter($character, $regexp)
	{
		$pos = $this->text->indexOf($character);
		if ($pos === \false)
			return;
		foreach ($this->getEmphasisByBlock($regexp, $pos) as $block)
			$this->processEmphasisBlock($block);
	}
	protected function getEmphasisByBlock($regexp, $pos)
	{
		$block    = [];
		$blocks   = [];
		$breakPos = $this->text->indexOf("\x17", $pos);
		\preg_match_all($regexp, $this->text, $matches, \PREG_OFFSET_CAPTURE, $pos);
		foreach ($matches[0] as $m)
		{
			$matchPos = $m[1];
			$matchLen = \strlen($m[0]);
			if ($matchPos > $breakPos)
			{
				$blocks[] = $block;
				$block    = [];
				$breakPos = $this->text->indexOf("\x17", $matchPos);
			}
			if (!$this->ignoreEmphasis($matchPos, $matchLen))
				$block[] = [$matchPos, $matchLen];
		}
		$blocks[] = $block;
		return $blocks;
	}
	protected function ignoreEmphasis($matchPos, $matchLen)
	{
		return ($this->text->charAt($matchPos) === '_' && $matchLen === 1 && $this->text->isSurroundedByAlnum($matchPos, $matchLen));
	}
	protected function openSpans($pos)
	{
		if ($this->remaining & 1)
			$this->emPos     = $pos - $this->remaining;
		if ($this->remaining & 2)
			$this->strongPos = $pos - $this->remaining;
	}
	protected function processEmphasisBlock(array $block)
	{
		$this->emPos     = \null;
		$this->strongPos = \null;
		foreach ($block as $_aab3a45e)
		{
			list($matchPos, $matchLen) = $_aab3a45e;
			$this->processEmphasisMatch($matchPos, $matchLen);
		}
	}
	protected function processEmphasisMatch($matchPos, $matchLen)
	{
		$canOpen  = !$this->text->isBeforeWhitespace($matchPos + $matchLen - 1);
		$canClose = !$this->text->isAfterWhitespace($matchPos);
		$closeLen = ($canClose) ? \min($matchLen, 3) : 0;
		$this->closeEm      = ($closeLen & 1) && isset($this->emPos);
		$this->closeStrong  = ($closeLen & 2) && isset($this->strongPos);
		$this->emEndPos     = $matchPos;
		$this->strongEndPos = $matchPos;
		$this->remaining    = $matchLen;
		$this->adjustStartingPositions();
		$this->adjustEndingPositions();
		$this->closeSpans();
		$this->remaining = ($canOpen) ? \min($this->remaining, 3) : 0;
		$this->openSpans($matchPos + $matchLen);
	}
}