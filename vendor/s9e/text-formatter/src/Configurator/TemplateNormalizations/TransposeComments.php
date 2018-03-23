<?php

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\TemplateNormalizations;
use DOMNode;
class TransposeComments extends AbstractNormalization
{
	protected $queries = ['//comment()'];
	protected function normalizeNode(DOMNode $node)
	{
		$xslComment = $this->createElement('xsl:comment', $node->nodeValue);
		$node->parentNode->replaceChild($xslComment, $node);
	}
}