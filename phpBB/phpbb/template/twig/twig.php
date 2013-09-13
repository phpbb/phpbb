<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Twig Template class.
* @package phpBB3
*/
class phpbb_template_twig extends phpbb_template_base
{
	/**
	* Path of the cache directory for the template
	*
	* Cannot be changed during runtime.
	*
	* @var string
	*/
	private $cachepath = '';

	/**
	* phpBB root path
	* @var string
	*/
	protected $phpbb_root_path;

	/**
	* PHP file extension
	* @var string
	*/
	protected $php_ext;

	/**
	* phpBB config instance
	* @var phpbb_config
	*/
	protected $config;

	/**
	* Current user
	* @var phpbb_user
	*/
	protected $user;

	/**
	* Extension manager.
	*
	* @var phpbb_extension_manager
	*/
	protected $extension_manager;

	/**
	* Twig Environment
	*
	* @var Twig_Environment
	*/
	protected $twig;

	/**
	* Constructor.
	*
	* @param string $phpbb_root_path phpBB root path
	* @param string $php_ext php extension (typically 'php')
	* @param phpbb_config $config
	* @param phpbb_user $user
	* @param phpbb_template_context $context template context
	* @param phpbb_extension_manager $extension_manager extension manager, if null then template events will not be invoked
	* @param string $adm_relative_path relative path to adm directory
	*/
	public function __construct($phpbb_root_path, $php_ext, $config, $user, phpbb_template_context $context, phpbb_extension_manager $extension_manager = null, $adm_relative_path = null)
	{
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->config = $config;
		$this->user = $user;
		$this->context = $context;
		$this->extension_manager = $extension_manager;

		$this->cachepath = $phpbb_root_path . 'cache/twig/';

		// Initiate the loader, __main__ namespace paths will be setup later in set_style_names()
		$loader = new phpbb_template_twig_loader('');

		$this->twig = new phpbb_template_twig_environment(
			$this->config,
			($this->extension_manager) ? $this->extension_manager->all_enabled() : array(),
			$this->phpbb_root_path,
			$loader,
			array(
				'cache'			=> (defined('IN_INSTALL')) ? false : $this->cachepath,
				'debug'			=> defined('DEBUG'),
				'auto_reload'	=> (bool) $this->config['load_tplcompile'],
				'autoescape'	=> false,
			)
		);

		$this->twig->addExtension(
			new phpbb_template_twig_extension(
				$this->context,
				$this->user
			)
		);

		$lexer = new phpbb_template_twig_lexer($this->twig);

		$this->twig->setLexer($lexer);

		// Add admin namespace
		if ($adm_relative_path !== null && is_dir($this->phpbb_root_path . $adm_relative_path . 'style/'))
		{
			$this->twig->getLoader()->setPaths($this->phpbb_root_path . $adm_relative_path . 'style/', 'admin');
		}
	}

	/**
	* Clear the cache
	*
	* @return phpbb_template
	*/
	public function clear_cache()
	{
		if (is_dir($this->cachepath))
		{
			$this->twig->clearCacheFiles();
		}

		return $this;
	}

	/**
	* Get the style tree of the style preferred by the current user
	*
	* @return array Style tree, most specific first
	*/
	public function get_user_style()
	{
		$style_list = array(
			$this->user->style['style_path'],
		);

		if ($this->user->style['style_parent_id'])
		{
			$style_list = array_merge($style_list, array_reverse(explode('/', $this->user->style['style_parent_tree'])));
		}

		return $style_list;
	}

