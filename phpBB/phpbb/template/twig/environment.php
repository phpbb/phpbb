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

class environment extends \Twig_Environment
{
	/** @var \phpbb\config\config */
	protected $phpbb_config;

	/** @var \phpbb\path_helper */
	protected $phpbb_path_helper;

	/** @var \phpbb\extension\manager */
	protected $extension_manager;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $web_root_path;

	/** @var array **/
	protected $namespace_look_up_order = array('__main__');

	/**
	* Constructor
	*
	* @param \phpbb\config\config $phpbb_config The phpBB configuration
	* @param \phpbb\path_helper $path_helper phpBB path helper
	* @param \phpbb\extension\manager $extension_manager phpBB extension manager
	* @param \Twig_LoaderInterface $loader Twig loader interface
	* @param array $options Array of options to pass to Twig
	*/
	public function __construct($phpbb_config, \phpbb\path_helper $path_helper, \phpbb\extension\manager $extension_manager = null, \Twig_LoaderInterface $loader = null, $options = array())
	{
		$this->phpbb_config = $phpbb_config;

		$this->phpbb_path_helper = $path_helper;
		$this->extension_manager = $extension_manager;

		$this->phpbb_root_path = $this->phpbb_path_helper->get_phpbb_root_path();
		$this->web_root_path = $this->phpbb_path_helper->get_web_root_path();

		return parent::__construct($loader, $options);
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
	* @return \phpbb\config\config
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
	* Get the web root path
	*
	* @return string
	*/
	public function get_web_root_path()
	{
		return $this->web_root_path;
	}

	/**
	* Get the phpbb path helper object
	*
	* @return \phpbb\path_helper
	*/
	public function get_path_helper()
	{
		return $this->phpbb_path_helper;
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
	* @return \Twig_Environment
	*/
	public function setNamespaceLookUpOrder($namespace)
	{
		$this->namespace_look_up_order = $namespace;

		return $this;
	}

	/**
	* Loads a template by name.
	*
	* @param string  $name  The template name
	* @param integer $index The index if it is an embedded template
	* @return \Twig_TemplateInterface A template instance representing the given template name
	* @throws \Twig_Error_Loader
	*/
	public function loadTemplate($name, $index = null)
	{
		if (strpos($name, '@') === false)
		{
			foreach ($this->getNamespaceLookUpOrder() as $namespace)
			{
				try
				{
					if ($namespace === '__main__')
					{
						return parent::loadTemplate($name, $index);
					}

					return parent::loadTemplate('@' . $namespace . '/' . $name, $index);
				}
				catch (\Twig_Error_Loader $e)
				{
				}
			}

			// We were unable to load any templates
			throw $e;
		}
		else
		{
			return parent::loadTemplate($name, $index);
		}
	}

	/**
	* Finds a template by name.
	*
	* @param string  $name  The template name
	* @return string
	* @throws \Twig_Error_Loader
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
				catch (\Twig_Error_Loader $e)
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
