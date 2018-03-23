<?php

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Plugins\Emoji;
use s9e\TextFormatter\Configurator\Helpers\ConfigHelper;
use s9e\TextFormatter\Configurator\Helpers\RegexpBuilder;
use s9e\TextFormatter\Configurator\Items\Regexp;
use s9e\TextFormatter\Plugins\ConfiguratorBase;
class Configurator extends ConfiguratorBase
{
	protected $attrName = 'seq';
	protected $aliases = [];
	protected $tagName = 'EMOJI';
	protected function setUp()
	{
		if (isset($this->configurator->tags[$this->tagName]))
			return;
		$tag = $this->configurator->tags->add($this->tagName);
		$tag->attributes->add($this->attrName)->filterChain->append(
			$this->configurator->attributeFilters['#identifier']
		);
		$tag->template = '<img alt="{.}" class="emoji" draggable="false" src="//cdn.jsdelivr.net/emojione/assets/3.1/png/64/{@seq}.png"/>';
	}
	public function addAlias($alias, $emoji)
	{
		$this->aliases[$alias] = $emoji;
	}
	public function removeAlias($alias)
	{
		unset($this->aliases[$alias]);
	}
	public function getAliases()
	{
		return $this->aliases;
	}
	public function asConfig()
	{
		$config = [
			'attrName' => $this->attrName,
			'tagName'  => $this->tagName
		];
		if (!empty($this->aliases))
		{
			$aliases = \array_keys($this->aliases);
			$regexp  = '/' . RegexpBuilder::fromList($aliases) . '/';
			$config['aliases']       = $this->aliases;
			$config['aliasesRegexp'] = new Regexp($regexp, \true);
			$quickMatch = ConfigHelper::generateQuickMatchFromList($aliases);
			if ($quickMatch !== \false)
				$config['aliasesQuickMatch'] = $quickMatch;
		}
		return $config;
	}
	public function getJSHints()
	{
		$quickMatch = ConfigHelper::generateQuickMatchFromList(\array_keys($this->aliases));
		return [
			'EMOJI_HAS_ALIASES'          => !empty($this->aliases),
			'EMOJI_HAS_ALIAS_QUICKMATCH' => ($quickMatch !== \false)
		];
	}
}