	/**
	* Set style location based on (current) user's chosen style.
	*
	* @param array $style_directories The directories to add style paths for
	* 	E.g. array('ext/foo/bar/styles', 'styles')
	* 	Default: array('styles') (phpBB's style directory)
	* @return phpbb_template $this
	*/
	public function set_style($style_directories = array('styles'))
	{
		if ($style_directories !== array('styles') && $this->twig->getLoader()->getPaths('core') === array())
		{
			// We should set up the core styles path since not already setup
			$this->set_style();
		}

		$names = $this->get_user_style();

		$paths = array();
		foreach ($style_directories as $directory)
		{
			foreach ($names as $name)
			{
				$path = $this->phpbb_root_path . trim($directory, '/') . "/{$name}/";
				$template_path = $path . 'template/';

				if (is_dir($template_path))
				{
					// Add the base style directory as a safe directory
					$this->twig->getLoader()->addSafeDirectory($path);

					$paths[] = $template_path;
				}
			}
		}

		// If we're setting up the main phpBB styles directory and the core
		// namespace isn't setup yet, we will set it up now
		if ($style_directories === array('styles') && $this->twig->getLoader()->getPaths('core') === array())
		{
			// Set up the core style paths namespace
			$this->twig->getLoader()->setPaths($paths, 'core');
		}

		$this->set_custom_style($names, $paths);

		return $this;
	}

	/**
	* Set custom style location (able to use directory outside of phpBB).
	*
	* Note: Templates are still compiled to phpBB's cache directory.
	*
	* @param string|array $names Array of names or string of name of template(s) in inheritance tree order, used by extensions.
	* @param string|array or string $paths Array of style paths, relative to current root directory
	* @return phpbb_template $this
	*/
	public function set_custom_style($names, $paths)
	{
		$paths = (is_string($paths)) ? array($paths) : $paths;
		$names = (is_string($names)) ? array($names) : $names;

		// Set as __main__ namespace
		$this->twig->getLoader()->setPaths($paths);

		// Add all namespaces for all extensions
		if ($this->extension_manager instanceof phpbb_extension_manager)
		{
			$names[] = 'all';

			foreach ($this->extension_manager->all_enabled() as $ext_namespace => $ext_path)
			{
				// namespaces cannot contain /
				$namespace = str_replace('/', '_', $ext_namespace);
				$paths = array();

				foreach ($names as $style_name)
				{
					$ext_style_path = $ext_path . 'styles/' . $style_name . '/';
					$ext_style_template_path = $ext_style_path . 'template/';

					if (is_dir($ext_style_template_path))
					{
						// Add the base style directory as a safe directory
						$this->twig->getLoader()->addSafeDirectory($ext_style_path);

						$paths[] = $ext_style_template_path;
					}
				}

				$this->twig->getLoader()->setPaths($paths, $namespace);
			}
		}

		return $this;
	}

	/**
	* Display a template for provided handle.
	*
	* The template will be loaded and compiled, if necessary, first.
	*
	* This function calls hooks.
	*
	* @param string $handle Handle to display
	* @return phpbb_template $this
	*/
	public function display($handle)
	{
		$result = $this->call_hook($handle, __FUNCTION__);
		if ($result !== false)
		{
			return $result[0];
		}

		$this->twig->display($this->get_filename_from_handle($handle), $this->get_template_vars());

		return $this;
	}

	/**
	* Display the handle and assign the output to a template variable
	* or return the compiled result.
	*
	* @param string $handle Handle to operate on
	* @param string $template_var Template variable to assign compiled handle to
	* @param bool $return_content If true return compiled handle, otherwise assign to $template_var
	* @return phpbb_template|string if $return_content is true return string of the compiled handle, otherwise return $this
	*/
	public function assign_display($handle, $template_var = '', $return_content = true)
	{
		if ($return_content)
		{
			return $this->twig->render($this->get_filename_from_handle($handle), $this->get_template_vars());
		}

		$this->assign_var($template_var, $this->twig->render($this->get_filename_from_handle($handle, $this->get_template_vars())));

		return $this;
	}

	/**
	* Get template vars in a format Twig will use (from the context)
	*
	* @return array
	*/
	protected function get_template_vars()
	{
		$context_vars = $this->context->get_data_ref();

		$vars = array_merge(
			$context_vars['.'][0], // To get normal vars
			array(
				'definition'	=> new phpbb_template_twig_definition(),
				'user'			=> $this->user,
				'loops'			=> $context_vars, // To get loops
			)
		);

		// cleanup
		unset($vars['loops']['.']);

		return $vars;
	}

	/**
	* Get path to template for handle (required for BBCode parser)
	*
	* @return string
	*/
	public function get_source_file_for_handle($handle)
	{
		return $this->twig->getLoader()->getCacheKey($this->get_filename_from_handle($handle));
	}
}
