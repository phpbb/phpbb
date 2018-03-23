<?php

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Plugins\BBCodes\Configurator;
use InvalidArgumentException;
use s9e\TextFormatter\Configurator\Collections\AttributeList;
use s9e\TextFormatter\Configurator\ConfigProvider;
use s9e\TextFormatter\Configurator\Helpers\ConfigHelper;
use s9e\TextFormatter\Configurator\JavaScript\Dictionary;
use s9e\TextFormatter\Configurator\Traits\Configurable;
use s9e\TextFormatter\Configurator\Validators\AttributeName;
use s9e\TextFormatter\Configurator\Validators\TagName;
class BBCode implements ConfigProvider
{
	use Configurable;
	protected $contentAttributes;
	protected $defaultAttribute;
	protected $forceLookahead = \false;
	protected $predefinedAttributes;
	protected $tagName;
	public function __construct(array $options = \null)
	{
		$this->contentAttributes    = new AttributeList;
		$this->predefinedAttributes = new AttributeValueCollection;
		if (isset($options))
			foreach ($options as $optionName => $optionValue)
				$this->__set($optionName, $optionValue);
	}
	public function asConfig()
	{
		$config = ConfigHelper::toArray(\get_object_vars($this));
		if (!$this->forceLookahead)
			unset($config['forceLookahead']);
		if (isset($config['predefinedAttributes']))
			$config['predefinedAttributes'] = new Dictionary($config['predefinedAttributes']);
		return $config;
	}
	public static function normalizeName($bbcodeName)
	{
		if ($bbcodeName === '*')
			return '*';
		if (!TagName::isValid($bbcodeName))
			throw new InvalidArgumentException("Invalid BBCode name '" . $bbcodeName . "'");
		return TagName::normalize($bbcodeName);
	}
	public function setDefaultAttribute($attrName)
	{
		$this->defaultAttribute = AttributeName::normalize($attrName);
	}
	public function setTagName($tagName)
	{
		$this->tagName = TagName::normalize($tagName);
	}
}