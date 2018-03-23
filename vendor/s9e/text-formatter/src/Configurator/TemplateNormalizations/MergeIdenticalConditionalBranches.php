<?php

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\TemplateNormalizations;
use DOMElement;
use DOMNode;
use s9e\TextFormatter\Configurator\Helpers\TemplateParser;
class MergeIdenticalConditionalBranches extends AbstractNormalization
{
	protected $queries = ['//xsl:choose'];
	protected function collectCompatibleBranches(DOMNode $node)
	{
		$nodes  = [];
		$key    = \null;
		$values = [];
		while ($node && $this->isXsl($node, 'when'))
		{
			$branch = TemplateParser::parseEqualityExpr($node->getAttribute('test'));
			if ($branch === \false || \count($branch) !== 1)
				break;
			if (isset($key) && \key($branch) !== $key)
				break;
			if (\array_intersect($values, \end($branch)))
				break;
			$key    = \key($branch);
			$values = \array_merge($values, \end($branch));
			$nodes[] = $node;
			$node    = $node->nextSibling;
		}
		return $nodes;
	}
	protected function mergeBranches(array $nodes)
	{
		$sortedNodes = [];
		foreach ($nodes as $node)
		{
			$outerXML = $node->ownerDocument->saveXML($node);
			$innerXML = \preg_replace('([^>]+>(.*)<[^<]+)s', '$1', $outerXML);
			$sortedNodes[$innerXML][] = $node;
		}
		foreach ($sortedNodes as $identicalNodes)
		{
			if (\count($identicalNodes) < 2)
				continue;
			$expr = [];
			foreach ($identicalNodes as $i => $node)
			{
				$expr[] = $node->getAttribute('test');
				if ($i > 0)
					$node->parentNode->removeChild($node);
			}
			$identicalNodes[0]->setAttribute('test', \implode(' or ', $expr));
		}
	}
	protected function mergeCompatibleBranches(DOMElement $choose)
	{
		$node = $choose->firstChild;
		while ($node)
		{
			$nodes = $this->collectCompatibleBranches($node);
			if (\count($nodes) > 1)
			{
				$node = \end($nodes)->nextSibling;
				$this->mergeBranches($nodes);
			}
			else
				$node = $node->nextSibling;
		}
	}
	protected function mergeConsecutiveBranches(DOMElement $choose)
	{
		$nodes = [];
		foreach ($choose->childNodes as $node)
			if ($this->isXsl($node, 'when'))
				$nodes[] = $node;
		$i = \count($nodes);
		while (--$i > 0)
			$this->mergeBranches([$nodes[$i - 1], $nodes[$i]]);
	}
	protected function normalizeElement(DOMElement $element)
	{
		$this->mergeCompatibleBranches($element);
		$this->mergeConsecutiveBranches($element);
	}
}