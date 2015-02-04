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

/**
* s9e\TextFormatter\Renderer adapter
* @package phpBB3
*/
class renderer extends \phpbb\textformatter\renderer
{
	/**
	* @var s9e\TextFormatter\Renderer
	*/
	protected $renderer;

	/**
	* @var bool Status of the viewcensors option
	*/
	protected $viewcensors = false;

	/**
	* @var bool Status of the viewflash option
	*/
	protected $viewflash = false;

	/**
	* @var bool Status of the viewimg option
	*/
	protected $viewimg = false;

	/**
	* @var bool Status of the viewsmilies option
	*/
	protected $viewsmilies = false;

	/**
	* Constructor
	*
	* @param  \phpbb\cache\driver\driver_interface $cache
	* @param  string $cache_dir Path to the cache dir
	* @param  string $key Cache key
	* @param  factory $factory
	* @return null
	*/
	public function __construct(\phpbb\cache\driver\driver_interface $cache, $cache_dir, $key, factory $factory)
	{
		$renderer_data = $cache->get($key);

		if ($renderer_data)
		{
			$class = $renderer_data['class'];

			if (!class_exists($class, false))
			{
				// Try to load the renderer class from its cache file
				$cache_file = $cache_dir . $class . '.php';

				if (file_exists($cache_file))
				{
					include($cache_file);
				}
			}

			if (class_exists($class, false))
			{
				$renderer = unserialize($renderer_data['renderer']);
			}
		}

		if (!isset($renderer))
		{
			list(, $renderer) = $factory->regenerate();
		}

		$this->renderer = $renderer;
	}

	/**
	* {@inheritdoc}
	*/
	public function configure_user(\phpbb\user $user, \phpbb\config\config $config, \phpbb\auth\auth $auth)
	{
		parent::configure_user($user, $config, $auth);

		// Set the stylesheet parameters
		foreach (array_keys($this->renderer->getParameters()) as $param_name)
		{
			if (substr($param_name, 0, 2) === 'L_')
			{
				// L_FOO is set to $user->lang('FOO')
				$this->renderer->setParameter($param_name, $user->lang(substr($param_name, 2)));
			}
		}

		// Set the style id
		$this->renderer->setParameter('STYLE_ID', $user->style['style_id']);
	}

	/**
	* {@inheritdoc}
	*/
	public function get_viewcensors()
	{
		return $this->viewcensors;
	}

	/**
	* {@inheritdoc}
	*/
	public function get_viewflash()
	{
		return $this->viewflash;
	}

	/**
	* {@inheritdoc}
	*/
	public function get_viewimg()
	{
		return $this->viewimg;
	}

	/**
	* {@inheritdoc}
	*/
	public function get_viewsmilies()
	{
		return $this->viewsmilies;
	}

	/**
	* {@inheritdoc}
	*/
	public function render($text)
	{
		$html = $this->renderer->render($text);

		/**
		* @see bbcode::bbcode_second_pass_code()
		*/
		$html = preg_replace_callback(
			'#(<code>)(.*?)(</code>)#is',
			function ($captures)
			{
				$code = $captures[2];

				$code = str_replace("\t", '&nbsp; &nbsp;', $code);
				$code = str_replace('  ', '&nbsp; ', $code);
				$code = str_replace('  ', ' &nbsp;', $code);
				$code = str_replace("\n ", "\n&nbsp;", $code);

				// keep space at the beginning
				if (!empty($code) && $code[0] == ' ')
				{
					$code = '&nbsp;' . substr($code, 1);
				}

				// remove newline at the beginning
				if (!empty($code) && $code[0] == "\n")
				{
					$code = substr($code, 1);
				}

				return $captures[1] . $code . $captures[3];
			},
			$html
		);

		return $html;
	}

	/**
	* {@inheritdoc}
	*/
	public function set_smilies_path($path)
	{
		$this->renderer->setParameter('T_SMILIES_PATH', $path);
	}

	/**
	* {@inheritdoc}
	*/
	public function set_viewcensors($value)
	{
		$this->viewcensors = $value;
		$this->renderer->setParameter('S_VIEWCENSORS', $value);
	}

	/**
	* {@inheritdoc}
	*/
	public function set_viewflash($value)
	{
		$this->viewflash = $value;
		$this->renderer->setParameter('S_VIEWFLASH', $value);
	}

	/**
	* {@inheritdoc}
	*/
	public function set_viewimg($value)
	{
		$this->viewimg = $value;
		$this->renderer->setParameter('S_VIEWIMG', $value);
	}

	/**
	* {@inheritdoc}
	*/
	public function set_viewsmilies($value)
	{
		$this->viewsmilies = $value;
		$this->renderer->setParameter('S_VIEWSMILIES', $value);
	}
}
