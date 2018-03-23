<?php

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Plugins\Litedown\Parser\Passes;
class Subscript extends AbstractScript
{
	public function parse()
	{
		$this->parseAbstractScript('SUB', '~', '/~(?!\\()[^\\x17\\s~()]++~?/', '/~\\([^\\x17()]+\\)/');
	}
}