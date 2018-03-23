<?php

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\Items\AttributeFilters;
class NumberFilter extends RegexpFilter
{
	public function __construct()
	{
		parent::__construct('/^[0-9]+$/D');
		$this->markAsSafeAsURL();
		$this->markAsSafeInCSS();
		$this->markAsSafeInJS();
	}
}