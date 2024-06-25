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

use phpbb\config\config;
use phpbb\event\dispatcher_interface;
use phpbb\extension\manager;
use phpbb\filesystem\filesystem;
use phpbb\path_helper;
use phpbb\template\assets_bag;
use Twig\Loader\LoaderInterface;

class environment extends \Twig\Environment
{
	/** @var config */
	protected $phpbb_config;

	/** @var filesystem */
	protected $filesystem;

	/** @var path_helper */
	protected $phpbb_path_helper;

	/** @var \Symfony\Component\DependencyInjection\ContainerInterface */
	protected $container;

	/** @var manager */
	protected $extension_manager;

	/** @var dispatcher_interface */
	protected $phpbb_dispatcher;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $web_root_path;

	/** @var array **/
	protected $namespace_look_up_order = array('__main__');

	/** @var assets_bag */
	protected $assets_bag;

	/**
	* Constructor
	*
	* @param assets_bag $assets_bag Assets bag
	* @param config $phpbb_config The phpBB configuration
	* @param filesystem $filesystem
	* @param path_helper $path_helper phpBB path helper
	* @param string $cache_path The path to the cache directory
	* @param manager|null $extension_manager phpBB extension manager
	* @param LoaderInterface|null $loader Twig loader interface
	* @param dispatcher_interface|null	$phpbb_dispatcher	Event dispatcher object
	* @param array $options Array of options to pass to Twig
	*/
	public function __construct(assets_bag $assets_bag, config $phpbb_config, filesystem $filesystem, path_helper $path_helper, $cache_path, manager $extension_manager = null, LoaderInterface $loader = null, dispatcher_interface $phpbb_dispatcher = null, $options = array())
	{
		$this->phpbb_config = $phpbb_config;

		$this->filesystem = $filesystem;
		$this->phpbb_path_helper = $path_helper;
		$this->extension_manager = $extension_manager;
		$this->phpbb_dispatcher = $phpbb_dispatcher;

		$this->phpbb_root_path = $this->phpbb_path_helper->get_phpbb_root_path();

		$this->assets_bag = $assets_bag;

		$options = array_merge(array(
			'cache'			=> (defined('IN_INSTALL')) ? false : $cache_path,
			'debug'			=> false,
			'auto_reload'	=> (bool) $this->phpbb_config['load_tplcompile'],
			'autoescape'	=> false,
		), $options);

		parent::__construct($loader, $options);
	}

	/**
	* Get the list of enabled phpBB extensions
	*
	* Used in EVENT node
	*
	* @return array
	*/
	public function get_phpbb_extensions()
	{
		return ($this->extension_manager) ? $this->extension_manager->all_enabled() : array();
	}

	/**
	* Get phpBB config
	*
	* @return config
	*/
	public function get_phpbb_config()
	{
		return $this->phpbb_config;
	}

	/**
	 * Get the phpBB root path
	 *
	 * @return string
	 */
	public function get_phpbb_root_path()
	{
		return $this->phpbb_root_path;
	}

	/**
	* Get the filesystem object
	*
	* @return filesystem
	*/
	public function get_filesystem()
	{
		return $this->filesystem;
	}

	/**
	* Get the web root path
	*
	* @return string
	*/
	public function get_web_root_path()
	{
		return $this->web_root_path ?? $this->web_root_path = $this->phpbb_path_helper->get_web_root_path();
	}

	/**
	* Get the phpbb path helper object
	*
	* @return path_helper
	*/
	public function get_path_helper()
	{
		return $this->phpbb_path_helper;
	}

	/**
	 * Gets the assets bag
	 *
	 * @return assets_bag
	 */
	public function get_assets_bag()
	{
		return $this->assets_bag;
	}

	/**
	* Get the namespace look up order
	*
	* @return array
	*/
	public function getNamespaceLookUpOrder()
	{
		return $this->namespace_look_up_order;
	}

