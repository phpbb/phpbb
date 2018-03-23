<?php

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\Items\AttributeFilters;
class ColorFilter extends RegexpFilter
{
	public function __construct()
	{
		parent::__construct('/^(?>#[0-9a-f]{3,6}|rgb\\(\\d{1,3}, *\\d{1,3}, *\\d{1,3}\\)|[a-z]+)$/Di');
		$this->markAsSafeInCSS();
	}
}