<?php

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Plugins\MediaEmbed;
use InvalidArgumentException;
use RuntimeException;
use s9e\TextFormatter\Configurator\Helpers\RegexpBuilder;
use s9e\TextFormatter\Configurator\Items\Attribute;
use s9e\TextFormatter\Configurator\Items\AttributePreprocessor;
use s9e\TextFormatter\Configurator\Items\Tag;
use s9e\TextFormatter\Plugins\ConfiguratorBase;
use s9e\TextFormatter\Plugins\MediaEmbed\Configurator\Collections\CachedDefinitionCollection;
use s9e\TextFormatter\Plugins\MediaEmbed\Configurator\Collections\SiteCollection;
use s9e\TextFormatter\Plugins\MediaEmbed\Configurator\TemplateBuilder;
class Configurator extends ConfiguratorBase
{
	public $allowedFilters = [
		'hexdec',
		'stripslashes',
		'urldecode'
	];
	protected $appendTemplate = '';
	public $captureURLs = \true;
	protected $collection;
	protected $createMediaBBCode = \true;
	public $createIndividualBBCodes = \false;
	public $defaultSites;
	protected $tagName = 'MEDIA';
	protected $templateBuilder;
	protected function setUp()
	{
		$this->collection = new SiteCollection;
		$this->configurator->registeredVars['mediasites'] = $this->collection;
		$tag = $this->configurator->tags->add($this->tagName);
		$tag->rules->autoClose();
		$tag->rules->denyChild($this->tagName);
		$tag->filterChain->clear();
		$tag->filterChain
		    ->append([__NAMESPACE__ . '\\Parser', 'filterTag'])
		    ->addParameterByName('parser')
		    ->addParameterByName('mediasites')
		    ->setJS(\file_get_contents(__DIR__ . '/Parser/tagFilter.js'));
		if ($this->createMediaBBCode)
			$this->configurator->BBCodes->set(
				$this->tagName,
				[
					'contentAttributes' => ['url'],
					'defaultAttribute'  => 'site'
				]
			);
		if (!isset($this->defaultSites))
			$this->defaultSites = new CachedDefinitionCollection;
		$this->templateBuilder = new TemplateBuilder;
	}
	public function asConfig()
	{
		if (!$this->captureURLs || !\count($this->collection))
			return;
		$regexp  = 'https?:\\/\\/';
		$schemes = $this->getSchemes();
		if (!empty($schemes))
			$regexp = '(?>' . RegexpBuilder::fromList($schemes) . ':|' . $regexp . ')';
		return [
			'quickMatch' => (empty($schemes)) ? '://' : ':',
			'regexp'     => '/\\b' . $regexp . '[^["\'\\s]+/Si',
			'tagName'    => $this->tagName
		];
	}
	public function add($siteId, array $siteConfig = \null)
	{
		$siteId = $this->normalizeId($siteId);
		$siteConfig = (isset($siteConfig)) ? $this->defaultSites->normalizeValue($siteConfig) : $this->defaultSites->get($siteId);
		$this->collection[$siteId] = $siteConfig;
		$tag = new Tag;
		$tag->rules->allowChild('URL');
		$tag->rules->autoClose();
		$tag->rules->denyChild($siteId);
		$tag->rules->denyChild($this->tagName);
		$attributes = [
			'url' => ['type' => 'url']
		];
		$attributes += $this->addScrapes($tag, $siteConfig['scrape']);
		foreach ($siteConfig['extract'] as $regexp)
		{
			$attrRegexps = $tag->attributePreprocessors->add('url', $regexp)->getAttributes();
			foreach ($attrRegexps as $attrName => $attrRegexp)
				$attributes[$attrName]['regexp'] = $attrRegexp;
		}
		if (isset($siteConfig['attributes']))
			foreach ($siteConfig['attributes'] as $attrName => $attrConfig)
				foreach ($attrConfig as $configName => $configValue)
					$attributes[$attrName][$configName] = $configValue;
		$hasRequiredAttribute = \false;
		foreach ($attributes as $attrName => $attrConfig)
		{
			$attribute = $this->addAttribute($tag, $attrName, $attrConfig);
			$hasRequiredAttribute |= $attribute->required;
		}
		if (isset($attributes['id']['regexp']))
		{
			$attrRegexp = \preg_replace('(\\^(.*)\\$)s', "^(?'id'$1)$", $attributes['id']['regexp']);
			$tag->attributePreprocessors->add('url', $attrRegexp);
		}
		if (!$hasRequiredAttribute)
			$tag->filterChain
				->append([__NAMESPACE__ . '\\Parser', 'hasNonDefaultAttribute'])
				->setJS(\file_get_contents(__DIR__ . '/Parser/hasNonDefaultAttribute.js'));
		$tag->template = $this->templateBuilder->build($siteId, $siteConfig) . $this->appendTemplate;
		$this->configurator->templateNormalizer->normalizeTag($tag);
		$this->configurator->templateChecker->checkTag($tag);
		$this->configurator->tags->add($siteId, $tag);
		if ($this->createIndividualBBCodes)
			$this->configurator->BBCodes->add(
				$siteId,
				[
					'defaultAttribute'  => 'url',
					'contentAttributes' => ['url']
				]
			);
		return $tag;
	}
	public function appendTemplate($template = '')
	{
		$this->appendTemplate = $this->configurator->templateNormalizer->normalizeTemplate($template);
	}
	protected function addAttribute(Tag $tag, $attrName, array $attrConfig)
	{
		$attribute = $tag->attributes->add($attrName);
		if (isset($attrConfig['preFilter']))
			$this->appendFilter($attribute, $attrConfig['preFilter']);
		if (isset($attrConfig['type']))
		{
			$filter = $this->configurator->attributeFilters['#' . $attrConfig['type']];
			$attribute->filterChain->append($filter);
		}
		elseif (isset($attrConfig['regexp']))
			$attribute->filterChain->append('#regexp')->setRegexp($attrConfig['regexp']);
		if (isset($attrConfig['required']))
			$attribute->required = $attrConfig['required'];
		else
			$attribute->required = ($attrName === 'id');
		if (isset($attrConfig['postFilter']))
			$this->appendFilter($attribute, $attrConfig['postFilter']);
		if (isset($attrConfig['defaultValue']))
			$attribute->defaultValue = $attrConfig['defaultValue'];
		return $attribute;
	}
	protected function addScrapes(Tag $tag, array $scrapes)
	{
		$attributes   = [];
		$scrapeConfig = [];
		foreach ($scrapes as $scrape)
		{
			$attrNames = [];
			foreach ($scrape['extract'] as $extractRegexp)
			{
				$attributePreprocessor = new AttributePreprocessor($extractRegexp);
				foreach ($attributePreprocessor->getAttributes() as $attrName => $attrRegexp)
				{
					$attrNames[] = $attrName;
					$attributes[$attrName]['regexp'] = $attrRegexp;
				}
			}
			$attrNames = \array_unique($attrNames);
			\sort($attrNames);
			$entry = [$scrape['match'], $scrape['extract'], $attrNames];
			if (isset($scrape['url']))
				$entry[] = $scrape['url'];
			$scrapeConfig[] = $entry;
		}
		$tag->filterChain->insert(1, __NAMESPACE__ . '\\Parser::scrape')
		                 ->addParameterByName('scrapeConfig')
		                 ->addParameterByName('cacheDir')
		                 ->setVar('scrapeConfig', $scrapeConfig)
		                 ->setJS('returnTrue');
		return $attributes;
	}
	protected function appendFilter(Attribute $attribute, $filter)
	{
		if (!\in_array($filter, $this->allowedFilters, \true))
			throw new RuntimeException("Filter '" . $filter . "' is not allowed");
		$attribute->filterChain->append($this->configurator->attributeFilters[$filter]);
	}
	protected function getSchemes()
	{
		$schemes = [];
		foreach ($this->collection as $site)
			if (isset($site['scheme']))
				foreach ((array) $site['scheme'] as $scheme)
					$schemes[] = $scheme;
		return $schemes;
	}
	protected function normalizeId($siteId)
	{
		$siteId = \strtolower($siteId);
		if (!\preg_match('(^[a-z0-9]+$)', $siteId))
			throw new InvalidArgumentException('Invalid site ID');
		return $siteId;
	}
}