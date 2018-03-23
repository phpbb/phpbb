<?php

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Plugins\Censor;
use ArrayAccess;
use Countable;
use Iterator;
use s9e\TextFormatter\Configurator\Collections\NormalizedCollection;
use s9e\TextFormatter\Configurator\Helpers\ConfigHelper;
use s9e\TextFormatter\Configurator\Helpers\RegexpBuilder;
use s9e\TextFormatter\Configurator\Items\Regexp;
use s9e\TextFormatter\Configurator\JavaScript\Code;
use s9e\TextFormatter\Configurator\JavaScript\RegexpConvertor;
use s9e\TextFormatter\Configurator\Traits\CollectionProxy;
use s9e\TextFormatter\Plugins\ConfiguratorBase;
class Configurator extends ConfiguratorBase implements ArrayAccess, Countable, Iterator
{
	use CollectionProxy;
	protected $allowed = [];
	protected $attrName = 'with';
	protected $collection;
	protected $defaultReplacement = '****';
	protected $regexpOptions = [
		'caseInsensitive' => \true,
		'specialChars'    => [
			'*' => '[\\pL\\pN]*',
			'?' => '.',
			' ' => '\\s*'
		]
	];
	protected $tagName = 'CENSOR';
	protected function setUp()
	{
		$this->collection = new NormalizedCollection;
		$this->collection->onDuplicate('replace');
		if (isset($this->configurator->tags[$this->tagName]))
			return;
		$tag = $this->configurator->tags->add($this->tagName);
		$tag->attributes->add($this->attrName)->required = \false;
		$tag->rules->ignoreTags();
		$tag->template =
			'<xsl:choose>
				<xsl:when test="@' . $this->attrName . '">
					<xsl:value-of select="@' . \htmlspecialchars($this->attrName) . '"/>
				</xsl:when>
				<xsl:otherwise>' . \htmlspecialchars($this->defaultReplacement) . '</xsl:otherwise>
			</xsl:choose>';
	}
	public function allow($word)
	{
		$this->allowed[$word] = \true;
	}
	public function getHelper()
	{
		$config = $this->asConfig();
		if (isset($config))
			$config = ConfigHelper::filterConfig($config, 'PHP');
		else
			$config = [
				'attrName' => $this->attrName,
				'regexp'   => '/(?!)/',
				'tagName'  => $this->tagName
			];
		return new Helper($config);
	}
	public function asConfig()
	{
		$words = $this->getWords();
		if (empty($words))
			return;
		$config = [
			'attrName'   => $this->attrName,
			'regexp'     => $this->getWordsRegexp(\array_keys($words)),
			'regexpHtml' => $this->getWordsRegexp(\array_map('htmlspecialchars', \array_keys($words))),
			'tagName'    => $this->tagName
		];
		$replacementWords = [];
		foreach ($words as $word => $replacement)
			if (isset($replacement) && $replacement !== $this->defaultReplacement)
				$replacementWords[$replacement][] = $word;
		foreach ($replacementWords as $replacement => $words)
		{
			$wordsRegexp = '/^' . RegexpBuilder::fromList($words, $this->regexpOptions) . '$/Diu';
			$regexp = new Regexp($wordsRegexp);
			$regexp->setJS(RegexpConvertor::toJS(\str_replace('[\\pL\\pN]', '[^\\s!-\\/:-?]', $wordsRegexp)));
			$config['replacements'][] = [$regexp, $replacement];
		}
		if (!empty($this->allowed))
			$config['allowed'] = $this->getWordsRegexp(\array_keys($this->allowed));
		return $config;
	}
	public function getJSHints()
	{
		$hints = [
			'CENSOR_HAS_ALLOWED'      => !empty($this->allowed),
			'CENSOR_HAS_REPLACEMENTS' => \false
		];
		foreach ($this->getWords() as $replacement)
			if (isset($replacement) && $replacement !== $this->defaultReplacement)
			{
				$hints['CENSOR_HAS_REPLACEMENTS'] = \true;
				break;
			}
		return $hints;
	}
	protected function getWords()
	{
		return \array_diff_key(\iterator_to_array($this->collection), $this->allowed);
	}
	protected function getWordsRegexp(array $words)
	{
		$expr = RegexpBuilder::fromList($words, $this->regexpOptions);
		$expr = \preg_replace('/(?<!\\\\)((?>\\\\\\\\)*)\\(\\?:/', '$1(?>', $expr);
		$regexp = new Regexp('/(?<![\\pL\\pN])' . $expr . '(?![\\pL\\pN])/Siu');
		$regexp->setJS('/(?:^|\\W)' . \str_replace('[\\pL\\pN]', '[^\\s!-\\/:-?]', $expr) . '(?!\\w)/gi');
		return $regexp;
	}
}