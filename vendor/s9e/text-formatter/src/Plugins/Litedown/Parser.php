<?php

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Plugins\Litedown;
use s9e\TextFormatter\Parser\Tag;
use s9e\TextFormatter\Plugins\Litedown\Parser\ParsedText;
use s9e\TextFormatter\Plugins\Litedown\Parser\Passes\Blocks;
use s9e\TextFormatter\Plugins\Litedown\Parser\Passes\Emphasis;
use s9e\TextFormatter\Plugins\Litedown\Parser\Passes\ForcedLineBreaks;
use s9e\TextFormatter\Plugins\Litedown\Parser\Passes\Images;
use s9e\TextFormatter\Plugins\Litedown\Parser\Passes\InlineCode;
use s9e\TextFormatter\Plugins\Litedown\Parser\Passes\LinkReferences;
use s9e\TextFormatter\Plugins\Litedown\Parser\Passes\Links;
use s9e\TextFormatter\Plugins\Litedown\Parser\Passes\Strikethrough;
use s9e\TextFormatter\Plugins\Litedown\Parser\Passes\Subscript;
use s9e\TextFormatter\Plugins\Litedown\Parser\Passes\Superscript;
use s9e\TextFormatter\Plugins\ParserBase;
class Parser extends ParserBase
{
	public function parse($text, array $matches)
	{
		$text = new ParsedText($text);
		$text->decodeHtmlEntities = $this->config['decodeHtmlEntities'];
		(new Blocks($this->parser, $text))->parse();
		(new LinkReferences($this->parser, $text))->parse();
		(new InlineCode($this->parser, $text))->parse();
		(new Images($this->parser, $text))->parse();
		(new Links($this->parser, $text))->parse();
		(new Strikethrough($this->parser, $text))->parse();
		(new Subscript($this->parser, $text))->parse();
		(new Superscript($this->parser, $text))->parse();
		(new Emphasis($this->parser, $text))->parse();
		(new ForcedLineBreaks($this->parser, $text))->parse();
	}
}