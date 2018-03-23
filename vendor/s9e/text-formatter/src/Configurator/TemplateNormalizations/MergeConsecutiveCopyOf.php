<?php

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\TemplateNormalizations;
use DOMElement;
class MergeConsecutiveCopyOf extends AbstractNormalization
{
	protected $queries = ['//xsl:copy-of'];
	protected function normalizeElement(DOMElement $element)
	{
		while ($this->nextSiblingIsCopyOf($element))
		{
			$element->setAttribute('select', $element->getAttribute('select') . '|' . $element->nextSibling->getAttribute('select'));
			$element->parentNode->removeChild($element->nextSibling);
		}
	}
	protected function nextSiblingIsCopyOf(DOMElement $element)
	{
		return ($element->nextSibling && $this->isXsl($element->nextSibling, 'copy-of'));
	}
}