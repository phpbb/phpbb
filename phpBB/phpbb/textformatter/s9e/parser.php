<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

namespace phpbb\textformatter\s9e;

use s9e\TextFormatter\Parser\AttributeFilters\UrlFilter;
use s9e\TextFormatter\Parser\Logger;
use s9e\TextFormatter\Parser\Tag;

/**
* s9e\TextFormatter\Parser adapter
*/
class parser implements \phpbb\textformatter\parser_interface
{
	/**
	* @var \phpbb\event\dispatcher_interface
	*/
	protected $dispatcher;

	/**
	* @var \s9e\TextFormatter\Parser
	*/
	protected $parser;

	/**
	* Constructor
	*
	* @param \phpbb\cache\driver\driver_interface $cache
	* @param string $key Cache key
	* @param factory $factory
	* @param \phpbb\event\dispatcher_interface $dispatcher
	*/
	public function __construct(\phpbb\cache\driver\driver_interface $cache, $key, factory $factory, \phpbb\event\dispatcher_interface $dispatcher)
	{
		$parser = $cache->get($key);
		if (!$parser)
		{
			$objects = $factory->regenerate();
			$parser  = $objects['parser'];
		}

		$this->dispatcher = $dispatcher;
		$this->parser = $parser;

		$parser = $this;

		/**
		* Configure the parser service
		*
		* Can be used to:
		*  - toggle features or BBCodes
		*  - register variables or custom parsers in the s9e\TextFormatter parser
		*  - configure the s9e\TextFormatter parser's runtime settings
		*
		* @event core.text_formatter_s9e_parser_setup
		* @var \phpbb\textformatter\s9e\parser parser This parser service
		* @since 3.2.0-a1
		*/
		$vars = array('parser');
		extract($dispatcher->trigger_event('core.text_formatter_s9e_parser_setup', compact($vars)));
	}

	/**
	* {@inheritdoc}
	*/
	public function parse($text)
	{
		$parser = $this;

		/**
		* Modify a text before it is parsed
		*
		* @event core.text_formatter_s9e_parse_before
		* @var \phpbb\textformatter\s9e\parser parser This parser service
		* @var string text The original text
		* @since 3.2.0-a1
		*/
		$vars = array('parser', 'text');
		extract($this->dispatcher->trigger_event('core.text_formatter_s9e_parse_before', compact($vars)));

		$xml = $this->parser->parse($text);

		/**
		* Modify a parsed text in its XML form
		*
		* @event core.text_formatter_s9e_parse_after
		* @var \phpbb\textformatter\s9e\parser parser This parser service
		* @var string xml The parsed text, in XML
		* @since 3.2.0-a1
		*/
		$vars = array('parser', 'xml');
		extract($this->dispatcher->trigger_event('core.text_formatter_s9e_parse_after', compact($vars)));

		return $xml;
	}

	/**
	* {@inheritdoc}
	*/
	public function disable_bbcode($name)
	{
		$this->parser->disableTag(strtoupper($name));
	}

	/**
	* {@inheritdoc}
	*/
	public function disable_bbcodes()
	{
		$this->parser->disablePlugin('BBCodes');
	}

	/**
	* {@inheritdoc}
	*/
	public function disable_censor()
	{
		$this->parser->disablePlugin('Censor');
	}

	/**
	* {@inheritdoc}
	*/
	public function disable_magic_url()
	{
		$this->parser->disablePlugin('Autoemail');
		$this->parser->disablePlugin('Autolink');
	}

	/**
	* {@inheritdoc}
	*/
	public function disable_smilies()
	{
		$this->parser->disablePlugin('Emoticons');
		$this->parser->disablePlugin('Emoji');
	}

	/**
	* {@inheritdoc}
	*/
	public function enable_bbcode($name)
	{
		$this->parser->enableTag(strtoupper($name));
	}

	/**
	* {@inheritdoc}
	*/
	public function enable_bbcodes()
	{
		$this->parser->enablePlugin('BBCodes');
	}

	/**
	* {@inheritdoc}
	*/
	public function enable_censor()
	{
		$this->parser->enablePlugin('Censor');
	}

	/**
	* {@inheritdoc}
	*/
	public function enable_magic_url()
	{
		$this->parser->enablePlugin('Autoemail');
		$this->parser->enablePlugin('Autolink');
	}

	/**
	* {@inheritdoc}
	*/
	public function enable_smilies()
	{
		$this->parser->enablePlugin('Emoticons');
		$this->parser->enablePlugin('Emoji');
	}

