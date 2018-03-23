<?php

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Plugins\Litedown\Parser\Passes;
use s9e\TextFormatter\Plugins\Litedown\Parser\LinkAttributesSetter;
class Links extends AbstractPass
{
	use LinkAttributesSetter;
	public function parse()
	{
		if ($this->text->indexOf('](') !== \false)
			$this->parseInlineLinks();
		if ($this->text->hasReferences)
			$this->parseReferenceLinks();
	}
	protected function addLinkTag($startPos, $endPos, $endLen, $linkInfo)
	{
		$priority = ($endLen === 1) ? 1 : -1;
		$tag = $this->parser->addTagPair('URL', $startPos, 1, $endPos, $endLen, $priority);
		$this->setLinkAttributes($tag, $linkInfo, 'url');
		$this->text->overwrite($startPos, 1);
		$this->text->overwrite($endPos,   $endLen);
	}
	protected function getLabels()
	{
		\preg_match_all(
			'/\\[((?:[^\\x17[\\]]|\\[[^\\x17[\\]]*\\])*)\\]/',
			$this->text,
			$matches,
			\PREG_OFFSET_CAPTURE
		);
		$labels = [];
		foreach ($matches[1] as $m)
			$labels[$m[1] - 1] = \strtolower($m[0]);
		return $labels;
	}
	protected function parseInlineLinks()
	{
		\preg_match_all(
			'/\\[(?:[^\\x17[\\]]|\\[[^\\x17[\\]]*\\])*\\]\\(( *(?:[^\\x17\\s()]|\\([^\\x17\\s()]*\\))*(?=[ )]) *(?:"[^\\x17]*?"|\'[^\\x17]*?\'|\\([^\\x17)]*\\))? *)\\)/',
			$this->text,
			$matches,
			\PREG_OFFSET_CAPTURE | \PREG_SET_ORDER
		);
		foreach ($matches as $m)
		{
			$linkInfo = $m[1][0];
			$startPos = $m[0][1];
			$endLen   = 3 + \strlen($linkInfo);
			$endPos   = $startPos + \strlen($m[0][0]) - $endLen;
			$this->addLinkTag($startPos, $endPos, $endLen, $linkInfo);
		}
	}
	protected function parseReferenceLinks()
	{
		$labels = $this->getLabels();
		foreach ($labels as $startPos => $id)
		{
			$labelPos = $startPos + 2 + \strlen($id);
			$endPos   = $labelPos - 1;
			$endLen   = 1;
			if ($this->text->charAt($labelPos) === ' ')
				++$labelPos;
			if (isset($labels[$labelPos], $this->text->linkReferences[$labels[$labelPos]]))
			{
				$id     = $labels[$labelPos];
				$endLen = $labelPos + 2 + \strlen($id) - $endPos;
			}
			if (isset($this->text->linkReferences[$id]))
				$this->addLinkTag($startPos, $endPos, $endLen, $this->text->linkReferences[$id]);
		}
	}
}