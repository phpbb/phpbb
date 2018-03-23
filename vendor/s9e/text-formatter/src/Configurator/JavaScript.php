<?php

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator;
use ReflectionClass;
use s9e\TextFormatter\Configurator;
use s9e\TextFormatter\Configurator\Helpers\ConfigHelper;
use s9e\TextFormatter\Configurator\JavaScript\CallbackGenerator;
use s9e\TextFormatter\Configurator\JavaScript\Code;
use s9e\TextFormatter\Configurator\JavaScript\ConfigOptimizer;
use s9e\TextFormatter\Configurator\JavaScript\Dictionary;
use s9e\TextFormatter\Configurator\JavaScript\Encoder;
use s9e\TextFormatter\Configurator\JavaScript\HintGenerator;
use s9e\TextFormatter\Configurator\JavaScript\Minifier;
use s9e\TextFormatter\Configurator\JavaScript\Minifiers\Noop;
use s9e\TextFormatter\Configurator\JavaScript\RegexpConvertor;
use s9e\TextFormatter\Configurator\JavaScript\StylesheetCompressor;
use s9e\TextFormatter\Configurator\RendererGenerators\XSLT;
class JavaScript
{
	protected $callbackGenerator;
	protected $config;
	protected $configOptimizer;
	protected $configurator;
	public $encoder;
	public $exportMethods = [
		'disablePlugin',
		'disableTag',
		'enablePlugin',
		'enableTag',
		'getLogger',
		'parse',
		'preview',
		'setNestingLimit',
		'setParameter',
		'setTagLimit'
	];
	protected $hintGenerator;
	protected $minifier;
	protected $stylesheetCompressor;
	protected $xsl;
	public function __construct(Configurator $configurator)
	{
		$this->encoder              = new Encoder;
		$this->callbackGenerator    = new CallbackGenerator;
		$this->configOptimizer      = new ConfigOptimizer($this->encoder);
		$this->configurator         = $configurator;
		$this->hintGenerator        = new HintGenerator;
		$this->stylesheetCompressor = new StylesheetCompressor;
	}
	public function getMinifier()
	{
		if (!isset($this->minifier))
			$this->minifier = new Noop;
		return $this->minifier;
	}
	public function getParser(array $config = \null)
	{
		$this->configOptimizer->reset();
		$xslt      = new XSLT;
		$xslt->optimizer->normalizer->remove('RemoveLivePreviewAttributes');
		$this->xsl = $xslt->getXSL($this->configurator->rendering);
		$this->config = (isset($config)) ? $config : $this->configurator->asConfig();
		$this->config = ConfigHelper::filterConfig($this->config, 'JS');
		$this->config = $this->callbackGenerator->replaceCallbacks($this->config);
		$src = $this->getHints() . $this->injectConfig($this->getSource());
		$src .= "if (!window['s9e']) window['s9e'] = {};\n" . $this->getExports();
		$src = $this->getMinifier()->get($src);
		$src = '(function(){' . $src . '})()';
		return $src;
	}
	public function setMinifier($minifier)
	{
		if (\is_string($minifier))
		{
			$className = __NAMESPACE__ . '\\JavaScript\\Minifiers\\' . $minifier;
			$args = \array_slice(\func_get_args(), 1);
			if (!empty($args))
			{
				$reflection = new ReflectionClass($className);
				$minifier   = $reflection->newInstanceArgs($args);
			}
			else
				$minifier = new $className;
		}
		$this->minifier = $minifier;
		return $minifier;
	}
	protected function encode($value)
	{
		return $this->encoder->encode($value);
	}
	protected function getExports()
	{
		if (empty($this->exportMethods))
			return '';
		$methods = [];
		foreach ($this->exportMethods as $method)
			$methods[] = "'" . $method . "':" . $method;
		return "window['s9e']['TextFormatter'] = {" . \implode(',', $methods) . '}';
	}
	protected function getHints()
	{
		$this->hintGenerator->setConfig($this->config);
		$this->hintGenerator->setPlugins($this->configurator->plugins);
		$this->hintGenerator->setXSL($this->xsl);
		return $this->hintGenerator->getHints();
	}
	protected function getPluginsConfig()
	{
		$plugins = new Dictionary;
		foreach ($this->config['plugins'] as $pluginName => $pluginConfig)
		{
			if (!isset($pluginConfig['js']))
				continue;
			$js = $pluginConfig['js'];
			unset($pluginConfig['js']);
			unset($pluginConfig['className']);
			if (isset($pluginConfig['quickMatch']))
			{
				$valid = [
					'[[:ascii:]]',
					'[\\xC0-\\xDF][\\x80-\\xBF]',
					'[\\xE0-\\xEF][\\x80-\\xBF]{2}',
					'[\\xF0-\\xF7][\\x80-\\xBF]{3}'
				];
				$regexp = '#(?>' . \implode('|', $valid) . ')+#';
				if (\preg_match($regexp, $pluginConfig['quickMatch'], $m))
					$pluginConfig['quickMatch'] = $m[0];
				else
					unset($pluginConfig['quickMatch']);
			}
			$globalKeys = [
				'quickMatch'  => 1,
				'regexp'      => 1,
				'regexpLimit' => 1
			];
			$globalConfig = \array_intersect_key($pluginConfig, $globalKeys);
			$localConfig  = \array_diff_key($pluginConfig, $globalKeys);
			if (isset($globalConfig['regexp']) && !($globalConfig['regexp'] instanceof Code))
				$globalConfig['regexp'] = new Code(RegexpConvertor::toJS($globalConfig['regexp'], \true));
			$globalConfig['parser'] = new Code(
				'/**
				* @param {!string} text
				* @param {!Array.<Array>} matches
				*/
				function(text, matches)
				{
					/** @const */
					var config=' . $this->encode($localConfig) . ';
					' . $js . '
				}'
			);
			$plugins[$pluginName] = $globalConfig;
		}
		return $plugins;
	}
	protected function getRegisteredVarsConfig()
	{
		$registeredVars = $this->config['registeredVars'];
		unset($registeredVars['cacheDir']);
		return new Dictionary($registeredVars);
	}
	protected function getRootContext()
	{
		return $this->config['rootContext'];
	}
	protected function getSource()
	{
		$rootDir = __DIR__ . '/..';
		$src     = '';
		$logger = (\in_array('getLogger', $this->exportMethods)) ? 'Logger.js' : 'NullLogger.js';
		$files   = \glob($rootDir . '/Parser/AttributeFilters/*.js');
		$files[] = $rootDir . '/Parser/utils.js';
		$files[] = $rootDir . '/Parser/' . $logger;
		$files[] = $rootDir . '/Parser/Tag.js';
		$files[] = $rootDir . '/Parser.js';
		if (\in_array('preview', $this->exportMethods, \true))
		{
			$files[] = $rootDir . '/render.js';
			$src .= '/** @const */ var xsl=' . $this->getStylesheet() . ";\n";
		}
		$src .= \implode("\n", \array_map('file_get_contents', $files));
		return $src;
	}
	protected function getStylesheet()
	{
		return $this->stylesheetCompressor->encode($this->xsl);
	}
	protected function getTagsConfig()
	{
		$tags = new Dictionary;
		foreach ($this->config['tags'] as $tagName => $tagConfig)
		{
			if (isset($tagConfig['attributes']))
				$tagConfig['attributes'] = new Dictionary($tagConfig['attributes']);
			$tags[$tagName] = $tagConfig;
		}
		return $tags;
	}
	protected function injectConfig($src)
	{
		$config = \array_map(
			[$this, 'encode'],
			$this->configOptimizer->optimize(
				[
					'plugins'        => $this->getPluginsConfig(),
					'registeredVars' => $this->getRegisteredVarsConfig(),
					'rootContext'    => $this->getRootContext(),
					'tagsConfig'     => $this->getTagsConfig()
				]
			)
		);
		$src = \preg_replace_callback(
			'/(\\nvar (' . \implode('|', \array_keys($config)) . '))(;)/',
			function ($m) use ($config)
			{
				return $m[1] . '=' . $config[$m[2]] . $m[3];
			},
			$src
		);
		$src = $this->configOptimizer->getVarDeclarations() . $src;
		return $src;
	}
}