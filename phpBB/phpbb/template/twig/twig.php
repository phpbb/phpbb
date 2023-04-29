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

namespace phpbb\template\twig;

use phpbb\template\exception\user_object_not_available;

/**
* Twig Template class.
*/
class twig extends \phpbb\template\base
{
	/**
	 * Path of the cache directory for the template
	 * Cannot be changed during runtime.
	 *
	 * @var string
	 */
	private $cachepath = '';

	/** @var \phpbb\path_helper */
	protected $path_helper;

	/** @var string phpBB root path */
	protected $phpbb_root_path;

	/** @var string php File extension	*/
	protected $php_ext;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\extension\manager */
	protected $extension_manager;

	/** @var environment */
	protected $twig;

	/** @var loader */
	protected $loader;

	/**
	* Constructor.
	*
	* @param \phpbb\path_helper				$path_helper		Path helper object
	* @param \phpbb\config\config			$config				Config object
	* @param \phpbb\template\context		$context			Template context
	* @param environment					$twig_environment	Twig environment
	* @param string							$cache_path			Template's cache directory path
	* @param null|\phpbb\user				$user				User object
	* @param array|\ArrayAccess				$extensions			Template extensions
	* @param null|\phpbb\extension\manager	$extension_manager	If null then template events will not be invoked
	*/
	public function __construct(
		\phpbb\path_helper $path_helper,
		\phpbb\config\config $config,
		\phpbb\template\context $context,
		environment $twig_environment,
		$cache_path,
		\phpbb\user $user = null,
		$extensions = [],
		\phpbb\extension\manager $extension_manager = null
	)
	{
		$this->path_helper = $path_helper;
		$this->phpbb_root_path = $path_helper->get_phpbb_root_path();
		$this->php_ext = $path_helper->get_php_ext();
		$this->config = $config;
		$this->user = $user;
		$this->context = $context;
		$this->extension_manager = $extension_manager;
		$this->cachepath = $cache_path;
		$this->twig = $twig_environment;
		$this->loader = $twig_environment->getLoader();

		foreach ($extensions as $extension)
		{
			$this->twig->addExtension($extension);
		}

		// Add admin namespace
		if ($this->path_helper->get_adm_relative_path() !== null
			&& is_dir($this->phpbb_root_path . $this->path_helper->get_adm_relative_path() . 'style/')
			&& $this->loader instanceof \Twig\Loader\FilesystemLoader)
		{
			$this->loader->setPaths($this->phpbb_root_path . $this->path_helper->get_adm_relative_path() . 'style/', 'admin');
		}
	}