	/**
	* {@inheritdoc}
	*
	* This will convert the log entries found in s9e\TextFormatter's logger into phpBB error
	* messages
	*/
	public function get_errors()
	{
		$errors = array();
		$entries = $this->parser->getLogger()->getLogs();

		foreach ($entries as $entry)
		{
			list(, $msg, $context) = $entry;

			if ($msg === 'Tag limit exceeded')
			{
				if ($context['tagName'] === 'E')
				{
					$errors[] = array('TOO_MANY_SMILIES', $context['tagLimit']);
				}
				else if ($context['tagName'] === 'URL')
				{
					$errors[] = array('TOO_MANY_URLS', $context['tagLimit']);
				}
			}
			else if ($msg === 'MAX_FONT_SIZE_EXCEEDED')
			{
				$errors[] = array($msg, $context['max_size']);
			}
			else if (preg_match('/^MAX_IMG_(HEIGHT|WIDTH)_EXCEEDED$/D', $msg, $m))
			{
				$errors[] = array($msg, $context['max_' . strtolower($m[1])]);
			}
			else if ($msg === 'Tag is disabled' && $this->is_a_bbcode($context['tag']))
			{
				$name = strtolower($context['tag']->getName());
				$errors[] = array('UNAUTHORISED_BBCODE', '[' . $name . ']');
			}
			else if ($msg === 'UNABLE_GET_IMAGE_SIZE')
			{
				$errors[] = array($msg);
			}
		}

		// Deduplicate error messages. array_unique() only works on strings so we have to serialize
		if (!empty($errors))
		{
			$errors = array_map('unserialize', array_unique(array_map('serialize', $errors)));
		}

		$parser = $this;

		/**
		* Modify error messages generated by the s9e\TextFormatter's logger
		*
		* @event core.text_formatter_s9e_get_errors
		* @var parser	parser		This parser service
		* @var array	entries		s9e\TextFormatter's logger entries
		* @var array	errors		Error arrays with language key and optional arguments
		* @since 3.2.10-RC1
		* @since 3.3.1-RC1
		*/
		$vars = [
			'parser',
			'entries',
			'errors',
		];
		extract($this->dispatcher->trigger_event('core.text_formatter_s9e_get_errors', compact($vars)));

		return $errors;
	}

	/**
	* Return the instance of s9e\TextFormatter\Parser used by this object
	*
	* @return \s9e\TextFormatter\Parser
	*/
	public function get_parser()
	{
		return $this->parser;
	}

	/**
	* {@inheritdoc}
	*/
	public function set_var($name, $value)
	{
		if ($name === 'max_smilies')
		{
			$this->parser->setTagLimit('E', $value ?: PHP_INT_MAX);
		}
		else if ($name === 'max_urls')
		{
			$this->parser->setTagLimit('URL', $value ?: PHP_INT_MAX);
		}
		else
		{
			$this->parser->registeredVars[$name] = $value;
		}
	}

	/**
	* {@inheritdoc}
	*/
	public function set_vars(array $vars)
	{
		foreach ($vars as $name => $value)
		{
			$this->set_var($name, $value);
		}
	}

	/**
	* Filter the value used in a [size] BBCode
	*
	* @see bbcode_firstpass::bbcode_size()
	*
	* @param  string  $size     Original size
	* @param  integer $max_size Maximum allowed size
	* @param  Logger  $logger
	* @return mixed             Original value if valid, FALSE otherwise
	*/
	public static function filter_font_size($size, $max_size, Logger $logger)
	{
		if ($max_size && $size > $max_size)
		{
			$logger->err('MAX_FONT_SIZE_EXCEEDED', array('max_size' => $max_size));

			return false;
		}

		if ($size < 1 || !is_numeric($size))
		{
			return false;
		}

		return $size;
	}

	/**
	* Filter an image's URL to enforce restrictions on its dimensions
	*
	* @see bbcode_firstpass::bbcode_img()
	*
	* @param  string  $url        Original URL
	* @param  array   $url_config Config used by the URL filter
	* @param  Logger  $logger
	*
	* @return string|bool         Original value if valid, FALSE otherwise
	*/
	public static function filter_img_url($url, array $url_config, Logger $logger)
	{
		// Validate the URL
		$url = UrlFilter::filter($url, $url_config, $logger);
		if ($url === false)
		{
			return false;
		}

		return $url;
	}

	/**
	* Test whether given tag consumes text that looks like BBCode-styled markup
	*
	* @param  Tag  $tag Original tag
	* @return bool
	*/
	protected function is_a_bbcode(Tag $tag)
	{
		if ($tag->getLen() < 3)
		{
			return false;
		}
		$markup = substr($this->parser->getText(), $tag->getPos(), $tag->getLen());

		return (bool) preg_match('(^\\[\\w++.*?\\]$)s', $markup);
	}
}
