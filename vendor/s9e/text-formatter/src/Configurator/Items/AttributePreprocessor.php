<?php

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\Items;
use InvalidArgumentException;
use s9e\TextFormatter\Configurator\Items\Regexp;
class AttributePreprocessor extends Regexp
{
	public function getAttributes()
	{
		return $this->getNamedCaptures();
	}
	
	public function getRegexp()
	{
		return $this->regexp;
	}
}