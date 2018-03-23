<?php

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Plugins\Litedown\Parser\Passes;
use s9e\TextFormatter\Parser as Rules;
class Blocks extends AbstractPass
{
	protected $setextLines = [];
	public function parse()
	{
		$this->matchSetextLines();
		$codeFence    = \null;
		$codeIndent   = 4;
		$codeTag      = \null;
		$lineIsEmpty  = \true;
		$lists        = [];
		$listsCnt     = 0;
		$newContext   = \false;
		$quotes       = [];
		$quotesCnt    = 0;
		$textBoundary = 0;
		$regexp = '/^(?:(?=[-*+\\d \\t>`~#_])((?: {0,3}> ?)+)?([ \\t]+)?(\\* *\\* *\\*[* ]*$|- *- *-[- ]*$|_ *_ *_[_ ]*$|=+$)?((?:[-*+]|\\d+\\.)[ \\t]+(?=\\S))?[ \\t]*(#{1,6}[ \\t]+|```+[^`\\n]*$|~~~+[^~\\n]*$)?)?/m';
		\preg_match_all($regexp, $this->text, $matches, \PREG_OFFSET_CAPTURE | \PREG_SET_ORDER);
		foreach ($matches as $m)
		{
			$matchPos   = $m[0][1];
			$matchLen   = \strlen($m[0][0]);
			$ignoreLen  = 0;
			$quoteDepth = 0;
			$continuation = !$lineIsEmpty;
			$lfPos       = $this->text->indexOf("\n", $matchPos);
			$lineIsEmpty = ($lfPos === $matchPos + $matchLen && empty($m[3][0]) && empty($m[4][0]) && empty($m[5][0]));
			$breakParagraph = ($lineIsEmpty && $continuation);
			if (!empty($m[1][0]))
			{
				$quoteDepth = \substr_count($m[1][0], '>');
				$ignoreLen  = \strlen($m[1][0]);
				if (isset($codeTag) && $codeTag->hasAttribute('quoteDepth'))
				{
					$quoteDepth = \min($quoteDepth, $codeTag->getAttribute('quoteDepth'));
					$ignoreLen  = $this->computeQuoteIgnoreLen($m[1][0], $quoteDepth);
				}
				$this->text->overwrite($matchPos, $ignoreLen);
			}
			if ($quoteDepth < $quotesCnt && !$continuation)
			{
				$newContext = \true;
				do
				{
					$this->parser->addEndTag('QUOTE', $textBoundary, 0)
					             ->pairWith(\array_pop($quotes));
				}
				while ($quoteDepth < --$quotesCnt);
			}
			if ($quoteDepth > $quotesCnt && !$lineIsEmpty)
			{
				$newContext = \true;
				do
				{
					$tag = $this->parser->addStartTag('QUOTE', $matchPos, 0, $quotesCnt - 999);
					$quotes[] = $tag;
				}
				while ($quoteDepth > ++$quotesCnt);
			}
			$indentWidth = 0;
			$indentPos   = 0;
			if (!empty($m[2][0]) && !$codeFence)
			{
				$indentStr = $m[2][0];
				$indentLen = \strlen($indentStr);
				do
				{
					if ($indentStr[$indentPos] === ' ')
						++$indentWidth;
					else
						$indentWidth = ($indentWidth + 4) & ~3;
				}
				while (++$indentPos < $indentLen && $indentWidth < $codeIndent);
			}
			if (isset($codeTag) && !$codeFence && $indentWidth < $codeIndent && !$lineIsEmpty)
				$newContext = \true;
			if ($newContext)
			{
				$newContext = \false;
				if (isset($codeTag))
				{
					if ($textBoundary > $codeTag->getPos())
					{
						$this->text->overwrite($codeTag->getPos(), $textBoundary - $codeTag->getPos());
						$endTag = $this->parser->addEndTag('CODE', $textBoundary, 0, -1);
						$endTag->pairWith($codeTag);
					}
					else
						$codeTag->invalidate();
					$codeTag = \null;
					$codeFence = \null;
				}
				foreach ($lists as $list)
					$this->closeList($list, $textBoundary);
				$lists    = [];
				$listsCnt = 0;
				if ($matchPos)
					$this->text->markBoundary($matchPos - 1);
			}
			if ($indentWidth >= $codeIndent)
			{
				if (isset($codeTag) || !$continuation)
				{
					$ignoreLen += $indentPos;
					if (!isset($codeTag))
						$codeTag = $this->parser->addStartTag('CODE', $matchPos + $ignoreLen, 0, -999);
					$m = [];
				}
			}
			else
			{
				$hasListItem = !empty($m[4][0]);
				if (!$indentWidth && !$continuation && !$hasListItem)
					$listIndex = -1;
				elseif ($continuation && !$hasListItem)
					$listIndex = $listsCnt - 1;
				elseif (!$listsCnt)
					$listIndex = ($hasListItem) ? 0 : -1;
				else
				{
					$listIndex = 0;
					while ($listIndex < $listsCnt && $indentWidth > $lists[$listIndex]['maxIndent'])
						++$listIndex;
				}
				while ($listIndex < $listsCnt - 1)
				{
					$this->closeList(\array_pop($lists), $textBoundary);
					--$listsCnt;
				}
				if ($listIndex === $listsCnt && !$hasListItem)
					--$listIndex;
				if ($hasListItem && $listIndex >= 0)
				{
					$breakParagraph = \true;
					$tagPos = $matchPos + $ignoreLen + $indentPos;
					$tagLen = \strlen($m[4][0]);
					$itemTag = $this->parser->addStartTag('LI', $tagPos, $tagLen);
					$this->text->overwrite($tagPos, $tagLen);
					if ($listIndex < $listsCnt)
					{
						$this->parser->addEndTag('LI', $textBoundary, 0)
						             ->pairWith($lists[$listIndex]['itemTag']);
						$lists[$listIndex]['itemTag']    = $itemTag;
						$lists[$listIndex]['itemTags'][] = $itemTag;
					}
					else
					{
						++$listsCnt;
						if ($listIndex)
						{
							$minIndent = $lists[$listIndex - 1]['maxIndent'] + 1;
							$maxIndent = \max($minIndent, $listIndex * 4);
						}
						else
						{
							$minIndent = 0;
							$maxIndent = $indentWidth;
						}
						$listTag = $this->parser->addStartTag('LIST', $tagPos, 0);
						if (\strpos($m[4][0], '.') !== \false)
						{
							$listTag->setAttribute('type', 'decimal');
							$start = (int) $m[4][0];
							if ($start !== 1)
								$listTag->setAttribute('start', $start);
						}
						$lists[] = [
							'listTag'   => $listTag,
							'itemTag'   => $itemTag,
							'itemTags'  => [$itemTag],
							'minIndent' => $minIndent,
							'maxIndent' => $maxIndent,
							'tight'     => \true
						];
					}
				}
				if ($listsCnt && !$continuation && !$lineIsEmpty)
					if (\count($lists[0]['itemTags']) > 1 || !$hasListItem)
					{
						foreach ($lists as &$list)
							$list['tight'] = \false;
						unset($list);
					}
				$codeIndent = ($listsCnt + 1) * 4;
			}
			if (isset($m[5]))
			{
				if ($m[5][0][0] === '#')
				{
					$startLen = \strlen($m[5][0]);
					$startPos = $matchPos + $matchLen - $startLen;
					$endLen   = $this->getAtxHeaderEndTagLen($matchPos + $matchLen, $lfPos);
					$endPos   = $lfPos - $endLen;
					$this->parser->addTagPair('H' . \strspn($m[5][0], '#', 0, 6), $startPos, $startLen, $endPos, $endLen);
					$this->text->markBoundary($startPos);
					$this->text->markBoundary($lfPos);
					if ($continuation)
						$breakParagraph = \true;
				}
				elseif ($m[5][0][0] === '`' || $m[5][0][0] === '~')
				{
					$tagPos = $matchPos + $ignoreLen;
					$tagLen = $lfPos - $tagPos;
					if (isset($codeTag) && $m[5][0] === $codeFence)
					{
						$endTag = $this->parser->addEndTag('CODE', $tagPos, $tagLen, -1);
						$endTag->pairWith($codeTag);
						$this->parser->addIgnoreTag($textBoundary, $tagPos - $textBoundary);
						$this->text->overwrite($codeTag->getPos(), $tagPos + $tagLen - $codeTag->getPos());
						$codeTag = \null;
						$codeFence = \null;
					}
					elseif (!isset($codeTag))
					{
						$codeTag   = $this->parser->addStartTag('CODE', $tagPos, $tagLen);
						$codeFence = \substr($m[5][0], 0, \strspn($m[5][0], '`~'));
						$codeTag->setAttribute('quoteDepth', $quoteDepth);
						$this->parser->addIgnoreTag($tagPos + $tagLen, 1);
						$lang = \trim(\trim($m[5][0], '`~'));
						if ($lang !== '')
							$codeTag->setAttribute('lang', $lang);
					}
				}
			}
			elseif (!empty($m[3][0]) && !$listsCnt && $this->text->charAt($matchPos + $matchLen) !== "\x17")
			{
				$this->parser->addSelfClosingTag('HR', $matchPos + $ignoreLen, $matchLen - $ignoreLen);
				$breakParagraph = \true;
				$this->text->markBoundary($lfPos);
			}
			elseif (isset($this->setextLines[$lfPos]) && $this->setextLines[$lfPos]['quoteDepth'] === $quoteDepth && !$lineIsEmpty && !$listsCnt && !isset($codeTag))
			{
				$this->parser->addTagPair(
					$this->setextLines[$lfPos]['tagName'],
					$matchPos + $ignoreLen,
					0,
					$this->setextLines[$lfPos]['endPos'],
					$this->setextLines[$lfPos]['endLen']
				);
				$this->text->markBoundary($this->setextLines[$lfPos]['endPos'] + $this->setextLines[$lfPos]['endLen']);
			}
			if ($breakParagraph)
			{
				$this->parser->addParagraphBreak($textBoundary);
				$this->text->markBoundary($textBoundary);
			}
			if (!$lineIsEmpty)
				$textBoundary = $lfPos;
			if ($ignoreLen)
				$this->parser->addIgnoreTag($matchPos, $ignoreLen, 1000);
		}
	}
	protected function closeList(array $list, $textBoundary)
	{
		$this->parser->addEndTag('LIST', $textBoundary, 0)->pairWith($list['listTag']);
		$this->parser->addEndTag('LI',   $textBoundary, 0)->pairWith($list['itemTag']);
		if ($list['tight'])
			foreach ($list['itemTags'] as $itemTag)
				$itemTag->removeFlags(Rules::RULE_CREATE_PARAGRAPHS);
	}
	protected function computeQuoteIgnoreLen($str, $maxQuoteDepth)
	{
		$remaining = $str;
		while (--$maxQuoteDepth >= 0)
			$remaining = \preg_replace('/^ *> ?/', '', $remaining);
		return \strlen($str) - \strlen($remaining);
	}
	protected function getAtxHeaderEndTagLen($startPos, $endPos)
	{
		$content = \substr($this->text, $startPos, $endPos - $startPos);
		\preg_match('/[ \\t]*#*[ \\t]*$/', $content, $m);
		return \strlen($m[0]);
	}
	protected function matchSetextLines()
	{
		if ($this->text->indexOf('-') === \false && $this->text->indexOf('=') === \false)
			return;
		$regexp = '/^(?=[-=>])(?:> ?)*(?=[-=])(?:-+|=+) *$/m';
		if (!\preg_match_all($regexp, $this->text, $matches, \PREG_OFFSET_CAPTURE))
			return;
		foreach ($matches[0] as $_4b034d25)
		{
			list($match, $matchPos) = $_4b034d25;
			$endPos = $matchPos - 1;
			while ($endPos > 0 && $this->text->charAt($endPos - 1) === ' ')
				--$endPos;
			$this->setextLines[$matchPos - 1] = [
				'endLen'     => $matchPos + \strlen($match) - $endPos,
				'endPos'     => $endPos,
				'quoteDepth' => \substr_count($match, '>'),
				'tagName'    => ($match[0] === '=') ? 'H1' : 'H2'
			];
		}
	}
}