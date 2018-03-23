<?php

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter;
use InvalidArgumentException;
use RuntimeException;
use s9e\TextFormatter\Configurator\BundleGenerator;
use s9e\TextFormatter\Configurator\Collections\AttributeFilterCollection;
use s9e\TextFormatter\Configurator\Collections\PluginCollection;
use s9e\TextFormatter\Configurator\Collections\Ruleset;
use s9e\TextFormatter\Configurator\Collections\TagCollection;
use s9e\TextFormatter\Configurator\ConfigProvider;
use s9e\TextFormatter\Configurator\Helpers\ConfigHelper;
use s9e\TextFormatter\Configurator\Helpers\RulesHelper;
use s9e\TextFormatter\Configurator\JavaScript;
use s9e\TextFormatter\Configurator\JavaScript\Dictionary;
use s9e\TextFormatter\Configurator\Rendering;
use s9e\TextFormatter\Configurator\RulesGenerator;
use s9e\TextFormatter\Configurator\TemplateChecker;
use s9e\TextFormatter\Configurator\TemplateNormalizer;
use s9e\TextFormatter\Configurator\UrlConfig;
class Configurator implements ConfigProvider
{
	public $attributeFilters;
	public $bundleGenerator;
	public $javascript;
	public $plugins;
	public $registeredVars;
	public $rendering;
	public $rootRules;
	public $rulesGenerator;
	public $tags;
	public $templateChecker;
	public $templateNormalizer;
	public function __construct()
	{
		$this->attributeFilters   = new AttributeFilterCollection;
		$this->bundleGenerator    = new BundleGenerator($this);
		$this->plugins            = new PluginCollection($this);
		$this->registeredVars     = ['urlConfig' => new UrlConfig];
		$this->rendering          = new Rendering($this);
		$this->rootRules          = new Ruleset;
		$this->rulesGenerator     = new RulesGenerator;
		$this->tags               = new TagCollection;
		$this->templateChecker    = new TemplateChecker;
		$this->templateNormalizer = new TemplateNormalizer;
	}
	public function __get($k)
	{
		if (\preg_match('#^[A-Z][A-Za-z_0-9]+$#D', $k))
			return (isset($this->plugins[$k]))
			     ? $this->plugins[$k]
			     : $this->plugins->load($k);
		if (isset($this->registeredVars[$k]))
			return $this->registeredVars[$k];
		throw new RuntimeException("Undefined property '" . __CLASS__ . '::$' . $k . "'");
	}
	public function __isset($k)
	{
		if (\preg_match('#^[A-Z][A-Za-z_0-9]+$#D', $k))
			return isset($this->plugins[$k]);
		return isset($this->registeredVars[$k]);
	}
	public function __set($k, $v)
	{
		if (\preg_match('#^[A-Z][A-Za-z_0-9]+$#D', $k))
			$this->plugins[$k] = $v;
		else
			$this->registeredVars[$k] = $v;
	}
	public function __unset($k)
	{
		if (\preg_match('#^[A-Z][A-Za-z_0-9]+$#D', $k))
			unset($this->plugins[$k]);
		else
			unset($this->registeredVars[$k]);
	}
	public function enableJavaScript()
	{
		if (!isset($this->javascript))
			$this->javascript = new JavaScript($this);
	}
	public function finalize()
	{
		$return = [];
		$this->plugins->finalize();
		foreach ($this->tags as $tag)
			$this->templateNormalizer->normalizeTag($tag);
		$return['renderer'] = $this->rendering->getRenderer();
		$this->addTagRules();
		$config = $this->asConfig();
		if (isset($this->javascript))
			$return['js'] = $this->javascript->getParser(ConfigHelper::filterConfig($config, 'JS'));
		$config = ConfigHelper::filterConfig($config, 'PHP');
		ConfigHelper::optimizeArray($config);
		$return['parser'] = new Parser($config);
		return $return;
	}
	public function loadBundle($bundleName)
	{
		if (!\preg_match('#^[A-Z][A-Za-z0-9]+$#D', $bundleName))
			throw new InvalidArgumentException("Invalid bundle name '" . $bundleName . "'");
		$className = __CLASS__ . '\\Bundles\\' . $bundleName;
		$bundle = new $className;
		$bundle->configure($this);
	}
	public function saveBundle($className, $filepath, array $options = [])
	{
		$file = "<?php\n\n" . $this->bundleGenerator->generate($className, $options);
		return (\file_put_contents($filepath, $file) !== \false);
	}
	public function asConfig()
	{
		$this->plugins->finalize();
		$properties = \get_object_vars($this);
		unset($properties['attributeFilters']);
		unset($properties['bundleGenerator']);
		unset($properties['javascript']);
		unset($properties['rendering']);
		unset($properties['rulesGenerator']);
		unset($properties['registeredVars']);
		unset($properties['templateChecker']);
		unset($properties['templateNormalizer']);
		unset($properties['stylesheet']);
		$config    = ConfigHelper::toArray($properties);
		$bitfields = RulesHelper::getBitfields($this->tags, $this->rootRules);
		$config['rootContext'] = $bitfields['root'];
		$config['rootContext']['flags'] = $config['rootRules']['flags'];
		$config['registeredVars'] = ConfigHelper::toArray($this->registeredVars, \true);
		$config += [
			'plugins' => [],
			'tags'    => []
		];
		$config['tags'] = \array_intersect_key($config['tags'], $bitfields['tags']);
		foreach ($bitfields['tags'] as $tagName => $tagBitfields)
			$config['tags'][$tagName] += $tagBitfields;
		unset($config['rootRules']);
		return $config;
	}
	protected function addTagRules()
	{
		$rules = $this->rulesGenerator->getRules($this->tags);
		$this->rootRules->merge($rules['root'], \false);
		foreach ($rules['tags'] as $tagName => $tagRules)
			$this->tags[$tagName]->rules->merge($tagRules, \false);
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator;
use s9e\TextFormatter\Configurator;
use s9e\TextFormatter\Configurator\RendererGenerators\PHP;
class BundleGenerator
{
	protected $configurator;
	public $serializer = 'serialize';
	public $unserializer = 'unserialize';
	public function __construct(Configurator $configurator)
	{
		$this->configurator = $configurator;
	}
	public function generate($className, array $options = [])
	{
		$options += ['autoInclude' => \true];
		$objects  = $this->configurator->finalize();
		$parser   = $objects['parser'];
		$renderer = $objects['renderer'];
		$namespace = '';
		if (\preg_match('#(.*)\\\\([^\\\\]+)$#', $className, $m))
		{
			$namespace = $m[1];
			$className = $m[2];
		}
		$php = [];
		$php[] = '/**';
		$php[] = '* @package   s9e\TextFormatter';
		$php[] = '* @copyright Copyright (c) 2010-2017 The s9e Authors';
		$php[] = '* @license   http://www.opensource.org/licenses/mit-license.php The MIT License';
		$php[] = '*/';
		if ($namespace)
		{
			$php[] = 'namespace ' . $namespace . ';';
			$php[] = '';
		}
		$php[] = 'abstract class ' . $className . ' extends \\s9e\\TextFormatter\\Bundle';
		$php[] = '{';
		$php[] = '	/**';
		$php[] = '	* @var s9e\\TextFormatter\\Parser Singleton instance used by parse()';
		$php[] = '	*/';
		$php[] = '	protected static $parser;';
		$php[] = '';
		$php[] = '	/**';
		$php[] = '	* @var s9e\\TextFormatter\\Renderer Singleton instance used by render()';
		$php[] = '	*/';
		$php[] = '	protected static $renderer;';
		$php[] = '';
		$events = [
			'beforeParse'
				=> 'Callback executed before parse(), receives the original text as argument',
			'afterParse'
				=> 'Callback executed after parse(), receives the parsed text as argument',
			'beforeRender'
				=> 'Callback executed before render(), receives the parsed text as argument',
			'afterRender'
				=> 'Callback executed after render(), receives the output as argument',
			'beforeUnparse'
				=> 'Callback executed before unparse(), receives the parsed text as argument',
			'afterUnparse'
				=> 'Callback executed after unparse(), receives the original text as argument'
		];
		foreach ($events as $eventName => $eventDesc)
			if (isset($options[$eventName]))
			{
				$php[] = '	/**';
				$php[] = '	* @var ' . $eventDesc;
				$php[] = '	*/';
				$php[] = '	public static $' . $eventName . ' = ' . \var_export($options[$eventName], \true) . ';';
				$php[] = '';
			}
		$php[] = '	/**';
		$php[] = '	* Return a new instance of s9e\\TextFormatter\\Parser';
		$php[] = '	*';
		$php[] = '	* @return s9e\\TextFormatter\\Parser';
		$php[] = '	*/';
		$php[] = '	public static function getParser()';
		$php[] = '	{';
		if (isset($options['parserSetup']))
		{
			$php[] = '		$parser = ' . $this->exportObject($parser) . ';';
			$php[] = '		' . $this->exportCallback($namespace, $options['parserSetup'], '$parser') . ';';
			$php[] = '';
			$php[] = '		return $parser;';
		}
		else
			$php[] = '		return ' . $this->exportObject($parser) . ';';
		$php[] = '	}';
		$php[] = '';
		$php[] = '	/**';
		$php[] = '	* Return a new instance of s9e\\TextFormatter\\Renderer';
		$php[] = '	*';
		$php[] = '	* @return s9e\\TextFormatter\\Renderer';
		$php[] = '	*/';
		$php[] = '	public static function getRenderer()';
		$php[] = '	{';
		if (!empty($options['autoInclude'])
		 && $this->configurator->rendering->engine instanceof PHP
		 && isset($this->configurator->rendering->engine->lastFilepath))
		{
			$className = \get_class($renderer);
			$filepath  = \realpath($this->configurator->rendering->engine->lastFilepath);
			$php[] = '		if (!class_exists(' . \var_export($className, \true) . ', false)';
			$php[] = '		 && file_exists(' . \var_export($filepath, \true) . '))';
			$php[] = '		{';
			$php[] = '			include ' . \var_export($filepath, \true) . ';';
			$php[] = '		}';
			$php[] = '';
		}
		if (isset($options['rendererSetup']))
		{
			$php[] = '		$renderer = ' . $this->exportObject($renderer) . ';';
			$php[] = '		' . $this->exportCallback($namespace, $options['rendererSetup'], '$renderer') . ';';
			$php[] = '';
			$php[] = '		return $renderer;';
		}
		else
			$php[] = '		return ' . $this->exportObject($renderer) . ';';
		$php[] = '	}';
		$php[] = '}';
		return \implode("\n", $php);
	}
	protected function exportCallback($namespace, callable $callback, $argument)
	{
		if (\is_array($callback) && \is_string($callback[0]))
			$callback = $callback[0] . '::' . $callback[1];
		if (!\is_string($callback))
			return 'call_user_func(' . \var_export($callback, \true) . ', ' . $argument . ')';
		if ($callback[0] !== '\\')
			$callback = '\\' . $callback;
		if (\substr($callback, 0, 2 + \strlen($namespace)) === '\\' . $namespace . '\\')
			$callback = \substr($callback, 2 + \strlen($namespace));
		return $callback . '(' . $argument . ')';
	}
	protected function exportObject($obj)
	{
		$str = \call_user_func($this->serializer, $obj);
		$str = \var_export($str, \true);
		return $this->unserializer . '(' . $str . ')';
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator;
interface ConfigProvider
{
	public function asConfig();
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator;
interface FilterableConfigValue
{
	public function filterConfig($target);
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\Helpers;
use DOMAttr;
use RuntimeException;
abstract class AVTHelper
{
	public static function parse($attrValue)
	{
		$tokens  = [];
		$attrLen = \strlen($attrValue);
		$pos = 0;
		while ($pos < $attrLen)
		{
			if ($attrValue[$pos] === '{')
			{
				if (\substr($attrValue, $pos, 2) === '{{')
				{
					$tokens[] = ['literal', '{'];
					$pos += 2;
					continue;
				}
				++$pos;
				$expr = '';
				while ($pos < $attrLen)
				{
					$spn = \strcspn($attrValue, '\'"}', $pos);
					if ($spn)
					{
						$expr .= \substr($attrValue, $pos, $spn);
						$pos += $spn;
					}
					if ($pos >= $attrLen)
						throw new RuntimeException('Unterminated XPath expression');
					$c = $attrValue[$pos];
					++$pos;
					if ($c === '}')
						break;
					$quotePos = \strpos($attrValue, $c, $pos);
					if ($quotePos === \false)
						throw new RuntimeException('Unterminated XPath expression');
					$expr .= $c . \substr($attrValue, $pos, $quotePos + 1 - $pos);
					$pos = 1 + $quotePos;
				}
				$tokens[] = ['expression', $expr];
			}
			$spn = \strcspn($attrValue, '{', $pos);
			if ($spn)
			{
				$str = \substr($attrValue, $pos, $spn);
				$str = \str_replace('}}', '}', $str);
				$tokens[] = ['literal', $str];
				$pos += $spn;
			}
		}
		return $tokens;
	}
	public static function replace(DOMAttr $attribute, callable $callback)
	{
		$tokens = self::parse($attribute->value);
		foreach ($tokens as $k => $token)
			$tokens[$k] = $callback($token);
		$attribute->value = \htmlspecialchars(self::serialize($tokens), \ENT_NOQUOTES, 'UTF-8');
	}
	public static function serialize(array $tokens)
	{
		$attrValue = '';
		foreach ($tokens as $token)
			if ($token[0] === 'literal')
				$attrValue .= \preg_replace('([{}])', '$0$0', $token[1]);
			elseif ($token[0] === 'expression')
				$attrValue .= '{' . $token[1] . '}';
			else
				throw new RuntimeException('Unknown token type');
		return $attrValue;
	}
	public static function toXSL($attrValue)
	{
		$xsl = '';
		foreach (self::parse($attrValue) as $_f6b3b659)
		{
			list($type, $content) = $_f6b3b659;
			if ($type === 'expression')
				$xsl .= '<xsl:value-of select="' . \htmlspecialchars($content, \ENT_COMPAT, 'UTF-8') . '"/>';
			elseif (\trim($content) !== $content)
				$xsl .= '<xsl:text>' . \htmlspecialchars($content, \ENT_NOQUOTES, 'UTF-8') . '</xsl:text>';
			else
				$xsl .= \htmlspecialchars($content, \ENT_NOQUOTES, 'UTF-8');
		}
		return $xsl;
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\Helpers;
class CharacterClassBuilder
{
	protected $chars;
	public $delimiter = '/';
	protected $ranges;
	public function fromList(array $chars)
	{
		$this->chars = $chars;
		$this->unescapeLiterals();
		\sort($this->chars);
		$this->storeRanges();
		$this->reorderDash();
		$this->fixCaret();
		$this->escapeSpecialChars();
		return $this->buildCharacterClass();
	}
	protected function buildCharacterClass()
	{
		$str = '[';
		foreach ($this->ranges as $_b7914274)
		{
			list($start, $end) = $_b7914274;
			if ($end > $start + 2)
				$str .= $this->chars[$start] . '-' . $this->chars[$end];
			else
				$str .= \implode('', \array_slice($this->chars, $start, $end + 1 - $start));
		}
		$str .= ']';
		return $str;
	}
	protected function escapeSpecialChars()
	{
		$specialChars = ['\\', ']', $this->delimiter];
		foreach (\array_intersect($this->chars, $specialChars) as $k => $v)
			$this->chars[$k] = '\\' . $v;
	}
	protected function fixCaret()
	{
		$k = \array_search('^', $this->chars, \true);
		if ($this->ranges[0][0] !== $k)
			return;
		if (isset($this->ranges[1]))
		{
			$range           = $this->ranges[0];
			$this->ranges[0] = $this->ranges[1];
			$this->ranges[1] = $range;
		}
		else
			$this->chars[$k] = '\\^';
	}
	protected function reorderDash()
	{
		$dashIndex = \array_search('-', $this->chars, \true);
		if ($dashIndex === \false)
			return;
		$k = \array_search([$dashIndex, $dashIndex], $this->ranges, \true);
		if ($k > 0)
		{
			unset($this->ranges[$k]);
			\array_unshift($this->ranges, [$dashIndex, $dashIndex]);
		}
		$commaIndex = \array_search(',', $this->chars);
		$range      = [$commaIndex, $dashIndex];
		$k          = \array_search($range, $this->ranges, \true);
		if ($k !== \false)
		{
			$this->ranges[$k] = [$commaIndex, $commaIndex];
			\array_unshift($this->ranges, [$dashIndex, $dashIndex]);
		}
	}
	protected function storeRanges()
	{
		$values = [];
		foreach ($this->chars as $char)
			if (\strlen($char) === 1)
				$values[] = \ord($char);
			else
				$values[] = \false;
		$i = \count($values) - 1;
		$ranges = [];
		while ($i >= 0)
		{
			$start = $i;
			$end   = $i;
			while ($start > 0 && $values[$start - 1] === $values[$end] - ($end + 1 - $start))
				--$start;
			$ranges[] = [$start, $end];
			$i = $start - 1;
		}
		$this->ranges = \array_reverse($ranges);
	}
	protected function unescapeLiterals()
	{
		foreach ($this->chars as $k => $char)
			if ($char[0] === '\\' && \preg_match('(^\\\\[^a-z]$)Di', $char))
				$this->chars[$k] = \substr($char, 1);
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\Helpers;
use RuntimeException;
use Traversable;
use s9e\TextFormatter\Configurator\ConfigProvider;
use s9e\TextFormatter\Configurator\FilterableConfigValue;
use s9e\TextFormatter\Configurator\JavaScript\Dictionary;
abstract class ConfigHelper
{
	public static function filterConfig(array $config, $target = 'PHP')
	{
		$filteredConfig = [];
		foreach ($config as $name => $value)
		{
			if ($value instanceof FilterableConfigValue)
			{
				$value = $value->filterConfig($target);
				if (!isset($value))
					continue;
			}
			if (\is_array($value))
				$value = self::filterConfig($value, $target);
			$filteredConfig[$name] = $value;
		}
		return $filteredConfig;
	}
	public static function generateQuickMatchFromList(array $strings)
	{
		foreach ($strings as $string)
		{
			$stringLen  = \strlen($string);
			$substrings = [];
			for ($len = $stringLen; $len; --$len)
			{
				$pos = $stringLen - $len;
				do
				{
					$substrings[\substr($string, $pos, $len)] = 1;
				}
				while (--$pos >= 0);
			}
			if (isset($goodStrings))
			{
				$goodStrings = \array_intersect_key($goodStrings, $substrings);
				if (empty($goodStrings))
					break;
			}
			else
				$goodStrings = $substrings;
		}
		if (empty($goodStrings))
			return \false;
		return \strval(\key($goodStrings));
	}
	public static function optimizeArray(array &$config, array &$cache = [])
	{
		foreach ($config as $k => &$v)
		{
			if (!\is_array($v))
				continue;
			self::optimizeArray($v, $cache);
			$cacheKey = \serialize($v);
			if (!isset($cache[$cacheKey]))
				$cache[$cacheKey] = $v;
			$config[$k] =& $cache[$cacheKey];
		}
		unset($v);
	}
	public static function toArray($value, $keepEmpty = \false, $keepNull = \false)
	{
		$array = [];
		foreach ($value as $k => $v)
		{
			$isDictionary = $v instanceof Dictionary;
			if ($v instanceof ConfigProvider)
				$v = $v->asConfig();
			elseif ($v instanceof Traversable || \is_array($v))
				$v = self::toArray($v, $keepEmpty, $keepNull);
			elseif (\is_scalar($v) || \is_null($v))
				;
			else
			{
				$type = (\is_object($v))
				      ? 'an instance of ' . \get_class($v)
				      : 'a ' . \gettype($v);
				throw new RuntimeException('Cannot convert ' . $type . ' to array');
			}
			if (!isset($v) && !$keepNull)
				continue;
			if (!$keepEmpty && $v === [])
				continue;
			$array[$k] = ($isDictionary) ? new Dictionary($v) : $v;
		}
		return $array;
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\Helpers;
use DOMElement;
use DOMXPath;
class ElementInspector
{
	protected static $htmlElements = [
		'a'=>['c'=>"\17\0\0\0\0\1",'c3'=>'@href','ac'=>"\0",'dd'=>"\10\0\0\0\0\1",'t'=>1,'fe'=>1],
		'abbr'=>['c'=>"\7",'ac'=>"\4",'dd'=>"\0"],
		'address'=>['c'=>"\3\40",'ac'=>"\1",'dd'=>"\0\45",'b'=>1,'cp'=>['p']],
		'article'=>['c'=>"\3\4",'ac'=>"\1",'dd'=>"\0",'b'=>1,'cp'=>['p']],
		'aside'=>['c'=>"\3\4",'ac'=>"\1",'dd'=>"\0\0\0\0\10",'b'=>1,'cp'=>['p']],
		'audio'=>['c'=>"\57",'c3'=>'@controls','c1'=>'@controls','ac'=>"\0\0\0\104",'ac26'=>'not(@src)','dd'=>"\0\0\0\0\0\2",'dd41'=>'@src','t'=>1],
		'b'=>['c'=>"\7",'ac'=>"\4",'dd'=>"\0",'fe'=>1],
		'base'=>['c'=>"\20",'ac'=>"\0",'dd'=>"\0",'nt'=>1,'e'=>1,'v'=>1,'b'=>1],
		'bdi'=>['c'=>"\7",'ac'=>"\4",'dd'=>"\0"],
		'bdo'=>['c'=>"\7",'ac'=>"\4",'dd'=>"\0"],
		'blockquote'=>['c'=>"\203",'ac'=>"\1",'dd'=>"\0",'b'=>1,'cp'=>['p']],
		'body'=>['c'=>"\200\0\4",'ac'=>"\1",'dd'=>"\0",'b'=>1],
		'br'=>['c'=>"\5",'ac'=>"\0",'dd'=>"\0",'nt'=>1,'e'=>1,'v'=>1],
		'button'=>['c'=>"\117",'ac'=>"\4",'dd'=>"\10"],
		'canvas'=>['c'=>"\47",'ac'=>"\0",'dd'=>"\0",'t'=>1],
		'caption'=>['c'=>"\0\2",'ac'=>"\1",'dd'=>"\0\0\0\200",'b'=>1],
		'cite'=>['c'=>"\7",'ac'=>"\4",'dd'=>"\0"],
		'code'=>['c'=>"\7",'ac'=>"\4",'dd'=>"\0",'fe'=>1],
		'col'=>['c'=>"\0\0\20",'ac'=>"\0",'dd'=>"\0",'nt'=>1,'e'=>1,'v'=>1,'b'=>1],
		'colgroup'=>['c'=>"\0\2",'ac'=>"\0\0\20",'ac20'=>'not(@span)','dd'=>"\0",'nt'=>1,'e'=>1,'e?'=>'@span','b'=>1],
		'data'=>['c'=>"\7",'ac'=>"\4",'dd'=>"\0"],
		'datalist'=>['c'=>"\5",'ac'=>"\4\200\0\10",'dd'=>"\0"],
		'dd'=>['c'=>"\0\0\200",'ac'=>"\1",'dd'=>"\0",'b'=>1,'cp'=>['dd','dt']],
		'del'=>['c'=>"\5",'ac'=>"\0",'dd'=>"\0",'t'=>1],
		'details'=>['c'=>"\213",'ac'=>"\1\0\0\2",'dd'=>"\0",'b'=>1,'cp'=>['p']],
		'dfn'=>['c'=>"\7\0\0\0\40",'ac'=>"\4",'dd'=>"\0\0\0\0\40"],
		'div'=>['c'=>"\3",'ac'=>"\1",'dd'=>"\0",'b'=>1,'cp'=>['p']],
		'dl'=>['c'=>"\3",'c1'=>'dt and dd','ac'=>"\0\200\200",'dd'=>"\0",'nt'=>1,'b'=>1,'cp'=>['p']],
		'dt'=>['c'=>"\0\0\200",'ac'=>"\1",'dd'=>"\0\5\0\40",'b'=>1,'cp'=>['dd','dt']],
		'em'=>['c'=>"\7",'ac'=>"\4",'dd'=>"\0",'fe'=>1],
		'embed'=>['c'=>"\57",'ac'=>"\0",'dd'=>"\0",'nt'=>1,'e'=>1,'v'=>1],
		'fieldset'=>['c'=>"\303",'ac'=>"\1\0\0\20",'dd'=>"\0",'b'=>1,'cp'=>['p']],
		'figcaption'=>['c'=>"\0\0\0\0\0\4",'ac'=>"\1",'dd'=>"\0",'b'=>1,'cp'=>['p']],
		'figure'=>['c'=>"\203",'ac'=>"\1\0\0\0\0\4",'dd'=>"\0",'b'=>1,'cp'=>['p']],
		'footer'=>['c'=>"\3\40",'ac'=>"\1",'dd'=>"\0\0\0\0\10",'b'=>1,'cp'=>['p']],
		'form'=>['c'=>"\3\0\0\0\20",'ac'=>"\1",'dd'=>"\0\0\0\0\20",'b'=>1,'cp'=>['p']],
		'h1'=>['c'=>"\3\1",'ac'=>"\4",'dd'=>"\0",'b'=>1,'cp'=>['p']],
		'h2'=>['c'=>"\3\1",'ac'=>"\4",'dd'=>"\0",'b'=>1,'cp'=>['p']],
		'h3'=>['c'=>"\3\1",'ac'=>"\4",'dd'=>"\0",'b'=>1,'cp'=>['p']],
		'h4'=>['c'=>"\3\1",'ac'=>"\4",'dd'=>"\0",'b'=>1,'cp'=>['p']],
		'h5'=>['c'=>"\3\1",'ac'=>"\4",'dd'=>"\0",'b'=>1,'cp'=>['p']],
		'h6'=>['c'=>"\3\1",'ac'=>"\4",'dd'=>"\0",'b'=>1,'cp'=>['p']],
		'head'=>['c'=>"\0\0\4",'ac'=>"\20",'dd'=>"\0",'nt'=>1,'b'=>1],
		'header'=>['c'=>"\3\40\0\40",'ac'=>"\1",'dd'=>"\0\0\0\0\10",'b'=>1,'cp'=>['p']],
		'hr'=>['c'=>"\1\100",'ac'=>"\0",'dd'=>"\0",'nt'=>1,'e'=>1,'v'=>1,'b'=>1,'cp'=>['p']],
		'html'=>['c'=>"\0",'ac'=>"\0\0\4",'dd'=>"\0",'nt'=>1,'b'=>1],
		'i'=>['c'=>"\7",'ac'=>"\4",'dd'=>"\0",'fe'=>1],
		'iframe'=>['c'=>"\57",'ac'=>"\4",'dd'=>"\0"],
		'img'=>['c'=>"\57\20\10",'c3'=>'@usemap','ac'=>"\0",'dd'=>"\0",'nt'=>1,'e'=>1,'v'=>1],
		'input'=>['c'=>"\17\20",'c3'=>'@type!="hidden"','c12'=>'@type!="hidden" or @type="hidden"','c1'=>'@type!="hidden"','ac'=>"\0",'dd'=>"\0",'nt'=>1,'e'=>1,'v'=>1],
		'ins'=>['c'=>"\7",'ac'=>"\0",'dd'=>"\0",'t'=>1],
		'kbd'=>['c'=>"\7",'ac'=>"\4",'dd'=>"\0"],
		'keygen'=>['c'=>"\117",'ac'=>"\0",'dd'=>"\0",'nt'=>1,'e'=>1,'v'=>1],
		'label'=>['c'=>"\17\20\0\0\4",'ac'=>"\4",'dd'=>"\0\0\1\0\4"],
		'legend'=>['c'=>"\0\0\0\20",'ac'=>"\4",'dd'=>"\0",'b'=>1],
		'li'=>['c'=>"\0\0\0\0\200",'ac'=>"\1",'dd'=>"\0",'b'=>1,'cp'=>['li']],
		'link'=>['c'=>"\20",'ac'=>"\0",'dd'=>"\0",'nt'=>1,'e'=>1,'v'=>1,'b'=>1],
		'main'=>['c'=>"\3\0\0\0\10",'ac'=>"\1",'dd'=>"\0",'b'=>1,'cp'=>['p']],
		'mark'=>['c'=>"\7",'ac'=>"\4",'dd'=>"\0"],
		'media element'=>['c'=>"\0\0\0\0\0\2",'ac'=>"\0",'dd'=>"\0",'nt'=>1,'b'=>1],
		'menu'=>['c'=>"\1\100",'ac'=>"\0\300",'dd'=>"\0",'nt'=>1,'b'=>1,'cp'=>['p']],
		'menuitem'=>['c'=>"\0\100",'ac'=>"\0",'dd'=>"\0",'nt'=>1,'e'=>1,'v'=>1,'b'=>1],
		'meta'=>['c'=>"\20",'ac'=>"\0",'dd'=>"\0",'nt'=>1,'e'=>1,'v'=>1,'b'=>1],
		'meter'=>['c'=>"\7\0\1\0\2",'ac'=>"\4",'dd'=>"\0\0\0\0\2"],
		'nav'=>['c'=>"\3\4",'ac'=>"\1",'dd'=>"\0\0\0\0\10",'b'=>1,'cp'=>['p']],
		'noscript'=>['c'=>"\25",'ac'=>"\0",'dd'=>"\0",'nt'=>1],
		'object'=>['c'=>"\147",'ac'=>"\0\0\0\0\1",'dd'=>"\0",'t'=>1],
		'ol'=>['c'=>"\3",'c1'=>'li','ac'=>"\0\200\0\0\200",'dd'=>"\0",'nt'=>1,'b'=>1,'cp'=>['p']],
		'optgroup'=>['c'=>"\0\0\2",'ac'=>"\0\200\0\10",'dd'=>"\0",'nt'=>1,'b'=>1,'cp'=>['optgroup','option']],
		'option'=>['c'=>"\0\0\2\10",'ac'=>"\0",'dd'=>"\0",'b'=>1,'cp'=>['option']],
		'output'=>['c'=>"\107",'ac'=>"\4",'dd'=>"\0"],
		'p'=>['c'=>"\3",'ac'=>"\4",'dd'=>"\0",'b'=>1,'cp'=>['p']],
		'param'=>['c'=>"\0\0\0\0\1",'ac'=>"\0",'dd'=>"\0",'nt'=>1,'e'=>1,'v'=>1,'b'=>1],
		'picture'=>['c'=>"\45",'ac'=>"\0\200\10",'dd'=>"\0",'nt'=>1],
		'pre'=>['c'=>"\3",'ac'=>"\4",'dd'=>"\0",'pre'=>1,'b'=>1,'cp'=>['p']],
		'progress'=>['c'=>"\7\0\1\1",'ac'=>"\4",'dd'=>"\0\0\0\1"],
		'q'=>['c'=>"\7",'ac'=>"\4",'dd'=>"\0"],
		'rb'=>['c'=>"\0\10",'ac'=>"\4",'dd'=>"\0",'b'=>1],
		'rp'=>['c'=>"\0\10\100",'ac'=>"\4",'dd'=>"\0",'b'=>1,'cp'=>['rp','rt']],
		'rt'=>['c'=>"\0\10\100",'ac'=>"\4",'dd'=>"\0",'b'=>1,'cp'=>['rp','rt']],
		'rtc'=>['c'=>"\0\10",'ac'=>"\4\0\100",'dd'=>"\0",'b'=>1],
		'ruby'=>['c'=>"\7",'ac'=>"\4\10",'dd'=>"\0"],
		's'=>['c'=>"\7",'ac'=>"\4",'dd'=>"\0",'fe'=>1],
		'samp'=>['c'=>"\7",'ac'=>"\4",'dd'=>"\0"],
		'script'=>['c'=>"\25\200",'ac'=>"\0",'dd'=>"\0",'to'=>1],
		'section'=>['c'=>"\3\4",'ac'=>"\1",'dd'=>"\0",'b'=>1,'cp'=>['p']],
		'select'=>['c'=>"\117",'ac'=>"\0\200\2",'dd'=>"\0",'nt'=>1],
		'small'=>['c'=>"\7",'ac'=>"\4",'dd'=>"\0",'fe'=>1],
		'source'=>['c'=>"\0\0\10\4",'ac'=>"\0",'dd'=>"\0",'nt'=>1,'e'=>1,'v'=>1,'b'=>1],
		'span'=>['c'=>"\7",'ac'=>"\4",'dd'=>"\0"],
		'strong'=>['c'=>"\7",'ac'=>"\4",'dd'=>"\0",'fe'=>1],
		'style'=>['c'=>"\20",'ac'=>"\0",'dd'=>"\0",'to'=>1,'b'=>1],
		'sub'=>['c'=>"\7",'ac'=>"\4",'dd'=>"\0"],
		'summary'=>['c'=>"\0\0\0\2",'ac'=>"\4\1",'dd'=>"\0",'b'=>1],
		'sup'=>['c'=>"\7",'ac'=>"\4",'dd'=>"\0"],
		'table'=>['c'=>"\3\0\0\200",'ac'=>"\0\202",'dd'=>"\0",'nt'=>1,'b'=>1,'cp'=>['p']],
		'tbody'=>['c'=>"\0\2",'ac'=>"\0\200\0\0\100",'dd'=>"\0",'nt'=>1,'b'=>1,'cp'=>['tbody','td','tfoot','th','thead','tr']],
		'td'=>['c'=>"\200\0\40",'ac'=>"\1",'dd'=>"\0",'b'=>1,'cp'=>['td','th']],
		'template'=>['c'=>"\25\200\20",'ac'=>"\0",'dd'=>"\0",'nt'=>1],
		'textarea'=>['c'=>"\117",'ac'=>"\0",'dd'=>"\0",'pre'=>1,'to'=>1],
		'tfoot'=>['c'=>"\0\2",'ac'=>"\0\200\0\0\100",'dd'=>"\0",'nt'=>1,'b'=>1,'cp'=>['tbody','td','th','thead','tr']],
		'th'=>['c'=>"\0\0\40",'ac'=>"\1",'dd'=>"\0\5\0\40",'b'=>1,'cp'=>['td','th']],
		'thead'=>['c'=>"\0\2",'ac'=>"\0\200\0\0\100",'dd'=>"\0",'nt'=>1,'b'=>1],
		'time'=>['c'=>"\7",'ac'=>"\4",'ac2'=>'@datetime','dd'=>"\0"],
		'title'=>['c'=>"\20",'ac'=>"\0",'dd'=>"\0",'to'=>1,'b'=>1],
		'tr'=>['c'=>"\0\2\0\0\100",'ac'=>"\0\200\40",'dd'=>"\0",'nt'=>1,'b'=>1,'cp'=>['td','th','tr']],
		'track'=>['c'=>"\0\0\0\100",'ac'=>"\0",'dd'=>"\0",'nt'=>1,'e'=>1,'v'=>1,'b'=>1],
		'u'=>['c'=>"\7",'ac'=>"\4",'dd'=>"\0",'fe'=>1],
		'ul'=>['c'=>"\3",'c1'=>'li','ac'=>"\0\200\0\0\200",'dd'=>"\0",'nt'=>1,'b'=>1,'cp'=>['p']],
		'var'=>['c'=>"\7",'ac'=>"\4",'dd'=>"\0"],
		'video'=>['c'=>"\57",'c3'=>'@controls','ac'=>"\0\0\0\104",'ac26'=>'not(@src)','dd'=>"\0\0\0\0\0\2",'dd41'=>'@src','t'=>1],
		'wbr'=>['c'=>"\5",'ac'=>"\0",'dd'=>"\0",'nt'=>1,'e'=>1,'v'=>1]
	];
	public static function closesParent(DOMElement $child, DOMElement $parent)
	{
		$parentName = $parent->nodeName;
		$childName  = $child->nodeName;
		return !empty(self::$htmlElements[$parentName]['cp']) && \in_array($childName, self::$htmlElements[$parentName]['cp'], \true);
	}
	public static function disallowsText(DOMElement $element)
	{
		return self::hasProperty($element, 'nt');
	}
	public static function getAllowChildBitfield(DOMElement $element)
	{
		return self::getBitfield($element, 'ac');
	}
	public static function getCategoryBitfield(DOMElement $element)
	{
		return self::getBitfield($element, 'c');
	}
	public static function getDenyDescendantBitfield(DOMElement $element)
	{
		return self::getBitfield($element, 'dd');
	}
	public static function isBlock(DOMElement $element)
	{
		return self::hasProperty($element, 'b');
	}
	public static function isEmpty(DOMElement $element)
	{
		return self::hasProperty($element, 'e');
	}
	public static function isFormattingElement(DOMElement $element)
	{
		return self::hasProperty($element, 'fe');
	}
	public static function isTextOnly(DOMElement $element)
	{
		return self::hasProperty($element, 'to');
	}
	public static function isTransparent(DOMElement $element)
	{
		return self::hasProperty($element, 't');
	}
	public static function isVoid(DOMElement $element)
	{
		return self::hasProperty($element, 'v');
	}
	public static function preservesWhitespace(DOMElement $element)
	{
		return self::hasProperty($element, 'pre');
	}
	protected static function evaluate($query, DOMElement $element)
	{
		$xpath = new DOMXPath($element->ownerDocument);
		return $xpath->evaluate('boolean(' . $query . ')', $element);
	}
	protected static function getBitfield(DOMElement $element, $name)
	{
		$props    = self::getProperties($element);
		$bitfield = self::toBin($props[$name]);
		foreach (\array_keys(\array_filter(\str_split($bitfield, 1))) as $bitNumber)
		{
			$conditionName = $name . $bitNumber;
			if (isset($props[$conditionName]) && !self::evaluate($props[$conditionName], $element))
				$bitfield[$bitNumber] = '0';
		}
		return self::toRaw($bitfield);
	}
	protected static function getProperties(DOMElement $element)
	{
		return (isset(self::$htmlElements[$element->nodeName])) ? self::$htmlElements[$element->nodeName] : self::$htmlElements['span'];
	}
	protected static function hasProperty(DOMElement $element, $propName)
	{
		$props = self::getProperties($element);
		return !empty($props[$propName]) && (!isset($props[$propName . '?']) || self::evaluate($props[$propName . '?'], $element));
	}
	protected static function toBin($raw)
	{
		$bin = '';
		foreach (\str_split($raw, 1) as $char)
			$bin .= \strrev(\substr('0000000' . \decbin(\ord($char)), -8));
		return $bin;
	}
	protected static function toRaw($bin)
	{
		return \implode('', \array_map('chr', \array_map('bindec', \array_map('strrev', \str_split($bin, 8)))));
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\Helpers;
use RuntimeException;
abstract class RegexpBuilder
{
	protected static $characterClassBuilder;
	public static function fromList(array $words, array $options = [])
	{
		if (empty($words))
			return '';
		$options += [
			'delimiter'       => '/',
			'caseInsensitive' => \false,
			'specialChars'    => [],
			'unicode'         => \true,
			'useLookahead'    => \false
		];
		if ($options['caseInsensitive'])
		{
			foreach ($words as &$word)
				$word = \strtr(
					$word,
					'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
					'abcdefghijklmnopqrstuvwxyz'
				);
			unset($word);
		}
		$words = \array_unique($words);
		\sort($words);
		$initials = [];
		$esc  = $options['specialChars'];
		$esc += [$options['delimiter'] => '\\' . $options['delimiter']];
		$esc += [
			'!' => '!',
			'-' => '-',
			':' => ':',
			'<' => '<',
			'=' => '=',
			'>' => '>',
			'}' => '}'
		];
		$splitWords = [];
		foreach ($words as $word)
		{
			$regexp = ($options['unicode']) ? '(.)us' : '(.)s';
			if (\preg_match_all($regexp, $word, $matches) === \false)
				throw new RuntimeException("Invalid UTF-8 string '" . $word . "'");
			$splitWord = [];
			foreach ($matches[0] as $pos => $c)
			{
				if (!isset($esc[$c]))
					$esc[$c] = \preg_quote($c);
				if ($pos === 0)
					$initials[] = $esc[$c];
				$splitWord[] = $esc[$c];
			}
			$splitWords[] = $splitWord;
		}
		self::$characterClassBuilder            = new CharacterClassBuilder;
		self::$characterClassBuilder->delimiter = $options['delimiter'];
		$regexp = self::assemble([self::mergeChains($splitWords)]);
		if ($options['useLookahead']
		 && \count($initials) > 1
		 && $regexp[0] !== '[')
		{
			$useLookahead = \true;
			foreach ($initials as $initial)
				if (!self::canBeUsedInCharacterClass($initial))
				{
					$useLookahead = \false;
					break;
				}
			if ($useLookahead)
				$regexp = '(?=' . self::generateCharacterClass($initials) . ')' . $regexp;
		}
		return $regexp;
	}
	protected static function mergeChains(array $chains, $preventRemerge = \false)
	{
		if (!isset($chains[1]))
			return $chains[0];
		$mergedChain = self::removeLongestCommonPrefix($chains);
		if (!isset($chains[0][0])
		 && !\array_filter($chains))
			return $mergedChain;
		$suffix = self::removeLongestCommonSuffix($chains);
		if (isset($chains[1]))
		{
			self::optimizeDotChains($chains);
			self::optimizeCatchallChains($chains);
		}
		$endOfChain = \false;
		$remerge = \false;
		$groups = [];
		foreach ($chains as $chain)
		{
			if (!isset($chain[0]))
			{
				$endOfChain = \true;
				continue;
			}
			$head = $chain[0];
			if (isset($groups[$head]))
				$remerge = \true;
			$groups[$head][] = $chain;
		}
		$characterClass = [];
		foreach ($groups as $head => $groupChains)
		{
			$head = (string) $head;
			if ($groupChains === [[$head]]
			 && self::canBeUsedInCharacterClass($head))
				$characterClass[$head] = $head;
		}
		\sort($characterClass);
		if (isset($characterClass[1]))
		{
			foreach ($characterClass as $char)
				unset($groups[$char]);
			$head = self::generateCharacterClass($characterClass);
			$groups[$head][] = [$head];
			$groups = [$head => $groups[$head]]
			        + $groups;
		}
		if ($remerge && !$preventRemerge)
		{
			$mergedChains = [];
			foreach ($groups as $head => $groupChains)
				$mergedChains[] = self::mergeChains($groupChains);
			self::mergeTails($mergedChains);
			$regexp = \implode('', self::mergeChains($mergedChains, \true));
			if ($endOfChain)
				$regexp = self::makeRegexpOptional($regexp);
			$mergedChain[] = $regexp;
		}
		else
		{
			self::mergeTails($chains);
			$mergedChain[] = self::assemble($chains);
		}
		foreach ($suffix as $atom)
			$mergedChain[] = $atom;
		return $mergedChain;
	}
	protected static function mergeTails(array &$chains)
	{
		self::mergeTailsCC($chains);
		self::mergeTailsAltern($chains);
		$chains = \array_values($chains);
	}
	protected static function mergeTailsCC(array &$chains)
	{
		$groups = [];
		foreach ($chains as $k => $chain)
			if (isset($chain[1])
			 && !isset($chain[2])
			 && self::canBeUsedInCharacterClass($chain[0]))
				$groups[$chain[1]][$k] = $chain;
		foreach ($groups as $groupChains)
		{
			if (\count($groupChains) < 2)
				continue;
			$chains = \array_diff_key($chains, $groupChains);
			$chains[] = self::mergeChains(\array_values($groupChains));
		}
	}
	protected static function mergeTailsAltern(array &$chains)
	{
		$groups = [];
		foreach ($chains as $k => $chain)
			if (!empty($chain))
			{
				$tail = \array_slice($chain, -1);
				$groups[$tail[0]][$k] = $chain;
			}
		foreach ($groups as $tail => $groupChains)
		{
			if (\count($groupChains) < 2)
				continue;
			$mergedChain = self::mergeChains(\array_values($groupChains));
			$oldLen = 0;
			foreach ($groupChains as $groupChain)
				$oldLen += \array_sum(\array_map('strlen', $groupChain));
			if ($oldLen <= \array_sum(\array_map('strlen', $mergedChain)))
				continue;
			$chains = \array_diff_key($chains, $groupChains);
			$chains[] = $mergedChain;
		}
	}
	protected static function removeLongestCommonPrefix(array &$chains)
	{
		$pLen = 0;
		while (1)
		{
			$c = \null;
			foreach ($chains as $chain)
			{
				if (!isset($chain[$pLen]))
					break 2;
				if (!isset($c))
				{
					$c = $chain[$pLen];
					continue;
				}
				if ($chain[$pLen] !== $c)
					break 2;
			}
			++$pLen;
		}
		if (!$pLen)
			return [];
		$prefix = \array_slice($chains[0], 0, $pLen);
		foreach ($chains as &$chain)
			$chain = \array_slice($chain, $pLen);
		unset($chain);
		return $prefix;
	}
	protected static function removeLongestCommonSuffix(array &$chains)
	{
		$chainsLen = \array_map('count', $chains);
		$maxLen = \min($chainsLen);
		if (\max($chainsLen) === $maxLen)
			--$maxLen;
		$sLen = 0;
		while ($sLen < $maxLen)
		{
			$c = \null;
			foreach ($chains as $k => $chain)
			{
				$pos = $chainsLen[$k] - ($sLen + 1);
				if (!isset($c))
				{
					$c = $chain[$pos];
					continue;
				}
				if ($chain[$pos] !== $c)
					break 2;
			}
			++$sLen;
		}
		if (!$sLen)
			return [];
		$suffix = \array_slice($chains[0], -$sLen);
		foreach ($chains as &$chain)
			$chain = \array_slice($chain, 0, -$sLen);
		unset($chain);
		return $suffix;
	}
	protected static function assemble(array $chains)
	{
		$endOfChain = \false;
		$regexps        = [];
		$characterClass = [];
		foreach ($chains as $chain)
		{
			if (empty($chain))
			{
				$endOfChain = \true;
				continue;
			}
			if (!isset($chain[1])
			 && self::canBeUsedInCharacterClass($chain[0]))
				$characterClass[$chain[0]] = $chain[0];
			else
				$regexps[] = \implode('', $chain);
		}
		if (!empty($characterClass))
		{
			\sort($characterClass);
			$regexp = (isset($characterClass[1]))
					? self::generateCharacterClass($characterClass)
					: $characterClass[0];
			\array_unshift($regexps, $regexp);
		}
		if (empty($regexps))
			return '';
		if (isset($regexps[1]))
		{
			$regexp = \implode('|', $regexps);
			$regexp = ((self::canUseAtomicGrouping($regexp)) ? '(?>' : '(?:') . $regexp . ')';
		}
		else
			$regexp = $regexps[0];
		if ($endOfChain)
			$regexp = self::makeRegexpOptional($regexp);
		return $regexp;
	}
	protected static function makeRegexpOptional($regexp)
	{
		if (\preg_match('#^\\.\\+\\??$#', $regexp))
			return \str_replace('+', '*', $regexp);
		if (\preg_match('#^(\\\\?.)((?:\\1\\?)+)$#Du', $regexp, $m))
			return $m[1] . '?' . $m[2];
		if (\preg_match('#^(?:[$^]|\\\\[bBAZzGQEK])$#', $regexp))
			return '';
		if (\preg_match('#^\\\\?.$#Dus', $regexp))
			$isAtomic = \true;
		elseif (\preg_match('#^[^[(].#s', $regexp))
			$isAtomic = \false;
		else
		{
			$def    = RegexpParser::parse('#' . $regexp . '#');
			$tokens = $def['tokens'];
			switch (\count($tokens))
			{
				case 1:
					$startPos = $tokens[0]['pos'];
					$len      = $tokens[0]['len'];
					$isAtomic = (bool) ($startPos === 0 && $len === \strlen($regexp));
					if ($isAtomic && $tokens[0]['type'] === 'characterClass')
					{
						$regexp = \rtrim($regexp, '+*?');
						if (!empty($tokens[0]['quantifiers']) && $tokens[0]['quantifiers'] !== '?')
							$regexp .= '*';
					}
					break;
				case 2:
					if ($tokens[0]['type'] === 'nonCapturingSubpatternStart'
					 && $tokens[1]['type'] === 'nonCapturingSubpatternEnd')
					{
						$startPos = $tokens[0]['pos'];
						$len      = $tokens[1]['pos'] + $tokens[1]['len'];
						$isAtomic = (bool) ($startPos === 0 && $len === \strlen($regexp));
						break;
					}
					default:
					$isAtomic = \false;
			}
		}
		if (!$isAtomic)
			$regexp = ((self::canUseAtomicGrouping($regexp)) ? '(?>' : '(?:') . $regexp . ')';
		$regexp .= '?';
		return $regexp;
	}
	protected static function generateCharacterClass(array $chars)
	{
		return self::$characterClassBuilder->fromList($chars);
	}
	protected static function canBeUsedInCharacterClass($char)
	{
		if (\preg_match('#^\\\\[aefnrtdDhHsSvVwW]$#D', $char))
			return \true;
		if (\preg_match('#^\\\\[^A-Za-z0-9]$#Dus', $char))
			return \true;
		if (\preg_match('#..#Dus', $char))
			return \false;
		if (\preg_quote($char) !== $char
		 && !\preg_match('#^[-!:<=>}]$#D', $char))
			return \false;
		return \true;
	}
	protected static function optimizeDotChains(array &$chains)
	{
		$validAtoms = [
			'\\d' => 1, '\\D' => 1, '\\h' => 1, '\\H' => 1,
			'\\s' => 1, '\\S' => 1, '\\v' => 1, '\\V' => 1,
			'\\w' => 1, '\\W' => 1,
			'\\^' => 1, '\\$' => 1, '\\.' => 1, '\\?' => 1,
			'\\[' => 1, '\\]' => 1, '\\(' => 1, '\\)' => 1,
			'\\+' => 1, '\\*' => 1, '\\\\' => 1
		];
		do
		{
			$hasMoreDots = \false;
			foreach ($chains as $k1 => $dotChain)
			{
				$dotKeys = \array_keys($dotChain, '.?', \true);
				if (!empty($dotKeys))
				{
					$dotChain[$dotKeys[0]] = '.';
					$chains[$k1] = $dotChain;
					\array_splice($dotChain, $dotKeys[0], 1);
					$chains[] = $dotChain;
					if (isset($dotKeys[1]))
						$hasMoreDots = \true;
				}
			}
		}
		while ($hasMoreDots);
		foreach ($chains as $k1 => $dotChain)
		{
			$dotKeys = \array_keys($dotChain, '.', \true);
			if (empty($dotKeys))
				continue;
			foreach ($chains as $k2 => $tmpChain)
			{
				if ($k2 === $k1)
					continue;
				foreach ($dotKeys as $dotKey)
				{
					if (!isset($tmpChain[$dotKey]))
						continue 2;
					if (!\preg_match('#^.$#Du', \preg_quote($tmpChain[$dotKey]))
					 && !isset($validAtoms[$tmpChain[$dotKey]]))
						continue 2;
					$tmpChain[$dotKey] = '.';
				}
				if ($tmpChain === $dotChain)
					unset($chains[$k2]);
			}
		}
	}
	protected static function optimizeCatchallChains(array &$chains)
	{
		$precedence = [
			'.*'  => 3,
			'.*?' => 2,
			'.+'  => 1,
			'.+?' => 0
		];
		$tails = [];
		foreach ($chains as $k => $chain)
		{
			if (!isset($chain[0]))
				continue;
			$head = $chain[0];
			if (!isset($precedence[$head]))
				continue;
			$tail = \implode('', \array_slice($chain, 1));
			if (!isset($tails[$tail])
			 || $precedence[$head] > $tails[$tail]['precedence'])
				$tails[$tail] = [
					'key'        => $k,
					'precedence' => $precedence[$head]
				];
		}
		$catchallChains = [];
		foreach ($tails as $tail => $info)
			$catchallChains[$info['key']] = $chains[$info['key']];
		foreach ($catchallChains as $k1 => $catchallChain)
		{
			$headExpr = $catchallChain[0];
			$tailExpr = \false;
			$match    = \array_slice($catchallChain, 1);
			if (isset($catchallChain[1])
			 && isset($precedence[\end($catchallChain)]))
				$tailExpr = \array_pop($match);
			$matchCnt = \count($match);
			foreach ($chains as $k2 => $chain)
			{
				if ($k2 === $k1)
					continue;
				$start = 0;
				$end = \count($chain);
				if ($headExpr[1] === '+')
				{
					$found = \false;
					foreach ($chain as $start => $atom)
						if (self::matchesAtLeastOneCharacter($atom))
						{
							$found = \true;
							break;
						}
					if (!$found)
						continue;
				}
				if ($tailExpr === \false)
					$end = $start;
				else
				{
					if ($tailExpr[1] === '+')
					{
						$found = \false;
						while (--$end > $start)
							if (self::matchesAtLeastOneCharacter($chain[$end]))
							{
								$found = \true;
								break;
							}
						if (!$found)
							continue;
					}
					$end -= $matchCnt;
				}
				while ($start <= $end)
				{
					if (\array_slice($chain, $start, $matchCnt) === $match)
					{
						unset($chains[$k2]);
						break;
					}
					++$start;
				}
			}
		}
	}
	protected static function matchesAtLeastOneCharacter($expr)
	{
		if (\preg_match('#^[$*?^]$#', $expr))
			return \false;
		if (\preg_match('#^.$#u', $expr))
			return \true;
		if (\preg_match('#^.\\+#u', $expr))
			return \true;
		if (\preg_match('#^\\\\[^bBAZzGQEK1-9](?![*?])#', $expr))
			return \true;
		return \false;
	}
	protected static function canUseAtomicGrouping($expr)
	{
		if (\preg_match('#(?<!\\\\)(?>\\\\\\\\)*\\.#', $expr))
			return \false;
		if (\preg_match('#(?<!\\\\)(?>\\\\\\\\)*[+*]#', $expr))
			return \false;
		if (\preg_match('#(?<!\\\\)(?>\\\\\\\\)*\\(?(?<!\\()\\?#', $expr))
			return \false;
		if (\preg_match('#(?<!\\\\)(?>\\\\\\\\)*\\\\[a-z0-9]#', $expr))
			return \false;
		return \true;
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\Helpers;
use s9e\TextFormatter\Configurator\Collections\Ruleset;
use s9e\TextFormatter\Configurator\Collections\TagCollection;
abstract class RulesHelper
{
	public static function getBitfields(TagCollection $tags, Ruleset $rootRules)
	{
		$rules = ['*root*' => \iterator_to_array($rootRules)];
		foreach ($tags as $tagName => $tag)
			$rules[$tagName] = \iterator_to_array($tag->rules);
		$matrix = self::unrollRules($rules);
		self::pruneMatrix($matrix);
		$groupedTags = [];
		foreach (\array_keys($matrix) as $tagName)
		{
			if ($tagName === '*root*')
				continue;
			$k = '';
			foreach ($matrix as $tagMatrix)
			{
				$k .= $tagMatrix['allowedChildren'][$tagName];
				$k .= $tagMatrix['allowedDescendants'][$tagName];
			}
			$groupedTags[$k][] = $tagName;
		}
		$bitTag     = [];
		$bitNumber  = 0;
		$tagsConfig = [];
		foreach ($groupedTags as $tagNames)
		{
			foreach ($tagNames as $tagName)
			{
				$tagsConfig[$tagName]['bitNumber'] = $bitNumber;
				$bitTag[$bitNumber] = $tagName;
			}
			++$bitNumber;
		}
		foreach ($matrix as $tagName => $tagMatrix)
		{
			$allowedChildren    = '';
			$allowedDescendants = '';
			foreach ($bitTag as $targetName)
			{
				$allowedChildren    .= $tagMatrix['allowedChildren'][$targetName];
				$allowedDescendants .= $tagMatrix['allowedDescendants'][$targetName];
			}
			$tagsConfig[$tagName]['allowed'] = self::pack($allowedChildren, $allowedDescendants);
		}
		$return = [
			'root' => $tagsConfig['*root*'],
			'tags' => $tagsConfig
		];
		unset($return['tags']['*root*']);
		return $return;
	}
	protected static function initMatrix(array $rules)
	{
		$matrix   = [];
		$tagNames = \array_keys($rules);
		foreach ($rules as $tagName => $tagRules)
		{
			$matrix[$tagName]['allowedChildren']    = \array_fill_keys($tagNames, 0);
			$matrix[$tagName]['allowedDescendants'] = \array_fill_keys($tagNames, 0);
		}
		return $matrix;
	}
	protected static function applyTargetedRule(array &$matrix, $rules, $ruleName, $key, $value)
	{
		foreach ($rules as $tagName => $tagRules)
		{
			if (!isset($tagRules[$ruleName]))
				continue;
			foreach ($tagRules[$ruleName] as $targetName)
				$matrix[$tagName][$key][$targetName] = $value;
		}
	}
	protected static function unrollRules(array $rules)
	{
		$matrix = self::initMatrix($rules);
		$tagNames = \array_keys($rules);
		foreach ($rules as $tagName => $tagRules)
		{
			if (!empty($tagRules['ignoreTags']))
			{
				$rules[$tagName]['denyChild']      = $tagNames;
				$rules[$tagName]['denyDescendant'] = $tagNames;
			}
			if (!empty($tagRules['requireParent']))
			{
				$denyParents = \array_diff($tagNames, $tagRules['requireParent']);
				foreach ($denyParents as $parentName)
					$rules[$parentName]['denyChild'][] = $tagName;
			}
		}
		self::applyTargetedRule($matrix, $rules, 'allowChild',      'allowedChildren',    1);
		self::applyTargetedRule($matrix, $rules, 'allowDescendant', 'allowedDescendants', 1);
		self::applyTargetedRule($matrix, $rules, 'denyChild',      'allowedChildren',    0);
		self::applyTargetedRule($matrix, $rules, 'denyDescendant', 'allowedDescendants', 0);
		return $matrix;
	}
	protected static function pruneMatrix(array &$matrix)
	{
		$usableTags = ['*root*' => 1];
		$parentTags = $usableTags;
		do
		{
			$nextTags = [];
			foreach (\array_keys($parentTags) as $tagName)
				$nextTags += \array_filter($matrix[$tagName]['allowedChildren']);
			$parentTags  = \array_diff_key($nextTags, $usableTags);
			$parentTags  = \array_intersect_key($parentTags, $matrix);
			$usableTags += $parentTags;
		}
		while (!empty($parentTags));
		$matrix = \array_intersect_key($matrix, $usableTags);
		unset($usableTags['*root*']);
		foreach ($matrix as $tagName => &$tagMatrix)
		{
			$tagMatrix['allowedChildren']
				= \array_intersect_key($tagMatrix['allowedChildren'], $usableTags);
			$tagMatrix['allowedDescendants']
				= \array_intersect_key($tagMatrix['allowedDescendants'], $usableTags);
		}
		unset($tagMatrix);
	}
	protected static function pack($allowedChildren, $allowedDescendants)
	{
		$allowedChildren    = \str_split($allowedChildren,    8);
		$allowedDescendants = \str_split($allowedDescendants, 8);
		$allowed = [];
		foreach (\array_keys($allowedChildren) as $k)
			$allowed[] = \bindec(\sprintf(
				'%1$08s%2$08s',
				\strrev($allowedDescendants[$k]),
				\strrev($allowedChildren[$k])
			));
		return $allowed;
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\Helpers;
use DOMAttr;
use DOMCharacterData;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMProcessingInstruction;
use DOMXPath;
use RuntimeException;
use s9e\TextFormatter\Configurator\Exceptions\InvalidXslException;
use s9e\TextFormatter\Configurator\Helpers\RegexpBuilder;
abstract class TemplateHelper
{
	const XMLNS_XSL = 'http://www.w3.org/1999/XSL/Transform';
	public static function getAttributesByRegexp(DOMDocument $dom, $regexp)
	{
		$xpath = new DOMXPath($dom);
		$nodes = [];
		foreach ($xpath->query('//@*') as $attribute)
			if (\preg_match($regexp, $attribute->name))
				$nodes[] = $attribute;
		foreach ($xpath->query('//xsl:attribute') as $attribute)
			if (\preg_match($regexp, $attribute->getAttribute('name')))
				$nodes[] = $attribute;
		foreach ($xpath->query('//xsl:copy-of') as $node)
		{
			$expr = $node->getAttribute('select');
			if (\preg_match('/^@(\\w+)$/', $expr, $m)
			 && \preg_match($regexp, $m[1]))
				$nodes[] = $node;
		}
		return $nodes;
	}
	public static function getCSSNodes(DOMDocument $dom)
	{
		$regexp = '/^style$/i';
		$nodes  = \array_merge(
			self::getAttributesByRegexp($dom, $regexp),
			self::getElementsByRegexp($dom, '/^style$/i')
		);
		return $nodes;
	}
	public static function getElementsByRegexp(DOMDocument $dom, $regexp)
	{
		$xpath = new DOMXPath($dom);
		$nodes = [];
		foreach ($xpath->query('//*') as $element)
			if (\preg_match($regexp, $element->localName))
				$nodes[] = $element;
		foreach ($xpath->query('//xsl:element') as $element)
			if (\preg_match($regexp, $element->getAttribute('name')))
				$nodes[] = $element;
		foreach ($xpath->query('//xsl:copy-of') as $node)
		{
			$expr = $node->getAttribute('select');
			if (\preg_match('/^\\w+$/', $expr)
			 && \preg_match($regexp, $expr))
				$nodes[] = $node;
		}
		return $nodes;
	}
	public static function getJSNodes(DOMDocument $dom)
	{
		$regexp = '/^(?>data-s9e-livepreview-postprocess$|on)/i';
		$nodes  = \array_merge(
			self::getAttributesByRegexp($dom, $regexp),
			self::getElementsByRegexp($dom, '/^script$/i')
		);
		return $nodes;
	}
	public static function getObjectParamsByRegexp(DOMDocument $dom, $regexp)
	{
		$xpath = new DOMXPath($dom);
		$nodes = [];
		foreach (self::getAttributesByRegexp($dom, $regexp) as $attribute)
			if ($attribute->nodeType === \XML_ATTRIBUTE_NODE)
			{
				if (\strtolower($attribute->parentNode->localName) === 'embed')
					$nodes[] = $attribute;
			}
			elseif ($xpath->evaluate('ancestor::embed', $attribute))
				$nodes[] = $attribute;
		foreach ($dom->getElementsByTagName('object') as $object)
			foreach ($object->getElementsByTagName('param') as $param)
				if (\preg_match($regexp, $param->getAttribute('name')))
					$nodes[] = $param;
		return $nodes;
	}
	public static function getParametersFromXSL($xsl)
	{
		$paramNames = [];
		$xsl = '<xsl:stylesheet xmlns:xsl="' . self::XMLNS_XSL . '"><xsl:template>'
		     . $xsl
		     . '</xsl:template></xsl:stylesheet>';
		$dom = new DOMDocument;
		$dom->loadXML($xsl);
		$xpath = new DOMXPath($dom);
		$query = '//xsl:*/@match | //xsl:*/@select | //xsl:*/@test';
		foreach ($xpath->query($query) as $attribute)
			foreach (XPathHelper::getVariables($attribute->value) as $varName)
			{
				$varQuery = 'ancestor-or-self::*/preceding-sibling::xsl:variable[@name="' . $varName . '"]';
				if (!$xpath->query($varQuery, $attribute)->length)
					$paramNames[] = $varName;
			}
		$query = '//*[namespace-uri() != "' . self::XMLNS_XSL . '"]/@*[contains(., "{")]';
		foreach ($xpath->query($query) as $attribute)
		{
			$tokens = AVTHelper::parse($attribute->value);
			foreach ($tokens as $token)
			{
				if ($token[0] !== 'expression')
					continue;
				foreach (XPathHelper::getVariables($token[1]) as $varName)
				{
					$varQuery = 'ancestor-or-self::*/preceding-sibling::xsl:variable[@name="' . $varName . '"]';
					if (!$xpath->query($varQuery, $attribute)->length)
						$paramNames[] = $varName;
				}
			}
		}
		$paramNames = \array_unique($paramNames);
		\sort($paramNames);
		return $paramNames;
	}
	public static function getURLNodes(DOMDocument $dom)
	{
		$regexp = '/(?>^(?>action|background|c(?>ite|lassid|odebase)|data|formaction|href|icon|longdesc|manifest|p(?>luginspage|oster|rofile)|usemap)|src)$/i';
		$nodes  = self::getAttributesByRegexp($dom, $regexp);
		foreach (self::getObjectParamsByRegexp($dom, '/^(?:dataurl|movie)$/i') as $param)
		{
			$node = $param->getAttributeNode('value');
			if ($node)
				$nodes[] = $node;
		}
		return $nodes;
	}
	public static function highlightNode(DOMNode $node, $prepend, $append)
	{
		$uniqid = \uniqid('_');
		if ($node instanceof DOMAttr)
			$node->value .= $uniqid;
		elseif ($node instanceof DOMElement)
			$node->setAttribute($uniqid, '');
		elseif ($node instanceof DOMCharacterData
		     || $node instanceof DOMProcessingInstruction)
			$node->data .= $uniqid;
		$dom = $node->ownerDocument;
		$dom->formatOutput = \true;
		$docXml = self::innerXML($dom->documentElement);
		$docXml = \trim(\str_replace("\n  ", "\n", $docXml));
		$nodeHtml = \htmlspecialchars(\trim($dom->saveXML($node)));
		$docHtml  = \htmlspecialchars($docXml);
		$html = \str_replace($nodeHtml, $prepend . $nodeHtml . $append, $docHtml);
		if ($node instanceof DOMAttr)
		{
			$node->value = \substr($node->value, 0, -\strlen($uniqid));
			$html = \str_replace($uniqid, '', $html);
		}
		elseif ($node instanceof DOMElement)
		{
			$node->removeAttribute($uniqid);
			$html = \str_replace(' ' . $uniqid . '=&quot;&quot;', '', $html);
		}
		elseif ($node instanceof DOMCharacterData
		     || $node instanceof DOMProcessingInstruction)
		{
			$node->data .= $uniqid;
			$html = \str_replace($uniqid, '', $html);
		}
		return $html;
	}
	public static function loadTemplate($template)
	{
		$dom = self::loadTemplateAsXML($template);
		if ($dom)
			return $dom;
		$dom = self::loadTemplateAsXML(self::fixEntities($template));
		if ($dom)
			return $dom;
		if (\strpos($template, '<xsl:') !== \false)
		{
			$error = \libxml_get_last_error();
			throw new InvalidXslException($error->message);
		}
		return self::loadTemplateAsHTML($template);
	}
	public static function replaceHomogeneousTemplates(array &$templates, $minCount = 3)
	{
		$tagNames = [];
		$expr = 'name()';
		foreach ($templates as $tagName => $template)
		{
			$elName = \strtolower(\preg_replace('/^[^:]+:/', '', $tagName));
			if ($template === '<' . $elName . '><xsl:apply-templates/></' . $elName . '>')
			{
				$tagNames[] = $tagName;
				if (\strpos($tagName, ':') !== \false)
					$expr = 'local-name()';
			}
		}
		if (\count($tagNames) < $minCount)
			return;
		$chars = \preg_replace('/[^A-Z]+/', '', \count_chars(\implode('', $tagNames), 3));
		if (\is_string($chars) && $chars !== '')
			$expr = 'translate(' . $expr . ",'" . $chars . "','" . \strtolower($chars) . "')";
		$template = '<xsl:element name="{' . $expr . '}"><xsl:apply-templates/></xsl:element>';
		foreach ($tagNames as $tagName)
			$templates[$tagName] = $template;
	}
	public static function replaceTokens($template, $regexp, $fn)
	{
		if ($template === '')
			return $template;
		$dom   = self::loadTemplate($template);
		$xpath = new DOMXPath($dom);
		foreach ($xpath->query('//@*') as $attribute)
		{
			$attrValue = \preg_replace_callback(
				$regexp,
				function ($m) use ($fn, $attribute)
				{
					$replacement = $fn($m, $attribute);
					if ($replacement[0] === 'expression')
						return '{' . $replacement[1] . '}';
					elseif ($replacement[0] === 'passthrough')
						return '{.}';
					else
						return $replacement[1];
				},
				$attribute->value
			);
			$attribute->value = \htmlspecialchars($attrValue, \ENT_COMPAT, 'UTF-8');
		}
		foreach ($xpath->query('//text()') as $node)
		{
			\preg_match_all(
				$regexp,
				$node->textContent,
				$matches,
				\PREG_SET_ORDER | \PREG_OFFSET_CAPTURE
			);
			if (empty($matches))
				continue;
			$parentNode = $node->parentNode;
			$lastPos = 0;
			foreach ($matches as $m)
			{
				$pos = $m[0][1];
				if ($pos > $lastPos)
					$parentNode->insertBefore(
						$dom->createTextNode(
							\substr($node->textContent, $lastPos, $pos - $lastPos)
						),
						$node
					);
				$lastPos = $pos + \strlen($m[0][0]);
				$_m = [];
				foreach ($m as $capture)
					$_m[] = $capture[0];
				$replacement = $fn($_m, $node);
				if ($replacement[0] === 'expression')
					$parentNode
						->insertBefore(
							$dom->createElementNS(self::XMLNS_XSL, 'xsl:value-of'),
							$node
						)
						->setAttribute('select', $replacement[1]);
				elseif ($replacement[0] === 'passthrough')
					$parentNode->insertBefore(
						$dom->createElementNS(self::XMLNS_XSL, 'xsl:apply-templates'),
						$node
					);
				else
					$parentNode->insertBefore($dom->createTextNode($replacement[1]), $node);
			}
			$text = \substr($node->textContent, $lastPos);
			if ($text > '')
				$parentNode->insertBefore($dom->createTextNode($text), $node);
			$parentNode->removeChild($node);
		}
		return self::saveTemplate($dom);
	}
	public static function saveTemplate(DOMDocument $dom)
	{
		return self::innerXML($dom->documentElement);
	}
	protected static function fixEntities($template)
	{
		return \preg_replace_callback(
			'(&(?!quot;|amp;|apos;|lt;|gt;)\\w+;)',
			function ($m)
			{
				return \html_entity_decode($m[0], \ENT_NOQUOTES, 'UTF-8');
			},
			\preg_replace('(&(?![A-Za-z0-9]+;|#\\d+;|#x[A-Fa-f0-9]+;))', '&amp;', $template)
		);
	}
	protected static function innerXML(DOMElement $element)
	{
		$xml = $element->ownerDocument->saveXML($element);
		$pos = 1 + \strpos($xml, '>');
		$len = \strrpos($xml, '<') - $pos;
		if ($len < 1)
			return '';
		$xml = \substr($xml, $pos, $len);
		return $xml;
	}
	protected static function loadTemplateAsHTML($template)
	{
		$dom  = new DOMDocument;
		$html = '<?xml version="1.0" encoding="utf-8" ?><html><body><div>' . $template . '</div></body></html>';
		$useErrors = \libxml_use_internal_errors(\true);
		$dom->loadHTML($html);
		self::removeInvalidAttributes($dom);
		\libxml_use_internal_errors($useErrors);
		$xml = '<?xml version="1.0" encoding="utf-8" ?><xsl:template xmlns:xsl="' . self::XMLNS_XSL . '">' . self::innerXML($dom->documentElement->firstChild->firstChild) . '</xsl:template>';
		$useErrors = \libxml_use_internal_errors(\true);
		$dom->loadXML($xml);
		\libxml_use_internal_errors($useErrors);
		return $dom;
	}
	protected static function loadTemplateAsXML($template)
	{
		$xml = '<?xml version="1.0" encoding="utf-8" ?><xsl:template xmlns:xsl="' . self::XMLNS_XSL . '">' . $template . '</xsl:template>';
		$useErrors = \libxml_use_internal_errors(\true);
		$dom       = new DOMDocument;
		$success   = $dom->loadXML($xml);
		self::removeInvalidAttributes($dom);
		\libxml_use_internal_errors($useErrors);
		return ($success) ? $dom : \false;
	}
	protected static function removeInvalidAttributes(DOMDocument $dom)
	{
		$xpath = new DOMXPath($dom);
		foreach ($xpath->query('//@*') as $attribute)
			if (!\preg_match('(^(?:[-\\w]+:)?(?!\\d)[-\\w]+$)D', $attribute->nodeName))
				$attribute->parentNode->removeAttributeNode($attribute);
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\Helpers;
use DOMElement;
use DOMXPath;
class TemplateInspector
{
	const XMLNS_XSL = 'http://www.w3.org/1999/XSL/Transform';
	protected $allowChildBitfields = [];
	protected $allowsChildElements;
	protected $allowsText;
	protected $branches;
	protected $contentBitfield = "\0";
	protected $defaultBranchBitfield;
	protected $denyDescendantBitfield = "\0";
	protected $dom;
	protected $hasElements = \false;
	protected $hasRootText;
	protected $isBlock = \false;
	protected $isEmpty;
	protected $isFormattingElement;
	protected $isPassthrough = \false;
	protected $isTransparent = \false;
	protected $isVoid;
	protected $leafNodes = [];
	protected $preservesNewLines = \false;
	protected $rootBitfields = [];
	protected $rootNodes = [];
	protected $xpath;
	public function __construct($template)
	{
		$this->dom   = TemplateHelper::loadTemplate($template);
		$this->xpath = new DOMXPath($this->dom);
		$this->defaultBranchBitfield = ElementInspector::getAllowChildBitfield($this->dom->createElement('div'));
		$this->analyseRootNodes();
		$this->analyseBranches();
		$this->analyseContent();
	}
	public function allowsChild(TemplateInspector $child)
	{
		if (!$this->allowsDescendant($child))
			return \false;
		foreach ($child->rootBitfields as $rootBitfield)
			foreach ($this->allowChildBitfields as $allowChildBitfield)
				if (!self::match($rootBitfield, $allowChildBitfield))
					return \false;
		return ($this->allowsText || !$child->hasRootText);
	}
	public function allowsDescendant(TemplateInspector $descendant)
	{
		if (self::match($descendant->contentBitfield, $this->denyDescendantBitfield))
			return \false;
		return ($this->allowsChildElements || !$descendant->hasElements);
	}
	public function allowsChildElements()
	{
		return $this->allowsChildElements;
	}
	public function allowsText()
	{
		return $this->allowsText;
	}
	public function closesParent(TemplateInspector $parent)
	{
		foreach ($this->rootNodes as $rootNode)
			foreach ($parent->leafNodes as $leafNode)
				if (ElementInspector::closesParent($leafNode, $rootNode))
					return \true;
		return \false;
	}
	public function evaluate($expr, DOMElement $node = \null)
	{
		return $this->xpath->evaluate($expr, $node);
	}
	public function isBlock()
	{
		return $this->isBlock;
	}
	public function isFormattingElement()
	{
		return $this->isFormattingElement;
	}
	public function isEmpty()
	{
		return $this->isEmpty;
	}
	public function isPassthrough()
	{
		return $this->isPassthrough;
	}
	public function isTransparent()
	{
		return $this->isTransparent;
	}
	public function isVoid()
	{
		return $this->isVoid;
	}
	public function preservesNewLines()
	{
		return $this->preservesNewLines;
	}
	protected function analyseContent()
	{
		$query = '//*[namespace-uri() != "' . self::XMLNS_XSL . '"]';
		foreach ($this->xpath->query($query) as $node)
		{
			$this->contentBitfield |= ElementInspector::getCategoryBitfield($node);
			$this->hasElements = \true;
		}
		$this->isPassthrough = (bool) $this->evaluate('count(//xsl:apply-templates)');
	}
	protected function analyseRootNodes()
	{
		$query = '//*[namespace-uri() != "' . self::XMLNS_XSL . '"][not(ancestor::*[namespace-uri() != "' . self::XMLNS_XSL . '"])]';
		foreach ($this->xpath->query($query) as $node)
		{
			$this->rootNodes[] = $node;
			if ($this->elementIsBlock($node))
				$this->isBlock = \true;
			$this->rootBitfields[] = ElementInspector::getCategoryBitfield($node);
		}
		$predicate = '[not(ancestor::*[namespace-uri() != "' . self::XMLNS_XSL . '"])]';
		$predicate .= '[not(ancestor::xsl:attribute | ancestor::xsl:comment | ancestor::xsl:variable)]';
		$query = '//text()[normalize-space() != ""]' . $predicate
		       . '|//xsl:text[normalize-space() != ""]' . $predicate
		       . '|//xsl:value-of' . $predicate;
		$this->hasRootText = (bool) $this->evaluate('count(' . $query . ')');
	}
	protected function analyseBranches()
	{
		$this->branches = [];
		foreach ($this->xpath->query('//xsl:apply-templates') as $applyTemplates)
		{
			$query            = 'ancestor::*[namespace-uri() != "' . self::XMLNS_XSL . '"]';
			$this->branches[] = \iterator_to_array($this->xpath->query($query, $applyTemplates));
		}
		$this->computeAllowsChildElements();
		$this->computeAllowsText();
		$this->computeBitfields();
		$this->computeFormattingElement();
		$this->computeIsEmpty();
		$this->computeIsTransparent();
		$this->computeIsVoid();
		$this->computePreservesNewLines();
		$this->storeLeafNodes();
	}
	protected function anyBranchHasProperty($methodName)
	{
		foreach ($this->branches as $branch)
			foreach ($branch as $element)
				if (ElementInspector::$methodName($element))
					return \true;
		return \false;
	}
	protected function computeBitfields()
	{
		if (empty($this->branches))
		{
			$this->allowChildBitfields = ["\0"];
			return;
		}
		foreach ($this->branches as $branch)
		{
			$branchBitfield = $this->defaultBranchBitfield;
			foreach ($branch as $element)
			{
				if (!ElementInspector::isTransparent($element))
					$branchBitfield = "\0";
				$branchBitfield |= ElementInspector::getAllowChildBitfield($element);
				$this->denyDescendantBitfield |= ElementInspector::getDenyDescendantBitfield($element);
			}
			$this->allowChildBitfields[] = $branchBitfield;
		}
	}
	protected function computeAllowsChildElements()
	{
		$this->allowsChildElements = ($this->anyBranchHasProperty('isTextOnly')) ? \false : !empty($this->branches);
	}
	protected function computeAllowsText()
	{
		foreach (\array_filter($this->branches) as $branch)
			if (ElementInspector::disallowsText(\end($branch)))
			{
				$this->allowsText = \false;
				return;
			}
		$this->allowsText = \true;
	}
	protected function computeFormattingElement()
	{
		foreach ($this->branches as $branch)
			foreach ($branch as $element)
				if (!ElementInspector::isFormattingElement($element) && !$this->isFormattingSpan($element))
				{
					$this->isFormattingElement = \false;
					return;
				}
		$this->isFormattingElement = (bool) \count(\array_filter($this->branches));
	}
	protected function computeIsEmpty()
	{
		$this->isEmpty = ($this->anyBranchHasProperty('isEmpty')) || empty($this->branches);
	}
	protected function computeIsTransparent()
	{
		foreach ($this->branches as $branch)
			foreach ($branch as $element)
				if (!ElementInspector::isTransparent($element))
				{
					$this->isTransparent = \false;
					return;
				}
		$this->isTransparent = !empty($this->branches);
	}
	protected function computeIsVoid()
	{
		$this->isVoid = ($this->anyBranchHasProperty('isVoid')) || empty($this->branches);
	}
	protected function computePreservesNewLines()
	{
		foreach ($this->branches as $branch)
		{
			$style = '';
			foreach ($branch as $element)
				$style .= $this->getStyle($element, \true);
			if (\preg_match('(.*white-space\\s*:\\s*(no|pre))is', $style, $m) && \strtolower($m[1]) === 'pre')
			{
				$this->preservesNewLines = \true;
				return;
			}
		}
		$this->preservesNewLines = \false;
	}
	protected function elementIsBlock(DOMElement $element)
	{
		$style = $this->getStyle($element);
		if (\preg_match('(\\bdisplay\\s*:\\s*block)i', $style))
			return \true;
		if (\preg_match('(\\bdisplay\\s*:\\s*(?:inli|no)ne)i', $style))
			return \false;
		return ElementInspector::isBlock($element);
	}
	protected function getStyle(DOMElement $node, $deep = \false)
	{
		$style = '';
		if (ElementInspector::preservesWhitespace($node))
			$style .= 'white-space:pre;';
		$style .= $node->getAttribute('style');
		$query = (($deep) ? './/' : './') . 'xsl:attribute[@name="style"]';
		foreach ($this->xpath->query($query, $node) as $attribute)
			$style .= ';' . $attribute->textContent;
		return $style;
	}
	protected function isFormattingSpan(DOMElement $node)
	{
		if ($node->nodeName !== 'span')
			return \false;
		if ($node->getAttribute('class') === '' && $node->getAttribute('style') === '')
			return \false;
		foreach ($node->attributes as $attrName => $attribute)
			if ($attrName !== 'class' && $attrName !== 'style')
				return \false;
		return \true;
	}
	protected function storeLeafNodes()
	{
		foreach (\array_filter($this->branches) as $branch)
			$this->leafNodes[] = \end($branch);
	}
	protected static function match($bitfield1, $bitfield2)
	{
		return (\trim($bitfield1 & $bitfield2, "\0") !== '');
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\Helpers;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use RuntimeException;
class TemplateParser
{
	const XMLNS_XSL = 'http://www.w3.org/1999/XSL/Transform';
	public static $voidRegexp = '/^(?:area|base|br|col|command|embed|hr|img|input|keygen|link|meta|param|source|track|wbr)$/Di';
	public static function parse($template)
	{
		$xsl = '<xsl:template xmlns:xsl="' . self::XMLNS_XSL . '">' . $template . '</xsl:template>';
		$dom = new DOMDocument;
		$dom->loadXML($xsl);
		$ir = new DOMDocument;
		$ir->loadXML('<template/>');
		self::parseChildren($ir->documentElement, $dom->documentElement);
		self::normalize($ir);
		return $ir;
	}
	public static function parseEqualityExpr($expr)
	{
		$eq = '(?<equality>(?<key>@[-\\w]+|\\$\\w+|\\.)(?<operator>\\s*=\\s*)(?:(?<literal>(?<string>"[^"]*"|\'[^\']*\')|0|[1-9][0-9]*)|(?<concat>concat\\(\\s*(?&string)\\s*(?:,\\s*(?&string)\\s*)+\\)))|(?:(?<literal>(?&literal))|(?<concat>(?&concat)))(?&operator)(?<key>(?&key)))';
		$regexp = '(^(?J)\\s*' . $eq . '\\s*(?:or\\s*(?&equality)\\s*)*$)';
		if (!\preg_match($regexp, $expr))
			return \false;
		\preg_match_all("((?J)$eq)", $expr, $matches, \PREG_SET_ORDER);
		$map = [];
		foreach ($matches as $m)
		{
			$key = $m['key'];
			if (!empty($m['concat']))
			{
				\preg_match_all('(\'[^\']*\'|"[^"]*")', $m['concat'], $strings);
				$value = '';
				foreach ($strings[0] as $string)
					$value .= \substr($string, 1, -1);
			}
			else
			{
				$value = $m['literal'];
				if ($value[0] === "'" || $value[0] === '"')
					$value = \substr($value, 1, -1);
			}
			$map[$key][] = $value;
		}
		return $map;
	}
	protected static function parseChildren(DOMElement $ir, DOMElement $parent)
	{
		foreach ($parent->childNodes as $child)
		{
			switch ($child->nodeType)
			{
				case \XML_COMMENT_NODE:
					break;
				case \XML_TEXT_NODE:
					if (\trim($child->textContent) !== '')
						self::appendOutput($ir, 'literal', $child->textContent);
					break;
				case \XML_ELEMENT_NODE:
					self::parseNode($ir, $child);
					break;
				default:
					throw new RuntimeException("Cannot parse node '" . $child->nodeName . "''");
			}
		}
	}
	protected static function parseNode(DOMElement $ir, DOMElement $node)
	{
		if ($node->namespaceURI === self::XMLNS_XSL)
		{
			$methodName = 'parseXsl' . \str_replace(' ', '', \ucwords(\str_replace('-', ' ', $node->localName)));
			if (!\method_exists(__CLASS__, $methodName))
				throw new RuntimeException("Element '" . $node->nodeName . "' is not supported");
			return self::$methodName($ir, $node);
		}
		$element = self::appendElement($ir, 'element');
		$element->setAttribute('name', $node->nodeName);
		$xpath = new DOMXPath($node->ownerDocument);
		foreach ($xpath->query('namespace::*', $node) as $ns)
			if ($node->hasAttribute($ns->nodeName))
			{
				$irAttribute = self::appendElement($element, 'attribute');
				$irAttribute->setAttribute('name', $ns->nodeName);
				self::appendOutput($irAttribute, 'literal', $ns->nodeValue);
			}
		foreach ($node->attributes as $attribute)
		{
			$irAttribute = self::appendElement($element, 'attribute');
			$irAttribute->setAttribute('name', $attribute->nodeName);
			self::appendOutput($irAttribute, 'avt', $attribute->value);
		}
		self::parseChildren($element, $node);
	}
	protected static function parseXslApplyTemplates(DOMElement $ir, DOMElement $node)
	{
		$applyTemplates = self::appendElement($ir, 'applyTemplates');
		if ($node->hasAttribute('select'))
			$applyTemplates->setAttribute(
				'select',
				$node->getAttribute('select')
			);
	}
	protected static function parseXslAttribute(DOMElement $ir, DOMElement $node)
	{
		$attrName = $node->getAttribute('name');
		if ($attrName !== '')
		{
			$attribute = self::appendElement($ir, 'attribute');
			$attribute->setAttribute('name', $attrName);
			self::parseChildren($attribute, $node);
		}
	}
	protected static function parseXslChoose(DOMElement $ir, DOMElement $node)
	{
		$switch = self::appendElement($ir, 'switch');
		foreach ($node->getElementsByTagNameNS(self::XMLNS_XSL, 'when') as $when)
		{
			if ($when->parentNode !== $node)
				continue;
			$case = self::appendElement($switch, 'case');
			$case->setAttribute('test', $when->getAttribute('test'));
			self::parseChildren($case, $when);
		}
		foreach ($node->getElementsByTagNameNS(self::XMLNS_XSL, 'otherwise') as $otherwise)
		{
			if ($otherwise->parentNode !== $node)
				continue;
			$case = self::appendElement($switch, 'case');
			self::parseChildren($case, $otherwise);
			break;
		}
	}
	protected static function parseXslComment(DOMElement $ir, DOMElement $node)
	{
		$comment = self::appendElement($ir, 'comment');
		self::parseChildren($comment, $node);
	}
	protected static function parseXslCopyOf(DOMElement $ir, DOMElement $node)
	{
		$expr = $node->getAttribute('select');
		if (\preg_match('#^@([-\\w]+)$#', $expr, $m))
		{
			$switch = self::appendElement($ir, 'switch');
			$case   = self::appendElement($switch, 'case');
			$case->setAttribute('test', $expr);
			$attribute = self::appendElement($case, 'attribute');
			$attribute->setAttribute('name', $m[1]);
			self::appendOutput($attribute, 'xpath', $expr);
			return;
		}
		if ($expr === '@*')
		{
			self::appendElement($ir, 'copyOfAttributes');
			return;
		}
		throw new RuntimeException("Unsupported <xsl:copy-of/> expression '" . $expr . "'");
	}
	protected static function parseXslElement(DOMElement $ir, DOMElement $node)
	{
		$elName = $node->getAttribute('name');
		if ($elName !== '')
		{
			$element = self::appendElement($ir, 'element');
			$element->setAttribute('name', $elName);
			self::parseChildren($element, $node);
		}
	}
	protected static function parseXslIf(DOMElement $ir, DOMElement $node)
	{
		$switch = self::appendElement($ir, 'switch');
		$case   = self::appendElement($switch, 'case');
		$case->setAttribute('test', $node->getAttribute('test'));
		self::parseChildren($case, $node);
	}
	protected static function parseXslText(DOMElement $ir, DOMElement $node)
	{
		self::appendOutput($ir, 'literal', $node->textContent);
	}
	protected static function parseXslValueOf(DOMElement $ir, DOMElement $node)
	{
		self::appendOutput($ir, 'xpath', $node->getAttribute('select'));
	}
	protected static function normalize(DOMDocument $ir)
	{
		self::addDefaultCase($ir);
		self::addElementIds($ir);
		self::addCloseTagElements($ir);
		self::markEmptyElements($ir);
		self::optimize($ir);
		self::markConditionalCloseTagElements($ir);
		self::setOutputContext($ir);
		self::markBranchTables($ir);
	}
	protected static function addDefaultCase(DOMDocument $ir)
	{
		$xpath = new DOMXPath($ir);
		foreach ($xpath->query('//switch[not(case[not(@test)])]') as $switch)
			self::appendElement($switch, 'case');
	}
	protected static function addElementIds(DOMDocument $ir)
	{
		$id = 0;
		foreach ($ir->getElementsByTagName('element') as $element)
			$element->setAttribute('id', ++$id);
	}
	protected static function addCloseTagElements(DOMDocument $ir)
	{
		$xpath = new DOMXPath($ir);
		$exprs = [
			'//applyTemplates[not(ancestor::attribute)]',
			'//comment',
			'//element',
			'//output[not(ancestor::attribute)]'
		];
		foreach ($xpath->query(\implode('|', $exprs)) as $node)
		{
			$parentElementId = self::getParentElementId($node);
			if (isset($parentElementId))
				$node->parentNode
				     ->insertBefore($ir->createElement('closeTag'), $node)
				     ->setAttribute('id', $parentElementId);
			if ($node->nodeName === 'element')
			{
				$id = $node->getAttribute('id');
				self::appendElement($node, 'closeTag')->setAttribute('id', $id);
			}
		}
	}
	protected static function markConditionalCloseTagElements(DOMDocument $ir)
	{
		$xpath = new DOMXPath($ir);
		foreach ($ir->getElementsByTagName('closeTag') as $closeTag)
		{
			$id = $closeTag->getAttribute('id');
			$query = 'ancestor::switch/following-sibling::*/descendant-or-self::closeTag[@id = "' . $id . '"]';
			foreach ($xpath->query($query, $closeTag) as $following)
			{
				$following->setAttribute('check', '');
				$closeTag->setAttribute('set', '');
			}
		}
	}
	protected static function markEmptyElements(DOMDocument $ir)
	{
		foreach ($ir->getElementsByTagName('element') as $element)
		{
			$elName = $element->getAttribute('name');
			if (\strpos($elName, '{') !== \false)
				$element->setAttribute('void', 'maybe');
			elseif (\preg_match(self::$voidRegexp, $elName))
				$element->setAttribute('void', 'yes');
			$isEmpty = self::isEmpty($element);
			if ($isEmpty === 'yes' || $isEmpty === 'maybe')
				$element->setAttribute('empty', $isEmpty);
		}
	}
	protected static function getOutputContext(DOMNode $output)
	{
		$xpath = new DOMXPath($output->ownerDocument);
		if ($xpath->evaluate('boolean(ancestor::attribute)', $output))
			return 'attribute';
		if ($xpath->evaluate('boolean(ancestor::element[@name="script"])', $output))
			return 'raw';
		return 'text';
	}
	protected static function getParentElementId(DOMNode $node)
	{
		$parentNode = $node->parentNode;
		while (isset($parentNode))
		{
			if ($parentNode->nodeName === 'element')
				return $parentNode->getAttribute('id');
			$parentNode = $parentNode->parentNode;
		}
	}
	protected static function setOutputContext(DOMDocument $ir)
	{
		foreach ($ir->getElementsByTagName('output') as $output)
			$output->setAttribute('escape', self::getOutputContext($output));
	}
	protected static function optimize(DOMDocument $ir)
	{
		$xml = $ir->saveXML();
		$remainingLoops = 10;
		do
		{
			$old = $xml;
			self::optimizeCloseTagElements($ir);
			$xml = $ir->saveXML();
		}
		while (--$remainingLoops > 0 && $xml !== $old);
		self::removeCloseTagSiblings($ir);
		self::removeContentFromVoidElements($ir);
		self::mergeConsecutiveLiteralOutputElements($ir);
		self::removeEmptyDefaultCases($ir);
	}
	protected static function removeCloseTagSiblings(DOMDocument $ir)
	{
		$xpath = new DOMXPath($ir);
		$query = '//switch[not(case[not(closeTag)])]/following-sibling::closeTag';
		foreach ($xpath->query($query) as $closeTag)
			$closeTag->parentNode->removeChild($closeTag);
	}
	protected static function removeEmptyDefaultCases(DOMDocument $ir)
	{
		$xpath = new DOMXPath($ir);
		foreach ($xpath->query('//case[not(@test | node())]') as $case)
			$case->parentNode->removeChild($case);
	}
	protected static function mergeConsecutiveLiteralOutputElements(DOMDocument $ir)
	{
		$xpath = new DOMXPath($ir);
		foreach ($xpath->query('//output[@type="literal"]') as $output)
			while ($output->nextSibling
				&& $output->nextSibling->nodeName === 'output'
				&& $output->nextSibling->getAttribute('type') === 'literal')
			{
				$output->nodeValue
					= \htmlspecialchars($output->nodeValue . $output->nextSibling->nodeValue);
				$output->parentNode->removeChild($output->nextSibling);
			}
	}
	protected static function optimizeCloseTagElements(DOMDocument $ir)
	{
		self::cloneCloseTagElementsIntoSwitch($ir);
		self::cloneCloseTagElementsOutOfSwitch($ir);
		self::removeRedundantCloseTagElementsInSwitch($ir);
		self::removeRedundantCloseTagElements($ir);
	}
	protected static function cloneCloseTagElementsIntoSwitch(DOMDocument $ir)
	{
		$xpath = new DOMXPath($ir);
		$query = '//switch[name(following-sibling::*) = "closeTag"]';
		foreach ($xpath->query($query) as $switch)
		{
			$closeTag = $switch->nextSibling;
			foreach ($switch->childNodes as $case)
				if (!$case->lastChild || $case->lastChild->nodeName !== 'closeTag')
					$case->appendChild($closeTag->cloneNode());
		}
	}
	protected static function cloneCloseTagElementsOutOfSwitch(DOMDocument $ir)
	{
		$xpath = new DOMXPath($ir);
		$query = '//switch[not(preceding-sibling::closeTag)]';
		foreach ($xpath->query($query) as $switch)
		{
			foreach ($switch->childNodes as $case)
				if (!$case->firstChild || $case->firstChild->nodeName !== 'closeTag')
					continue 2;
			$switch->parentNode->insertBefore($switch->lastChild->firstChild->cloneNode(), $switch);
		}
	}
	protected static function removeRedundantCloseTagElementsInSwitch(DOMDocument $ir)
	{
		$xpath = new DOMXPath($ir);
		$query = '//switch[name(following-sibling::*) = "closeTag"]';
		foreach ($xpath->query($query) as $switch)
			foreach ($switch->childNodes as $case)
				while ($case->lastChild && $case->lastChild->nodeName === 'closeTag')
					$case->removeChild($case->lastChild);
	}
	protected static function removeRedundantCloseTagElements(DOMDocument $ir)
	{
		$xpath = new DOMXPath($ir);
		foreach ($xpath->query('//closeTag') as $closeTag)
		{
			$id    = $closeTag->getAttribute('id');
			$query = 'following-sibling::*/descendant-or-self::closeTag[@id="' . $id . '"]';
			foreach ($xpath->query($query, $closeTag) as $dupe)
				$dupe->parentNode->removeChild($dupe);
		}
	}
	protected static function removeContentFromVoidElements(DOMDocument $ir)
	{
		$xpath = new DOMXPath($ir);
		foreach ($xpath->query('//element[@void="yes"]') as $element)
		{
			$id    = $element->getAttribute('id');
			$query = './/closeTag[@id="' . $id . '"]/following-sibling::*';
			foreach ($xpath->query($query, $element) as $node)
				$node->parentNode->removeChild($node);
		}
	}
	protected static function markBranchTables(DOMDocument $ir)
	{
		$xpath = new DOMXPath($ir);
		foreach ($xpath->query('//switch[case[2][@test]]') as $switch)
		{
			$key = \null;
			$branchValues = [];
			foreach ($switch->childNodes as $i => $case)
			{
				if (!$case->hasAttribute('test'))
					continue;
				$map = self::parseEqualityExpr($case->getAttribute('test'));
				if ($map === \false)
					continue 2;
				if (\count($map) !== 1)
					continue 2;
				if (isset($key) && $key !== \key($map))
					continue 2;
				$key = \key($map);
				$branchValues[$i] = \end($map);
			}
			$switch->setAttribute('branch-key', $key);
			foreach ($branchValues as $i => $values)
			{
				\sort($values);
				$switch->childNodes->item($i)->setAttribute('branch-values', \serialize($values));
			}
		}
	}
	protected static function appendElement(DOMElement $parentNode, $name, $value = '')
	{
		if ($value === '')
			$element = $parentNode->ownerDocument->createElement($name);
		else
			$element = $parentNode->ownerDocument->createElement($name, $value);
		$parentNode->appendChild($element);
		return $element;
	}
	protected static function appendOutput(DOMElement $ir, $type, $content)
	{
		if ($type === 'avt')
		{
			foreach (AVTHelper::parse($content) as $token)
			{
				$type = ($token[0] === 'expression') ? 'xpath' : 'literal';
				self::appendOutput($ir, $type, $token[1]);
			}
			return;
		}
		if ($type === 'xpath')
			$content = \trim($content);
		if ($type === 'literal' && $content === '')
			return;
		self::appendElement($ir, 'output', \htmlspecialchars($content))
			->setAttribute('type', $type);
	}
	protected static function isEmpty(DOMElement $ir)
	{
		$xpath = new DOMXPath($ir->ownerDocument);
		if ($xpath->evaluate('count(comment | element | output[@type="literal"])', $ir))
			return 'no';
		$cases = [];
		foreach ($xpath->query('switch/case', $ir) as $case)
			$cases[self::isEmpty($case)] = 1;
		if (isset($cases['maybe']))
			return 'maybe';
		if (isset($cases['no']))
		{
			if (!isset($cases['yes']))
				return 'no';
			return 'maybe';
		}
		if ($xpath->evaluate('count(applyTemplates | output[@type="xpath"])', $ir))
			return 'maybe';
		return 'yes';
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\Helpers;
use RuntimeException;
use s9e\TextFormatter\Utils\XPath;
abstract class XPathHelper
{
	public static function getVariables($expr)
	{
		$expr = \preg_replace('/(["\']).*?\\1/s', '$1$1', $expr);
		\preg_match_all('/\\$(\\w+)/', $expr, $matches);
		$varNames = \array_unique($matches[1]);
		\sort($varNames);
		return $varNames;
	}
	public static function isExpressionNumeric($expr)
	{
		$expr = \strrev(\preg_replace('(\\((?!\\s*(?!vid(?!\\w))\\w))', ' ', \strrev($expr)));
		$expr = \str_replace(')', ' ', $expr);
		if (\preg_match('(^\\s*([$@][-\\w]++|-?\\.\\d++|-?\\d++(?:\\.\\d++)?)(?>\\s*(?>[-+*]|div)\\s*(?1))++\\s*$)', $expr))
			return \true;
		return \false;
	}
	public static function minify($expr)
	{
		$old     = $expr;
		$strings = [];
		$expr = \preg_replace_callback(
			'/"[^"]*"|\'[^\']*\'/',
			function ($m) use (&$strings)
			{
				$uniqid = '(' . \sha1(\uniqid()) . ')';
				$strings[$uniqid] = $m[0];
				return $uniqid;
			},
			\trim($expr)
		);
		if (\preg_match('/[\'"]/', $expr))
			throw new RuntimeException("Cannot parse XPath expression '" . $old . "'");
		$expr = \preg_replace('/\\s+/', ' ', $expr);
		$expr = \preg_replace('/([-a-z_0-9]) ([^-a-z_0-9])/i', '$1$2', $expr);
		$expr = \preg_replace('/([^-a-z_0-9]) ([-a-z_0-9])/i', '$1$2', $expr);
		$expr = \preg_replace('/(?!- -)([^-a-z_0-9]) ([^-a-z_0-9])/i', '$1$2', $expr);
		$expr = \preg_replace('/ - ([a-z_0-9])/i', ' -$1', $expr);
		$expr = \preg_replace('/((?:^|[ \\(])\\d+) div ?/', '$1div', $expr);
		$expr = \preg_replace('/([^-a-z_0-9]div) (?=[$0-9@])/', '$1', $expr);
		$expr = \strtr($expr, $strings);
		return $expr;
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\Items;
use DOMDocument;
use s9e\TextFormatter\Configurator\Helpers\TemplateInspector;
use s9e\TextFormatter\Configurator\Helpers\TemplateHelper;
use s9e\TextFormatter\Configurator\TemplateNormalizer;
class Template
{
	protected $inspector;
	protected $isNormalized = \false;
	protected $template;
	public function __construct($template)
	{
		$this->template = $template;
	}
	public function __call($methodName, $args)
	{
		return \call_user_func_array([$this->getInspector(), $methodName], $args);
	}
	public function __toString()
	{
		return $this->template;
	}
	public function asDOM()
	{
		$xml = '<xsl:template xmlns:xsl="http://www.w3.org/1999/XSL/Transform">'
		     . $this->__toString()
		     . '</xsl:template>';
		$dom = new TemplateDocument($this);
		$dom->loadXML($xml);
		return $dom;
	}
	public function getCSSNodes()
	{
		return TemplateHelper::getCSSNodes($this->asDOM());
	}
	public function getInspector()
	{
		if (!isset($this->inspector))
			$this->inspector = new TemplateInspector($this->__toString());
		return $this->inspector;
	}
	public function getJSNodes()
	{
		return TemplateHelper::getJSNodes($this->asDOM());
	}
	public function getURLNodes()
	{
		return TemplateHelper::getURLNodes($this->asDOM());
	}
	public function getParameters()
	{
		return TemplateHelper::getParametersFromXSL($this->__toString());
	}
	public function isNormalized($bool = \null)
	{
		if (isset($bool))
			$this->isNormalized = $bool;
		return $this->isNormalized;
	}
	public function normalize(TemplateNormalizer $templateNormalizer)
	{
		$this->inspector    = \null;
		$this->template     = $templateNormalizer->normalizeTemplate($this->template);
		$this->isNormalized = \true;
	}
	public function replaceTokens($regexp, $fn)
	{
		$this->inspector    = \null;
		$this->template     = TemplateHelper::replaceTokens($this->template, $regexp, $fn);
		$this->isNormalized = \false;
	}
	public function setContent($template)
	{
		$this->inspector    = \null;
		$this->template     = (string) $template;
		$this->isNormalized = \false;
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\JavaScript;
use InvalidArgumentException;
class FunctionProvider
{
	public static $cache = [
		'addslashes'=>'function(str)
{
	return str.replace(/["\'\\\\]/g, \'\\\\$&\').replace(/\\u0000/g, \'\\\\0\');
}',
		'dechex'=>'function(str)
{
	return parseInt(str).toString(16);
}',
		'intval'=>'function(str)
{
	return parseInt(str) || 0;
}',
		'ltrim'=>'function(str)
{
	return str.replace(/^[ \\n\\r\\t\\0\\x0B]+/g, \'\');
}',
		'mb_strtolower'=>'function(str)
{
	return str.toLowerCase();
}',
		'mb_strtoupper'=>'function(str)
{
	return str.toUpperCase();
}',
		'mt_rand'=>'function(min, max)
{
	return (min + Math.floor(Math.random() * (max + 1 - min)));
}',
		'rawurlencode'=>'function(str)
{
	return encodeURIComponent(str).replace(
		/[!\'()*]/g,
		/**
		* @param {!string} c
		*/
		function(c)
		{
			return \'%\' + c.charCodeAt(0).toString(16).toUpperCase();
		}
	);
}',
		'rtrim'=>'function(str)
{
	return str.replace(/[ \\n\\r\\t\\0\\x0B]+$/g, \'\');
}',
		'str_rot13'=>'function(str)
{
	return str.replace(
		/[a-z]/gi,
		function(c)
		{
			return String.fromCharCode(c.charCodeAt(0) + ((c.toLowerCase() < \'n\') ? 13 : -13));
		}
	);
}',
		'stripslashes'=>'function(str)
{
	// NOTE: this will not correctly transform \\0 into a NULL byte. I consider this a feature
	//       rather than a bug. There\'s no reason to use NULL bytes in a text.
	return str.replace(/\\\\([\\s\\S]?)/g, \'\\\\1\');
}',
		'strrev'=>'function(str)
{
	return str.split(\'\').reverse().join(\'\');
}',
		'strtolower'=>'function(str)
{
	return str.toLowerCase();
}',
		'strtotime'=>'function(str)
{
	return Date.parse(str) / 1000;
}',
		'strtoupper'=>'function(str)
{
	return str.toUpperCase();
}',
		'trim'=>'function(str)
{
	return str.replace(/^[ \\n\\r\\t\\0\\x0B]+/g, \'\').replace(/[ \\n\\r\\t\\0\\x0B]+$/g, \'\');
}',
		'ucfirst'=>'function(str)
{
	return str[0].toUpperCase() + str.substr(1);
}',
		'ucwords'=>'function(str)
{
	return str.replace(
		/(?:^|\\s)[a-z]/g,
		function(m)
		{
			return m.toUpperCase()
		}
	);
}',
		'urldecode'=>'function(str)
{
	return decodeURIComponent(str);
}',
		'urlencode'=>'function(str)
{
	return encodeURIComponent(str);
}'
	];
	public static function get($funcName)
	{
		if (isset(self::$cache[$funcName]))
			return self::$cache[$funcName];
		if (\preg_match('(^[a-z_0-9]+$)D', $funcName))
		{
			$filepath = __DIR__ . '/Configurator/JavaScript/functions/' . $funcName . '.js';
			if (\file_exists($filepath))
				return \file_get_contents($filepath);
		}
		throw new InvalidArgumentException("Unknown function '" . $funcName . "'");
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator;
interface RendererGenerator
{
	public function getRenderer(Rendering $rendering);
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\RendererGenerators\PHP;
abstract class AbstractOptimizer
{
	protected $cnt;
	protected $i;
	protected $changed;
	protected $tokens;
	public function optimize($php)
	{
		$this->reset($php);
		$this->optimizeTokens();
		if ($this->changed)
			$php = $this->serialize();
		unset($this->tokens);
		return $php;
	}
	abstract protected function optimizeTokens();
	protected function reset($php)
	{
		$this->tokens  = \token_get_all('<?php ' . $php);
		$this->i       = 0;
		$this->cnt     = \count($this->tokens);
		$this->changed = \false;
	}
	protected function serialize()
	{
		unset($this->tokens[0]);
		$php = '';
		foreach ($this->tokens as $token)
			$php .= (\is_string($token)) ? $token : $token[1];
		return $php;
	}
	protected function skipToString($str)
	{
		while (++$this->i < $this->cnt && $this->tokens[$this->i] !== $str);
	}
	protected function skipWhitespace()
	{
		while (++$this->i < $this->cnt && $this->tokens[$this->i][0] === \T_WHITESPACE);
	}
	protected function unindentBlock($start, $end)
	{
		$this->i = $start;
		do
		{
			if ($this->tokens[$this->i][0] === \T_WHITESPACE || $this->tokens[$this->i][0] === \T_DOC_COMMENT)
				$this->tokens[$this->i][1] = \preg_replace("/^\t/m", '', $this->tokens[$this->i][1]);
		}
		while (++$this->i <= $end);
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\RendererGenerators\PHP;
class BranchOutputOptimizer
{
	protected $cnt;
	protected $i;
	protected $tokens;
	public function optimize(array $tokens)
	{
		$this->tokens = $tokens;
		$this->i      = 0;
		$this->cnt    = \count($this->tokens);
		$php = '';
		while (++$this->i < $this->cnt)
			if ($this->tokens[$this->i][0] === \T_IF)
				$php .= $this->serializeIfBlock($this->parseIfBlock());
			else
				$php .= $this->serializeToken($this->tokens[$this->i]);
		unset($this->tokens);
		return $php;
	}
	protected function captureOutput()
	{
		$expressions = [];
		while ($this->skipOutputAssignment())
		{
			do
			{
				$expressions[] = $this->captureOutputExpression();
			}
			while ($this->tokens[$this->i++] === '.');
		}
		return $expressions;
	}
	protected function captureOutputExpression()
	{
		$parens = 0;
		$php = '';
		do
		{
			if ($this->tokens[$this->i] === ';')
				break;
			elseif ($this->tokens[$this->i] === '.' && !$parens)
				break;
			elseif ($this->tokens[$this->i] === '(')
				++$parens;
			elseif ($this->tokens[$this->i] === ')')
				--$parens;
			$php .= $this->serializeToken($this->tokens[$this->i]);
		}
		while (++$this->i < $this->cnt);
		return $php;
	}
	protected function captureStructure()
	{
		$php = '';
		do
		{
			$php .= $this->serializeToken($this->tokens[$this->i]);
		}
		while ($this->tokens[++$this->i] !== '{');
		++$this->i;
		return $php;
	}
	protected function isBranchToken()
	{
		return \in_array($this->tokens[$this->i][0], [\T_ELSE, \T_ELSEIF, \T_IF], \true);
	}
	protected function mergeIfBranches(array $branches)
	{
		$lastBranch = \end($branches);
		if ($lastBranch['structure'] === 'else')
		{
			$before = $this->optimizeBranchesHead($branches);
			$after  = $this->optimizeBranchesTail($branches);
		}
		else
			$before = $after = [];
		$source = '';
		foreach ($branches as $branch)
			$source .= $this->serializeBranch($branch);
		return [
			'before' => $before,
			'source' => $source,
			'after'  => $after
		];
	}
	protected function mergeOutput(array $left, array $right)
	{
		if (empty($left))
			return $right;
		if (empty($right))
			return $left;
		$k = \count($left) - 1;
		if (\substr($left[$k], -1) === "'" && $right[0][0] === "'")
		{
			$right[0] = \substr($left[$k], 0, -1) . \substr($right[0], 1);
			unset($left[$k]);
		}
		return \array_merge($left, $right);
	}
	protected function optimizeBranchesHead(array &$branches)
	{
		$before = $this->optimizeBranchesOutput($branches, 'head');
		foreach ($branches as &$branch)
		{
			if ($branch['body'] !== '' || !empty($branch['tail']))
				continue;
			$branch['tail'] = \array_reverse($branch['head']);
			$branch['head'] = [];
		}
		unset($branch);
		return $before;
	}
	protected function optimizeBranchesOutput(array &$branches, $which)
	{
		$expressions = [];
		while (isset($branches[0][$which][0]))
		{
			$expr = $branches[0][$which][0];
			foreach ($branches as $branch)
				if (!isset($branch[$which][0]) || $branch[$which][0] !== $expr)
					break 2;
			$expressions[] = $expr;
			foreach ($branches as &$branch)
				\array_shift($branch[$which]);
			unset($branch);
		}
		return $expressions;
	}
	protected function optimizeBranchesTail(array &$branches)
	{
		return $this->optimizeBranchesOutput($branches, 'tail');
	}
	protected function parseBranch()
	{
		$structure = $this->captureStructure();
		$head = $this->captureOutput();
		$body = '';
		$tail = [];
		$braces = 0;
		do
		{
			$tail = $this->mergeOutput($tail, \array_reverse($this->captureOutput()));
			if ($this->tokens[$this->i] === '}' && !$braces)
				break;
			$body .= $this->serializeOutput(\array_reverse($tail));
			$tail  = [];
			if ($this->tokens[$this->i][0] === \T_IF)
			{
				$child = $this->parseIfBlock();
				if ($body === '')
					$head = $this->mergeOutput($head, $child['before']);
				else
					$body .= $this->serializeOutput($child['before']);
				$body .= $child['source'];
				$tail  = $child['after'];
			}
			else
			{
				$body .= $this->serializeToken($this->tokens[$this->i]);
				if ($this->tokens[$this->i] === '{')
					++$braces;
				elseif ($this->tokens[$this->i] === '}')
					--$braces;
			}
		}
		while (++$this->i < $this->cnt);
		return [
			'structure' => $structure,
			'head'      => $head,
			'body'      => $body,
			'tail'      => $tail
		];
	}
	protected function parseIfBlock()
	{
		$branches = [];
		do
		{
			$branches[] = $this->parseBranch();
		}
		while (++$this->i < $this->cnt && $this->isBranchToken());
		--$this->i;
		return $this->mergeIfBranches($branches);
	}
	protected function serializeBranch(array $branch)
	{
		if ($branch['structure'] === 'else'
		 && $branch['body']      === ''
		 && empty($branch['head'])
		 && empty($branch['tail']))
			return '';
		return $branch['structure'] . '{' . $this->serializeOutput($branch['head']) . $branch['body'] . $this->serializeOutput(\array_reverse($branch['tail'])) . '}';
	}
	protected function serializeIfBlock(array $block)
	{
		return $this->serializeOutput($block['before']) . $block['source'] . $this->serializeOutput(\array_reverse($block['after']));
	}
	protected function serializeOutput(array $expressions)
	{
		if (empty($expressions))
			return '';
		return '$this->out.=' . \implode('.', $expressions) . ';';
	}
	protected function serializeToken($token)
	{
		return (\is_array($token)) ? $token[1] : $token;
	}
	protected function skipOutputAssignment()
	{
		if ($this->tokens[$this->i    ][0] !== \T_VARIABLE
		 || $this->tokens[$this->i    ][1] !== '$this'
		 || $this->tokens[$this->i + 1][0] !== \T_OBJECT_OPERATOR
		 || $this->tokens[$this->i + 2][0] !== \T_STRING
		 || $this->tokens[$this->i + 2][1] !== 'out'
		 || $this->tokens[$this->i + 3][0] !== \T_CONCAT_EQUAL)
			 return \false;
		$this->i += 4;
		return \true;
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\RendererGenerators\PHP;
class Optimizer
{
	public $branchOutputOptimizer;
	protected $cnt;
	protected $i;
	public $maxLoops = 10;
	protected $tokens;
	public function __construct()
	{
		$this->branchOutputOptimizer = new BranchOutputOptimizer;
	}
	public function optimize($php)
	{
		$this->tokens = \token_get_all('<?php ' . $php);
		$this->cnt    = \count($this->tokens);
		$this->i      = 0;
		foreach ($this->tokens as &$token)
			if (\is_array($token))
				unset($token[2]);
		unset($token);
		$passes = [
			'optimizeOutConcatEqual',
			'optimizeConcatenations',
			'optimizeHtmlspecialchars'
		];
		$remainingLoops = $this->maxLoops;
		do
		{
			$continue = \false;
			foreach ($passes as $pass)
			{
				$this->$pass();
				$cnt = \count($this->tokens);
				if ($this->cnt !== $cnt)
				{
					$this->tokens = \array_values($this->tokens);
					$this->cnt    = $cnt;
					$continue     = \true;
				}
			}
		}
		while ($continue && --$remainingLoops);
		$php = $this->branchOutputOptimizer->optimize($this->tokens);
		unset($this->tokens);
		return $php;
	}
	protected function isBetweenHtmlspecialcharCalls()
	{
		return ($this->tokens[$this->i + 1]    === [\T_STRING, 'htmlspecialchars']
		     && $this->tokens[$this->i + 2]    === '('
		     && $this->tokens[$this->i - 1]    === ')'
		     && $this->tokens[$this->i - 2][0] === \T_LNUMBER
		     && $this->tokens[$this->i - 3]    === ',');
	}
	protected function isHtmlspecialcharSafeVar()
	{
		return ($this->tokens[$this->i    ]    === [\T_VARIABLE,        '$node']
		     && $this->tokens[$this->i + 1]    === [\T_OBJECT_OPERATOR, '->']
		     && ($this->tokens[$this->i + 2]   === [\T_STRING,          'localName']
		      || $this->tokens[$this->i + 2]   === [\T_STRING,          'nodeName'])
		     && $this->tokens[$this->i + 3]    === ','
		     && $this->tokens[$this->i + 4][0] === \T_LNUMBER
		     && $this->tokens[$this->i + 5]    === ')');
	}
	protected function isOutputAssignment()
	{
		return ($this->tokens[$this->i    ] === [\T_VARIABLE,        '$this']
		     && $this->tokens[$this->i + 1] === [\T_OBJECT_OPERATOR, '->']
		     && $this->tokens[$this->i + 2] === [\T_STRING,          'out']
		     && $this->tokens[$this->i + 3] === [\T_CONCAT_EQUAL,    '.=']);
	}
	protected function isPrecededByOutputVar()
	{
		return ($this->tokens[$this->i - 1] === [\T_STRING,          'out']
		     && $this->tokens[$this->i - 2] === [\T_OBJECT_OPERATOR, '->']
		     && $this->tokens[$this->i - 3] === [\T_VARIABLE,        '$this']);
	}
	protected function mergeConcatenatedHtmlSpecialChars()
	{
		if (!$this->isBetweenHtmlspecialcharCalls())
			 return \false;
		$escapeMode = $this->tokens[$this->i - 2][1];
		$startIndex = $this->i - 3;
		$endIndex = $this->i + 2;
		$this->i = $endIndex;
		$parens = 0;
		while (++$this->i < $this->cnt)
		{
			if ($this->tokens[$this->i] === ',' && !$parens)
				break;
			if ($this->tokens[$this->i] === '(')
				++$parens;
			elseif ($this->tokens[$this->i] === ')')
				--$parens;
		}
		if ($this->tokens[$this->i + 1] !== [\T_LNUMBER, $escapeMode])
			return \false;
		$this->tokens[$startIndex] = '.';
		$this->i = $startIndex;
		while (++$this->i <= $endIndex)
			unset($this->tokens[$this->i]);
		return \true;
	}
	protected function mergeConcatenatedStrings()
	{
		if ($this->tokens[$this->i - 1][0]    !== \T_CONSTANT_ENCAPSED_STRING
		 || $this->tokens[$this->i + 1][0]    !== \T_CONSTANT_ENCAPSED_STRING
		 || $this->tokens[$this->i - 1][1][0] !== $this->tokens[$this->i + 1][1][0])
			return \false;
		$this->tokens[$this->i + 1][1] = \substr($this->tokens[$this->i - 1][1], 0, -1)
		                               . \substr($this->tokens[$this->i + 1][1], 1);
		unset($this->tokens[$this->i - 1]);
		unset($this->tokens[$this->i]);
		++$this->i;
		return \true;
	}
	protected function optimizeOutConcatEqual()
	{
		$this->i = 3;
		while ($this->skipTo([\T_CONCAT_EQUAL, '.=']))
		{
			if (!$this->isPrecededByOutputVar())
				 continue;
			while ($this->skipPast(';'))
			{
				if (!$this->isOutputAssignment())
					 break;
				$this->tokens[$this->i - 1] = '.';
				unset($this->tokens[$this->i++]);
				unset($this->tokens[$this->i++]);
				unset($this->tokens[$this->i++]);
				unset($this->tokens[$this->i++]);
			}
		}
	}
	protected function optimizeConcatenations()
	{
		$this->i = 1;
		while ($this->skipTo('.'))
			$this->mergeConcatenatedStrings() || $this->mergeConcatenatedHtmlSpecialChars();
	}
	protected function optimizeHtmlspecialchars()
	{
		$this->i = 0;
		while ($this->skipPast([\T_STRING, 'htmlspecialchars']))
			if ($this->tokens[$this->i] === '(')
			{
				++$this->i;
				$this->replaceHtmlspecialcharsLiteral() || $this->removeHtmlspecialcharsSafeVar();
			}
	}
	protected function removeHtmlspecialcharsSafeVar()
	{
		if (!$this->isHtmlspecialcharSafeVar())
			 return \false;
		unset($this->tokens[$this->i - 2]);
		unset($this->tokens[$this->i - 1]);
		unset($this->tokens[$this->i + 3]);
		unset($this->tokens[$this->i + 4]);
		unset($this->tokens[$this->i + 5]);
		$this->i += 6;
		return \true;
	}
	protected function replaceHtmlspecialcharsLiteral()
	{
		if ($this->tokens[$this->i    ][0] !== \T_CONSTANT_ENCAPSED_STRING
		 || $this->tokens[$this->i + 1]    !== ','
		 || $this->tokens[$this->i + 2][0] !== \T_LNUMBER
		 || $this->tokens[$this->i + 3]    !== ')')
			return \false;
		$this->tokens[$this->i][1] = \var_export(
			\htmlspecialchars(
				\stripslashes(\substr($this->tokens[$this->i][1], 1, -1)),
				$this->tokens[$this->i + 2][1]
			),
			\true
		);
		unset($this->tokens[$this->i - 2]);
		unset($this->tokens[$this->i - 1]);
		unset($this->tokens[++$this->i]);
		unset($this->tokens[++$this->i]);
		unset($this->tokens[++$this->i]);
		return \true;
	}
	protected function skipPast($token)
	{
		return ($this->skipTo($token) && ++$this->i < $this->cnt);
	}
	protected function skipTo($token)
	{
		while (++$this->i < $this->cnt)
			if ($this->tokens[$this->i] === $token)
				return \true;
		return \false;
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\RendererGenerators\PHP;
use Closure;
use RuntimeException;
use s9e\TextFormatter\Configurator\Helpers\RegexpBuilder;
class Quick
{
	public static function getSource(array $compiledTemplates)
	{
		$map         = ['dynamic' => [], 'php' => [], 'static' => []];
		$tagNames    = [];
		$unsupported = [];
		unset($compiledTemplates['br']);
		unset($compiledTemplates['e']);
		unset($compiledTemplates['i']);
		unset($compiledTemplates['p']);
		unset($compiledTemplates['s']);
		foreach ($compiledTemplates as $tagName => $php)
		{
			$rendering = self::getRenderingStrategy($php);
			if ($rendering === \false)
			{
				$unsupported[] = $tagName;
				continue;
			}
			foreach ($rendering as $i => $_562c18b7)
			{
				list($strategy, $replacement) = $_562c18b7;
				$match = (($i) ? '/' : '') . $tagName;
				$map[$strategy][$match] = $replacement;
			}
			if (!isset($rendering[1]))
				$tagNames[] = $tagName;
		}
		$php = [];
		$php[] = '	/** {@inheritdoc} */';
		$php[] = '	public $enableQuickRenderer=true;';
		$php[] = '	/** {@inheritdoc} */';
		$php[] = '	protected $static=' . self::export($map['static']) . ';';
		$php[] = '	/** {@inheritdoc} */';
		$php[] = '	protected $dynamic=' . self::export($map['dynamic']) . ';';
		$quickSource = '';
		if (!empty($map['php']))
			$quickSource = SwitchStatement::generate('$id', $map['php']);
		$regexp  = '(<(?:(?!/)(';
		$regexp .= ($tagNames) ? RegexpBuilder::fromList($tagNames) : '(?!)';
		$regexp .= ')(?: [^>]*)?>.*?</\\1|(/?(?!br/|p>)[^ />]+)[^>]*?(/)?)>)s';
		$php[] = '	/** {@inheritdoc} */';
		$php[] = '	protected $quickRegexp=' . \var_export($regexp, \true) . ';';
		if (!empty($unsupported))
		{
			$regexp = '(<(?:[!?]|' . RegexpBuilder::fromList($unsupported) . '[ />]))';
			$php[]  = '	/** {@inheritdoc} */';
			$php[]  = '	protected $quickRenderingTest=' . \var_export($regexp, \true) . ';';
		}
		$php[] = '	/** {@inheritdoc} */';
		$php[] = '	protected function renderQuickTemplate($id, $xml)';
		$php[] = '	{';
		$php[] = '		$attributes=$this->matchAttributes($xml);';
		$php[] = "		\$html='';" . $quickSource;
		$php[] = '';
		$php[] = '		return $html;';
		$php[] = '	}';
		return \implode("\n", $php);
	}
	protected static function export(array $arr)
	{
		$exportKeys = (\array_keys($arr) !== \range(0, \count($arr) - 1));
		\ksort($arr);
		$entries = [];
		foreach ($arr as $k => $v)
			$entries[] = (($exportKeys) ? \var_export($k, \true) . '=>' : '')
			           . ((\is_array($v)) ? self::export($v) : \var_export($v, \true));
		return '[' . \implode(',', $entries) . ']';
	}
	public static function getRenderingStrategy($php)
	{
		$chunks = \explode('$this->at($node);', $php);
		$renderings = [];
		if (\count($chunks) <= 2)
		{
			foreach ($chunks as $k => $chunk)
			{
				$rendering = self::getStaticRendering($chunk);
				if ($rendering !== \false)
				{
					$renderings[$k] = ['static', $rendering];
					continue;
				}
				if ($k === 0)
				{
					$rendering = self::getDynamicRendering($chunk);
					if ($rendering !== \false)
					{
						$renderings[$k] = ['dynamic', $rendering];
						continue;
					}
				}
				$renderings[$k] = \false;
			}
			if (!\in_array(\false, $renderings, \true))
				return $renderings;
		}
		$phpRenderings = self::getQuickRendering($php);
		if ($phpRenderings === \false)
			return \false;
		foreach ($phpRenderings as $i => $phpRendering)
			if (!isset($renderings[$i]) || $renderings[$i] === \false || \strpos($phpRendering, '$this->attributes[]') !== \false)
				$renderings[$i] = ['php', $phpRendering];
		return $renderings;
	}
	protected static function getQuickRendering($php)
	{
		if (\preg_match('(\\$this->at\\((?!\\$node\\);))', $php))
			return \false;
		$tokens   = \token_get_all('<?php ' . $php);
		$tokens[] = [0, ''];
		\array_shift($tokens);
		$cnt = \count($tokens);
		$branch = [
			'braces'      => -1,
			'branches'    => [],
			'head'        => '',
			'passthrough' => 0,
			'statement'   => '',
			'tail'        => ''
		];
		$braces = 0;
		$i = 0;
		do
		{
			if ($tokens[$i    ][0] === \T_VARIABLE
			 && $tokens[$i    ][1] === '$this'
			 && $tokens[$i + 1][0] === \T_OBJECT_OPERATOR
			 && $tokens[$i + 2][0] === \T_STRING
			 && $tokens[$i + 2][1] === 'at'
			 && $tokens[$i + 3]    === '('
			 && $tokens[$i + 4][0] === \T_VARIABLE
			 && $tokens[$i + 4][1] === '$node'
			 && $tokens[$i + 5]    === ')'
			 && $tokens[$i + 6]    === ';')
			{
				if (++$branch['passthrough'] > 1)
					return \false;
				$i += 6;
				continue;
			}
			$key = ($branch['passthrough']) ? 'tail' : 'head';
			$branch[$key] .= (\is_array($tokens[$i])) ? $tokens[$i][1] : $tokens[$i];
			if ($tokens[$i] === '{')
			{
				++$braces;
				continue;
			}
			if ($tokens[$i] === '}')
			{
				--$braces;
				if ($branch['braces'] === $braces)
				{
					$branch[$key] = \substr($branch[$key], 0, -1);
					$branch =& $branch['parent'];
					$j = $i;
					while ($tokens[++$j][0] === \T_WHITESPACE);
					if ($tokens[$j][0] !== \T_ELSEIF
					 && $tokens[$j][0] !== \T_ELSE)
					{
						$passthroughs = self::getBranchesPassthrough($branch['branches']);
						if ($passthroughs === [0])
						{
							foreach ($branch['branches'] as $child)
								$branch['head'] .= $child['statement'] . '{' . $child['head'] . '}';
							$branch['branches'] = [];
							continue;
						}
						if ($passthroughs === [1])
						{
							++$branch['passthrough'];
							continue;
						}
						return \false;
					}
				}
				continue;
			}
			if ($branch['passthrough'])
				continue;
			if ($tokens[$i][0] === \T_IF
			 || $tokens[$i][0] === \T_ELSEIF
			 || $tokens[$i][0] === \T_ELSE)
			{
				$branch[$key] = \substr($branch[$key], 0, -\strlen($tokens[$i][1]));
				$branch['branches'][] = [
					'braces'      => $braces,
					'branches'    => [],
					'head'        => '',
					'parent'      => &$branch,
					'passthrough' => 0,
					'statement'   => '',
					'tail'        => ''
				];
				$branch =& $branch['branches'][\count($branch['branches']) - 1];
				do
				{
					$branch['statement'] .= (\is_array($tokens[$i])) ? $tokens[$i][1] : $tokens[$i];
				}
				while ($tokens[++$i] !== '{');
				++$braces;
			}
		}
		while (++$i < $cnt);
		list($head, $tail) = self::buildPHP($branch['branches']);
		$head  = $branch['head'] . $head;
		$tail .= $branch['tail'];
		self::convertPHP($head, $tail, (bool) $branch['passthrough']);
		if (\preg_match('((?<!-|\\$this)->)', $head . $tail))
			return \false;
		return ($branch['passthrough']) ? [$head, $tail] : [$head];
	}
	protected static function convertPHP(&$head, &$tail, $passthrough)
	{
		$saveAttributes = (bool) \preg_match('(\\$node->(?:get|has)Attribute)', $tail);
		\preg_match_all(
			"(\\\$node->getAttribute\\('([^']+)'\\))",
			\preg_replace_callback(
				'(if\\(\\$node->hasAttribute\\(([^\\)]+)[^}]+)',
				function ($m)
				{
					return \str_replace('$node->getAttribute(' . $m[1] . ')', '', $m[0]);
				},
				$head . $tail
			),
			$matches
		);
		$attrNames = \array_unique($matches[1]);
		self::replacePHP($head);
		self::replacePHP($tail);
		if (!$passthrough && \strpos($head, '$node->textContent') !== \false)
			$head = '$textContent=$this->getQuickTextContent($xml);' . \str_replace('$node->textContent', '$textContent', $head);
		if (!empty($attrNames))
		{
			\ksort($attrNames);
			$head = "\$attributes+=['" . \implode("'=>null,'", $attrNames) . "'=>null];" . $head;
		}
		if ($saveAttributes)
		{
			$head .= '$this->attributes[]=$attributes;';
			$tail  = '$attributes=array_pop($this->attributes);' . $tail;
		}
	}
	protected static function replacePHP(&$php)
	{
		$getAttribute = "\\\$node->getAttribute\\(('[^']+')\\)";
		$replacements = [
			'$this->out' => '$html',
			'(htmlspecialchars\\(' . $getAttribute . ',' . \ENT_NOQUOTES . '\\))'
				=> "str_replace('&quot;','\"',\$attributes[\$1])",
			'(htmlspecialchars\\((' . $getAttribute . '(?:\\.' . $getAttribute . ')*),' . \ENT_COMPAT . '\\))'
				=> function ($m) use ($getAttribute)
				{
					return \preg_replace('(' . $getAttribute . ')', '$attributes[$1]', $m[1]);
				},
			'(htmlspecialchars\\(strtr\\(' . $getAttribute . ",('[^\"&\\\\';<>aglmopqtu]+'),('[^\"&\\\\'<>]+')\\)," . \ENT_COMPAT . '\\))'
				=> 'strtr($attributes[$1],$2,$3)',
			'(' . $getAttribute . '(!?=+)' . $getAttribute . ')'
				=> '$attributes[$1]$2$attributes[$3]',
			'(' . $getAttribute . "===('.*?(?<!\\\\)(?:\\\\\\\\)*'))s"
				=> function ($m)
				{
					return '$attributes[' . $m[1] . ']===' . \htmlspecialchars($m[2], \ENT_COMPAT);
				},
			"(('.*?(?<!\\\\)(?:\\\\\\\\)*')===" . $getAttribute . ')s'
				=> function ($m)
				{
					return \htmlspecialchars($m[1], \ENT_COMPAT) . '===$attributes[' . $m[2] . ']';
				},
			'(strpos\\(' . $getAttribute . ",('.*?(?<!\\\\)(?:\\\\\\\\)*')\\)([!=]==(?:0|false)))s"
				=> function ($m)
				{
					return 'strpos($attributes[' . $m[1] . "]," . \htmlspecialchars($m[2], \ENT_COMPAT) . ')' . $m[3];
				},
			"(strpos\\(('.*?(?<!\\\\)(?:\\\\\\\\)*')," . $getAttribute . '\\)([!=]==(?:0|false)))s'
				=> function ($m)
				{
					return 'strpos(' . \htmlspecialchars($m[1], \ENT_COMPAT) . ',$attributes[' . $m[2] . '])' . $m[3];
				},
			'(' . $getAttribute . '(?=(?:==|[-+*])\\d+))'        => '$attributes[$1]',
			'((?<!\\w)(\\d+(?:==|[-+*]))' . $getAttribute . ')'  => '$1$attributes[$2]',
			"(empty\\(\\\$node->getAttribute\\(('[^']+')\\)\\))" => 'empty($attributes[$1])',
			"(\\\$node->hasAttribute\\(('[^']+')\\))"            => 'isset($attributes[$1])',
			'if($node->attributes->length)' => 'if($this->hasNonNullValues($attributes))',
			"(\\\$node->getAttribute\\(('[^']+')\\))" => 'htmlspecialchars_decode($attributes[$1])'
		];
		foreach ($replacements as $match => $replace)
			if ($replace instanceof Closure)
				$php = \preg_replace_callback($match, $replace, $php);
			elseif ($match[0] === '(')
				$php = \preg_replace($match, $replace, $php);
			else
				$php = \str_replace($match, $replace, $php);
	}
	protected static function buildPHP(array $branches)
	{
		$return = ['', ''];
		foreach ($branches as $branch)
		{
			$return[0] .= $branch['statement'] . '{' . $branch['head'];
			$return[1] .= $branch['statement'] . '{';
			if ($branch['branches'])
			{
				list($head, $tail) = self::buildPHP($branch['branches']);
				$return[0] .= $head;
				$return[1] .= $tail;
			}
			$return[0] .= '}';
			$return[1] .= $branch['tail'] . '}';
		}
		return $return;
	}
	protected static function getBranchesPassthrough(array $branches)
	{
		$values = [];
		foreach ($branches as $branch)
			$values[] = $branch['passthrough'];
		if ($branch['statement'] !== 'else')
			$values[] = 0;
		return \array_unique($values);
	}
	protected static function getDynamicRendering($php)
	{
		$rendering = '';
		$literal   = "(?<literal>'((?>[^'\\\\]+|\\\\['\\\\])*)')";
		$attribute = "(?<attribute>htmlspecialchars\\(\\\$node->getAttribute\\('([^']+)'\\),2\\))";
		$value     = "(?<value>$literal|$attribute)";
		$output    = "(?<output>\\\$this->out\\.=$value(?:\\.(?&value))*;)";
		$copyOfAttribute = "(?<copyOfAttribute>if\\(\\\$node->hasAttribute\\('([^']+)'\\)\\)\\{\\\$this->out\\.=' \\g-1=\"'\\.htmlspecialchars\\(\\\$node->getAttribute\\('\\g-1'\\),2\\)\\.'\"';\\})";
		$regexp = '(^(' . $output . '|' . $copyOfAttribute . ')*$)';
		if (!\preg_match($regexp, $php, $m))
			return \false;
		$copiedAttributes = [];
		$usedAttributes = [];
		$regexp = '(' . $output . '|' . $copyOfAttribute . ')A';
		$offset = 0;
		while (\preg_match($regexp, $php, $m, 0, $offset))
			if ($m['output'])
			{
				$offset += 12;
				while (\preg_match('(' . $value . ')A', $php, $m, 0, $offset))
				{
					if ($m['literal'])
					{
						$str = \stripslashes(\substr($m[0], 1, -1));
						$rendering .= \preg_replace('([\\\\$](?=\\d))', '\\\\$0', $str);
					}
					else
					{
						$attrName = \end($m);
						if (!isset($usedAttributes[$attrName]))
							$usedAttributes[$attrName] = \uniqid($attrName, \true);
						$rendering .= $usedAttributes[$attrName];
					}
					$offset += 1 + \strlen($m[0]);
				}
			}
			else
			{
				$attrName = \end($m);
				if (!isset($copiedAttributes[$attrName]))
					$copiedAttributes[$attrName] = \uniqid($attrName, \true);
				$rendering .= $copiedAttributes[$attrName];
				$offset += \strlen($m[0]);
			}
		$attrNames = \array_keys($copiedAttributes + $usedAttributes);
		\sort($attrNames);
		$remainingAttributes = \array_combine($attrNames, $attrNames);
		$regexp = '(^[^ ]+';
		$index  = 0;
		foreach ($attrNames as $attrName)
		{
			$regexp .= '(?> (?!' . RegexpBuilder::fromList($remainingAttributes) . '=)[^=]+="[^"]*")*';
			unset($remainingAttributes[$attrName]);
			$regexp .= '(';
			if (isset($copiedAttributes[$attrName]))
				self::replacePlaceholder($rendering, $copiedAttributes[$attrName], ++$index);
			else
				$regexp .= '?>';
			$regexp .= ' ' . $attrName . '="';
			if (isset($usedAttributes[$attrName]))
			{
				$regexp .= '(';
				self::replacePlaceholder($rendering, $usedAttributes[$attrName], ++$index);
			}
			$regexp .= '[^"]*';
			if (isset($usedAttributes[$attrName]))
				$regexp .= ')';
			$regexp .= '")?';
		}
		$regexp .= '.*)s';
		return [$regexp, $rendering];
	}
	protected static function getStaticRendering($php)
	{
		if ($php === '')
			return '';
		$regexp = "(^\\\$this->out\.='((?>[^'\\\\]+|\\\\['\\\\])*)';\$)";
		if (\preg_match($regexp, $php, $m))
			return \stripslashes($m[1]);
		return \false;
	}
	protected static function replacePlaceholder(&$str, $uniqid, $index)
	{
		$str = \preg_replace_callback(
			'(' . \preg_quote($uniqid) . '(.))',
			function ($m) use ($index)
			{
				if (\is_numeric($m[1]))
					return '${' . $index . '}' . $m[1];
				else
					return '$' . $index . $m[1];
			},
			$str
		);
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\RendererGenerators\PHP;
use DOMElement;
use DOMXPath;
use RuntimeException;
use s9e\TextFormatter\Configurator\Helpers\AVTHelper;
use s9e\TextFormatter\Configurator\Helpers\TemplateParser;
class Serializer
{
	public $convertor;
	public $useMultibyteStringFunctions = \false;
	public function __construct()
	{
		$this->convertor = new XPathConvertor;
	}
	protected function convertAttributeValueTemplate($attrValue)
	{
		$phpExpressions = [];
		foreach (AVTHelper::parse($attrValue) as $token)
			if ($token[0] === 'literal')
				$phpExpressions[] = \var_export($token[1], \true);
			else
				$phpExpressions[] = $this->convertXPath($token[1]);
		return \implode('.', $phpExpressions);
	}
	public function convertCondition($expr)
	{
		$this->convertor->useMultibyteStringFunctions = $this->useMultibyteStringFunctions;
		return $this->convertor->convertCondition($expr);
	}
	public function convertXPath($expr)
	{
		$this->convertor->useMultibyteStringFunctions = $this->useMultibyteStringFunctions;
		return $this->convertor->convertXPath($expr);
	}
	protected function escapeLiteral($text, $context)
	{
		if ($context === 'raw')
			return $text;
		$escapeMode = ($context === 'attribute') ? \ENT_COMPAT : \ENT_NOQUOTES;
		return \htmlspecialchars($text, $escapeMode);
	}
	protected function escapePHPOutput($php, $context)
	{
		if ($context === 'raw')
			return $php;
		$escapeMode = ($context === 'attribute') ? \ENT_COMPAT : \ENT_NOQUOTES;
		return 'htmlspecialchars(' . $php . ',' . $escapeMode . ')';
	}
	protected function hasMultipleCases(DOMElement $switch)
	{
		$xpath = new DOMXPath($switch->ownerDocument);
		return $xpath->evaluate('count(case[@test]) > 1', $switch);
	}
	protected function serializeApplyTemplates(DOMElement $applyTemplates)
	{
		$php = '$this->at($node';
		if ($applyTemplates->hasAttribute('select'))
			$php .= ',' . \var_export($applyTemplates->getAttribute('select'), \true);
		$php .= ');';
		return $php;
	}
	protected function serializeAttribute(DOMElement $attribute)
	{
		$attrName = $attribute->getAttribute('name');
		$phpAttrName = $this->convertAttributeValueTemplate($attrName);
		$phpAttrName = 'htmlspecialchars(' . $phpAttrName . ',' . \ENT_QUOTES . ')';
		return "\$this->out.=' '." . $phpAttrName . ".'=\"';"
		     . $this->serializeChildren($attribute)
		     . "\$this->out.='\"';";
	}
	public function serialize(DOMElement $ir)
	{
		return $this->serializeChildren($ir);
	}
	protected function serializeChildren(DOMElement $ir)
	{
		$php = '';
		foreach ($ir->childNodes as $node)
		{
			$methodName = 'serialize' . \ucfirst($node->localName);
			$php .= $this->$methodName($node);
		}
		return $php;
	}
	protected function serializeCloseTag(DOMElement $closeTag)
	{
		$php = '';
		$id  = $closeTag->getAttribute('id');
		if ($closeTag->hasAttribute('check'))
			$php .= 'if(!isset($t' . $id . ')){';
		if ($closeTag->hasAttribute('set'))
			$php .= '$t' . $id . '=1;';
		$xpath   = new DOMXPath($closeTag->ownerDocument);
		$element = $xpath->query('ancestor::element[@id="' . $id . '"]', $closeTag)->item(0);
		if (!($element instanceof DOMElement))
			throw new RuntimeException;
		$php .= "\$this->out.='>';";
		if ($element->getAttribute('void') === 'maybe')
			$php .= 'if(!$v' . $id . '){';
		if ($closeTag->hasAttribute('check'))
			$php .= '}';
		return $php;
	}
	protected function serializeComment(DOMElement $comment)
	{
		return "\$this->out.='<!--';"
		     . $this->serializeChildren($comment)
		     . "\$this->out.='-->';";
	}
	protected function serializeCopyOfAttributes(DOMElement $copyOfAttributes)
	{
		return 'foreach($node->attributes as $attribute){'
		     . "\$this->out.=' ';\$this->out.=\$attribute->name;\$this->out.='=\"';\$this->out.=htmlspecialchars(\$attribute->value," . \ENT_COMPAT . ");\$this->out.='\"';"
		     . '}';
	}
	protected function serializeElement(DOMElement $element)
	{
		$php     = '';
		$elName  = $element->getAttribute('name');
		$id      = $element->getAttribute('id');
		$isVoid  = $element->getAttribute('void');
		$isDynamic = (bool) (\strpos($elName, '{') !== \false);
		$phpElName = $this->convertAttributeValueTemplate($elName);
		$phpElName = 'htmlspecialchars(' . $phpElName . ',' . \ENT_QUOTES . ')';
		if ($isDynamic)
		{
			$varName = '$e' . $id;
			$php .= $varName . '=' . $phpElName . ';';
			$phpElName = $varName;
		}
		if ($isVoid === 'maybe')
			$php .= '$v' . $id . '=preg_match(' . \var_export(TemplateParser::$voidRegexp, \true) . ',' . $phpElName . ');';
		$php .= "\$this->out.='<'." . $phpElName . ';';
		$php .= $this->serializeChildren($element);
		if ($isVoid !== 'yes')
			$php .= "\$this->out.='</'." . $phpElName . ".'>';";
		if ($isVoid === 'maybe')
			$php .= '}';
		return $php;
	}
	protected function serializeHash(DOMElement $switch)
	{
		$statements = [];
		foreach ($switch->getElementsByTagName('case') as $case)
		{
			if (!$case->parentNode->isSameNode($switch))
				continue;
			if ($case->hasAttribute('branch-values'))
			{
				$php = $this->serializeChildren($case);
				foreach (\unserialize($case->getAttribute('branch-values')) as $value)
					$statements[$value] = $php;
			}
		}
		if (!isset($case))
			throw new RuntimeException;
		$defaultCode = ($case->hasAttribute('branch-values')) ? '' : $this->serializeChildren($case);
		$expr        = $this->convertXPath($switch->getAttribute('branch-key'));
		return SwitchStatement::generate($expr, $statements, $defaultCode);
	}
	protected function serializeOutput(DOMElement $output)
	{
		$context = $output->getAttribute('escape');
		$php = '$this->out.=';
		if ($output->getAttribute('type') === 'xpath')
			$php .= $this->escapePHPOutput($this->convertXPath($output->textContent), $context);
		else
			$php .= \var_export($this->escapeLiteral($output->textContent, $context), \true);
		$php .= ';';
		return $php;
	}
	protected function serializeSwitch(DOMElement $switch)
	{
		if ($switch->hasAttribute('branch-key') && $this->hasMultipleCases($switch))
			return $this->serializeHash($switch);
		$php  = '';
		$else = '';
		foreach ($switch->getElementsByTagName('case') as $case)
		{
			if (!$case->parentNode->isSameNode($switch))
				continue;
			if ($case->hasAttribute('test'))
				$php .= $else . 'if(' . $this->convertCondition($case->getAttribute('test')) . ')';
			else
				$php .= 'else';
			$else = 'else';
			$php .= '{';
			$php .= $this->serializeChildren($case);
			$php .= '}';
		}
		return $php;
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\RendererGenerators\PHP;
class SwitchStatement
{
	protected $branchesCode;
	protected $defaultCode;
	public function __construct(array $branchesCode, $defaultCode = '')
	{
		\ksort($branchesCode);
		$this->branchesCode = $branchesCode;
		$this->defaultCode  = $defaultCode;
	}
	public static function generate($expr, array $branchesCode, $defaultCode = '')
	{
		$switch = new static($branchesCode, $defaultCode);
		return $switch->getSource($expr);
	}
	protected function getSource($expr)
	{
		$php = 'switch(' . $expr . '){';
		foreach ($this->getValuesPerCodeBranch() as $branchCode => $values)
		{
			foreach ($values as $value)
				$php .= 'case' . \var_export((string) $value, \true) . ':';
			$php .= $branchCode . 'break;';
		}
		if ($this->defaultCode > '')
			$php .= 'default:' . $this->defaultCode;
		$php = \preg_replace('(break;$)', '', $php) . '}';
		return $php;
	}
	protected function getValuesPerCodeBranch()
	{
		$values = [];
		foreach ($this->branchesCode as $value => $branchCode)
			$values[$branchCode][] = $value;
		return $values;
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\RendererGenerators\PHP;
use LogicException;
use RuntimeException;
class XPathConvertor
{
	public $pcreVersion;
	protected $regexp;
	public $useMultibyteStringFunctions = \false;
	public function __construct()
	{
		$this->pcreVersion = \PCRE_VERSION;
	}
	public function convertCondition($expr)
	{
		$expr = \trim($expr);
		if (\preg_match('#^@([-\\w]+)$#', $expr, $m))
			return '$node->hasAttribute(' . \var_export($m[1], \true) . ')';
		if ($expr === '@*')
			return '$node->attributes->length';
		if (\preg_match('#^not\\(@([-\\w]+)\\)$#', $expr, $m))
			return '!$node->hasAttribute(' . \var_export($m[1], \true) . ')';
		if (\preg_match('#^\\$(\\w+)$#', $expr, $m))
			return '!empty($this->params[' . \var_export($m[1], \true) . '])';
		if (\preg_match('#^not\\(\\$(\\w+)\\)$#', $expr, $m))
			return 'empty($this->params[' . \var_export($m[1], \true) . '])';
		if (\preg_match('#^([$@][-\\w]+)\\s*([<>])\\s*(\\d+)$#', $expr, $m))
			return $this->convertXPath($m[1]) . $m[2] . $m[3];
		if (!\preg_match('#[=<>]|\\bor\\b|\\band\\b|^[-\\w]+\\s*\\(#', $expr))
			$expr = 'boolean(' . $expr . ')';
		return $this->convertXPath($expr);
	}
	public function convertXPath($expr)
	{
		$expr = \trim($expr);
		$this->generateXPathRegexp();
		if (\preg_match($this->regexp, $expr, $m))
		{
			$methodName = \null;
			foreach ($m as $k => $v)
			{
				if (\is_numeric($k) || $v === '' || $v === \null || !\method_exists($this, $k))
					continue;
				$methodName = $k;
				break;
			}
			if (isset($methodName))
			{
				$args = [$m[$methodName]];
				$i = 0;
				while (isset($m[$methodName . $i]))
				{
					$args[$i] = $m[$methodName . $i];
					++$i;
				}
				return \call_user_func_array([$this, $methodName], $args);
			}
		}
		if (!\preg_match('#[=<>]|\\bor\\b|\\band\\b|^[-\\w]+\\s*\\(#', $expr))
			$expr = 'string(' . $expr . ')';
		return '$this->xpath->evaluate(' . $this->exportXPath($expr) . ',$node)';
	}
	protected function attr($attrName)
	{
		return '$node->getAttribute(' . \var_export($attrName, \true) . ')';
	}
	protected function dot()
	{
		return '$node->textContent';
	}
	protected function param($paramName)
	{
		return '$this->params[' . \var_export($paramName, \true) . ']';
	}
	protected function string($string)
	{
		return \var_export(\substr($string, 1, -1), \true);
	}
	protected function lname()
	{
		return '$node->localName';
	}
	protected function name()
	{
		return '$node->nodeName';
	}
	protected function number($number)
	{
		return "'" . $number . "'";
	}
	protected function strlen($expr)
	{
		if ($expr === '')
			$expr = '.';
		$php = $this->convertXPath($expr);
		return ($this->useMultibyteStringFunctions)
			? 'mb_strlen(' . $php . ",'utf-8')"
			: "strlen(preg_replace('(.)us','.'," . $php . '))';
	}
	protected function contains($haystack, $needle)
	{
		return '(strpos(' . $this->convertXPath($haystack) . ',' . $this->convertXPath($needle) . ')!==false)';
	}
	protected function startswith($string, $substring)
	{
		return '(strpos(' . $this->convertXPath($string) . ',' . $this->convertXPath($substring) . ')===0)';
	}
	protected function not($expr)
	{
		return '!(' . $this->convertCondition($expr) . ')';
	}
	protected function notcontains($haystack, $needle)
	{
		return '(strpos(' . $this->convertXPath($haystack) . ',' . $this->convertXPath($needle) . ')===false)';
	}
	protected function substr($exprString, $exprPos, $exprLen = \null)
	{
		if (!$this->useMultibyteStringFunctions)
		{
			$expr = 'substring(' . $exprString . ',' . $exprPos;
			if (isset($exprLen))
				$expr .= ',' . $exprLen;
			$expr .= ')';
			return '$this->xpath->evaluate(' . $this->exportXPath($expr) . ',$node)';
		}
		$php = 'mb_substr(' . $this->convertXPath($exprString) . ',';
		if (\is_numeric($exprPos))
			$php .= \max(0, $exprPos - 1);
		else
			$php .= 'max(0,' . $this->convertXPath($exprPos) . '-1)';
		$php .= ',';
		if (isset($exprLen))
			if (\is_numeric($exprLen))
				if (\is_numeric($exprPos) && $exprPos < 1)
					$php .= \max(0, $exprPos + $exprLen - 1);
				else
					$php .= \max(0, $exprLen);
			else
				$php .= 'max(0,' . $this->convertXPath($exprLen) . ')';
		else
			$php .= 'null';
		$php .= ",'utf-8')";
		return $php;
	}
	protected function substringafter($expr, $str)
	{
		return 'substr(strstr(' . $this->convertXPath($expr) . ',' . $this->convertXPath($str) . '),' . (\strlen($str) - 2) . ')';
	}
	protected function substringbefore($expr1, $expr2)
	{
		return 'strstr(' . $this->convertXPath($expr1) . ',' . $this->convertXPath($expr2) . ',true)';
	}
	protected function cmp($expr1, $operator, $expr2)
	{
		$operands  = [];
		$operators = [
			'='  => '===',
			'!=' => '!==',
			'>'  => '>',
			'>=' => '>=',
			'<'  => '<',
			'<=' => '<='
		];
		foreach ([$expr1, $expr2] as $expr)
			if (\is_numeric($expr))
			{
				$operators['=']  = '==';
				$operators['!='] = '!=';
				$operands[] = \preg_replace('(^0(.+))', '$1', $expr);
			}
			else
				$operands[] = $this->convertXPath($expr);
		return \implode($operators[$operator], $operands);
	}
	protected function bool($expr1, $operator, $expr2)
	{
		$operators = [
			'and' => '&&',
			'or'  => '||'
		];
		return $this->convertCondition($expr1) . $operators[$operator] . $this->convertCondition($expr2);
	}
	protected function parens($expr)
	{
		return '(' . $this->convertXPath($expr) . ')';
	}
	protected function translate($str, $from, $to)
	{
		\preg_match_all('(.)su', \substr($from, 1, -1), $matches);
		$from = $matches[0];
		\preg_match_all('(.)su', \substr($to, 1, -1), $matches);
		$to = $matches[0];
		if (\count($to) > \count($from))
			$to = \array_slice($to, 0, \count($from));
		else
			while (\count($from) > \count($to))
				$to[] = '';
		$from = \array_unique($from);
		$to   = \array_intersect_key($to, $from);
		$php = 'strtr(' . $this->convertXPath($str) . ',';
		if ([1] === \array_unique(\array_map('strlen', $from))
		 && [1] === \array_unique(\array_map('strlen', $to)))
			$php .= \var_export(\implode('', $from), \true) . ',' . \var_export(\implode('', $to), \true);
		else
		{
			$php .= '[';
			$cnt = \count($from);
			for ($i = 0; $i < $cnt; ++$i)
			{
				if ($i)
					$php .= ',';
				$php .= \var_export($from[$i], \true) . '=>' . \var_export($to[$i], \true);
			}
			$php .= ']';
		}
		$php .= ')';
		return $php;
	}
	protected function math($expr1, $operator, $expr2)
	{
		if (!\is_numeric($expr1))
			$expr1 = $this->convertXPath($expr1);
		if (!\is_numeric($expr2))
			$expr2 = $this->convertXPath($expr2);
		if ($operator === 'div')
			$operator = '/';
		return $expr1 . $operator . $expr2;
	}
	protected function exportXPath($expr)
	{
		$phpTokens = [];
		$pos = 0;
		$len = \strlen($expr);
		while ($pos < $len)
		{
			if ($expr[$pos] === "'" || $expr[$pos] === '"')
			{
				$nextPos = \strpos($expr, $expr[$pos], 1 + $pos);
				if ($nextPos === \false)
					throw new RuntimeException('Unterminated string literal in XPath expression ' . \var_export($expr, \true));
				$phpTokens[] = \var_export(\substr($expr, $pos, $nextPos + 1 - $pos), \true);
				$pos = $nextPos + 1;
				continue;
			}
			if ($expr[$pos] === '$' && \preg_match('/\\$(\\w+)/', $expr, $m, 0, $pos))
			{
				$phpTokens[] = '$this->getParamAsXPath(' . \var_export($m[1], \true) . ')';
				$pos += \strlen($m[0]);
				continue;
			}
			$spn = \strcspn($expr, '\'"$', $pos);
			if ($spn)
			{
				$phpTokens[] = \var_export(\substr($expr, $pos, $spn), \true);
				$pos += $spn;
			}
		}
		return \implode('.', $phpTokens);
	}
	protected function generateXPathRegexp()
	{
		if (isset($this->regexp))
			return;
		$patterns = [
			'attr'      => ['@', '(?<attr0>[-\\w]+)'],
			'dot'       => '\\.',
			'name'      => 'name\\(\\)',
			'lname'     => 'local-name\\(\\)',
			'param'     => ['\\$', '(?<param0>\\w+)'],
			'string'    => '"[^"]*"|\'[^\']*\'',
			'number'    => ['-?', '\\d++'],
			'strlen'    => ['string-length', '\\(', '(?<strlen0>(?&value)?)', '\\)'],
			'contains'  => [
				'contains',
				'\\(',
				'(?<contains0>(?&value))',
				',',
				'(?<contains1>(?&value))',
				'\\)'
			],
			'translate' => [
				'translate',
				'\\(',
				'(?<translate0>(?&value))',
				',',
				'(?<translate1>(?&string))',
				',',
				'(?<translate2>(?&string))',
				'\\)'
			],
			'substr' => [
				'substring',
				'\\(',
				'(?<substr0>(?&value))',
				',',
				'(?<substr1>(?&value))',
				'(?:, (?<substr2>(?&value)))?',
				'\\)'
			],
			'substringafter' => [
				'substring-after',
				'\\(',
				'(?<substringafter0>(?&value))',
				',',
				'(?<substringafter1>(?&string))',
				'\\)'
			],
			'substringbefore' => [
				'substring-before',
				'\\(',
				'(?<substringbefore0>(?&value))',
				',',
				'(?<substringbefore1>(?&value))',
				'\\)'
			],
			'startswith' => [
				'starts-with',
				'\\(',
				'(?<startswith0>(?&value))',
				',',
				'(?<startswith1>(?&value))',
				'\\)'
			],
			'math' => [
				'(?<math0>(?&attr)|(?&number)|(?&param))',
				'(?<math1>[-+*]|div)',
				'(?<math2>(?&math)|(?&math0))'
			],
			'notcontains' => [
				'not',
				'\\(',
				'contains',
				'\\(',
				'(?<notcontains0>(?&value))',
				',',
				'(?<notcontains1>(?&value))',
				'\\)',
				'\\)'
			]
		];
		$exprs = [];
		if (\version_compare($this->pcreVersion, '8.13', '>='))
		{
			$exprs[] = '(?<cmp>(?<cmp0>(?&value)) (?<cmp1>!?=) (?<cmp2>(?&value)))';
			$exprs[] = '(?<parens>\\( (?<parens0>(?&bool)|(?&cmp)|(?&math)) \\))';
			$exprs[] = '(?<bool>(?<bool0>(?&cmp)|(?&not)|(?&value)|(?&parens)) (?<bool1>and|or) (?<bool2>(?&bool)|(?&cmp)|(?&not)|(?&value)|(?&parens)))';
			$exprs[] = '(?<not>not \\( (?<not0>(?&bool)|(?&value)) \\))';
			$patterns['math'][0] = \str_replace('))', ')|(?&parens))', $patterns['math'][0]);
			$patterns['math'][1] = \str_replace('))', ')|(?&parens))', $patterns['math'][1]);
		}
		$valueExprs = [];
		foreach ($patterns as $name => $pattern)
		{
			if (\is_array($pattern))
				$pattern = \implode(' ', $pattern);
			if (\strpos($pattern, '?&') === \false || \version_compare($this->pcreVersion, '8.13', '>='))
				$valueExprs[] = '(?<' . $name . '>' . $pattern . ')';
		}
		\array_unshift($exprs, '(?<value>' . \implode('|', $valueExprs) . ')');

		$regexp = '#^(?:' . \implode('|', $exprs) . ')$#S';
		$regexp = \str_replace(' ', '\\s*', $regexp);
		$this->regexp = $regexp;
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\RulesGenerators\Interfaces;
use s9e\TextFormatter\Configurator\Helpers\TemplateInspector;
interface BooleanRulesGenerator
{
	public function generateBooleanRules(TemplateInspector $src);
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\RulesGenerators\Interfaces;
use s9e\TextFormatter\Configurator\Helpers\TemplateInspector;
interface TargetedRulesGenerator
{
	public function generateTargetedRules(TemplateInspector $src, TemplateInspector $trg);
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator;
use DOMElement;
use s9e\TextFormatter\Configurator\Items\Tag;
abstract class TemplateCheck
{
	const XMLNS_XSL = 'http://www.w3.org/1999/XSL/Transform';
	abstract public function check(DOMElement $template, Tag $tag);
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\TemplateNormalizations;
use DOMAttr;
use DOMComment;
use DOMElement;
use DOMNode;
use DOMXPath;
abstract class AbstractNormalization
{
	const XMLNS_XSL = 'http://www.w3.org/1999/XSL/Transform';
	public $onlyOnce = \false;
	protected $ownerDocument;
	protected $queries = [];
	protected $xpath;
	public function normalize(DOMElement $template)
	{
		$this->ownerDocument = $template->ownerDocument;
		$this->xpath         = new DOMXPath($this->ownerDocument);
		foreach ($this->getNodes() as $node)
			$this->normalizeNode($node);
		$this->reset();
	}
	protected function createElement($nodeName, $textContent = '')
	{
		$value = \htmlspecialchars($textContent, \ENT_NOQUOTES, 'UTF-8');
		$pos   = \strpos($nodeName, ':');
		if ($pos === \false)
			return $this->ownerDocument->createElement($nodeName, $value);
		$namespaceURI = $this->ownerDocument->lookupNamespaceURI(\substr($nodeName, 0, $pos));
		return $this->ownerDocument->createElementNS($namespaceURI, $nodeName, $value);
	}
	protected function createTextNode($content)
	{
		return $this->ownerDocument->createTextNode($content);
	}
	protected function getNodes()
	{
		$query = \implode(' | ', $this->queries);
		return ($query === '') ? [] : $this->xpath($query);
	}
	protected function isXsl(DOMNode $node, $localName = \null)
	{
		return ($node->namespaceURI === self::XMLNS_XSL && (!isset($localName) || $localName === $node->localName));
	}
	protected function lowercase($str)
	{
		return \strtr($str, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz');
	}
	protected function normalizeAttribute(DOMAttr $attribute)
	{
	}
	protected function normalizeElement(DOMElement $element)
	{
	}
	protected function normalizeNode(DOMNode $node)
	{
		if (!$node->parentNode)
			return;
		if ($node instanceof DOMElement)
			$this->normalizeElement($node);
		elseif ($node instanceof DOMAttr)
			$this->normalizeAttribute($node);
	}
	protected function reset()
	{
		$this->ownerDocument = \null;
		$this->xpath         = \null;
	}
	protected function xpath($query, DOMNode $node = \null)
	{
		$query = \str_replace('$XSL', '"' . self::XMLNS_XSL . '"', $query);
		return \iterator_to_array($this->xpath->query($query, $node));
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\Traits;
trait CollectionProxy
{
	public function __call($methodName, $args)
	{
		return \call_user_func_array([$this->collection, $methodName], $args);
	}
	public function offsetExists($offset)
	{
		return isset($this->collection[$offset]);
	}
	public function offsetGet($offset)
	{
		return $this->collection[$offset];
	}
	public function offsetSet($offset, $value)
	{
		$this->collection[$offset] = $value;
	}
	public function offsetUnset($offset)
	{
		unset($this->collection[$offset]);
	}
	public function count()
	{
		return \count($this->collection);
	}
	public function current()
	{
		return $this->collection->current();
	}
	public function key()
	{
		return $this->collection->key();
	}
	public function next()
	{
		return $this->collection->next();
	}
	public function rewind()
	{
		$this->collection->rewind();
	}
	public function valid()
	{
		return $this->collection->valid();
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\Traits;
use InvalidArgumentException;
use RuntimeException;
use Traversable;
use s9e\TextFormatter\Configurator\Collections\Collection;
use s9e\TextFormatter\Configurator\Collections\NormalizedCollection;
trait Configurable
{
	public function __get($propName)
	{
		$methodName = 'get' . \ucfirst($propName);
		if (\method_exists($this, $methodName))
			return $this->$methodName();
		if (!\property_exists($this, $propName))
			throw new RuntimeException("Property '" . $propName . "' does not exist");
		return $this->$propName;
	}
	public function __set($propName, $propValue)
	{
		$methodName = 'set' . \ucfirst($propName);
		if (\method_exists($this, $methodName))
		{
			$this->$methodName($propValue);
			return;
		}
		if (!isset($this->$propName))
		{
			$this->$propName = $propValue;
			return;
		}
		if ($this->$propName instanceof NormalizedCollection)
		{
			if (!\is_array($propValue)
			 && !($propValue instanceof Traversable))
				throw new InvalidArgumentException("Property '" . $propName . "' expects an array or a traversable object to be passed");
			$this->$propName->clear();
			foreach ($propValue as $k => $v)
				$this->$propName->set($k, $v);
			return;
		}
		if (\is_object($this->$propName))
		{
			if (!($propValue instanceof $this->$propName))
				throw new InvalidArgumentException("Cannot replace property '" . $propName . "' of class '" . \get_class($this->$propName) . "' with instance of '" . \get_class($propValue) . "'");
		}
		else
		{
			$oldType = \gettype($this->$propName);
			$newType = \gettype($propValue);
			if ($oldType === 'boolean')
				if ($propValue === 'false')
				{
					$newType   = 'boolean';
					$propValue = \false;
				}
				elseif ($propValue === 'true')
				{
					$newType   = 'boolean';
					$propValue = \true;
				}
			if ($oldType !== $newType)
			{
				$tmp = $propValue;
				\settype($tmp, $oldType);
				\settype($tmp, $newType);
				if ($tmp !== $propValue)
					throw new InvalidArgumentException("Cannot replace property '" . $propName . "' of type " . $oldType . ' with value of type ' . $newType);
				\settype($propValue, $oldType);
			}
		}
		$this->$propName = $propValue;
	}
	public function __isset($propName)
	{
		$methodName = 'isset' . \ucfirst($propName);
		if (\method_exists($this, $methodName))
			return $this->$methodName();
		return isset($this->$propName);
	}
	public function __unset($propName)
	{
		$methodName = 'unset' . \ucfirst($propName);
		if (\method_exists($this, $methodName))
		{
			$this->$methodName();
			return;
		}
		if (!isset($this->$propName))
			return;
		if ($this->$propName instanceof Collection)
		{
			$this->$propName->clear();
			return;
		}
		throw new RuntimeException("Property '" . $propName . "' cannot be unset");
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\Traits;
trait TemplateSafeness
{
	protected $markedSafe = [];
	protected function isSafe($context)
	{
		return !empty($this->markedSafe[$context]);
	}
	public function isSafeAsURL()
	{
		return $this->isSafe('AsURL');
	}
	public function isSafeInCSS()
	{
		return $this->isSafe('InCSS');
	}
	public function isSafeInJS()
	{
		return $this->isSafe('InJS');
	}
	public function markAsSafeAsURL()
	{
		$this->markedSafe['AsURL'] = \true;
		return $this;
	}
	public function markAsSafeInCSS()
	{
		$this->markedSafe['InCSS'] = \true;
		return $this;
	}
	public function markAsSafeInJS()
	{
		$this->markedSafe['InJS'] = \true;
		return $this;
	}
	public function resetSafeness()
	{
		$this->markedSafe = [];
		return $this;
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\Validators;
use InvalidArgumentException;
abstract class AttributeName
{
	public static function isValid($name)
	{
		return (bool) \preg_match('#^(?!xmlns$)[a-z_][-a-z_0-9]*$#Di', $name);
	}
	public static function normalize($name)
	{
		if (!static::isValid($name))
			throw new InvalidArgumentException("Invalid attribute name '" . $name . "'");
		return \strtolower($name);
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\Validators;
use InvalidArgumentException;
abstract class TagName
{
	public static function isValid($name)
	{
		return (bool) \preg_match('#^(?:(?!xmlns|xsl|s9e)[a-z_][a-z_0-9]*:)?[a-z_][-a-z_0-9]*$#Di', $name);
	}
	public static function normalize($name)
	{
		if (!static::isValid($name))
			throw new InvalidArgumentException("Invalid tag name '" . $name . "'");
		if (\strpos($name, ':') === \false)
			$name = \strtoupper($name);
		return $name;
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\Collections;
use Countable;
use Iterator;
use s9e\TextFormatter\Configurator\ConfigProvider;
use s9e\TextFormatter\Configurator\Helpers\ConfigHelper;
class Collection implements ConfigProvider, Countable, Iterator
{
	protected $items = [];
	public function clear()
	{
		$this->items = [];
	}
	public function asConfig()
	{
		return ConfigHelper::toArray($this->items, \true);
	}
	public function count()
	{
		return \count($this->items);
	}
	public function current()
	{
		return \current($this->items);
	}
	public function key()
	{
		return \key($this->items);
	}
	public function next()
	{
		return \next($this->items);
	}
	public function rewind()
	{
		\reset($this->items);
	}
	public function valid()
	{
		return (\key($this->items) !== \null);
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\Items;
use s9e\TextFormatter\Configurator\Collections\AttributeFilterChain;
use s9e\TextFormatter\Configurator\ConfigProvider;
use s9e\TextFormatter\Configurator\Helpers\ConfigHelper;
use s9e\TextFormatter\Configurator\Items\ProgrammableCallback;
use s9e\TextFormatter\Configurator\Traits\Configurable;
use s9e\TextFormatter\Configurator\Traits\TemplateSafeness;
class Attribute implements ConfigProvider
{
	use Configurable;
	use TemplateSafeness;
	protected $defaultValue;
	protected $filterChain;
	protected $generator;
	protected $required = \true;
	public function __construct(array $options = \null)
	{
		$this->filterChain = new AttributeFilterChain;
		if (isset($options))
			foreach ($options as $optionName => $optionValue)
				$this->__set($optionName, $optionValue);
	}
	protected function isSafe($context)
	{
		$methodName = 'isSafe' . $context;
		foreach ($this->filterChain as $filter)
			if ($filter->$methodName())
				return \true;
		return !empty($this->markedSafe[$context]);
	}
	public function setGenerator($callback)
	{
		if (!($callback instanceof ProgrammableCallback))
			$callback = new ProgrammableCallback($callback);
		$this->generator = $callback;
	}
	public function asConfig()
	{
		$vars = \get_object_vars($this);
		unset($vars['markedSafe']);
		return ConfigHelper::toArray($vars);
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\Items;
use InvalidArgumentException;
use s9e\TextFormatter\Configurator\ConfigProvider;
use s9e\TextFormatter\Configurator\Helpers\ConfigHelper;
use s9e\TextFormatter\Configurator\JavaScript\Code;
use s9e\TextFormatter\Configurator\JavaScript\FunctionProvider;
class ProgrammableCallback implements ConfigProvider
{
	protected $callback;
	protected $js = 'returnFalse';
	protected $params = [];
	protected $vars = [];
	public function __construct($callback)
	{
		if (!\is_callable($callback))
			throw new InvalidArgumentException(__METHOD__ . '() expects a callback');
		$this->callback = $this->normalizeCallback($callback);
		$this->autoloadJS();
	}
	public function addParameterByValue($paramValue)
	{
		$this->params[] = $paramValue;
		return $this;
	}
	public function addParameterByName($paramName)
	{
		if (\array_key_exists($paramName, $this->params))
			throw new InvalidArgumentException("Parameter '" . $paramName . "' already exists");
		$this->params[$paramName] = \null;
		return $this;
	}
	public function getCallback()
	{
		return $this->callback;
	}
	public function getJS()
	{
		return $this->js;
	}
	public function getVars()
	{
		return $this->vars;
	}
	public function resetParameters()
	{
		$this->params = [];
		return $this;
	}
	public function setJS($js)
	{
		$this->js = $js;
		return $this;
	}
	public function setVar($name, $value)
	{
		$this->vars[$name] = $value;
		return $this;
	}
	public function setVars(array $vars)
	{
		$this->vars = $vars;
		return $this;
	}
	public function asConfig()
	{
		$config = ['callback' => $this->callback];
		foreach ($this->params as $k => $v)
			if (\is_numeric($k))
				$config['params'][] = $v;
			elseif (isset($this->vars[$k]))
				$config['params'][] = $this->vars[$k];
			else
				$config['params'][$k] = \null;
		if (isset($config['params']))
			$config['params'] = ConfigHelper::toArray($config['params'], \true, \true);
		$config['js'] = new Code($this->js);
		return $config;
	}
	protected function autoloadJS()
	{
		if (!\is_string($this->callback))
			return;
		try
		{
			$this->js = FunctionProvider::get($this->callback);
		}
		catch (InvalidArgumentException $e)
		{
			}
	}
	protected function normalizeCallback($callback)
	{
		if (\is_array($callback) && \is_string($callback[0]))
			$callback = $callback[0] . '::' . $callback[1];
		if (\is_string($callback))
			$callback = \ltrim($callback, '\\');
		return $callback;
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\Items;
use InvalidArgumentException;
use s9e\TextFormatter\Configurator\ConfigProvider;
use s9e\TextFormatter\Configurator\FilterableConfigValue;
use s9e\TextFormatter\Configurator\Helpers\RegexpParser;
use s9e\TextFormatter\Configurator\JavaScript\Code;
use s9e\TextFormatter\Configurator\JavaScript\RegexpConvertor;
class Regexp implements ConfigProvider, FilterableConfigValue
{
	protected $isGlobal;
	protected $jsRegexp;
	protected $regexp;
	public function __construct($regexp, $isGlobal = \false)
	{
		if (@\preg_match($regexp, '') === \false)
			throw new InvalidArgumentException('Invalid regular expression ' . \var_export($regexp, \true));
		$this->regexp   = $regexp;
		$this->isGlobal = $isGlobal;
	}
	public function __toString()
	{
		return $this->regexp;
	}
	public function asConfig()
	{
		return $this;
	}
	public function filterConfig($target)
	{
		return ($target === 'JS') ? new Code($this->getJS()) : (string) $this;
	}
	public function getCaptureNames()
	{
		return RegexpParser::getCaptureNames($this->regexp);
	}
	public function getJS()
	{
		if (!isset($this->jsRegexp))
			$this->jsRegexp = RegexpConvertor::toJS($this->regexp, $this->isGlobal);
		return $this->jsRegexp;
	}
	public function getNamedCaptures()
	{
		$captures   = [];
		$regexpInfo = RegexpParser::parse($this->regexp);
		$start = $regexpInfo['delimiter'] . '^';
		$end   = '$' . $regexpInfo['delimiter'] . $regexpInfo['modifiers'];
		if (\strpos($regexpInfo['modifiers'], 'D') === \false)
			$end .= 'D';
		foreach ($this->getNamedCapturesExpressions($regexpInfo['tokens']) as $name => $expr)
			$captures[$name] = $start . $expr . $end;
		return $captures;
	}
	protected function getNamedCapturesExpressions(array $tokens)
	{
		$exprs = [];
		foreach ($tokens as $token)
		{
			if ($token['type'] !== 'capturingSubpatternStart' || !isset($token['name']))
				continue;
			$expr = $token['content'];
			if (\strpos($expr, '|') !== \false)
				$expr = '(?:' . $expr . ')';
			$exprs[$token['name']] = $expr;
		}
		return $exprs;
	}
	public function setJS($jsRegexp)
	{
		$this->jsRegexp = $jsRegexp;
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\Items;
use InvalidArgumentException;
use s9e\TextFormatter\Configurator\Collections\AttributeCollection;
use s9e\TextFormatter\Configurator\Collections\AttributePreprocessorCollection;
use s9e\TextFormatter\Configurator\Collections\Ruleset;
use s9e\TextFormatter\Configurator\Collections\TagFilterChain;
use s9e\TextFormatter\Configurator\ConfigProvider;
use s9e\TextFormatter\Configurator\Helpers\ConfigHelper;
use s9e\TextFormatter\Configurator\Items\Template;
use s9e\TextFormatter\Configurator\Traits\Configurable;
class Tag implements ConfigProvider
{
	use Configurable;
	protected $attributes;
	protected $attributePreprocessors;
	protected $filterChain;
	protected $nestingLimit = 10;
	protected $rules;
	protected $tagLimit = 5000;
	protected $template;
	public function __construct(array $options = \null)
	{
		$this->attributes             = new AttributeCollection;
		$this->attributePreprocessors = new AttributePreprocessorCollection;
		$this->filterChain            = new TagFilterChain;
		$this->rules                  = new Ruleset;
		$this->filterChain->append('s9e\\TextFormatter\\Parser::executeAttributePreprocessors')
		                  ->addParameterByName('tagConfig')
		                  ->setJS('executeAttributePreprocessors');
		$this->filterChain->append('s9e\\TextFormatter\\Parser::filterAttributes')
		                  ->addParameterByName('tagConfig')
		                  ->addParameterByName('registeredVars')
		                  ->addParameterByName('logger')
		                  ->setJS('filterAttributes');
		if (isset($options))
		{
			\ksort($options);
			foreach ($options as $optionName => $optionValue)
				$this->__set($optionName, $optionValue);
		}
	}
	public function asConfig()
	{
		$vars = \get_object_vars($this);
		unset($vars['template']);
		if (!\count($this->attributePreprocessors))
		{
			$callback = 's9e\\TextFormatter\\Parser::executeAttributePreprocessors';
			$filterChain = clone $vars['filterChain'];
			$i = \count($filterChain);
			while (--$i >= 0)
				if ($filterChain[$i]->getCallback() === $callback)
					unset($filterChain[$i]);
			$vars['filterChain'] = $filterChain;
		}
		return ConfigHelper::toArray($vars);
	}
	public function getTemplate()
	{
		return $this->template;
	}
	public function issetTemplate()
	{
		return isset($this->template);
	}
	public function setAttributePreprocessors($attributePreprocessors)
	{
		$this->attributePreprocessors->clear();
		$this->attributePreprocessors->merge($attributePreprocessors);
	}
	public function setNestingLimit($limit)
	{
		$limit = (int) $limit;
		if ($limit < 1)
			throw new InvalidArgumentException('nestingLimit must be a number greater than 0');
		$this->nestingLimit = $limit;
	}
	public function setRules($rules)
	{
		$this->rules->clear();
		$this->rules->merge($rules);
	}
	public function setTagLimit($limit)
	{
		$limit = (int) $limit;
		if ($limit < 1)
			throw new InvalidArgumentException('tagLimit must be a number greater than 0');
		$this->tagLimit = $limit;
	}
	public function setTemplate($template)
	{
		if (!($template instanceof Template))
			$template = new Template($template);
		$this->template = $template;
	}
	public function unsetTemplate()
	{
		unset($this->template);
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\JavaScript;
use s9e\TextFormatter\Configurator\FilterableConfigValue;
class Code implements FilterableConfigValue
{
	public $code;
	public function __construct($code)
	{
		$this->code = $code;
	}
	public function __toString()
	{
		return (string) $this->code;
	}
	public function filterConfig($target)
	{
		return ($target === 'JS') ? $this : \null;
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\RendererGenerators;
use DOMElement;
use s9e\TextFormatter\Configurator\Helpers\TemplateHelper;
use s9e\TextFormatter\Configurator\Helpers\TemplateParser;
use s9e\TextFormatter\Configurator\RendererGenerator;
use s9e\TextFormatter\Configurator\RendererGenerators\PHP\ControlStructuresOptimizer;
use s9e\TextFormatter\Configurator\RendererGenerators\PHP\Optimizer;
use s9e\TextFormatter\Configurator\RendererGenerators\PHP\Quick;
use s9e\TextFormatter\Configurator\RendererGenerators\PHP\Serializer;
use s9e\TextFormatter\Configurator\RendererGenerators\PHP\SwitchStatement;
use s9e\TextFormatter\Configurator\Rendering;
use s9e\TextFormatter\Configurator\TemplateNormalizer;
class PHP implements RendererGenerator
{
	const XMLNS_XSL = 'http://www.w3.org/1999/XSL/Transform';
	public $cacheDir;
	public $className;
	public $controlStructuresOptimizer;
	public $defaultClassPrefix = 'Renderer_';
	public $enableQuickRenderer = \true;
	public $filepath;
	public $lastClassName;
	public $lastFilepath;
	protected $normalizer;
	public $optimizer;
	public $serializer;
	public $useMultibyteStringFunctions;
	public function __construct($cacheDir = \null)
	{
		$this->cacheDir = (isset($cacheDir)) ? $cacheDir : \sys_get_temp_dir();
		if (\extension_loaded('tokenizer'))
		{
			$this->controlStructuresOptimizer = new ControlStructuresOptimizer;
			$this->optimizer = new Optimizer;
		}
		$this->useMultibyteStringFunctions = \extension_loaded('mbstring');
		$this->serializer = new Serializer;
		$this->normalizer = new TemplateNormalizer;
		$this->normalizer->clear();
		$this->normalizer->append('RemoveLivePreviewAttributes');
	}
	public function getRenderer(Rendering $rendering)
	{
		$php = $this->generate($rendering);
		if (isset($this->filepath))
			$filepath = $this->filepath;
		else
			$filepath = $this->cacheDir . '/' . \str_replace('\\', '_', $this->lastClassName) . '.php';
		\file_put_contents($filepath, "<?php\n" . $php);
		$this->lastFilepath = \realpath($filepath);
		if (!\class_exists($this->lastClassName, \false))
			include $filepath;
		return new $this->lastClassName;
	}
	public function generate(Rendering $rendering)
	{
		$this->serializer->useMultibyteStringFunctions = $this->useMultibyteStringFunctions;
		$compiledTemplates = \array_map([$this, 'compileTemplate'], $rendering->getTemplates());
		$php = [];
		$php[] = ' extends \\s9e\\TextFormatter\\Renderers\\PHP';
		$php[] = '{';
		$php[] = '	protected $params=' . self::export($rendering->getAllParameters()) . ';';
		$php[] = '	protected function renderNode(\\DOMNode $node)';
		$php[] = '	{';
		$php[] = '		' . SwitchStatement::generate('$node->nodeName', $compiledTemplates, '$this->at($node);');
		$php[] = '	}';
		if ($this->enableQuickRenderer)
			$php[] = Quick::getSource($compiledTemplates);
		$php[] = '}';
		$php = \implode("\n", $php);
		if (isset($this->controlStructuresOptimizer))
			$php = $this->controlStructuresOptimizer->optimize($php);
		$className = (isset($this->className))
		           ? $this->className
		           : $this->defaultClassPrefix . \sha1($php);
		$this->lastClassName = $className;
		$header = "\n/**\n* @package   s9e\TextFormatter\n* @copyright Copyright (c) 2010-2017 The s9e Authors\n* @license   http://www.opensource.org/licenses/mit-license.php The MIT License\n*/\n";
		$pos = \strrpos($className, '\\');
		if ($pos !== \false)
		{
			$header .= 'namespace ' . \substr($className, 0, $pos) . ";\n\n";
			$className = \substr($className, 1 + $pos);
		}
		$php = $header . 'class ' . $className . $php;
		return $php;
	}
	protected static function export(array $value)
	{
		$pairs = [];
		foreach ($value as $k => $v)
			$pairs[] = \var_export($k, \true) . '=>' . \var_export($v, \true);
		return '[' . \implode(',', $pairs) . ']';
	}
	protected function compileTemplate($template)
	{
		$template = $this->normalizer->normalizeTemplate($template);
		$ir = TemplateParser::parse($template);
		$php = $this->serializer->serialize($ir->documentElement);
		if (isset($this->optimizer))
			$php = $this->optimizer->optimize($php);
		return $php;
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\RendererGenerators\PHP;
class ControlStructuresOptimizer extends AbstractOptimizer
{
	protected $braces;
	protected $context;
	protected function blockEndsWithIf()
	{
		return \in_array($this->context['lastBlock'], [\T_IF, \T_ELSEIF], \true);
	}
	protected function isControlStructure()
	{
		return \in_array(
			$this->tokens[$this->i][0],
			[\T_ELSE, \T_ELSEIF, \T_FOR, \T_FOREACH, \T_IF, \T_WHILE],
			\true
		);
	}
	protected function isFollowedByElse()
	{
		if ($this->i > $this->cnt - 4)
			return \false;
		$k = $this->i + 1;
		if ($this->tokens[$k][0] === \T_WHITESPACE)
			++$k;
		return \in_array($this->tokens[$k][0], [\T_ELSEIF, \T_ELSE], \true);
	}
	protected function mustPreserveBraces()
	{
		return ($this->blockEndsWithIf() && $this->isFollowedByElse());
	}
	protected function optimizeTokens()
	{
		while (++$this->i < $this->cnt)
			if ($this->tokens[$this->i] === ';')
				++$this->context['statements'];
			elseif ($this->tokens[$this->i] === '{')
				++$this->braces;
			elseif ($this->tokens[$this->i] === '}')
			{
				if ($this->context['braces'] === $this->braces)
					$this->processEndOfBlock();
				--$this->braces;
			}
			elseif ($this->isControlStructure())
				$this->processControlStructure();
	}
	protected function processControlStructure()
	{
		$savedIndex = $this->i;
		if (!\in_array($this->tokens[$this->i][0], [\T_ELSE, \T_ELSEIF], \true))
			++$this->context['statements'];
		if ($this->tokens[$this->i][0] !== \T_ELSE)
			$this->skipCondition();
		$this->skipWhitespace();
		if ($this->tokens[$this->i] !== '{')
		{
			$this->i = $savedIndex;
			return;
		}
		++$this->braces;
		$replacement = [\T_WHITESPACE, ''];
		if ($this->tokens[$savedIndex][0]  === \T_ELSE
		 && $this->tokens[$this->i + 1][0] !== \T_VARIABLE
		 && $this->tokens[$this->i + 1][0] !== \T_WHITESPACE)
			$replacement = [\T_WHITESPACE, ' '];
		$this->context['lastBlock'] = $this->tokens[$savedIndex][0];
		$this->context = [
			'braces'      => $this->braces,
			'index'       => $this->i,
			'lastBlock'   => \null,
			'parent'      => $this->context,
			'replacement' => $replacement,
			'savedIndex'  => $savedIndex,
			'statements'  => 0
		];
	}
	protected function processEndOfBlock()
	{
		if ($this->context['statements'] < 2 && !$this->mustPreserveBraces())
			$this->removeBracesInCurrentContext();
		$this->context = $this->context['parent'];
		$this->context['parent']['lastBlock'] = $this->context['lastBlock'];
	}
	protected function removeBracesInCurrentContext()
	{
		$this->tokens[$this->context['index']] = $this->context['replacement'];
		$this->tokens[$this->i] = ($this->context['statements']) ? [\T_WHITESPACE, ''] : ';';
		foreach ([$this->context['index'] - 1, $this->i - 1] as $tokenIndex)
			if ($this->tokens[$tokenIndex][0] === \T_WHITESPACE)
				$this->tokens[$tokenIndex][1] = '';
		if ($this->tokens[$this->context['savedIndex']][0] === \T_ELSE)
		{
			$j = 1 + $this->context['savedIndex'];
			while ($this->tokens[$j][0] === \T_WHITESPACE
			    || $this->tokens[$j][0] === \T_COMMENT
			    || $this->tokens[$j][0] === \T_DOC_COMMENT)
				++$j;
			if ($this->tokens[$j][0] === \T_IF)
			{
				$this->tokens[$j] = [\T_ELSEIF, 'elseif'];
				$j = $this->context['savedIndex'];
				$this->tokens[$j] = [\T_WHITESPACE, ''];
				if ($this->tokens[$j - 1][0] === \T_WHITESPACE)
					$this->tokens[$j - 1][1] = '';
				$this->unindentBlock($j, $this->i - 1);
				$this->tokens[$this->context['index']] = [\T_WHITESPACE, ''];
			}
		}
		$this->changed = \true;
	}
	protected function reset($php)
	{
		parent::reset($php);
		$this->braces  = 0;
		$this->context = [
			'braces'      => 0,
			'index'       => -1,
			'parent'      => [],
			'preventElse' => \false,
			'savedIndex'  => 0,
			'statements'  => 0
		];
	}
	protected function skipCondition()
	{
		$this->skipToString('(');
		$parens = 0;
		while (++$this->i < $this->cnt)
			if ($this->tokens[$this->i] === ')')
				if ($parens)
					--$parens;
				else
					break;
			elseif ($this->tokens[$this->i] === '(')
				++$parens;
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator;
use ReflectionClass;
use RuntimeException;
use s9e\TextFormatter\Configurator;
use s9e\TextFormatter\Configurator\Collections\TemplateParameterCollection;
use s9e\TextFormatter\Configurator\RendererGenerator;
use s9e\TextFormatter\Configurator\Traits\Configurable;
class Rendering
{
	use Configurable;
	protected $configurator;
	protected $engine;
	protected $parameters;
	public function __construct(Configurator $configurator)
	{
		$this->configurator = $configurator;
		$this->parameters   = new TemplateParameterCollection;
	}
	public function getAllParameters()
	{
		$params = [];
		foreach ($this->configurator->tags as $tag)
			if (isset($tag->template))
				foreach ($tag->template->getParameters() as $paramName)
					$params[$paramName] = '';
		$params = \iterator_to_array($this->parameters) + $params;
		\ksort($params);
		return $params;
	}
	public function getEngine()
	{
		if (!isset($this->engine))
			$this->setEngine('XSLT');
		return $this->engine;
	}
	public function getRenderer()
	{
		return $this->getEngine()->getRenderer($this);
	}
	public function getTemplates()
	{
		$templates = [
			'br' => '<br/>',
			'e'  => '',
			'i'  => '',
			'p'  => '<p><xsl:apply-templates/></p>',
			's'  => ''
		];
		foreach ($this->configurator->tags as $tagName => $tag)
			if (isset($tag->template))
				$templates[$tagName] = (string) $tag->template;
		\ksort($templates);
		return $templates;
	}
	public function setEngine($engine)
	{
		if (!($engine instanceof RendererGenerator))
		{
			$className  = 's9e\\TextFormatter\\Configurator\\RendererGenerators\\' . $engine;
			$reflection = new ReflectionClass($className);
			$engine = $reflection->newInstanceArgs(\array_slice(\func_get_args(), 1));
		}
		$this->engine = $engine;
		return $engine;
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator;
use ArrayAccess;
use DOMDocument;
use Iterator;
use s9e\TextFormatter\Configurator\Collections\RulesGeneratorList;
use s9e\TextFormatter\Configurator\Collections\TagCollection;
use s9e\TextFormatter\Configurator\Helpers\TemplateInspector;
use s9e\TextFormatter\Configurator\RulesGenerators\Interfaces\BooleanRulesGenerator;
use s9e\TextFormatter\Configurator\RulesGenerators\Interfaces\TargetedRulesGenerator;
use s9e\TextFormatter\Configurator\Traits\CollectionProxy;
class RulesGenerator implements ArrayAccess, Iterator
{
	use CollectionProxy;
	protected $collection;
	public function __construct()
	{
		$this->collection = new RulesGeneratorList;
		$this->collection->append('AutoCloseIfVoid');
		$this->collection->append('AutoReopenFormattingElements');
		$this->collection->append('BlockElementsCloseFormattingElements');
		$this->collection->append('BlockElementsFosterFormattingElements');
		$this->collection->append('DisableAutoLineBreaksIfNewLinesArePreserved');
		$this->collection->append('EnforceContentModels');
		$this->collection->append('EnforceOptionalEndTags');
		$this->collection->append('IgnoreTagsInCode');
		$this->collection->append('IgnoreTextIfDisallowed');
		$this->collection->append('IgnoreWhitespaceAroundBlockElements');
		$this->collection->append('TrimFirstLineInCodeBlocks');
	}
	public function getRules(TagCollection $tags)
	{
		$tagInspectors = $this->getTagInspectors($tags);
		return [
			'root' => $this->generateRootRules($tagInspectors),
			'tags' => $this->generateTagRules($tagInspectors)
		];
	}
	protected function generateTagRules(array $tagInspectors)
	{
		$rules = [];
		foreach ($tagInspectors as $tagName => $tagInspector)
			$rules[$tagName] = $this->generateRuleset($tagInspector, $tagInspectors);
		return $rules;
	}
	protected function generateRootRules(array $tagInspectors)
	{
		$rootInspector = new TemplateInspector('<div><xsl:apply-templates/></div>');
		$rules         = $this->generateRuleset($rootInspector, $tagInspectors);
		unset($rules['autoClose']);
		unset($rules['autoReopen']);
		unset($rules['breakParagraph']);
		unset($rules['closeAncestor']);
		unset($rules['closeParent']);
		unset($rules['fosterParent']);
		unset($rules['ignoreSurroundingWhitespace']);
		unset($rules['isTransparent']);
		unset($rules['requireAncestor']);
		unset($rules['requireParent']);
		return $rules;
	}
	protected function generateRuleset(TemplateInspector $srcInspector, array $trgInspectors)
	{
		$rules = [];
		foreach ($this->collection as $rulesGenerator)
		{
			if ($rulesGenerator instanceof BooleanRulesGenerator)
				foreach ($rulesGenerator->generateBooleanRules($srcInspector) as $ruleName => $bool)
					$rules[$ruleName] = $bool;
			if ($rulesGenerator instanceof TargetedRulesGenerator)
				foreach ($trgInspectors as $tagName => $trgInspector)
				{
					$targetedRules = $rulesGenerator->generateTargetedRules($srcInspector, $trgInspector);
					foreach ($targetedRules as $ruleName)
						$rules[$ruleName][] = $tagName;
				}
		}
		return $rules;
	}
	protected function getTagInspectors(TagCollection $tags)
	{
		$tagInspectors = [];
		foreach ($tags as $tagName => $tag)
		{
			$template = (isset($tag->template)) ? $tag->template : '<xsl:apply-templates/>';
			$tagInspectors[$tagName] = new TemplateInspector($template);
		}
		return $tagInspectors;
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\RulesGenerators;
use s9e\TextFormatter\Configurator\Helpers\TemplateInspector;
use s9e\TextFormatter\Configurator\RulesGenerators\Interfaces\BooleanRulesGenerator;
class AutoCloseIfVoid implements BooleanRulesGenerator
{
	public function generateBooleanRules(TemplateInspector $src)
	{
		return ($src->isVoid()) ? ['autoClose' => \true] : [];
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\RulesGenerators;
use s9e\TextFormatter\Configurator\Helpers\TemplateInspector;
use s9e\TextFormatter\Configurator\RulesGenerators\Interfaces\BooleanRulesGenerator;
class AutoReopenFormattingElements implements BooleanRulesGenerator
{
	public function generateBooleanRules(TemplateInspector $src)
	{
		return ($src->isFormattingElement()) ? ['autoReopen' => \true] : [];
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\RulesGenerators;
use s9e\TextFormatter\Configurator\Helpers\TemplateInspector;
use s9e\TextFormatter\Configurator\RulesGenerators\Interfaces\TargetedRulesGenerator;
class BlockElementsCloseFormattingElements implements TargetedRulesGenerator
{
	public function generateTargetedRules(TemplateInspector $src, TemplateInspector $trg)
	{
		return ($src->isBlock() && $trg->isFormattingElement()) ? ['closeParent'] : [];
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\RulesGenerators;
use s9e\TextFormatter\Configurator\Helpers\TemplateInspector;
use s9e\TextFormatter\Configurator\RulesGenerators\Interfaces\TargetedRulesGenerator;
class BlockElementsFosterFormattingElements implements TargetedRulesGenerator
{
	public function generateTargetedRules(TemplateInspector $src, TemplateInspector $trg)
	{
		return ($src->isBlock() && $src->isPassthrough() && $trg->isFormattingElement()) ? ['fosterParent'] : [];
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\RulesGenerators;
use s9e\TextFormatter\Configurator\Helpers\TemplateInspector;
use s9e\TextFormatter\Configurator\RulesGenerators\Interfaces\BooleanRulesGenerator;
class DisableAutoLineBreaksIfNewLinesArePreserved implements BooleanRulesGenerator
{
	public function generateBooleanRules(TemplateInspector $src)
	{
		return ($src->preservesNewLines()) ? ['disableAutoLineBreaks' => \true] : [];
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\RulesGenerators;
use s9e\TextFormatter\Configurator\Helpers\TemplateInspector;
use s9e\TextFormatter\Configurator\RulesGenerators\Interfaces\BooleanRulesGenerator;
use s9e\TextFormatter\Configurator\RulesGenerators\Interfaces\TargetedRulesGenerator;
class EnforceContentModels implements BooleanRulesGenerator, TargetedRulesGenerator
{
	protected $br;
	protected $span;
	public function __construct()
	{
		$this->br   = new TemplateInspector('<br/>');
		$this->span = new TemplateInspector('<span><xsl:apply-templates/></span>');
	}
	public function generateBooleanRules(TemplateInspector $src)
	{
		$rules = [];
		if ($src->isTransparent())
			$rules['isTransparent'] = \true;
		if (!$src->allowsChild($this->br))
		{
			$rules['preventLineBreaks'] = \true;
			$rules['suspendAutoLineBreaks'] = \true;
		}
		if (!$src->allowsDescendant($this->br))
		{
			$rules['disableAutoLineBreaks'] = \true;
			$rules['preventLineBreaks'] = \true;
		}
		return $rules;
	}
	public function generateTargetedRules(TemplateInspector $src, TemplateInspector $trg)
	{
		$rules = [];
		if ($src->allowsChild($trg))
			$rules[] = 'allowChild';
		if ($src->allowsDescendant($trg))
			$rules[] = 'allowDescendant';
		return $rules;
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\RulesGenerators;
use s9e\TextFormatter\Configurator\Helpers\TemplateInspector;
use s9e\TextFormatter\Configurator\RulesGenerators\Interfaces\TargetedRulesGenerator;
class EnforceOptionalEndTags implements TargetedRulesGenerator
{
	public function generateTargetedRules(TemplateInspector $src, TemplateInspector $trg)
	{
		return ($src->closesParent($trg)) ? ['closeParent'] : [];
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\RulesGenerators;
use s9e\TextFormatter\Configurator\Helpers\TemplateInspector;
use s9e\TextFormatter\Configurator\RulesGenerators\Interfaces\BooleanRulesGenerator;
class IgnoreTagsInCode implements BooleanRulesGenerator
{
	public function generateBooleanRules(TemplateInspector $src)
	{
		return ($src->evaluate('count(//code//xsl:apply-templates)')) ? ['ignoreTags' => \true] : [];
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\RulesGenerators;
use s9e\TextFormatter\Configurator\Helpers\TemplateInspector;
use s9e\TextFormatter\Configurator\RulesGenerators\Interfaces\BooleanRulesGenerator;
class IgnoreTextIfDisallowed implements BooleanRulesGenerator
{
	public function generateBooleanRules(TemplateInspector $src)
	{
		return ($src->allowsText()) ? [] : ['ignoreText' => \true];
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\RulesGenerators;
use s9e\TextFormatter\Configurator\Helpers\TemplateInspector;
use s9e\TextFormatter\Configurator\RulesGenerators\Interfaces\BooleanRulesGenerator;
class IgnoreWhitespaceAroundBlockElements implements BooleanRulesGenerator
{
	public function generateBooleanRules(TemplateInspector $src)
	{
		return ($src->isBlock()) ? ['ignoreSurroundingWhitespace' => \true] : [];
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\RulesGenerators;
use s9e\TextFormatter\Configurator\Helpers\TemplateInspector;
use s9e\TextFormatter\Configurator\RulesGenerators\Interfaces\BooleanRulesGenerator;
class TrimFirstLineInCodeBlocks implements BooleanRulesGenerator
{
	public function generateBooleanRules(TemplateInspector $src)
	{
		return ($src->evaluate('count(//pre//code//xsl:apply-templates)')) ? ['trimFirstLine' => \true] : [];
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator;
use ArrayAccess;
use Iterator;
use s9e\TextFormatter\Configurator\Collections\TemplateCheckList;
use s9e\TextFormatter\Configurator\Helpers\TemplateHelper;
use s9e\TextFormatter\Configurator\Items\Tag;
use s9e\TextFormatter\Configurator\Items\UnsafeTemplate;
use s9e\TextFormatter\Configurator\TemplateChecks\DisallowElementNS;
use s9e\TextFormatter\Configurator\TemplateChecks\DisallowXPathFunction;
use s9e\TextFormatter\Configurator\TemplateChecks\RestrictFlashScriptAccess;
use s9e\TextFormatter\Configurator\Traits\CollectionProxy;
class TemplateChecker implements ArrayAccess, Iterator
{
	use CollectionProxy;
	protected $collection;
	protected $disabled = \false;
	public function __construct()
	{
		$this->collection = new TemplateCheckList;
		$this->collection->append('DisallowAttributeSets');
		$this->collection->append('DisallowCopy');
		$this->collection->append('DisallowDisableOutputEscaping');
		$this->collection->append('DisallowDynamicAttributeNames');
		$this->collection->append('DisallowDynamicElementNames');
		$this->collection->append('DisallowObjectParamsWithGeneratedName');
		$this->collection->append('DisallowPHPTags');
		$this->collection->append('DisallowUnsafeCopyOf');
		$this->collection->append('DisallowUnsafeDynamicCSS');
		$this->collection->append('DisallowUnsafeDynamicJS');
		$this->collection->append('DisallowUnsafeDynamicURL');
		$this->collection->append(new DisallowElementNS('http://icl.com/saxon', 'output'));
		$this->collection->append(new DisallowXPathFunction('document'));
		$this->collection->append(new RestrictFlashScriptAccess('sameDomain', \true));
	}
	public function checkTag(Tag $tag)
	{
		if (isset($tag->template) && !($tag->template instanceof UnsafeTemplate))
		{
			$template = (string) $tag->template;
			$this->checkTemplate($template, $tag);
		}
	}
	public function checkTemplate($template, Tag $tag = \null)
	{
		if ($this->disabled)
			return;
		if (!isset($tag))
			$tag = new Tag;
		$dom = TemplateHelper::loadTemplate($template);
		foreach ($this->collection as $check)
			$check->check($dom->documentElement, $tag);
	}
	public function disable()
	{
		$this->disabled = \true;
	}
	public function enable()
	{
		$this->disabled = \false;
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\TemplateChecks;
use DOMAttr;
use DOMElement;
use DOMNode;
use DOMXPath;
use s9e\TextFormatter\Configurator\Exceptions\UnsafeTemplateException;
use s9e\TextFormatter\Configurator\Helpers\AVTHelper;
use s9e\TextFormatter\Configurator\Items\Attribute;
use s9e\TextFormatter\Configurator\Items\Tag;
use s9e\TextFormatter\Configurator\TemplateCheck;
abstract class AbstractDynamicContentCheck extends TemplateCheck
{
	protected $ignoreUnknownAttributes = \false;
	abstract protected function getNodes(DOMElement $template);
	abstract protected function isSafe(Attribute $attribute);
	public function check(DOMElement $template, Tag $tag)
	{
		foreach ($this->getNodes($template) as $node)
			$this->checkNode($node, $tag);
	}
	public function detectUnknownAttributes()
	{
		$this->ignoreUnknownAttributes = \false;
	}
	public function ignoreUnknownAttributes()
	{
		$this->ignoreUnknownAttributes = \true;
	}
	protected function checkAttribute(DOMNode $node, Tag $tag, $attrName)
	{
		if (!isset($tag->attributes[$attrName]))
		{
			if ($this->ignoreUnknownAttributes)
				return;
			throw new UnsafeTemplateException("Cannot assess the safety of unknown attribute '" . $attrName . "'", $node);
		}
		if (!$this->tagFiltersAttributes($tag) || !$this->isSafe($tag->attributes[$attrName]))
			throw new UnsafeTemplateException("Attribute '" . $attrName . "' is not properly sanitized to be used in this context", $node);
	}
	protected function checkAttributeNode(DOMAttr $attribute, Tag $tag)
	{
		foreach (AVTHelper::parse($attribute->value) as $token)
			if ($token[0] === 'expression')
				$this->checkExpression($attribute, $token[1], $tag);
	}
	protected function checkContext(DOMNode $node)
	{
		$xpath     = new DOMXPath($node->ownerDocument);
		$ancestors = $xpath->query('ancestor::xsl:for-each', $node);
		if ($ancestors->length)
			throw new UnsafeTemplateException("Cannot assess context due to '" . $ancestors->item(0)->nodeName . "'", $node);
	}
	protected function checkCopyOfNode(DOMElement $node, Tag $tag)
	{
		$this->checkSelectNode($node->getAttributeNode('select'), $tag);
	}
	protected function checkElementNode(DOMElement $element, Tag $tag)
	{
		$xpath = new DOMXPath($element->ownerDocument);
		$predicate = ($element->localName === 'attribute') ? '' : '[not(ancestor::xsl:attribute)]';
		$query = './/xsl:value-of' . $predicate;
		foreach ($xpath->query($query, $element) as $valueOf)
			$this->checkSelectNode($valueOf->getAttributeNode('select'), $tag);
		$query = './/xsl:apply-templates' . $predicate;
		foreach ($xpath->query($query, $element) as $applyTemplates)
			throw new UnsafeTemplateException('Cannot allow unfiltered data in this context', $applyTemplates);
	}
	protected function checkExpression(DOMNode $node, $expr, Tag $tag)
	{
		$this->checkContext($node);
		if (\preg_match('/^\\$(\\w+)$/', $expr, $m))
		{
			$this->checkVariable($node, $tag, $m[1]);
			return;
		}
		if ($this->isExpressionSafe($expr))
			return;
		if (\preg_match('/^@(\\w+)$/', $expr, $m))
		{
			$this->checkAttribute($node, $tag, $m[1]);
			return;
		}
		throw new UnsafeTemplateException("Cannot assess the safety of expression '" . $expr . "'", $node);
	}
	protected function checkNode(DOMNode $node, Tag $tag)
	{
		if ($node instanceof DOMAttr)
			$this->checkAttributeNode($node, $tag);
		elseif ($node instanceof DOMElement)
			if ($node->namespaceURI === self::XMLNS_XSL
			 && $node->localName    === 'copy-of')
				$this->checkCopyOfNode($node, $tag);
			else
				$this->checkElementNode($node, $tag);
	}
	protected function checkVariable(DOMNode $node, $tag, $qname)
	{
		$this->checkVariableDeclaration($node, $tag, 'xsl:param[@name="' . $qname . '"]');
		$this->checkVariableDeclaration($node, $tag, 'xsl:variable[@name="' . $qname . '"]');
	}
	protected function checkVariableDeclaration(DOMNode $node, $tag, $query)
	{
		$query = 'ancestor-or-self::*/preceding-sibling::' . $query . '[@select]';
		$xpath = new DOMXPath($node->ownerDocument);
		foreach ($xpath->query($query, $node) as $varNode)
		{
			try
			{
				$this->checkExpression($varNode, $varNode->getAttribute('select'), $tag);
			}
			catch (UnsafeTemplateException $e)
			{
				$e->setNode($node);
				throw $e;
			}
		}
	}
	protected function checkSelectNode(DOMAttr $select, Tag $tag)
	{
		$this->checkExpression($select, $select->value, $tag);
	}
	protected function isExpressionSafe($expr)
	{
		return \false;
	}
	protected function tagFiltersAttributes(Tag $tag)
	{
		return $tag->filterChain->containsCallback('s9e\\TextFormatter\\Parser::filterAttributes');
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\TemplateChecks;
use DOMElement;
use DOMNode;
use DOMXPath;
use s9e\TextFormatter\Configurator\Exceptions\UnsafeTemplateException;
use s9e\TextFormatter\Configurator\Items\Tag;
use s9e\TextFormatter\Configurator\TemplateCheck;
abstract class AbstractFlashRestriction extends TemplateCheck
{
	public $defaultSetting;
	public $maxSetting;
	public $onlyIfDynamic;
	protected $settingName;
	protected $settings;
	protected $template;
	public function __construct($maxSetting, $onlyIfDynamic = \false)
	{
		$this->maxSetting    = $maxSetting;
		$this->onlyIfDynamic = $onlyIfDynamic;
	}
	public function check(DOMElement $template, Tag $tag)
	{
		$this->template = $template;
		$this->checkEmbeds();
		$this->checkObjects();
	}
	protected function checkAttributes(DOMElement $embed)
	{
		$settingName = \strtolower($this->settingName);
		$useDefault  = \true;
		foreach ($embed->attributes as $attribute)
		{
			$attrName = \strtolower($attribute->name);
			if ($attrName === $settingName)
			{
				$this->checkSetting($attribute, $attribute->value);
				$useDefault = \false;
			}
		}
		if ($useDefault)
			$this->checkSetting($embed, $this->defaultSetting);
	}
	protected function checkDynamicAttributes(DOMElement $embed)
	{
		$settingName = \strtolower($this->settingName);
		foreach ($embed->getElementsByTagNameNS(self::XMLNS_XSL, 'attribute') as $attribute)
		{
			$attrName = \strtolower($attribute->getAttribute('name'));
			if ($attrName === $settingName)
				throw new UnsafeTemplateException('Cannot assess the safety of dynamic attributes', $attribute);
		}
	}
	protected function checkDynamicParams(DOMElement $object)
	{
		foreach ($this->getObjectParams($object) as $param)
			foreach ($param->getElementsByTagNameNS(self::XMLNS_XSL, 'attribute') as $attribute)
				if (\strtolower($attribute->getAttribute('name')) === 'value')
					throw new UnsafeTemplateException('Cannot assess the safety of dynamic attributes', $attribute);
	}
	protected function checkEmbeds()
	{
		foreach ($this->getElements('embed') as $embed)
		{
			$this->checkDynamicAttributes($embed);
			$this->checkAttributes($embed);
		}
	}
	protected function checkObjects()
	{
		foreach ($this->getElements('object') as $object)
		{
			$this->checkDynamicParams($object);
			$params = $this->getObjectParams($object);
			foreach ($params as $param)
				$this->checkSetting($param, $param->getAttribute('value'));
			if (empty($params))
				$this->checkSetting($object, $this->defaultSetting);
		}
	}
	protected function checkSetting(DOMNode $node, $setting)
	{
		if (!isset($this->settings[\strtolower($setting)]))
		{
			if (\preg_match('/(?<!\\{)\\{(?:\\{\\{)*(?!\\{)/', $setting))
				throw new UnsafeTemplateException('Cannot assess ' . $this->settingName . " setting '" . $setting . "'", $node);
			throw new UnsafeTemplateException('Unknown ' . $this->settingName . " value '" . $setting . "'", $node);
		}
		$value    = $this->settings[\strtolower($setting)];
		$maxValue = $this->settings[\strtolower($this->maxSetting)];
		if ($value > $maxValue)
			throw new UnsafeTemplateException($this->settingName . " setting '" . $setting . "' exceeds restricted value '" . $this->maxSetting . "'", $node);
	}
	protected function isDynamic(DOMElement $node)
	{
		if ($node->getElementsByTagNameNS(self::XMLNS_XSL, '*')->length)
			return \true;
		$xpath = new DOMXPath($node->ownerDocument);
		$query = './/@*[contains(., "{")]';
		foreach ($xpath->query($query, $node) as $attribute)
			if (\preg_match('/(?<!\\{)\\{(?:\\{\\{)*(?!\\{)/', $attribute->value))
				return \true;
		return \false;
	}
	protected function getElements($tagName)
	{
		$nodes = [];
		foreach ($this->template->ownerDocument->getElementsByTagName($tagName) as $node)
			if (!$this->onlyIfDynamic || $this->isDynamic($node))
				$nodes[] = $node;
		return $nodes;
	}
	protected function getObjectParams(DOMElement $object)
	{
		$params      = [];
		$settingName = \strtolower($this->settingName);
		foreach ($object->getElementsByTagName('param') as $param)
		{
			$paramName = \strtolower($param->getAttribute('name'));
			if ($paramName === $settingName && $param->parentNode->isSameNode($object))
				$params[] = $param;
		}
		return $params;
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\TemplateChecks;
use DOMElement;
use DOMXPath;
use s9e\TextFormatter\Configurator\Exceptions\UnsafeTemplateException;
use s9e\TextFormatter\Configurator\Items\Tag;
use s9e\TextFormatter\Configurator\TemplateCheck;
class DisallowAttributeSets extends TemplateCheck
{
	public function check(DOMElement $template, Tag $tag)
	{
		$xpath = new DOMXPath($template->ownerDocument);
		$nodes = $xpath->query('//@use-attribute-sets');
		if ($nodes->length)
			throw new UnsafeTemplateException('Cannot assess the safety of attribute sets', $nodes->item(0));
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\TemplateChecks;
use DOMElement;
use s9e\TextFormatter\Configurator\Exceptions\UnsafeTemplateException;
use s9e\TextFormatter\Configurator\Items\Tag;
use s9e\TextFormatter\Configurator\TemplateCheck;
class DisallowCopy extends TemplateCheck
{
	public function check(DOMElement $template, Tag $tag)
	{
		$nodes = $template->getElementsByTagNameNS(self::XMLNS_XSL, 'copy');
		$node  = $nodes->item(0);
		if ($node)
			throw new UnsafeTemplateException("Cannot assess the safety of an '" . $node->nodeName . "' element", $node);
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\TemplateChecks;
use DOMElement;
use DOMXPath;
use s9e\TextFormatter\Configurator\Exceptions\UnsafeTemplateException;
use s9e\TextFormatter\Configurator\Items\Tag;
use s9e\TextFormatter\Configurator\TemplateCheck;
class DisallowDisableOutputEscaping extends TemplateCheck
{
	public function check(DOMElement $template, Tag $tag)
	{
		$xpath = new DOMXPath($template->ownerDocument);
		$node  = $xpath->query('//@disable-output-escaping')->item(0);
		if ($node)
			throw new UnsafeTemplateException("The template contains a 'disable-output-escaping' attribute", $node);
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\TemplateChecks;
use DOMElement;
use s9e\TextFormatter\Configurator\Exceptions\UnsafeTemplateException;
use s9e\TextFormatter\Configurator\Items\Tag;
use s9e\TextFormatter\Configurator\TemplateCheck;
class DisallowDynamicAttributeNames extends TemplateCheck
{
	public function check(DOMElement $template, Tag $tag)
	{
		$nodes = $template->getElementsByTagNameNS(self::XMLNS_XSL, 'attribute');
		foreach ($nodes as $node)
			if (\strpos($node->getAttribute('name'), '{') !== \false)
				throw new UnsafeTemplateException('Dynamic <xsl:attribute/> names are disallowed', $node);
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\TemplateChecks;
use DOMElement;
use s9e\TextFormatter\Configurator\Exceptions\UnsafeTemplateException;
use s9e\TextFormatter\Configurator\Items\Tag;
use s9e\TextFormatter\Configurator\TemplateCheck;
class DisallowDynamicElementNames extends TemplateCheck
{
	public function check(DOMElement $template, Tag $tag)
	{
		$nodes = $template->getElementsByTagNameNS(self::XMLNS_XSL, 'element');
		foreach ($nodes as $node)
			if (\strpos($node->getAttribute('name'), '{') !== \false)
				throw new UnsafeTemplateException('Dynamic <xsl:element/> names are disallowed', $node);
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\TemplateChecks;
use DOMElement;
use s9e\TextFormatter\Configurator\Exceptions\UnsafeTemplateException;
use s9e\TextFormatter\Configurator\Items\Tag;
use s9e\TextFormatter\Configurator\TemplateCheck;
class DisallowElementNS extends TemplateCheck
{
	public $elName;
	public $namespaceURI;
	public function __construct($namespaceURI, $elName)
	{
		$this->namespaceURI  = $namespaceURI;
		$this->elName        = $elName;
	}
	public function check(DOMElement $template, Tag $tag)
	{
		$node = $template->getElementsByTagNameNS($this->namespaceURI, $this->elName)->item(0);
		if ($node)
			throw new UnsafeTemplateException("Element '" . $node->nodeName . "' is disallowed", $node);
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\TemplateChecks;
use DOMElement;
use DOMXPath;
use s9e\TextFormatter\Configurator\Exceptions\UnsafeTemplateException;
use s9e\TextFormatter\Configurator\Items\Tag;
use s9e\TextFormatter\Configurator\TemplateCheck;
class DisallowObjectParamsWithGeneratedName extends TemplateCheck
{
	public function check(DOMElement $template, Tag $tag)
	{
		$xpath = new DOMXPath($template->ownerDocument);
		$query = '//object//param[contains(@name, "{") or .//xsl:attribute[translate(@name, "NAME", "name") = "name"]]';
		$nodes = $xpath->query($query);
		foreach ($nodes as $node)
			throw new UnsafeTemplateException("A 'param' element with a suspect name has been found", $node);
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\TemplateChecks;
use DOMElement;
use DOMXPath;
use s9e\TextFormatter\Configurator\Exceptions\UnsafeTemplateException;
use s9e\TextFormatter\Configurator\Items\Tag;
use s9e\TextFormatter\Configurator\TemplateCheck;
class DisallowPHPTags extends TemplateCheck
{
	public function check(DOMElement $template, Tag $tag)
	{
		$queries = [
			'//processing-instruction()["php" = translate(name(),"HP","hp")]'
				=> 'PHP tags are not allowed in the template',
			'//script["php" = translate(@language,"HP","hp")]'
				=> 'PHP tags are not allowed in the template',
			'//xsl:processing-instruction["php" = translate(@name,"HP","hp")]'
				=> 'PHP tags are not allowed in the output',
			'//xsl:processing-instruction[contains(@name, "{")]'
				=> 'Dynamic processing instructions are not allowed',
		];
		$xpath = new DOMXPath($template->ownerDocument);
		foreach ($queries as $query => $error)
		{
			$nodes = $xpath->query($query); 
			if ($nodes->length)
				throw new UnsafeTemplateException($error, $nodes->item(0));
		}
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\TemplateChecks;
use DOMElement;
use s9e\TextFormatter\Configurator\Exceptions\UnsafeTemplateException;
use s9e\TextFormatter\Configurator\Items\Tag;
use s9e\TextFormatter\Configurator\TemplateCheck;
class DisallowUnsafeCopyOf extends TemplateCheck
{
	public function check(DOMElement $template, Tag $tag)
	{
		$nodes = $template->getElementsByTagNameNS(self::XMLNS_XSL, 'copy-of');
		foreach ($nodes as $node)
		{
			$expr = $node->getAttribute('select');
			if (!\preg_match('#^@[-\\w]*$#D', $expr))
				throw new UnsafeTemplateException("Cannot assess the safety of '" . $node->nodeName . "' select expression '" . $expr . "'", $node);
		}
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\TemplateChecks;
use DOMElement;
use DOMXPath;
use s9e\TextFormatter\Configurator\Exceptions\UnsafeTemplateException;
use s9e\TextFormatter\Configurator\Helpers\AVTHelper;
use s9e\TextFormatter\Configurator\Items\Tag;
use s9e\TextFormatter\Configurator\TemplateCheck;
class DisallowXPathFunction extends TemplateCheck
{
	public $funcName;
	public function __construct($funcName)
	{
		$this->funcName = $funcName;
	}
	public function check(DOMElement $template, Tag $tag)
	{
		$regexp = '#(?!<\\pL)' . \preg_quote($this->funcName, '#') . '\\s*\\(#iu';
		$regexp = \str_replace('\\:', '\\s*:\\s*', $regexp);
		foreach ($this->getExpressions($template) as $expr => $node)
		{
			$expr = \preg_replace('#([\'"]).*?\\1#s', '', $expr);
			if (\preg_match($regexp, $expr))
				throw new UnsafeTemplateException('An XPath expression uses the ' . $this->funcName . '() function', $node);
		}
	}
	protected function getExpressions(DOMElement $template)
	{
		$xpath = new DOMXPath($template->ownerDocument);
		$exprs = [];
		foreach ($xpath->query('//@*') as $attribute)
			if ($attribute->parentNode->namespaceURI === self::XMLNS_XSL)
			{
				$expr = $attribute->value;
				$exprs[$expr] = $attribute;
			}
			else
				foreach (AVTHelper::parse($attribute->value) as $token)
					if ($token[0] === 'expression')
						$exprs[$token[1]] = $attribute;
		return $exprs;
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\TemplateNormalizations;
use DOMElement;
use DOMNode;
abstract class AbstractChooseOptimization extends AbstractNormalization
{
	protected $choose;
	protected $queries = ['//xsl:choose'];
	protected function getAttributes(DOMElement $element)
	{
		$attributes = array();
		foreach ($element->attributes as $attribute)
		{
			$key = $attribute->namespaceURI . '#' . $attribute->nodeName;
			$attributes[$key] = $attribute->nodeValue;
		}
		return $attributes;
	}
	protected function getBranches()
	{
		$query = 'xsl:when|xsl:otherwise';
		return $this->xpath($query, $this->choose);
	}
	protected function hasOtherwise()
	{
		return (bool) $this->xpath->evaluate('count(xsl:otherwise)', $this->choose);
	}
	protected function isEmpty()
	{
		$query = 'count(xsl:when/node() | xsl:otherwise/node())';
		return !$this->xpath->evaluate($query, $this->choose);
	}
	protected function isEqualNode(DOMNode $node1, DOMNode $node2)
	{
		return ($node1->ownerDocument->saveXML($node1) === $node2->ownerDocument->saveXML($node2));
	}
	protected function isEqualTag(DOMElement $el1, DOMElement $el2)
	{
		return ($el1->namespaceURI === $el2->namespaceURI && $el1->nodeName === $el2->nodeName && $this->getAttributes($el1) === $this->getAttributes($el2));
	}
	protected function normalizeElement(DOMElement $element)
	{
		$this->choose = $element;
		$this->optimizeChoose();
	}
	abstract protected function optimizeChoose();
	protected function reset()
	{
		$this->choose = \null;
		parent::reset();
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\TemplateNormalizations;
use DOMAttr;
use DOMElement;
use s9e\TextFormatter\Configurator\Helpers\AVTHelper;
abstract class AbstractConstantFolding extends AbstractNormalization
{
	protected $queries = [
		'//*[namespace-uri() != $XSL]/@*[contains(.,"{")]',
		'//xsl:value-of'
	];
	abstract protected function getOptimizationPasses();
	protected function evaluateExpression($expr)
	{
		$original = $expr;
		foreach ($this->getOptimizationPasses() as $regexp => $methodName)
		{
			$regexp = \str_replace(' ', '\\s*', $regexp);
			$expr   = \preg_replace_callback($regexp, [$this, $methodName], $expr);
		}
		return ($expr === $original) ? $expr : $this->evaluateExpression(\trim($expr));
	}
	protected function normalizeAttribute(DOMAttr $attribute)
	{
		AVTHelper::replace(
			$attribute,
			function ($token)
			{
				if ($token[0] === 'expression')
					$token[1] = $this->evaluateExpression($token[1]);
				return $token;
			}
		);
	}
	protected function normalizeElement(DOMElement $valueOf)
	{
		$valueOf->setAttribute('select', $this->evaluateExpression($valueOf->getAttribute('select')));
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\TemplateNormalizations;
use DOMAttr;
class FixUnescapedCurlyBracesInHtmlAttributes extends AbstractNormalization
{
	protected $queries = ['//*[namespace-uri() != $XSL]/@*[contains(., "{")]'];
	protected function normalizeAttribute(DOMAttr $attribute)
	{
		$match = [
			'(\\b(?:do|else|(?:if|while)\\s*\\(.*?\\))\\s*\\{(?![{@]))',
			'((?<!\\{)(?:\\{\\{)*\\{(?!\\{)[^}]*+$)',
			'((?<!\\{)\\{\\s*(?:"[^"]*"|\'[^\']*\'|[a-z]\\w*(?:\\s|:\\s|:(?:["\']|\\w+\\s*,))))i'
		];
		$replace = [
			'$0{',
			'{$0',
			'{$0'
		];
		$attrValue        = \preg_replace($match, $replace, $attribute->value);
		$attribute->value = \htmlspecialchars($attrValue, \ENT_NOQUOTES, 'UTF-8');
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\TemplateNormalizations;
use DOMElement;
use DOMText;
class InlineAttributes extends AbstractNormalization
{
	protected $queries = ['//*[namespace-uri() != $XSL]/xsl:attribute'];
	protected function normalizeElement(DOMElement $element)
	{
		$value = '';
		foreach ($element->childNodes as $node)
			if ($node instanceof DOMText || $this->isXsl($node, 'text'))
				$value .= \preg_replace('([{}])', '$0$0', $node->textContent);
			elseif ($this->isXsl($node, 'value-of'))
				$value .= '{' . $node->getAttribute('select') . '}';
			else
				return;
		$element->parentNode->setAttribute($element->getAttribute('name'), $value);
		$element->parentNode->removeChild($element);
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\TemplateNormalizations;
use DOMNode;
class InlineCDATA extends AbstractNormalization
{
	protected $queries = ['//text()'];
	protected function normalizeNode(DOMNode $node)
	{
		if ($node->nodeType === \XML_CDATA_SECTION_NODE)
			$node->parentNode->replaceChild($this->createTextNode($node->textContent), $node);
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\TemplateNormalizations;
use DOMElement;
use DOMException;
class InlineElements extends AbstractNormalization
{
	protected $queries = ['//xsl:element'];
	protected function normalizeElement(DOMElement $element)
	{
		$elName = $element->getAttribute('name');
		$dom    = $this->ownerDocument;
		try
		{
			$newElement = ($element->hasAttribute('namespace'))
						? $dom->createElementNS($element->getAttribute('namespace'), $elName)
						: $dom->createElement($elName);
		}
		catch (DOMException $e)
		{
			return;
		}
		$element->parentNode->replaceChild($newElement, $element);
		while ($element->firstChild)
			$newElement->appendChild($element->removeChild($element->firstChild));
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\TemplateNormalizations;
use DOMAttr;
use DOMElement;
use DOMNode;
use s9e\TextFormatter\Configurator\Helpers\AVTHelper;
use s9e\TextFormatter\Configurator\Helpers\TemplateParser;
class InlineInferredValues extends AbstractNormalization
{
	protected $queries = ['//xsl:if', '//xsl:when'];
	protected function normalizeElement(DOMElement $element)
	{
		$map = TemplateParser::parseEqualityExpr($element->getAttribute('test'));
		if ($map === \false || \count($map) !== 1 || \count($map[\key($map)]) !== 1)
			return;
		$expr  = \key($map);
		$value = \end($map[$expr]);
		$this->inlineInferredValue($element, $expr, $value);
	}
	protected function inlineInferredValue(DOMNode $node, $expr, $value)
	{
		$query = './/xsl:value-of[@select="' . $expr . '"]';
		foreach ($this->xpath($query, $node) as $valueOf)
			$this->replaceValueOf($valueOf, $value);
		$query = './/*[namespace-uri() != $XSL]/@*[contains(., "{' . $expr . '}")]';
		foreach ($this->xpath($query, $node) as $attribute)
			$this->replaceAttribute($attribute, $expr, $value);
	}
	protected function replaceAttribute(DOMAttr $attribute, $expr, $value)
	{
		AVTHelper::replace(
			$attribute,
			function ($token) use ($expr, $value)
			{
				if ($token[0] === 'expression' && $token[1] === $expr)
					$token = ['literal', $value];
				return $token;
			}
		);
	}
	protected function replaceValueOf(DOMElement $valueOf, $value)
	{
		$valueOf->parentNode->replaceChild(
			$valueOf->ownerDocument->createTextNode($value),
			$valueOf
		);
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\TemplateNormalizations;
use DOMElement;
class InlineTextElements extends AbstractNormalization
{
	protected $queries = ['//xsl:text'];
	protected function isFollowedByText(DOMElement $element)
	{
		return ($element->nextSibling && $element->nextSibling->nodeType === \XML_TEXT_NODE);
	}
	protected function isPrecededByText(DOMElement $element)
	{
		return ($element->previousSibling && $element->previousSibling->nodeType === \XML_TEXT_NODE);
	}
	protected function normalizeElement(DOMElement $element)
	{
		if (\trim($element->textContent) === '')
			if (!$this->isFollowedByText($element) && !$this->isPrecededByText($element))
				return;
		$element->parentNode->replaceChild($this->createTextNode($element->textContent), $element);
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\TemplateNormalizations;
use DOMAttr;
use DOMElement;
use s9e\TextFormatter\Configurator\Helpers\AVTHelper;
class InlineXPathLiterals extends AbstractNormalization
{
	protected $queries = [
		'//xsl:value-of',
		'//*[namespace-uri() != $XSL]/@*[contains(., "{")]'
	];
	protected function getTextContent($expr)
	{
		$expr = \trim($expr);
		if (\preg_match('(^(?:\'[^\']*\'|"[^"]*")$)', $expr))
			return \substr($expr, 1, -1);
		if (\preg_match('(^0*([0-9]+(?:\\.[0-9]+)?)$)', $expr, $m))
			return $m[1];
		return \false;
	}
	protected function normalizeAttribute(DOMAttr $attribute)
	{
		AVTHelper::replace(
			$attribute,
			function ($token)
			{
				if ($token[0] === 'expression')
				{
					$textContent = $this->getTextContent($token[1]);
					if ($textContent !== \false)
						$token = ['literal', $textContent];
				}
				return $token;
			}
		);
	}
	protected function normalizeElement(DOMElement $element)
	{
		$textContent = $this->getTextContent($element->getAttribute('select'));
		if ($textContent !== \false)
			$element->parentNode->replaceChild($this->createTextNode($textContent), $element);
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\TemplateNormalizations;
use DOMAttr;
use s9e\TextFormatter\Configurator\Helpers\TemplateHelper;
class MinifyInlineCSS extends AbstractNormalization
{
	protected $queries = ['//*[namespace-uri() != $XSL]/@style'];
	protected function normalizeAttribute(DOMAttr $attribute)
	{
		$css = $attribute->nodeValue;
		if (!\preg_match('(\\{(?!@\\w+\\}))', $css))
			$attribute->nodeValue = $this->minify($css);
	}
	protected function minify($css)
	{
		$css = \trim($css, " \n\t;");
		$css = \preg_replace('(\\s*([,:;])\\s*)', '$1', $css);
		$css = \preg_replace_callback(
			'((?<=[\\s:])#[0-9a-f]{3,6})i',
			function ($m)
			{
				return \strtolower($m[0]);
			},
			$css
		);
		$css = \preg_replace('((?<=[\\s:])#([0-9a-f])\\1([0-9a-f])\\2([0-9a-f])\\3)', '#$1$2$3', $css);
		$css = \preg_replace('((?<=[\\s:])#f00\\b)', 'red', $css);
		$css = \preg_replace('((?<=[\\s:])0px\\b)', '0', $css);
		return $css;
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\TemplateNormalizations;
use DOMAttr;
use s9e\TextFormatter\Configurator\Helpers\AVTHelper;
use s9e\TextFormatter\Configurator\Helpers\XPathHelper;
class MinifyXPathExpressions extends AbstractNormalization
{
	protected $queries = ['//@*[contains(., " ")]'];
	protected function normalizeAttribute(DOMAttr $attribute)
	{
		$element = $attribute->parentNode;
		if (!$this->isXsl($element))
			$this->replaceAVT($attribute);
		elseif (\in_array($attribute->nodeName, ['match', 'select', 'test'], \true))
		{
			$expr = XPathHelper::minify($attribute->nodeValue);
			$element->setAttribute($attribute->nodeName, $expr);
		}
	}
	protected function replaceAVT(DOMAttr $attribute)
	{
		AVTHelper::replace(
			$attribute,
			function ($token)
			{
				if ($token[0] === 'expression')
					$token[1] = XPathHelper::minify($token[1]);
				return $token;
			}
		);
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\TemplateNormalizations;
use DOMAttr;
use DOMElement;
class NormalizeAttributeNames extends AbstractNormalization
{
	protected $queries = ['//@*', '//xsl:attribute[not(contains(@name, "{"))]'];
	protected function normalizeAttribute(DOMAttr $attribute)
	{
		$attrName = $this->lowercase($attribute->localName);
		if ($attrName !== $attribute->localName)
		{
			$attribute->parentNode->setAttribute($attrName, $attribute->value);
			$attribute->parentNode->removeAttributeNode($attribute);
		}
	}
	protected function normalizeElement(DOMElement $element)
	{
		$element->setAttribute('name', $this->lowercase($element->getAttribute('name')));
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\TemplateNormalizations;
use DOMElement;
class NormalizeElementNames extends AbstractNormalization
{
	protected $queries = [
		'//*[namespace-uri() != $XSL]',
		'//xsl:element[not(contains(@name, "{"))]'
	];
	protected function normalizeElement(DOMElement $element)
	{
		if ($this->isXsl($element, 'element'))
			$this->replaceXslElement($element);
		else
			$this->replaceElement($element);
	}
	protected function replaceElement(DOMElement $element)
	{
		$elName = $this->lowercase($element->localName);
		if ($elName === $element->localName)
			return;
		$newElement = (\is_null($element->namespaceURI))
		            ? $this->ownerDocument->createElement($elName)
		            : $this->ownerDocument->createElementNS($element->namespaceURI, $elName);
		while ($element->firstChild)
			$newElement->appendChild($element->removeChild($element->firstChild));
		foreach ($element->attributes as $attribute)
			$newElement->setAttributeNS(
				$attribute->namespaceURI,
				$attribute->nodeName,
				$attribute->value
			);
		$element->parentNode->replaceChild($newElement, $element);
	}
	protected function replaceXslElement(DOMElement $element)
	{
		$elName = $this->lowercase($element->getAttribute('name'));
		$element->setAttribute('name', $elName);
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\TemplateNormalizations;
use DOMAttr;
use DOMElement;
use s9e\TextFormatter\Configurator\Helpers\AVTHelper;
use s9e\TextFormatter\Configurator\Helpers\TemplateHelper;
use s9e\TextFormatter\Parser\AttributeFilters\UrlFilter;
class NormalizeUrls extends AbstractNormalization
{
	protected function getNodes()
	{
		return TemplateHelper::getURLNodes($this->ownerDocument);
	}
	protected function normalizeAttribute(DOMAttr $attribute)
	{
		$tokens = AVTHelper::parse(\trim($attribute->value));
		$attrValue = '';
		foreach ($tokens as $_f6b3b659)
		{
			list($type, $content) = $_f6b3b659;
			if ($type === 'literal')
				$attrValue .= UrlFilter::sanitizeUrl($content);
			else
				$attrValue .= '{' . $content . '}';
		}
		$attrValue = $this->unescapeBrackets($attrValue);
		$attribute->value = \htmlspecialchars($attrValue);
	}
	protected function normalizeElement(DOMElement $element)
	{
		$query = './/text()[normalize-space() != ""]';
		foreach ($this->xpath($query, $element) as $i => $node)
		{
			$value = UrlFilter::sanitizeUrl($node->nodeValue);
			if (!$i)
				$value = $this->unescapeBrackets(\ltrim($value));
			$node->nodeValue = $value;
		}
		if (isset($node))
			$node->nodeValue = \rtrim($node->nodeValue);
	}
	protected function unescapeBrackets($url)
	{
		return \preg_replace('#^(\\w+://)%5B([-\\w:._%]+)%5D#i', '$1[$2]', $url);
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\TemplateNormalizations;
use DOMElement;
class OptimizeConditionalAttributes extends AbstractNormalization
{
	protected $queries = ['//xsl:if[starts-with(@test, "@")][count(descendant::node()) = 2][xsl:attribute[@name = substring(../@test, 2)][xsl:value-of[@select = ../../@test]]]'];
	protected function normalizeElement(DOMElement $element)
	{
		$copyOf = $this->createElement('xsl:copy-of');
		$copyOf->setAttribute('select', $element->getAttribute('test'));
		$element->parentNode->replaceChild($copyOf, $element);
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\TemplateNormalizations;
use DOMElement;
class OptimizeConditionalValueOf extends AbstractNormalization
{
	protected $queries = ['//xsl:if[count(descendant::node()) = 1]/xsl:value-of'];
	protected function normalizeElement(DOMElement $element)
	{
		$if     = $element->parentNode;
		$test   = $if->getAttribute('test');
		$select = $element->getAttribute('select');
		if ($select !== $test || !\preg_match('#^@[-\\w]+$#D', $select))
			return;
		$if->parentNode->replaceChild($if->removeChild($element), $if);
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\TemplateNormalizations;
use DOMNode;
class PreserveSingleSpaces extends AbstractNormalization
{
	protected $queries = ['//text()[. = " "][not(parent::xsl:text)]'];
	protected function normalizeNode(DOMNode $node)
	{
		$node->parentNode->replaceChild($this->createElement('xsl:text', ' '), $node);
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\TemplateNormalizations;
use DOMNode;
class RemoveComments extends AbstractNormalization
{
	protected $queries = ['//comment()'];
	protected function normalizeNode(DOMNode $node)
	{
		$node->parentNode->removeChild($node);
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\TemplateNormalizations;
use DOMNode;
class RemoveInterElementWhitespace extends AbstractNormalization
{
	protected $queries = ['//text()[normalize-space() = ""][. != " "][not(parent::xsl:text)]'];
	protected function normalizeNode(DOMNode $node)
	{
		$node->parentNode->removeChild($node);
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2016 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\TemplateNormalizations;
use DOMAttr;
use DOMElement;
class RemoveLivePreviewAttributes extends AbstractNormalization
{
	protected $queries = [
		'//@*           [starts-with(name(), "data-s9e-livepreview-")]',
		'//xsl:attribute[starts-with(@name,  "data-s9e-livepreview-")]'
	];
	protected function normalizeAttribute(DOMAttr $attribute)
	{
		$attribute->parentNode->removeAttributeNode($attribute);
	}
	protected function normalizeElement(DOMElement $element)
	{
		$element->parentNode->removeChild($element);
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\TemplateNormalizations;
use DOMElement;
class SetRelNoreferrerOnTargetedLinks extends AbstractNormalization
{
	protected $queries = ['//a', '//area'];
	protected function addRelAttribute(DOMElement $element)
	{
		$rel = $element->getAttribute('rel');
		if (\preg_match('(\\S$)', $rel))
			$rel .= ' ';
		$rel .= 'noreferrer';
		$element->setAttribute('rel', $rel);
	}
	protected function linkTargetCanAccessOpener(DOMElement $element)
	{
		if (!$element->hasAttribute('target'))
			return \false;
		if (\preg_match('(\\bno(?:open|referr)er\\b)', $element->getAttribute('rel')))
			return \false;
		return \true;
	}
	protected function normalizeElement(DOMElement $element)
	{
		if ($this->linkTargetCanAccessOpener($element))
			$this->addRelAttribute($element);
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\TemplateNormalizations;
use DOMAttr;
use DOMElement;
use s9e\TextFormatter\Configurator\Helpers\AVTHelper;
class UninlineAttributes extends AbstractNormalization
{
	protected $queries = ['//*[namespace-uri() != $XSL]'];
	protected function normalizeElement(DOMElement $element)
	{
		$firstChild = $element->firstChild;
		while ($element->attributes->length > 0)
		{
			$attribute = $element->attributes->item(0);
			$element->insertBefore($this->uninlineAttribute($attribute), $firstChild);
		}
	}
	protected function uninlineAttribute(DOMAttr $attribute)
	{
		$xslAttribute  = $this->createElement('xsl:attribute');
		$xslAttribute->setAttribute('name', $attribute->nodeName);
		foreach (AVTHelper::parse($attribute->value) as $_f6b3b659)
		{
			list($type, $content) = $_f6b3b659;
			if ($type === 'expression')
			{
				$childNode = $this->createElement('xsl:value-of');
				$childNode->setAttribute('select', $content);
			}
			else
			{
				$childNode = $this->createElement('xsl:text');
				$childNode->appendChild($this->createTextNode($content));
			}
			$xslAttribute->appendChild($childNode);
		}
		$attribute->parentNode->removeAttributeNode($attribute);
		return $xslAttribute;
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license'); The MIT License
*/
namespace s9e\TextFormatter\Configurator;
use ArrayAccess;
use Iterator;
use s9e\TextFormatter\Configurator\Collections\TemplateNormalizationList;
use s9e\TextFormatter\Configurator\Helpers\TemplateHelper;
use s9e\TextFormatter\Configurator\Items\Tag;
use s9e\TextFormatter\Configurator\Traits\CollectionProxy;
class TemplateNormalizer implements ArrayAccess, Iterator
{
	use CollectionProxy;
	protected $collection;
	protected $maxIterations = 100;
	public function __construct()
	{
		$this->collection = new TemplateNormalizationList;
		$this->collection->append('PreserveSingleSpaces');
		$this->collection->append('RemoveComments');
		$this->collection->append('RemoveInterElementWhitespace');
		$this->collection->append('FixUnescapedCurlyBracesInHtmlAttributes');
		$this->collection->append('UninlineAttributes');
		$this->collection->append('FoldArithmeticConstants');
		$this->collection->append('FoldConstantXPathExpressions');
		$this->collection->append('InlineCDATA');
		$this->collection->append('InlineElements');
		$this->collection->append('InlineTextElements');
		$this->collection->append('InlineXPathLiterals');
		$this->collection->append('MinifyXPathExpressions');
		$this->collection->append('NormalizeAttributeNames');
		$this->collection->append('NormalizeElementNames');
		$this->collection->append('NormalizeUrls');
		$this->collection->append('OptimizeConditionalAttributes');
		$this->collection->append('OptimizeConditionalValueOf');
		$this->collection->append('OptimizeChoose');
		$this->collection->append('OptimizeChooseText');
		$this->collection->append('InlineAttributes');
		$this->collection->append('InlineInferredValues');
		$this->collection->append('SetRelNoreferrerOnTargetedLinks');
		$this->collection->append('MinifyInlineCSS');
	}
	public function normalizeTag(Tag $tag)
	{
		if (isset($tag->template) && !$tag->template->isNormalized())
			$tag->template->normalize($this);
	}
	public function normalizeTemplate($template)
	{
		$dom = TemplateHelper::loadTemplate($template);
		$i = 0;
		do
		{
			$old = $template;
			foreach ($this->collection as $k => $normalization)
			{
				if ($i > 0 && !empty($normalization->onlyOnce))
					continue;
				$normalization->normalize($dom->documentElement);
			}
			$template = TemplateHelper::saveTemplate($dom);
		}
		while (++$i < $this->maxIterations && $template !== $old);
		return $template;
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator;
use RuntimeException;
use s9e\TextFormatter\Configurator\Collections\HostnameList;
use s9e\TextFormatter\Configurator\Collections\SchemeList;
use s9e\TextFormatter\Configurator\Helpers\ConfigHelper;
class UrlConfig implements ConfigProvider
{
	protected $allowedSchemes;
	protected $disallowedHosts;
	protected $restrictedHosts;
	public function __construct()
	{
		$this->disallowedHosts = new HostnameList;
		$this->restrictedHosts = new HostnameList;
		$this->allowedSchemes   = new SchemeList;
		$this->allowedSchemes[] = 'http';
		$this->allowedSchemes[] = 'https';
	}
	public function asConfig()
	{
		return ConfigHelper::toArray(\get_object_vars($this));
	}
	public function allowScheme($scheme)
	{
		if (\strtolower($scheme) === 'javascript')
			throw new RuntimeException('The JavaScript URL scheme cannot be allowed');
		$this->allowedSchemes[] = $scheme;
	}
	public function disallowHost($host, $matchSubdomains = \true)
	{
		$this->disallowedHosts[] = $host;
		if ($matchSubdomains && \substr($host, 0, 1) !== '*')
			$this->disallowedHosts[] = '*.' . $host;
	}
	public function disallowScheme($scheme)
	{
		$this->allowedSchemes->remove($scheme);
	}
	public function getAllowedSchemes()
	{
		return \iterator_to_array($this->allowedSchemes);
	}
	public function restrictHost($host, $matchSubdomains = \true)
	{
		$this->restrictedHosts[] = $host;
		if ($matchSubdomains && \substr($host, 0, 1) !== '*')
			$this->restrictedHosts[] = '*.' . $host;
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\Collections;
use InvalidArgumentException;
use s9e\TextFormatter\Configurator\Helpers\RegexpParser;
use s9e\TextFormatter\Configurator\Items\AttributePreprocessor;
use s9e\TextFormatter\Configurator\Items\Regexp;
use s9e\TextFormatter\Configurator\JavaScript\RegexpConvertor;
use s9e\TextFormatter\Configurator\Validators\AttributeName;
class AttributePreprocessorCollection extends Collection
{
	public function add($attrName, $regexp)
	{
		$attrName = AttributeName::normalize($attrName);
		$k = \serialize([$attrName, $regexp]);
		$this->items[$k] = new AttributePreprocessor($regexp);
		return $this->items[$k];
	}
	public function key()
	{
		list($attrName) = \unserialize(\key($this->items));
		return $attrName;
	}
	public function merge($attributePreprocessors)
	{
		$error = \false;
		if ($attributePreprocessors instanceof AttributePreprocessorCollection)
			foreach ($attributePreprocessors as $attrName => $attributePreprocessor)
				$this->add($attrName, $attributePreprocessor->getRegexp());
		elseif (\is_array($attributePreprocessors))
		{
			foreach ($attributePreprocessors as $values)
			{
				if (!\is_array($values))
				{
					$error = \true;
					break;
				}
				list($attrName, $value) = $values;
				if ($value instanceof AttributePreprocessor)
					$value = $value->getRegexp();
				$this->add($attrName, $value);
			}
		}
		else
			$error = \true;
		if ($error)
			throw new InvalidArgumentException('merge() expects an instance of AttributePreprocessorCollection or a 2D array where each element is a [attribute name, regexp] pair');
	}
	public function asConfig()
	{
		$config = [];
		foreach ($this->items as $k => $ap)
		{
			list($attrName) = \unserialize($k);
			$config[] = [$attrName, $ap, $ap->getCaptureNames()];
		}
		return $config;
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\Collections;
use ArrayAccess;
use InvalidArgumentException;
use RuntimeException;
class NormalizedCollection extends Collection implements ArrayAccess
{
	protected $onDuplicateAction = 'error';
	public function asConfig()
	{
		$config = parent::asConfig();
		\ksort($config);
		return $config;
	}
	public function onDuplicate($action = \null)
	{
		$old = $this->onDuplicateAction;
		if (\func_num_args() && $action !== 'error' && $action !== 'ignore' && $action !== 'replace')
			throw new InvalidArgumentException("Invalid onDuplicate action '" . $action . "'. Expected: 'error', 'ignore' or 'replace'");
		$this->onDuplicateAction = $action;
		return $old;
	}
	protected function getAlreadyExistsException($key)
	{
		return new RuntimeException("Item '" . $key . "' already exists");
	}
	protected function getNotExistException($key)
	{
		return new RuntimeException("Item '" . $key . "' does not exist");
	}
	public function normalizeKey($key)
	{
		return $key;
	}
	public function normalizeValue($value)
	{
		return $value;
	}
	public function add($key, $value = \null)
	{
		if ($this->exists($key))
			if ($this->onDuplicateAction === 'ignore')
				return $this->get($key);
			elseif ($this->onDuplicateAction === 'error')
				throw $this->getAlreadyExistsException($key);
		return $this->set($key, $value);
	}
	public function contains($value)
	{
		return \in_array($this->normalizeValue($value), $this->items);
	}
	public function delete($key)
	{
		$key = $this->normalizeKey($key);
		unset($this->items[$key]);
	}
	public function exists($key)
	{
		$key = $this->normalizeKey($key);
		return \array_key_exists($key, $this->items);
	}
	public function get($key)
	{
		if (!$this->exists($key))
			throw $this->getNotExistException($key);
		$key = $this->normalizeKey($key);
		return $this->items[$key];
	}
	public function indexOf($value)
	{
		return \array_search($this->normalizeValue($value), $this->items);
	}
	public function set($key, $value)
	{
		$key = $this->normalizeKey($key);
		$this->items[$key] = $this->normalizeValue($value);
		return $this->items[$key];
	}
	public function offsetExists($offset)
	{
		return $this->exists($offset);
	}
	public function offsetGet($offset)
	{
		return $this->get($offset);
	}
	public function offsetSet($offset, $value)
	{
		$this->set($offset, $value);
	}
	public function offsetUnset($offset)
	{
		$this->delete($offset);
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\Collections;
use ArrayAccess;
use BadMethodCallException;
use InvalidArgumentException;
use RuntimeException;
use s9e\TextFormatter\Configurator\ConfigProvider;
use s9e\TextFormatter\Configurator\JavaScript\Dictionary;
use s9e\TextFormatter\Configurator\Validators\TagName;
use s9e\TextFormatter\Parser;
class Ruleset extends Collection implements ArrayAccess, ConfigProvider
{
	protected $rules = [
		'allowChild'                  => 'addTargetedRule',
		'allowDescendant'             => 'addTargetedRule',
		'autoClose'                   => 'addBooleanRule',
		'autoReopen'                  => 'addBooleanRule',
		'breakParagraph'              => 'addBooleanRule',
		'closeAncestor'               => 'addTargetedRule',
		'closeParent'                 => 'addTargetedRule',
		'createChild'                 => 'addTargetedRule',
		'createParagraphs'            => 'addBooleanRule',
		'denyChild'                   => 'addTargetedRule',
		'denyDescendant'              => 'addTargetedRule',
		'disableAutoLineBreaks'       => 'addBooleanRule',
		'enableAutoLineBreaks'        => 'addBooleanRule',
		'fosterParent'                => 'addTargetedRule',
		'ignoreSurroundingWhitespace' => 'addBooleanRule',
		'ignoreTags'                  => 'addBooleanRule',
		'ignoreText'                  => 'addBooleanRule',
		'isTransparent'               => 'addBooleanRule',
		'preventLineBreaks'           => 'addBooleanRule',
		'requireParent'               => 'addTargetedRule',
		'requireAncestor'             => 'addTargetedRule',
		'suspendAutoLineBreaks'       => 'addBooleanRule',
		'trimFirstLine'               => 'addBooleanRule'
	];
	public function __construct()
	{
		$this->clear();
	}
	public function __call($methodName, array $args)
	{
		if (!isset($this->rules[$methodName]))
			throw new BadMethodCallException("Undefined method '" . $methodName . "'");
		\array_unshift($args, $methodName);
		\call_user_func_array([$this, $this->rules[$methodName]], $args);
		return $this;
	}
	public function offsetExists($k)
	{
		return isset($this->items[$k]);
	}
	public function offsetGet($k)
	{
		return $this->items[$k];
	}
	public function offsetSet($k, $v)
	{
		throw new RuntimeException('Not supported');
	}
	public function offsetUnset($k)
	{
		return $this->remove($k);
	}
	public function asConfig()
	{
		$config = $this->items;
		unset($config['allowChild']);
		unset($config['allowDescendant']);
		unset($config['denyChild']);
		unset($config['denyDescendant']);
		unset($config['requireParent']);
		$bitValues = [
			'autoClose'                   => Parser::RULE_AUTO_CLOSE,
			'autoReopen'                  => Parser::RULE_AUTO_REOPEN,
			'breakParagraph'              => Parser::RULE_BREAK_PARAGRAPH,
			'createParagraphs'            => Parser::RULE_CREATE_PARAGRAPHS,
			'disableAutoLineBreaks'       => Parser::RULE_DISABLE_AUTO_BR,
			'enableAutoLineBreaks'        => Parser::RULE_ENABLE_AUTO_BR,
			'ignoreSurroundingWhitespace' => Parser::RULE_IGNORE_WHITESPACE,
			'ignoreTags'                  => Parser::RULE_IGNORE_TAGS,
			'ignoreText'                  => Parser::RULE_IGNORE_TEXT,
			'isTransparent'               => Parser::RULE_IS_TRANSPARENT,
			'preventLineBreaks'           => Parser::RULE_PREVENT_BR,
			'suspendAutoLineBreaks'       => Parser::RULE_SUSPEND_AUTO_BR,
			'trimFirstLine'               => Parser::RULE_TRIM_FIRST_LINE
		];
		$bitfield = 0;
		foreach ($bitValues as $ruleName => $bitValue)
		{
			if (!empty($config[$ruleName]))
				$bitfield |= $bitValue;
			unset($config[$ruleName]);
		}
		foreach (['closeAncestor', 'closeParent', 'fosterParent'] as $ruleName)
			if (isset($config[$ruleName]))
			{
				$targets = \array_fill_keys($config[$ruleName], 1);
				$config[$ruleName] = new Dictionary($targets);
			}
		$config['flags'] = $bitfield;
		return $config;
	}
	public function merge($rules, $overwrite = \true)
	{
		if (!\is_array($rules)
		 && !($rules instanceof self))
			throw new InvalidArgumentException('merge() expects an array or an instance of Ruleset');
		foreach ($rules as $action => $value)
			if (\is_array($value))
				foreach ($value as $tagName)
					$this->$action($tagName);
			elseif ($overwrite || !isset($this->items[$action]))
				$this->$action($value);
	}
	public function remove($type, $tagName = \null)
	{
		if (\preg_match('(^default(?:Child|Descendant)Rule)', $type))
			throw new RuntimeException('Cannot remove ' . $type);
		if (isset($tagName))
		{
			$tagName = TagName::normalize($tagName);
			if (isset($this->items[$type]))
			{
				$this->items[$type] = \array_diff(
					$this->items[$type],
					[$tagName]
				);
				if (empty($this->items[$type]))
					unset($this->items[$type]);
				else
					$this->items[$type] = \array_values($this->items[$type]);
			}
		}
		else
			unset($this->items[$type]);
	}
	protected function addBooleanRule($ruleName, $bool = \true)
	{
		if (!\is_bool($bool))
			throw new InvalidArgumentException($ruleName . '() expects a boolean');
		$this->items[$ruleName] = $bool;
	}
	protected function addTargetedRule($ruleName, $tagName)
	{
		$this->items[$ruleName][] = TagName::normalize($tagName);
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\Items;
abstract class Filter extends ProgrammableCallback
{
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\TemplateChecks;
use DOMElement;
use s9e\TextFormatter\Configurator\Helpers\TemplateHelper;
use s9e\TextFormatter\Configurator\Helpers\XPathHelper;
use s9e\TextFormatter\Configurator\Items\Attribute;
class DisallowUnsafeDynamicCSS extends AbstractDynamicContentCheck
{
	protected function getNodes(DOMElement $template)
	{
		return TemplateHelper::getCSSNodes($template->ownerDocument);
	}
	protected function isExpressionSafe($expr)
	{
		return XPathHelper::isExpressionNumeric($expr);
	}
	protected function isSafe(Attribute $attribute)
	{
		return $attribute->isSafeInCSS();
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\TemplateChecks;
use DOMElement;
use s9e\TextFormatter\Configurator\Helpers\XPathHelper;
use s9e\TextFormatter\Configurator\Helpers\TemplateHelper;
use s9e\TextFormatter\Configurator\Items\Attribute;
class DisallowUnsafeDynamicJS extends AbstractDynamicContentCheck
{
	protected function getNodes(DOMElement $template)
	{
		return TemplateHelper::getJSNodes($template->ownerDocument);
	}
	protected function isExpressionSafe($expr)
	{
		return XPathHelper::isExpressionNumeric($expr);
	}
	protected function isSafe(Attribute $attribute)
	{
		return $attribute->isSafeInJS();
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\TemplateChecks;
use DOMAttr;
use DOMElement;
use DOMText;
use s9e\TextFormatter\Configurator\Helpers\TemplateHelper;
use s9e\TextFormatter\Configurator\Items\Attribute;
use s9e\TextFormatter\Configurator\Items\Tag;
class DisallowUnsafeDynamicURL extends AbstractDynamicContentCheck
{
	protected $exceptionRegexp = '(^(?:(?!data|\\w*script)\\w+:|[^:]*/|#))i';
	protected function getNodes(DOMElement $template)
	{
		return TemplateHelper::getURLNodes($template->ownerDocument);
	}
	protected function isSafe(Attribute $attribute)
	{
		return $attribute->isSafeAsURL();
	}
	protected function checkAttributeNode(DOMAttr $attribute, Tag $tag)
	{
		if (\preg_match($this->exceptionRegexp, $attribute->value))
			return;
		parent::checkAttributeNode($attribute, $tag);
	}
	protected function checkElementNode(DOMElement $element, Tag $tag)
	{
		if ($element->firstChild
		 && $element->firstChild instanceof DOMText
		 && \preg_match($this->exceptionRegexp, $element->firstChild->textContent))
			return;
		parent::checkElementNode($element, $tag);
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\TemplateChecks;
class RestrictFlashScriptAccess extends AbstractFlashRestriction
{
	public $defaultSetting = 'sameDomain';
	protected $settingName = 'allowScriptAccess';
	protected $settings = [
		'always'     => 3,
		'samedomain' => 2,
		'never'      => 1
	];
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\TemplateNormalizations;
use s9e\TextFormatter\Utils\XPath;
class FoldArithmeticConstants extends AbstractConstantFolding
{
	protected function getOptimizationPasses()
	{
		$n = '-?\\.\\d++|-?\\d++(?:\\.\\d++)?';
		return [
			'(^[-+0-9\\s]+$)'                                            => 'foldOperation',
			'( \\+ 0(?! [^+\\)])|(?<![-\\w])0 \\+ )'                     => 'foldAdditiveIdentity',
			'(^((?>' . $n . ' [-+] )*)(' . $n . ') div (' . $n . '))'    => 'foldDivision',
			'(^((?>' . $n . ' [-+] )*)(' . $n . ') \\* (' . $n . '))'    => 'foldMultiplication',
			'(\\( (?:' . $n . ') (?>(?>[-+*]|div) (?:' . $n . ') )+\\))' => 'foldSubExpression',
			'((?<=[-+*\\(]|\\bdiv|^) \\( ([@$][-\\w]+|' . $n . ') \\) (?=[-+*\\)]|div|$))' => 'removeParentheses'
		];
	}
	protected function evaluateExpression($expr)
	{
		$expr = \preg_replace_callback(
			'(([\'"])(.*?)\\1)s',
			function ($m)
			{
				return $m[1] . \bin2hex($m[2]) . $m[1];
			},
			$expr
		);
		$expr = parent::evaluateExpression($expr);
		$expr = \preg_replace_callback(
			'(([\'"])(.*?)\\1)s',
			function ($m)
			{
				return $m[1] . \hex2bin($m[2]) . $m[1];
			},
			$expr
		);
		return $expr;
	}
	protected function foldAdditiveIdentity(array $m)
	{
		return '';
	}
	protected function foldDivision(array $m)
	{
		return $m[1] . XPath::export($m[2] / $m[3]);
	}
	protected function foldMultiplication(array $m)
	{
		return $m[1] . XPath::export($m[2] * $m[3]);
	}
	protected function foldOperation(array $m)
	{
		return XPath::export($this->xpath->evaluate($m[0]));
	}
	protected function foldSubExpression(array $m)
	{
		return '(' . $this->evaluateExpression(\trim(\substr($m[0], 1, -1))) . ')';
	}
	protected function removeParentheses(array $m)
	{
		return ' ' . $m[1] . ' ';
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\TemplateNormalizations;
use s9e\TextFormatter\Utils\XPath;
class FoldConstantXPathExpressions extends AbstractConstantFolding
{
	protected $supportedFunctions = [
		'ceiling',
		'concat',
		'contains',
		'floor',
		'normalize-space',
		'number',
		'round',
		'starts-with',
		'string',
		'string-length',
		'substring',
		'substring-after',
		'substring-before',
		'sum',
		'translate'
	];
	protected function getOptimizationPasses()
	{
		return [
			'(^(?:"[^"]*"|\'[^\']*\'|\\.[0-9]|[^"$&\'./:<=>@[\\]])++$)' => 'foldConstantXPathExpression'
		];
	}
	protected function canBeSerialized($value)
	{
		return (\is_string($value) || \is_integer($value) || \is_float($value));
	}
	protected function evaluate($expr)
	{
		$useErrors = \libxml_use_internal_errors(\true);
		$result    = $this->xpath->evaluate($expr);
		\libxml_use_internal_errors($useErrors);
		return $result;
	}
	protected function foldConstantXPathExpression(array $m)
	{
		$expr = $m[0];
		if ($this->isConstantExpression($expr))
		{
			$result = $this->evaluate($expr);
			if ($this->canBeSerialized($result))
			{
				$foldedExpr = XPath::export($result);
				if (\strlen($foldedExpr) < \strlen($expr))
					$expr = $foldedExpr;
			}
		}
		return $expr;
	}
	protected function isConstantExpression($expr)
	{
		$expr = \preg_replace('("[^"]*"|\'[^\']*\')', '', $expr);
		\preg_match_all('(\\w[-\\w]+(?=\\())', $expr, $m);
		if (\count(\array_diff($m[0], $this->supportedFunctions)) > 0)
			return \false;
		return !\preg_match('([^\\s\\-0-9a-z\\(-.]|\\.(?![0-9])|\\b[-a-z](?![-\\w]+\\())i', $expr);
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\TemplateNormalizations;
use DOMElement;
class OptimizeChoose extends AbstractChooseOptimization
{
	protected function adoptChildren(DOMElement $branch)
	{
		while ($branch->firstChild->firstChild)
			$branch->appendChild($branch->firstChild->removeChild($branch->firstChild->firstChild));
		$branch->removeChild($branch->firstChild);
	}
	protected function matchBranches($childType)
	{
		$branches = $this->getBranches();
		if (!isset($branches[0]->$childType))
			return \false;
		$childNode = $branches[0]->$childType;
		foreach ($branches as $branch)
			if (!isset($branch->$childType) || !$this->isEqualNode($childNode, $branch->$childType))
				return \false;
		return \true;
	}
	protected function matchOnlyChild()
	{
		$branches = $this->getBranches();
		if (!isset($branches[0]->firstChild))
			return \false;
		$firstChild = $branches[0]->firstChild;
		if ($this->isXsl($firstChild, 'choose'))
			return \false;
		foreach ($branches as $branch)
		{
			if ($branch->childNodes->length !== 1 || !($branch->firstChild instanceof DOMElement))
				return \false;
			if (!$this->isEqualTag($firstChild, $branch->firstChild))
				return \false;
		}
		return \true;
	}
	protected function moveFirstChildBefore()
	{
		$branches = $this->getBranches();
		$this->choose->parentNode->insertBefore(\array_pop($branches)->firstChild, $this->choose);
		foreach ($branches as $branch)
			$branch->removeChild($branch->firstChild);
	}
	protected function moveLastChildAfter()
	{
		$branches = $this->getBranches();
		$node     = \array_pop($branches)->lastChild;
		if (isset($this->choose->nextSibling))
			$this->choose->parentNode->insertBefore($node, $this->choose->nextSibling);
		else
			$this->choose->parentNode->appendChild($node);
		foreach ($branches as $branch)
			$branch->removeChild($branch->lastChild);
	}
	protected function optimizeChoose()
	{
		if ($this->hasOtherwise())
		{
			$this->optimizeCommonFirstChild();
			$this->optimizeCommonLastChild();
			$this->optimizeCommonOnlyChild();
			$this->optimizeEmptyOtherwise();
		}
		if ($this->isEmpty())
			$this->choose->parentNode->removeChild($this->choose);
		else
			$this->optimizeSingleBranch();
	}
	protected function optimizeCommonFirstChild()
	{
		while ($this->matchBranches('firstChild'))
			$this->moveFirstChildBefore();
	}
	protected function optimizeCommonLastChild()
	{
		while ($this->matchBranches('lastChild'))
			$this->moveLastChildAfter();
	}
	protected function optimizeCommonOnlyChild()
	{
		while ($this->matchOnlyChild())
			$this->reparentChild();
	}
	protected function optimizeEmptyOtherwise()
	{
		$query = 'xsl:otherwise[count(node()) = 0]';
		foreach ($this->xpath($query, $this->choose) as $otherwise)
			$this->choose->removeChild($otherwise);
	}
	protected function optimizeSingleBranch()
	{
		$query = 'count(xsl:when) = 1 and not(xsl:otherwise)';
		if (!$this->xpath->evaluate($query, $this->choose))
			return;
		$when = $this->xpath('xsl:when', $this->choose)[0];
		$if   = $this->createElement('xsl:if');
		$if->setAttribute('test', $when->getAttribute('test'));
		while ($when->firstChild)
			$if->appendChild($when->removeChild($when->firstChild));
		$this->choose->parentNode->replaceChild($if, $this->choose);
	}
	protected function reparentChild()
	{
		$branches  = $this->getBranches();
		$childNode = $branches[0]->firstChild->cloneNode();
		$childNode->appendChild($this->choose->parentNode->replaceChild($childNode, $this->choose));
		foreach ($branches as $branch)
			$this->adoptChildren($branch);
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\TemplateNormalizations;
use DOMElement;
use DOMText;
class OptimizeChooseText extends AbstractChooseOptimization
{
	protected function adjustTextNodes($childType, $pos, $len = \PHP_INT_MAX)
	{
		foreach ($this->getBranches() as $branch)
		{
			$node            = $branch->$childType;
			$node->nodeValue = \substr($node->textContent, $pos, $len);
		}
	}
	protected function getPrefixLength(array $strings)
	{
		$i      = 0;
		$len    = 0;
		$maxLen = \min(\array_map('strlen', $strings));
		while ($i < $maxLen)
		{
			$c = $strings[0][$i];
			foreach ($strings as $string)
				if ($string[$i] !== $c)
					break 2;
			$len = ++$i;
		}
		return $len;
	}
	protected function getTextContent($childType)
	{
		$strings = [];
		foreach ($this->getBranches() as $branch)
		{
			if (!($branch->$childType instanceof DOMText))
				return [];
			$strings[] = $branch->$childType->textContent;
		}
		return $strings;
	}
	protected function optimizeChoose()
	{
		if (!$this->hasOtherwise())
			return;
		$this->optimizeLeadingText();
		$this->optimizeTrailingText();
	}
	protected function optimizeLeadingText()
	{
		$strings = $this->getTextContent('firstChild');
		if (empty($strings))
			return;
		$len = $this->getPrefixLength($strings);
		if ($len)
		{
			$this->adjustTextNodes('firstChild', $len);
			$this->choose->parentNode->insertBefore(
				$this->createTextNode(\substr($strings[0], 0, $len)),
				$this->choose
			);
		}
	}
	protected function optimizeTrailingText()
	{
		$strings = $this->getTextContent('lastChild');
		if (empty($strings))
			return;
		$len = $this->getPrefixLength(\array_map('strrev', $strings));
		if ($len)
		{
			$this->adjustTextNodes('lastChild', 0, -$len);
			$this->choose->parentNode->insertBefore(
				$this->createTextNode(\substr($strings[0], -$len)),
				$this->choose->nextSibling
			);
		}
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\Collections;
use RuntimeException;
use s9e\TextFormatter\Configurator\Items\Attribute;
use s9e\TextFormatter\Configurator\Validators\AttributeName;
class AttributeCollection extends NormalizedCollection
{
	protected $onDuplicateAction = 'replace';
	protected function getAlreadyExistsException($key)
	{
		return new RuntimeException("Attribute '" . $key . "' already exists");
	}
	protected function getNotExistException($key)
	{
		return new RuntimeException("Attribute '" . $key . "' does not exist");
	}
	public function normalizeKey($key)
	{
		return AttributeName::normalize($key);
	}
	public function normalizeValue($value)
	{
		return ($value instanceof Attribute)
		     ? $value
		     : new Attribute($value);
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\Collections;
use InvalidArgumentException;
use s9e\TextFormatter\Configurator\Items\AttributeFilter;
class AttributeFilterCollection extends NormalizedCollection
{
	public function get($key)
	{
		$key = $this->normalizeKey($key);
		if (!$this->exists($key))
			if ($key[0] === '#')
				$this->set($key, self::getDefaultFilter(\substr($key, 1)));
			else
				$this->set($key, new AttributeFilter($key));
		$filter = parent::get($key);
		$filter = clone $filter;
		return $filter;
	}
	public static function getDefaultFilter($filterName)
	{
		$filterName = \ucfirst(\strtolower($filterName));
		$className  = 's9e\\TextFormatter\\Configurator\\Items\\AttributeFilters\\' . $filterName . 'Filter';
		if (!\class_exists($className))
			throw new InvalidArgumentException("Unknown attribute filter '" . $filterName . "'");
		return new $className;
	}
	public function normalizeKey($key)
	{
		if (\preg_match('/^#[a-z_0-9]+$/Di', $key))
			return \strtolower($key);
		if (\is_string($key) && \is_callable($key))
			return $key;
		throw new InvalidArgumentException("Invalid filter name '" . $key . "'");
	}
	public function normalizeValue($value)
	{
		if ($value instanceof AttributeFilter)
			return $value;
		if (\is_callable($value))
			return new AttributeFilter($value);
		throw new InvalidArgumentException('Argument 1 passed to ' . __METHOD__ . ' must be a valid callback or an instance of s9e\\TextFormatter\\Configurator\\Items\\AttributeFilter');
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\Collections;
use InvalidArgumentException;
class NormalizedList extends NormalizedCollection
{
	public function add($value, $void = \null)
	{
		return $this->append($value);
	}
	public function append($value)
	{
		$value = $this->normalizeValue($value);
		$this->items[] = $value;
		return $value;
	}
	public function delete($key)
	{
		parent::delete($key);
		$this->items = \array_values($this->items);
	}
	public function insert($offset, $value)
	{
		$offset = $this->normalizeKey($offset);
		$value  = $this->normalizeValue($value);
		\array_splice($this->items, $offset, 0, [$value]);
		return $value;
	}
	public function normalizeKey($key)
	{
		$normalizedKey = \filter_var(
			(\preg_match('(^-\\d+$)D', $key)) ? \count($this->items) + $key : $key,
			\FILTER_VALIDATE_INT,
			[
				'options' => [
					'min_range' => 0,
					'max_range' => \count($this->items)
				]
			]
		);
		if ($normalizedKey === \false)
			throw new InvalidArgumentException("Invalid offset '" . $key . "'");
		return $normalizedKey;
	}
	public function offsetSet($offset, $value)
	{
		if ($offset === \null)
			$this->append($value);
		else
			parent::offsetSet($offset, $value);
	}
	public function prepend($value)
	{
		$value = $this->normalizeValue($value);
		\array_unshift($this->items, $value);
		return $value;
	}
	public function remove($value)
	{
		$keys = \array_keys($this->items, $this->normalizeValue($value));
		foreach ($keys as $k)
			unset($this->items[$k]);
		$this->items = \array_values($this->items);
		return \count($keys);
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\Collections;
use InvalidArgumentException;
use RuntimeException;
use s9e\TextFormatter\Configurator;
use s9e\TextFormatter\Plugins\ConfiguratorBase;
class PluginCollection extends NormalizedCollection
{
	protected $configurator;
	public function __construct(Configurator $configurator)
	{
		$this->configurator = $configurator;
	}
	public function finalize()
	{
		foreach ($this->items as $plugin)
			$plugin->finalize();
	}
	public function normalizeKey($pluginName)
	{
		if (!\preg_match('#^[A-Z][A-Za-z_0-9]+$#D', $pluginName))
			throw new InvalidArgumentException("Invalid plugin name '" . $pluginName . "'");
		return $pluginName;
	}
	public function normalizeValue($value)
	{
		if (\is_string($value) && \class_exists($value))
			$value = new $value($this->configurator);
		if ($value instanceof ConfiguratorBase)
			return $value;
		throw new InvalidArgumentException('PluginCollection::normalizeValue() expects a class name or an object that implements s9e\\TextFormatter\\Plugins\\ConfiguratorBase');
	}
	public function load($pluginName, array $overrideProps = [])
	{
		$pluginName = $this->normalizeKey($pluginName);
		$className  = 's9e\\TextFormatter\\Plugins\\' . $pluginName . '\\Configurator';
		if (!\class_exists($className))
			throw new RuntimeException("Class '" . $className . "' does not exist");
		$plugin = new $className($this->configurator, $overrideProps);
		$this->set($pluginName, $plugin);
		return $plugin;
	}
	public function asConfig()
	{
		$plugins = parent::asConfig();
		foreach ($plugins as $pluginName => &$pluginConfig)
		{
			$plugin = $this->get($pluginName);
			$pluginConfig += $plugin->getBaseProperties();
			if ($pluginConfig['quickMatch'] === \false)
				unset($pluginConfig['quickMatch']);
			if (!isset($pluginConfig['regexp']))
				unset($pluginConfig['regexpLimit']);
			$className = 's9e\\TextFormatter\\Plugins\\' . $pluginName . '\\Parser';
			if ($pluginConfig['className'] === $className)
				unset($pluginConfig['className']);
		}
		unset($pluginConfig);
		return $plugins;
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\Collections;
use RuntimeException;
use s9e\TextFormatter\Configurator\Items\Tag;
use s9e\TextFormatter\Configurator\Validators\TagName;
class TagCollection extends NormalizedCollection
{
	protected $onDuplicateAction = 'replace';
	protected function getAlreadyExistsException($key)
	{
		return new RuntimeException("Tag '" . $key . "' already exists");
	}
	protected function getNotExistException($key)
	{
		return new RuntimeException("Tag '" . $key . "' does not exist");
	}
	public function normalizeKey($key)
	{
		return TagName::normalize($key);
	}
	public function normalizeValue($value)
	{
		return ($value instanceof Tag)
		     ? $value
		     : new Tag($value);
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\Collections;
use s9e\TextFormatter\Configurator\Validators\TemplateParameterName;
class TemplateParameterCollection extends NormalizedCollection
{
	public function normalizeKey($key)
	{
		return TemplateParameterName::normalize($key);
	}
	public function normalizeValue($value)
	{
		return (string) $value;
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\Items;
use s9e\TextFormatter\Configurator\Traits\TemplateSafeness;
class AttributeFilter extends Filter
{
	use TemplateSafeness;
	public function __construct($callback)
	{
		parent::__construct($callback);
		$this->resetParameters();
		$this->addParameterByName('attrValue');
	}
	public function isSafeInJS()
	{
		$safeCallbacks = [
			'urlencode',
			'strtotime',
			'rawurlencode'
		];
		if (\in_array($this->callback, $safeCallbacks, \true))
			return \true;
		return $this->isSafe('InJS');
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\Items;
class TagFilter extends Filter
{
	public function __construct($callback)
	{
		parent::__construct($callback);
		$this->resetParameters();
		$this->addParameterByName('tag');
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\Collections;
use InvalidArgumentException;
use s9e\TextFormatter\Configurator\Items\ProgrammableCallback;
abstract class FilterChain extends NormalizedList
{
	abstract protected function getFilterClassName();
	public function containsCallback(callable $callback)
	{
		$pc = new ProgrammableCallback($callback);
		$callback = $pc->getCallback();
		foreach ($this->items as $filter)
			if ($callback === $filter->getCallback())
				return \true;
		return \false;
	}
	public function normalizeValue($value)
	{
		$className  = $this->getFilterClassName();
		if ($value instanceof $className)
			return $value;
		if (!\is_callable($value))
			throw new InvalidArgumentException('Filter ' . \var_export($value, \true) . ' is neither callable nor an instance of ' . $className);
		return new $className($value);
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\Collections;
use s9e\TextFormatter\Configurator\Helpers\RegexpBuilder;
use s9e\TextFormatter\Configurator\Items\Regexp;
class HostnameList extends NormalizedList
{
	public function asConfig()
	{
		if (empty($this->items))
			return \null;
		return new Regexp($this->getRegexp());
	}
	public function getRegexp()
	{
		$hosts = [];
		foreach ($this->items as $host)
			$hosts[] = $this->normalizeHostmask($host);
		$regexp = RegexpBuilder::fromList(
			$hosts,
			[
				'specialChars' => [
					'*' => '.*',
					'^' => '^',
					'$' => '$'
				]
			]
		);
		return '/' . $regexp . '/DSis';
	}
	protected function normalizeHostmask($host)
	{
		if (\preg_match('#[\\x80-\xff]#', $host) && \function_exists('idn_to_ascii'))
		{
			$variant = (\defined('INTL_IDNA_VARIANT_UTS46')) ? \INTL_IDNA_VARIANT_UTS46 : 0;
			$host = \idn_to_ascii($host, 0, $variant);
		}
		if (\substr($host, 0, 1) === '*')
			$host = \ltrim($host, '*');
		else
			$host = '^' . $host;
		if (\substr($host, -1) === '*')
			$host = \rtrim($host, '*');
		else
			$host .= '$';
		return $host;
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\Collections;
use InvalidArgumentException;
use s9e\TextFormatter\Configurator\RulesGenerators\Interfaces\BooleanRulesGenerator;
use s9e\TextFormatter\Configurator\RulesGenerators\Interfaces\TargetedRulesGenerator;
class RulesGeneratorList extends NormalizedList
{
	public function normalizeValue($generator)
	{
		if (\is_string($generator))
		{
			$className = 's9e\\TextFormatter\\Configurator\\RulesGenerators\\' . $generator;
			if (\class_exists($className))
				$generator = new $className;
		}
		if (!($generator instanceof BooleanRulesGenerator)
		 && !($generator instanceof TargetedRulesGenerator))
			throw new InvalidArgumentException('Invalid rules generator ' . \var_export($generator, \true));
		return $generator;
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\Collections;
use InvalidArgumentException;
use s9e\TextFormatter\Configurator\Helpers\RegexpBuilder;
use s9e\TextFormatter\Configurator\Items\Regexp;
class SchemeList extends NormalizedList
{
	public function asConfig()
	{
		return new Regexp('/^' . RegexpBuilder::fromList($this->items) . '$/Di');
	}
	public function normalizeValue($scheme)
	{
		if (!\preg_match('#^[a-z][a-z0-9+\\-.]*$#Di', $scheme))
			throw new InvalidArgumentException("Invalid scheme name '" . $scheme . "'");
		return \strtolower($scheme);
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\Collections;
use s9e\TextFormatter\Configurator\TemplateCheck;
class TemplateCheckList extends NormalizedList
{
	public function normalizeValue($check)
	{
		if (!($check instanceof TemplateCheck))
		{
			$className = 's9e\\TextFormatter\\Configurator\\TemplateChecks\\' . $check;
			$check     = new $className;
		}
		return $check;
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\Collections;
use s9e\TextFormatter\Configurator\TemplateNormalizations\AbstractNormalization;
use s9e\TextFormatter\Configurator\TemplateNormalizations\Custom;
class TemplateNormalizationList extends NormalizedList
{
	public function normalizeValue($value)
	{
		if ($value instanceof AbstractNormalization)
			return $value;
		if (\is_callable($value))
			return new Custom($value);
		$className = 's9e\\TextFormatter\\Configurator\\TemplateNormalizations\\' . $value;
		return new $className;
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\Items\AttributeFilters;
use s9e\TextFormatter\Configurator\Items\AttributeFilter;
class UrlFilter extends AttributeFilter
{
	public function __construct()
	{
		parent::__construct('s9e\\TextFormatter\\Parser\\AttributeFilters\\UrlFilter::filter');
		$this->resetParameters();
		$this->addParameterByName('attrValue');
		$this->addParameterByName('urlConfig');
		$this->addParameterByName('logger');
		$this->setJS('UrlFilter.filter');
	}
	public function isSafeInCSS()
	{
		return \true;
	}
	public function isSafeInJS()
	{
		return \true;
	}
	public function isSafeAsURL()
	{
		return \true;
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\Collections;
class AttributeFilterChain extends FilterChain
{
	public function getFilterClassName()
	{
		return 's9e\\TextFormatter\\Configurator\\Items\\AttributeFilter';
	}
	public function normalizeValue($value)
	{
		if (\is_string($value) && \preg_match('(^#\\w+$)', $value))
			$value = AttributeFilterCollection::getDefaultFilter(\substr($value, 1));
		return parent::normalizeValue($value);
	}
}

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\Collections;
class TagFilterChain extends FilterChain
{
	public function getFilterClassName()
	{
		return 's9e\\TextFormatter\\Configurator\\Items\\TagFilter';
	}
}