	/**
	* Get the style tree of the style preferred by the current user
	*
	* @return array Style tree, most specific first
	*
	* @throws user_object_not_available	When user service was not set
	*/
	public function get_user_style()
	{
		if ($this->user === null)
		{
			throw new user_object_not_available();
		}

		$style_list = [
			$this->user->style['style_path'],
		];

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
	* @return \phpbb\template\template $this
	*/
	public function set_style($style_directories = ['styles'])
	{
		if ($style_directories !== ['styles'] && $this->loader->getPaths('core') === [])
		{
			// We should set up the core styles path since not already setup
			$this->set_style();
		}

		$paths = [];

		// Add 'all' folder to $names array
		//	It allows extensions to load a template file from 'all' folder,
		//	if a style doesn't include it.
		$names = $this->get_user_style();
		$names[] = 'all';

		foreach ($style_directories as $directory)
		{
			foreach ($names as $name)
			{
				$path	= $this->phpbb_root_path . trim($directory, '/') . "/{$name}/";
				$handle	= @opendir($path);
				$valid	= false;

				if ($handle)
				{
					while (($file = readdir($handle)) !== false)
					{
						$dir = $path . $file;

						if ($file[0] !== '.' && is_dir($dir))
						{
							$paths[] = $dir;

							$valid = true;
						}
					}

					closedir($handle);
				}

				if ($valid)
				{
					// Add the base style directory as a safe directory
					$this->loader->addSafeDirectory($path);
				}
			}
		}

		// If we're setting up the main phpBB styles directory and the core
		// namespace isn't setup yet, we will set it up now
		if ($style_directories === ['styles'] && $this->loader->getPaths('core') === [])
		{
			// Set up the core style paths namespace
			$this->loader->setPaths($paths, 'core');
		}

		$this->set_custom_style($names, $paths);

		return $this;
	}

	/**
	* Set custom style location (able to use directory outside of phpBB).
	*
	* Note: Templates are still compiled to phpBB's cache directory.
	*
	* @param string|array $names Array of names (or detailed names) or string of name of template(s) in inheritance tree order, used by extensions.
	*	E.g. array(
	*			'name' 		=> 'adm',
	*			'ext_path' 	=> 'adm/style/',
	*		)
	* @param string|array $paths Array of style paths, relative to current root directory
	* @return \phpbb\template\template $this
	*/
	public function set_custom_style($names, $paths)
	{
		$paths = (is_string($paths)) ? [$paths] : $paths;
		$names = (is_string($names)) ? [$names] : $names;

		// Set as __main__ namespace
		$this->loader->setPaths($paths);

		// Add all namespaces for all extensions
		if ($this->extension_manager instanceof \phpbb\extension\manager)
		{
			$names[] = 'all';

			foreach ($this->extension_manager->all_enabled() as $ext_namespace => $ext_path)
			{
				// namespaces cannot contain /
				$namespace = str_replace('/', '_', $ext_namespace);
				$paths = [];

				foreach ($names as $template_dir)
				{
					if (is_array($template_dir))
					{
						if (isset($template_dir['ext_path']))
						{
							$ext_style_template_path = $ext_path . $template_dir['ext_path'];
							$ext_style_path = dirname($ext_style_template_path);
							$ext_style_theme_path = $ext_style_path . 'theme/';
						}
						else
						{
							$ext_style_path = $ext_path . 'styles/' . $template_dir['name'] . '/';
							$ext_style_template_path = $ext_style_path . 'template/';
							$ext_style_theme_path = $ext_style_path . 'theme/';
						}
					}
					else
					{
						$ext_style_path = $ext_path . 'styles/' . $template_dir . '/';
						$ext_style_template_path = $ext_style_path . 'template/';
						$ext_style_theme_path = $ext_style_path . 'theme/';
					}

					$is_valid_dir = false;
					if (is_dir($ext_style_template_path))
					{
						$is_valid_dir = true;
						$paths[] = $ext_style_template_path;
					}
					if (is_dir($ext_style_theme_path))
					{
						$is_valid_dir = true;
						$paths[] = $ext_style_theme_path;
					}

					if ($is_valid_dir)
					{
						// Add the base style directory as a safe directory
						$this->loader->addSafeDirectory($ext_style_path);
					}
				}

				$this->loader->setPaths($paths, $namespace);
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
	* @return \phpbb\template\template $this
	*/
	public function display($handle)
	{
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
	* @return \phpbb\template\template|string if $return_content is true return string of the compiled handle, otherwise return $this
	*/
	public function assign_display($handle, $template_var = '', $return_content = true)
	{
		if ($return_content)
		{
			return $this->twig->render($this->get_filename_from_handle($handle), $this->get_template_vars());
		}

		$this->assign_var($template_var, $this->twig->render($this->get_filename_from_handle($handle), $this->get_template_vars()));

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
			[
				'definition'	=> new \phpbb\template\twig\definition(),
				'loops'			=> $context_vars, // To get loops
			]
		);

		if ($this->user instanceof \phpbb\user)
		{
			$vars['user'] = $this->user;
		}

		// cleanup
		unset($vars['loops']['.']);

		// Inject in the main context the value added by assign_block_vars() to be able to use directly the Twig loops.
		foreach ($vars['loops'] as $key => &$value)
		{
			$vars[$key] = $value;
		}

		return $vars;
	}

	/**
	* {@inheritdoc}
	*/
	public function get_source_file_for_handle($handle)
	{
		return $this->loader->getCacheKey($this->get_filename_from_handle($handle));
	}
}
