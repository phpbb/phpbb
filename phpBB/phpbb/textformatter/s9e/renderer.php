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
*/
class renderer implements \phpbb\textformatter\renderer_interface
{
	/**
	* @var \s9e\TextFormatter\Plugins\Censor\Helper
	*/
	protected $censor;

	/**
	* @var \phpbb\event\dispatcher_interface
	*/
	protected $dispatcher;

	/**
	* @var mention_helper
	*/
	protected $mention_helper;

	/**
	* @var quote_helper
	*/
	protected $quote_helper;

	/**
	* @var \s9e\TextFormatter\Renderer
	*/
	protected $renderer;

	/**
	* @var bool Status of the viewcensors option
	*/
	protected $viewcensors = false;

	/**
	* @var bool Status of the viewimg option
	*/
	protected $viewimg = false;

	/**
	* @var bool Status of the viewsmilies option
	*/
	protected $viewsmilies = false;

	/**
	* @var bool Whether the user is allowed to use mentions
	*/
	protected $usemention = false;

	/**
	* Constructor
	*
	* @param \phpbb\cache\driver\driver_interface $cache
	* @param string $cache_dir Path to the cache dir
	* @param string $key Cache key
	* @param factory $factory
	* @param \phpbb\event\dispatcher_interface $dispatcher
	*/
	public function __construct(\phpbb\cache\driver\driver_interface $cache, $cache_dir, $key, factory $factory, \phpbb\event\dispatcher_interface $dispatcher)
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
				$renderer = new $class;
			}
			if (isset($renderer_data['censor']))
			{
				$censor = $renderer_data['censor'];
			}
		}
		if (!isset($renderer))
		{
			$objects  = $factory->regenerate();
			$renderer = $objects['renderer'];
		}

		if (isset($censor))
		{
			$this->censor = $censor;
		}
		$this->dispatcher = $dispatcher;
		$this->renderer = $renderer;
		$renderer = $this;

		/**
		* Configure the renderer service
		*
		* @event core.text_formatter_s9e_renderer_setup
		* @var \phpbb\textformatter\s9e\renderer renderer This renderer service
		* @since 3.2.0-a1
		* @psalm-ignore-var
		*/
		$vars = ['renderer'];
		extract($dispatcher->trigger_event('core.text_formatter_s9e_renderer_setup', compact($vars)));
	}

	/**
	* Configure the mention_helper object used to display extended information in mentions
	*
	* @param  mention_helper $mention_helper
	*/
	public function configure_mention_helper(mention_helper $mention_helper)
	{
		$this->mention_helper = $mention_helper;
	}

	/**
	* Configure the quote_helper object used to display extended information in quotes
	*
	* @param  quote_helper $quote_helper
	*/
	public function configure_quote_helper(quote_helper $quote_helper)
	{
		$this->quote_helper = $quote_helper;
	}

	/**
	* Automatically set the smilies path based on config
	*
	* @param  \phpbb\config\config $config
	* @param  \phpbb\path_helper   $path_helper
	* @return void
	*/
	public function configure_smilies_path(\phpbb\config\config $config, \phpbb\path_helper $path_helper)
	{
		/**
		* @see smiley_text()
		*/
		$root_path = $path_helper->get_web_root_path();

		$this->set_smilies_path($root_path . $config['smilies_path']);
	}

	/**
	* Configure this renderer as per the user's settings
	*
	* Should set the locale as well as the viewcensor/viewimg/viewsmilies options.
	*
	* @param  \phpbb\user          $user
	* @param  \phpbb\config\config $config
	* @param  \phpbb\auth\auth     $auth
	* @return void
	*/
	public function configure_user(\phpbb\user $user, \phpbb\config\config $config, \phpbb\auth\auth $auth)
	{
		$censor = $user->optionget('viewcensors') || !$config['allow_nocensors'] || !$auth->acl_get('u_chgcensors');

		$this->set_viewcensors($censor);
		$this->set_viewimg($user->optionget('viewimg'));
		$this->set_viewsmilies($user->optionget('viewsmilies'));
		$this->set_usemention($config['allow_mentions'] && $auth->acl_get('u_mention'));

		// Set the stylesheet parameters
		foreach (array_keys($this->renderer->getParameters()) as $param_name)
		{
			if (strpos($param_name, 'L_') === 0)
			{
				// L_FOO is set to $user->lang('FOO')
				$this->renderer->setParameter($param_name, $user->lang(substr($param_name, 2)));
			}
		}

		// Set this user's style id and other parameters
		$this->renderer->setParameters(array(
			'S_IS_BOT'			=> $user->data['is_bot'] ?? false,
			'S_REGISTERED_USER'	=> $user->data['is_registered'] ?? false,
			'S_USER_LOGGED_IN'	=> ($user->data['user_id'] != ANONYMOUS),
			'STYLE_ID'			=> $user->style['style_id'],
		));
	}

	/**
	* Return the instance of s9e\TextFormatter\Renderer used by this object
	*
	* @return \s9e\TextFormatter\Renderer
	*/
	public function get_renderer()
	{
		return $this->renderer;
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
		if (isset($this->mention_helper))
		{
			$text = $this->mention_helper->inject_metadata($text);
		}

		if (isset($this->quote_helper))
		{
			$text = $this->quote_helper->inject_metadata($text);
		}

		$renderer = $this;

		/**
		* Modify a parsed text before it is rendered
		*
		* @event core.text_formatter_s9e_render_before
		* @var \phpbb\textformatter\s9e\renderer renderer This renderer service
		* @var string text The parsed text, in its XML form
		* @since 3.2.0-a1
		* @psalm-ignore-var
		*/
		$vars = ['renderer', 'text'];
		extract($this->dispatcher->trigger_event('core.text_formatter_s9e_render_before', compact($vars)));

		$html = $this->renderer->render($text);
		if (isset($this->censor) && $this->viewcensors)
		{
			$html = $this->censor->censorHtml($html, true);
		}

		/**
		* Modify a rendered text
		*
		* @event core.text_formatter_s9e_render_after
		* @var string html The rendered text's HTML
		* @var \phpbb\textformatter\s9e\renderer renderer This renderer service
		* @since 3.2.0-a1
		* @psalm-ignore-var
		*/
		$vars = ['html', 'renderer'];
		extract($this->dispatcher->trigger_event('core.text_formatter_s9e_render_after', compact($vars)));

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

	/**
	* {@inheritdoc}
	*/
	public function set_usemention($value)
	{
		$this->usemention = $value;
		$this->renderer->setParameter('S_VIEWMENTION', $value);
	}
}
