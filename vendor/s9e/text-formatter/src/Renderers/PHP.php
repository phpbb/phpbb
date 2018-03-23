<?php

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Renderers;
use DOMNode;
use DOMXPath;
use RuntimeException;
use s9e\TextFormatter\Renderer;
use s9e\TextFormatter\Utils\XPath;
abstract class PHP extends Renderer
{
	protected $attributes;
	protected $dynamic;
	public $enableQuickRenderer = \false;
	protected $out;
	protected $quickRegexp = '((?!))';
	protected $quickRenderingTest = '(<[!?])';
	protected $static;
	protected $xpath;
	abstract protected function renderNode(DOMNode $node);
	public function __sleep()
	{
		return ['enableQuickRenderer', 'params'];
	}
	protected function at(DOMNode $root, $query = \null)
	{
		if ($root->nodeType === \XML_TEXT_NODE)
			$this->out .= \htmlspecialchars($root->textContent,0);
		else
		{
			$nodes = (isset($query)) ? $this->xpath->query($query, $root) : $root->childNodes;
			foreach ($nodes as $node)
				$this->renderNode($node);
		}
	}
	protected function canQuickRender($xml)
	{
		return ($this->enableQuickRenderer && !\preg_match($this->quickRenderingTest, $xml));
	}
	protected function getParamAsXPath($paramName)
	{
		return (isset($this->params[$paramName])) ? XPath::export($this->params[$paramName]) : "''";
	}
	protected function getQuickTextContent($xml)
	{
		return \htmlspecialchars_decode(\strip_tags($xml));
	}
	protected function hasNonNullValues(array $array)
	{
		foreach ($array as $v)
			if (isset($v))
				return \true;
		return \false;
	}
	protected function matchAttributes($xml)
	{
		if (\strpos($xml, '="') === \false)
			return [];
		\preg_match_all('(([^ =]++)="([^"]*))S', \substr($xml, 0, \strpos($xml, '>')), $m);
		return \array_combine($m[1], $m[2]);
	}
	protected function renderQuick($xml)
	{
		$this->attributes = [];
		$xml = $this->decodeSMP($xml);
		$html = \preg_replace_callback(
			$this->quickRegexp,
			[$this, 'renderQuickCallback'],
			\preg_replace(
				'(<[eis]>[^<]*</[eis]>)',
				'',
				\substr($xml, 1 + \strpos($xml, '>'), -4)
			)
		);
		return \str_replace('<br/>', '<br>', $html);
	}
	protected function renderQuickCallback(array $m)
	{
		if (isset($m[3]))
			return $this->renderQuickSelfClosingTag($m);
		if (isset($m[2]))
			$id = $m[2];
		else
		{
			$id = $m[1];
			$this->checkTagPairContent($id, $m[0]);
		}
		if (isset($this->static[$id]))
			return $this->static[$id];
		if (isset($this->dynamic[$id]))
			return \preg_replace($this->dynamic[$id][0], $this->dynamic[$id][1], $m[0], 1);
		return $this->renderQuickTemplate($id, $m[0]);
	}
	protected function checkTagPairContent($id, $xml)
	{
		if (\strpos($xml, '<' . $id, 1) !== \false)
			throw new RuntimeException;
	}
	protected function renderQuickSelfClosingTag(array $m)
	{
		unset($m[3]);
		$m[0] = \substr($m[0], 0, -2) . '>';
		$html = $this->renderQuickCallback($m);
		$m[0] = '</' . $m[2] . '>';
		$m[2] = '/' . $m[2];
		$html .= $this->renderQuickCallback($m);
		return $html;
	}
	protected function renderQuickTemplate($id, $xml)
	{
		throw new RuntimeException('Not implemented');
	}
	protected function renderRichText($xml)
	{
		try
		{
			if ($this->canQuickRender($xml))
				return $this->renderQuick($xml);
		}
		catch (RuntimeException $e)
		{
			}
		$dom         = $this->loadXML($xml);
		$this->out   = '';
		$this->xpath = new DOMXPath($dom);
		$this->at($dom->documentElement);
		$html        = $this->out;
		$this->reset();
		return $html;
	}
	protected function reset()
	{
		unset($this->attributes);
		unset($this->out);
		unset($this->xpath);
	}
}