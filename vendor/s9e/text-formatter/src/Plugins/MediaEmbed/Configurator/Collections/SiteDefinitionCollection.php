<?php

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Plugins\MediaEmbed\Configurator\Collections;
use InvalidArgumentException;
use RuntimeException;
use s9e\TextFormatter\Configurator\Collections\NormalizedCollection;
class SiteDefinitionCollection extends NormalizedCollection
{
	protected $onDuplicateAction = 'replace';
	protected function getAlreadyExistsException($key)
	{
		return new RuntimeException("Media site '" . $key . "' already exists");
	}
	protected function getNotExistException($key)
	{
		return new RuntimeException("Media site '" . $key . "' does not exist");
	}
	public function normalizeKey($siteId)
	{
		$siteId = \strtolower($siteId);
		if (!\preg_match('(^[a-z0-9]+$)', $siteId))
			throw new InvalidArgumentException('Invalid site ID');
		return $siteId;
	}
	public function normalizeValue($siteConfig)
	{
		if (!\is_array($siteConfig))
			throw new InvalidArgumentException('Invalid site definition type');
		$siteConfig           += ['extract' => [], 'scrape' => []];
		$siteConfig['extract'] = $this->normalizeRegexp($siteConfig['extract']);
		$siteConfig['scrape']  = $this->normalizeScrape($siteConfig['scrape']);
		return $siteConfig;
	}
	protected function normalizeRegexp($value)
	{
		return (array) $value;
	}
	protected function normalizeScrape($value)
	{
		if (!empty($value) && !isset($value[0]))
			$value = [$value];
		foreach ($value as &$scrape)
		{
			$scrape           += ['extract' => [], 'match' => '//'];
			$scrape['extract'] = $this->normalizeRegexp($scrape['extract']);
			$scrape['match']   = $this->normalizeRegexp($scrape['match']);
		}
		unset($scrape);
		return $value;
	}
}