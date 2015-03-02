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

use s9e\TextFormatter\Parser\BuiltInFilters;
use s9e\TextFormatter\Parser\Logger;

/**
* s9e\TextFormatter\Parser adapter
*/
class parser implements \phpbb\textformatter\parser
{
	/**
	* @var s9e\TextFormatter\Parser
	*/
	protected $parser;

	/**
	* @var phpbb\user User object, used for translating errors
	*/
	protected $user;

	/**
	* Constructor
	*
	* @param  \phpbb\cache\driver_interface $cache
	* @param  string $key Cache key
	* @param  \phpbb\user $user
	* @param  factory $factory
	* @return null
	*/
	public function __construct(\phpbb\cache\driver\driver_interface $cache, $key, \phpbb\user $user, factory $factory)
	{
		$this->user = $user;

		$parser = $cache->get($key);
		if (!$parser)
		{
			extract($factory->regenerate());
		}

		$this->parser = $parser;
	}

	/**
	* {@inheritdoc}
	*/
	public function parse($text)
	{
		return $this->parser->parse($text);
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
	}

	/**
	* {@inheritdoc}
	*
	* This will translate the log entries found in s9e\TextFormatter's logger into phpBB error
	* messages
	*/
	public function get_errors()
	{
		$errors = array();

		foreach ($this->parser->getLogger()->get() as $entry)
		{
			list($type, $msg, $context) = $entry;

			if ($msg === 'Tag limit exceeded')
			{
				if ($context['tagName'] === 'E')
				{
					$errors[] = $this->user->lang('TOO_MANY_SMILIES', $context['tagLimit']);
				}
				else if ($context['tagName'] === 'URL')
				{
					$errors[] = $this->user->lang('TOO_MANY_URLS', $context['tagLimit']);
				}
			}
			else if ($msg === 'MAX_FONT_SIZE_EXCEEDED')
			{
				$errors[] = $this->user->lang($msg, $context['max_size']);
			}
			else if (preg_match('/^MAX_(?:FLASH|IMG)_(HEIGHT|WIDTH)_EXCEEDED$/D', $msg, $m))
			{
				$errors[] = $this->user->lang($msg, $context['max_' . strtolower($m[1])]);
			}
			else if ($msg === 'Tag is disabled')
			{
				$name = strtolower($context['tag']->getName());
				$errors[] = $this->user->lang('UNAUTHORISED_BBCODE', '[' . $name . ']');
			}
			else if ($msg === 'UNABLE_GET_IMAGE_SIZE')
			{
				$errors[] = $this->user->lang[$msg];
			}
		}

		return array_unique($errors);
	}

	/**
	* Return the instance of s9e\TextFormatter\Parser used by this object
	*
	* @return s9e\TextFormatter\Parser
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

		if ($size < 1)
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
		$url = BuiltInFilters::filterUrl($url, $url_config, $logger);

		if ($url === false)
		{
			return false;
		}

		if ($max_height || $max_width)
		{
			$stats = @getimagesize($url);

			if ($stats === false)
			{
				$logger->err('UNABLE_GET_IMAGE_SIZE');

				return false;
			}

			if ($max_height && $max_height < $stats[1])
			{
				$logger->err('MAX_IMG_HEIGHT_EXCEEDED', array('max_height' => $max_height));

				return false;
			}

			if ($max_width && $max_width < $stats[0])
			{
				$logger->err('MAX_IMG_WIDTH_EXCEEDED', array('max_width' => $max_width));

				return false;
			}
		}

		return $url;
	}
}
