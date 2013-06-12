<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_template_twig_loader extends Twig_Loader_Filesystem
{
	protected $phpbb_locator;

    /**
    * Constructor.
    *
    * @param string|array $paths A path or an array of paths where to look for templates
    * @param phpbb_template_locator
    */
    public function __construct($paths = array(), phpbb_template_locator $phpbb_locator)
    {
        if ($paths) {
            $this->setPaths($paths);
        }

        $this->phpbb_locator = $phpbb_locator;
    }

    protected function findTemplate($name)
    {
        $name = (string) $name;

        if (!$name)
        {
        	throw new Twig_Error_Loader(sprintf('Unable to find template "%s".', $name));
		}

        $this->phpbb_locator->set_filenames(array(
        	'temp'		=> $name,
        ));
        $location = $this->phpbb_locator->get_source_file_for_handle('temp');

        if (!$location)
        {
        	throw new Twig_Error_Loader(sprintf('Unable to find template "%s".', $name));
		}

		return $this->cache[$name] = $location;
    }
}
