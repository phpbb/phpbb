<?php

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\TemplateNormalizations;
use DOMElement;
class OptimizeNestedConditionals extends AbstractNormalization
{
	protected $queries = ['//xsl:choose/xsl:otherwise[count(node()) = 1]/xsl:choose'];
	protected function normalizeElement(DOMElement $element)
	{
		$otherwise   = $element->parentNode;
		$outerChoose = $otherwise->parentNode;
		while ($element->firstChild)
			$outerChoose->appendChild($element->removeChild($element->firstChild));
		$outerChoose->removeChild($otherwise);
	}
}