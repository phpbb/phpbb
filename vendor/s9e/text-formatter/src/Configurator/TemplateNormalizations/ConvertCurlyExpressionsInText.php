<?php

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\TemplateNormalizations;
use DOMNode;
class ConvertCurlyExpressionsInText extends AbstractNormalization
{
	protected $queries = ['//*[namespace-uri() != $XSL]/text()[contains(., "{@") or contains(., "{$")]'];
	protected function insertTextBefore($text, $node)
	{
		$node->parentNode->insertBefore($this->createTextNode($text), $node);
	}
	protected function normalizeNode(DOMNode $node)
	{
		$parentNode = $node->parentNode;
		\preg_match_all(
			'#\\{([$@][-\\w]+)\\}#',
			$node->textContent,
			$matches,
			\PREG_SET_ORDER | \PREG_OFFSET_CAPTURE
		);
		$lastPos = 0;
		foreach ($matches as $m)
		{
			$pos = $m[0][1];
			if ($pos > $lastPos)
			{
				$text = \substr($node->textContent, $lastPos, $pos - $lastPos);
				$this->insertTextBefore($text, $node);
			}
			$lastPos = $pos + \strlen($m[0][0]);
			$parentNode
				->insertBefore($this->createElement('xsl:value-of'), $node)
				->setAttribute('select', $m[1][0]);
		}
		$text = \substr($node->textContent, $lastPos);
		$this->insertTextBefore($text, $node);
		$parentNode->removeChild($node);
	}
}