<?php

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Plugins\Litedown\Parser\Passes;
class Strikethrough extends AbstractPass
{
	public function parse()
	{
		$pos = $this->text->indexOf('~~');
		if ($pos === \false)
			return;
		\preg_match_all('/~~[^\\x17]+?~~(?!~)/', $this->text, $matches, \PREG_OFFSET_CAPTURE, $pos);
		foreach ($matches[0] as $_4b034d25)
		{
			list($match, $matchPos) = $_4b034d25;
			$matchLen = \strlen($match);
			$endPos   = $matchPos + $matchLen - 2;
			$this->parser->addTagPair('DEL', $matchPos, 2, $endPos, 2);
			$this->text->overwrite($matchPos, 2);
			$this->text->overwrite($endPos, 2);
		}
	}
}