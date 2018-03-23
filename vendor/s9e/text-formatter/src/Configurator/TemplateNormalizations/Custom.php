<?php

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\TemplateNormalizations;
use DOMElement;
class Custom extends AbstractNormalization
{
	protected $callback;
	public function __construct(callable $callback)
	{
		$this->callback = $callback;
	}
	public function normalize(DOMElement $template)
	{
		\call_user_func($this->callback, $template);
	}
}