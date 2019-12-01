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
	* @param \phpbb\cache\driver_interface $cache
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
		foreach ($this->parser->getLogger()->getLogs() as $entry)
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
			else if (preg_match('/^MAX_(?:FLASH|IMG)_(HEIGHT|WIDTH)_EXCEEDED$/D', $msg, $m))
			{
				$errors[] = array($msg, $context['max_' . strtolower($m[1])]);
			}
			else if ($msg === 'Tag is disabled')
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
	* Filter a flash object's height
	*
	* @see bbcode_firstpass::bbcode_flash()
	*
	* @param  string  $height
	* @param  integer $max_height
	* @param  Logger  $logger
	* @return mixed              Original value if valid, FALSE otherwise
	*/
	static public function filter_flash_height($height, $max_height, Logger $logger)
	{
		if ($max_height && $height > $max_height)
		{
			$logger->err('MAX_FLASH_HEIGHT_EXCEEDED', array('max_height' => $max_height));

			return false;
		}

		return $height;
	}

	/**
	* Filter a flash object's width
	*
	* @see bbcode_firstpass::bbcode_flash()
	*
	* @param  string  $width
	* @param  integer $max_width
	* @param  Logger  $logger
	* @return mixed              Original value if valid, FALSE otherwise
	*/
	static public function filter_flash_width($width, $max_width, Logger $logger)
	{
		if ($max_width && $width > $max_width)
		{
			$logger->err('MAX_FLASH_WIDTH_EXCEEDED', array('max_width' => $max_width));

			return false;
		}

		return $width;
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
	static public function filter_font_size($size, $max_size, Logger $logger)
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
	* @param  integer $max_height Maximum height allowed
	* @param  integer $max_width  Maximum width allowed
	* @return string|bool         Original value if valid, FALSE otherwise
	*/
	static public function filter_img_url($url, array $url_config, Logger $logger, $max_height, $max_width)
	{
		// Validate the URL
		$url = UrlFilter::filter($url, $url_config, $logger);
		if ($url === false)
		{
			return false;
		}

		if ($max_height || $max_width)
		{
			$imagesize = new \FastImageSize\FastImageSize();
			$size_info = $imagesize->getImageSize($url);
			if ($size_info === false)
			{
				$logger->err('UNABLE_GET_IMAGE_SIZE');
				return false;
			}

			if ($max_height && $max_height < $size_info['height'])
			{
				$logger->err('MAX_IMG_HEIGHT_EXCEEDED', array('max_height' => $max_height));
				return false;
			}

			if ($max_width && $max_width < $size_info['width'])
			{
				$logger->err('MAX_IMG_WIDTH_EXCEEDED', array('max_width' => $max_width));
				return false;
			}
		}

		return $url;
	}
}
