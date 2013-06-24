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
	protected $phpbbExtensions;

	/** @var array **/
	protected $namespaceLookUpOrder = array('__main__');

    /**
     * Gets the cache filename for a given template.
     *
     * @param string $name The template name
     *
     * @return string The cache file name
     */
    public function getCacheFilename($name)
    {
        if (false === $this->cache) {
            return false;
        }

    	return $this->getCache() . '/' . preg_replace('#[^a-zA-Z0-9_/]#', '_', $name) . '.php';
    }

	/**
	* Get the list of enabled phpBB extensions
	*
	* @return array
	*/
	public function get_phpbb_extensions()
	{
		return $this->phpbbExtensions;
	}

    /**
    * Store the list of enabled phpBB extensions
    *
    * @param array $extensions
    * @return Twig_Environment
    */
    public function set_phpbb_extensions($extensions)
    {
    	$this->phpbbExtensions = $extensions;

		return $this;
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
