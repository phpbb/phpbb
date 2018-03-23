<?php

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Plugins\MediaEmbed\Configurator\Collections;
use DOMDocument;
use DOMElement;
use InvalidArgumentException;
class XmlFileDefinitionCollection extends SiteDefinitionCollection
{
	protected $configTypes = [
		['(^defaultValue$)', '(^[1-9][0-9]*$)D',     'castToInt'],
		['(height$|width$)', '(^[1-9][0-9]*$)D',     'castToInt'],
		['(^required$)',     '(^(?:true|false)$)iD', 'castToBool']
	];
	public function __construct($path)
	{
		if (!\file_exists($path) || !\is_dir($path))
			throw new InvalidArgumentException('Invalid site directory');
		foreach (\glob($path . '/*.xml') as $filepath)
		{
			$siteId = \basename($filepath, '.xml');
			$this->add($siteId, $this->getConfigFromXmlFile($filepath));
		}
	}
	protected function castConfigValue($name, $value)
	{
		foreach ($this->configTypes as $_d1e08ffa)
		{
			list($nameRegexp, $valueRegexp, $methodName) = $_d1e08ffa;
			if (\preg_match($nameRegexp, $name) && \preg_match($valueRegexp, $value))
				return $this->$methodName($value);
		}
		return $value;
	}
	protected function castToBool($value)
	{
		return (\strtolower($value) === 'true');
	}
	protected function castToInt($value)
	{
		return (int) $value;
	}
	protected function convertValueTypes(array $config)
	{
		foreach ($config as $k => $v)
			if (\is_array($v))
				$config[$k] = $this->convertValueTypes($v);
			else
				$config[$k] = $this->castConfigValue($k, $v);
		return $config;
	}
	protected function flattenConfig(array $config)
	{
		foreach ($config as $k => $v)
			if (\is_array($v) && \count($v) === 1)
				$config[$k] = \end($v);
		return $config;
	}
	protected function getConfigFromXmlFile($filepath)
	{
		$dom = new DOMDocument;
		$dom->load($filepath, \LIBXML_NOCDATA);
		return $this->getElementConfig($dom->documentElement);
	}
	protected function getElementConfig(DOMElement $element)
	{
		$config = [];
		foreach ($element->attributes as $attribute)
			$config[$attribute->name][] = $attribute->value;
		foreach ($element->childNodes as $childNode)
			if ($childNode instanceof DOMElement)
				$config[$childNode->nodeName][] = $this->getValueFromElement($childNode);
		return $this->flattenConfig($this->convertValueTypes($config));
	}
	protected function getValueFromElement(DOMElement $element)
	{
		return (!$element->attributes->length && $element->childNodes->length === 1 && $element->firstChild->nodeType === \XML_TEXT_NODE)
		     ? $element->nodeValue
		     : $this->getElementConfig($element);
	}
}