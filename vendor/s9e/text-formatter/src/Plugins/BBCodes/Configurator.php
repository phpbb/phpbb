<?php

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Plugins\BBCodes;
use ArrayAccess;
use Countable;
use InvalidArgumentException;
use Iterator;
use RuntimeException;
use s9e\TextFormatter\Configurator\Helpers\RegexpBuilder;
use s9e\TextFormatter\Configurator\Helpers\RegexpParser;
use s9e\TextFormatter\Configurator\JavaScript\Dictionary;
use s9e\TextFormatter\Configurator\Traits\CollectionProxy;
use s9e\TextFormatter\Plugins\BBCodes\Configurator\BBCode;
use s9e\TextFormatter\Plugins\BBCodes\Configurator\BBCodeCollection;
use s9e\TextFormatter\Plugins\BBCodes\Configurator\BBCodeMonkey;
use s9e\TextFormatter\Plugins\BBCodes\Configurator\Repository;
use s9e\TextFormatter\Plugins\BBCodes\Configurator\RepositoryCollection;
use s9e\TextFormatter\Plugins\ConfiguratorBase;
class Configurator extends ConfiguratorBase implements ArrayAccess, Countable, Iterator
{
	use CollectionProxy;
	public $bbcodeMonkey;
	public $collection;
	protected $quickMatch = '[';
	protected $regexp = '#\\[/?(\\*|[-\\w]+)(?=[\\]\\s=:/])#';
	public $repositories;
	protected function setUp()
	{
		$this->bbcodeMonkey = new BBCodeMonkey($this->configurator);
		$this->collection   = new BBCodeCollection;
		$this->repositories = new RepositoryCollection($this->bbcodeMonkey);
		$this->repositories->add('default', __DIR__ . '/Configurator/repository.xml');
	}
	public function addCustom($usage, $template, array $options = [])
	{
		$config = $this->bbcodeMonkey->create($usage, $template);
		if (isset($options['tagName']))
			$config['bbcode']->tagName = $options['tagName'];
		if (isset($options['rules']))
			$config['tag']->rules->merge($options['rules']);
		return $this->addFromConfig($config);
	}
	public function addFromRepository($name, $repository = 'default', array $vars = [])
	{
		if (!($repository instanceof Repository))
		{
			if (!$this->repositories->exists($repository))
				throw new InvalidArgumentException("Repository '" . $repository . "' does not exist");
			$repository = $this->repositories->get($repository);
		}
		return $this->addFromConfig($repository->get($name, $vars));
	}
	protected function addFromConfig(array $config)
	{
		$bbcodeName = $config['bbcodeName'];
		$bbcode     = $config['bbcode'];
		$tag        = $config['tag'];
		if (!isset($bbcode->tagName))
			$bbcode->tagName = $bbcodeName;
		$this->configurator->templateNormalizer->normalizeTag($tag);
		$this->configurator->templateChecker->checkTag($tag);
		$this->collection->add($bbcodeName, $bbcode);
		$this->configurator->tags->add($bbcode->tagName, $tag);
		return $bbcode;
	}
	public function asConfig()
	{
		if (!\count($this->collection))
			return;
		return [
			'bbcodes'    => $this->collection->asConfig(),
			'quickMatch' => $this->quickMatch,
			'regexp'     => $this->regexp
		];
	}
}