<?php

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Plugins\Keywords;
use s9e\TextFormatter\Configurator\Collections\NormalizedList;
use s9e\TextFormatter\Configurator\Helpers\RegexpBuilder;
use s9e\TextFormatter\Configurator\Items\Regexp;
use s9e\TextFormatter\Configurator\Traits\CollectionProxy;
use s9e\TextFormatter\Plugins\ConfiguratorBase;
class Configurator extends ConfiguratorBase
{
	use CollectionProxy;
	protected $attrName = 'value';
	public $caseSensitive = \true;
	protected $collection;
	public $onlyFirst = \false;
	protected $tagName = 'KEYWORD';
	protected function setUp()
	{
		$this->collection = new NormalizedList;
		$this->configurator->tags->add($this->tagName)->attributes->add($this->attrName);
	}
	public function asConfig()
	{
		if (!\count($this->collection))
			return;
		$config = [
			'attrName' => $this->attrName,
			'tagName'  => $this->tagName
		];
		if (!empty($this->onlyFirst))
			$config['onlyFirst'] = $this->onlyFirst;
		$keywords = \array_unique(\iterator_to_array($this->collection));
		\sort($keywords);
		$groups   = [];
		$groupKey = 0;
		$groupLen = 0;
		foreach ($keywords as $keyword)
		{
			$keywordLen  = 4 + \strlen($keyword);
			$groupLen   += $keywordLen;
			if ($groupLen > 30000)
			{
				$groupLen = $keywordLen;
				++$groupKey;
			}
			$groups[$groupKey][] = $keyword;
		}
		foreach ($groups as $keywords)
		{
			$regexp = RegexpBuilder::fromList(
				$keywords,
				['caseInsensitive' => !$this->caseSensitive]
			);
			$regexp = '/\\b' . $regexp . '\\b/S';
			if (!$this->caseSensitive)
				$regexp .= 'i';
			if (\preg_match('/[^[:ascii:]]/', $regexp))
				$regexp .= 'u';
			$config['regexps'][] = new Regexp($regexp, \true);
		}
		return $config;
	}
}