<?php

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Plugins\Litedown\Parser\Passes;
use s9e\TextFormatter\Parser;
use s9e\TextFormatter\Plugins\Litedown\Parser\ParsedText;
abstract class AbstractPass
{
	protected $parser;
	protected $text;
	public function __construct(Parser $parser, ParsedText $text)
	{
		$this->parser = $parser;
		$this->text   = $text;
	}
	abstract public function parse();
}