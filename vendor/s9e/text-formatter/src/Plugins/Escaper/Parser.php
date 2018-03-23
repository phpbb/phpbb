<?php

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Plugins\Escaper;
use s9e\TextFormatter\Plugins\ParserBase;
class Parser extends ParserBase
{
	public function parse($text, array $matches)
	{
		foreach ($matches as $m)
			$this->parser->addTagPair(
				$this->config['tagName'],
				$m[0][1],
				1,
				$m[0][1] + \strlen($m[0][0]),
				0
			);
	}
}