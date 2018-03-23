<?php

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Plugins\Litedown\Parser\Passes;
class ForcedLineBreaks extends AbstractPass
{
	public function parse()
	{
		$pos = $this->text->indexOf("  \n");
		while ($pos !== \false)
		{
			$this->parser->addBrTag($pos + 2);
			$pos = $this->text->indexOf("  \n", $pos + 3);
		}
	}
}