	/**
	* Set the namespace look up order to load templates from
	*
	* @param array $namespace
	* @return \Twig\Environment
	*/
	public function setNamespaceLookUpOrder($namespace)
	{
		$this->namespace_look_up_order = $namespace;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function render($name, array $context = []) : string
	{
		return $this->display_with_assets($name, $context);
	}

	/**
	 * {@inheritdoc}
	 */
	public function display($name, array $context = []) : void
	{
		echo $this->display_with_assets($name, $context);
	}

	/**
	 * Get template with assets
	 */
	private function display_with_assets($name, array $context = [])
	{
		$placeholder_salt = unique_id();

		if (array_key_exists('definition', $context))
		{
			$context['definition']->set('SCRIPTS', '__SCRIPTS_' . $placeholder_salt . '__');
			$context['definition']->set('STYLESHEETS', '__STYLESHEETS_' . $placeholder_salt . '__');
		}

		/**
		* Allow changing the template output stream before rendering
		*
		* @event core.twig_environment_render_template_before
		* @var	array	context		Array with template variables
		* @var	string  name		The template name
		* @since 3.2.1-RC1
		*/
		if ($this->phpbb_dispatcher)
		{
			$vars = array('context', 'name');
			extract($this->phpbb_dispatcher->trigger_event('core.twig_environment_render_template_before', compact($vars)));
		}

		$output = parent::render($name, $context);

		/**
		* Allow changing the template output stream after rendering
		*
		* @event core.twig_environment_render_template_after
		* @var	array	context		Array with template variables
		* @var	string  name		The template name
		* @var	string	output		Rendered template output stream
		* @since 3.2.1-RC1
		*/
		if ($this->phpbb_dispatcher)
		{
			$vars = array('context', 'name', 'output');
			extract($this->phpbb_dispatcher->trigger_event('core.twig_environment_render_template_after', compact($vars)));
		}

		return $this->inject_assets($output, $placeholder_salt);
	}

	/**
	 * Injects the assets (from INCLUDECSS/JS) in the output.
	 *
	 * @param string $output
	 *
	 * @return string
	 */
	private function inject_assets($output, $placeholder_salt)
	{
		$output = str_replace('__STYLESHEETS_' . $placeholder_salt . '__', $this->assets_bag->get_stylesheets_content(), $output);
		$output = str_replace('__SCRIPTS_' . $placeholder_salt . '__', $this->assets_bag->get_scripts_content(), $output);

		return $output;
	}

	/**
	* Loads a template by name.
	*
	* @param string  $cls   The template class associated with the given template name
	* @param string  $name  The template name
	* @param integer|null $index The index if it is an embedded template
	* @return \Twig\Template A template instance representing the given template name
	* @throws \Twig\Error\LoaderError
	*/
	public function loadTemplate(string $cls, string $name, int $index = null) : \Twig\Template
	{
		if (strpos($name, '@') === false)
		{
			foreach ($this->getNamespaceLookUpOrder() as $namespace)
			{
				try
				{
					if ($namespace === '__main__')
					{
						return parent::loadTemplate($cls, $name, $index);
					}

					return parent::loadTemplate($this->getTemplateClass('@' . $namespace . '/' . $name), '@' . $namespace . '/' . $name, $index);
				}
				catch (\Twig\Error\LoaderError $e)
				{
				}
			}

			// We were unable to load any templates
			throw $e;
		}
		else
		{
			return parent::loadTemplate($cls, $name, $index);
		}
	}

	/**
	* Finds a template by name.
	*
	* @param string  $name  The template name
	* @return string
	* @throws \Twig\Error\LoaderError
	*/
	public function findTemplate($name)
	{
		if (strpos($name, '@') === false)
		{
			foreach ($this->getNamespaceLookUpOrder() as $namespace)
			{
				try
				{
					if ($namespace === '__main__')
					{
						return parent::getLoader()->getCacheKey($name);
					}

					return parent::getLoader()->getCacheKey('@' . $namespace . '/' . $name);
				}
				catch (\Twig\Error\LoaderError $e)
				{
				}
			}

			// We were unable to load any templates
			throw $e;
		}
		else
		{
			return parent::getLoader()->getCacheKey($name);
		}
	}
}
