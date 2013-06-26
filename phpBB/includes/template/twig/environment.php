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

class phpbb_template_twig_environment extends Twig_Environment
{
	/** @var array */
	protected $phpbb_extensions;

	/** @var phpbb_config */
	protected $phpbb_config;

	/** @var string */
	protected $phpbb_root_path;

	/** @var array **/
	protected $namespaceLookUpOrder = array('__main__');

    public function __construct($phpbb_config, $phpbb_extensions, $phpbb_root_path, Twig_LoaderInterface $loader = null, $options = array())
	{
		$this->phpbb_config = $phpbb_config;
		$this->phpbb_extensions = $phpbb_extensions;
		$this->phpbb_root_path = $phpbb_root_path;

		return parent::__construct($loader, $options);
	}

    /**
     * Gets the cache filename for a given template.
     *
     * @param string $name The template name
     *
     * @return string The cache file name
     */
    public function ignoregetCacheFilename($name)
    {
        if (false === $this->cache) {
            return false;
        }
// @todo
		$file_path = $this->getLoader()->getCacheKey($name);
		foreach ($this->getLoader()->getNamespaces() as $namespace)
		{
			foreach ($this->getLoader()->getPaths($namespace) as $path)
			{
				if (strpos($file_path, $path) === 0)
				{
					//return $this->getCache() . '/' . preg_replace('#[^a-zA-Z0-9_/]#', '_', $namespace . '/' . $name) . '.php';
				}
			}
		}

		// We probably should never get here under normal circumstances
    	return $this->getCache() . '/' . preg_replace('#[^a-zA-Z0-9_/]#', '_', $name) . '.php';
    	return $this->getCache() . '/' . preg_replace('#[^a-zA-Z0-9_/]#', '_', $name) . '_' . md5($this->getLoader()->getCacheKey($name)) . '.php';
    }

	/**
	* Get the list of enabled phpBB extensions
	*
	* @return array
	*/
	public function get_phpbb_extensions()
	{
		return $this->phpbb_extensions;
	}

	/**
	* Get phpBB config
	*
	* @return phpbb_config
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
	* Get the namespace look up order
	*
	* @return array
	*/
	public function getNamespaceLookUpOrder()
	{
		return $this->namespaceLookUpOrder;
	}

	/**
	* Set the namespace look up order to load templates from
	*
	* @param array $namespace
    * @return Twig_Environment
	*/
	public function setNamespaceLookUpOrder($namespace)
	{
		$this->namespaceLookUpOrder = $namespace;

		return $this;
	}

    /**
     * Loads a template by name.
     *
     * @param string  $name  The template name
     * @param integer $index The index if it is an embedded template
     *
     * @return Twig_TemplateInterface A template instance representing the given template name
     */
    public function loadTemplate($name, $index = null)
    {
    	if (strpos($name, '@') === false)
    	{
    		foreach ($this->namespaceLookUpOrder as $namespace)
    		{
        		try
    			{
    				if ($namespace === '__main__')
    				{
    					return parent::loadTemplate($name, $index);
					}

    				return parent::loadTemplate('@' . $namespace . '/' . $name, $index);
				}
				catch (Twig_Error_Loader $e)
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
	* recursive helper to set variables into $context so that Twig can properly fetch them for display
	*
	* @param array $data Data to set at the end of the chain
	* @param array $blocks Array of blocks to loop into still
	* @param mixed $current_location Current location in $context (recursive!)
	*/
	public function context_recursive_loop_builder($data, $blocks, &$current_location)
	{
		$block = array_shift($blocks);

		if (empty($blocks))
		{
			$current_location[$block] = $data;
		}
		else
		{
			$this->context_recursive_loop_builder($data, $blocks, $current_location[$block]);
		}
	}
}
