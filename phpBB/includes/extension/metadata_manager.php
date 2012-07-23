<?php
/**
*
* @package extension
* @copyright (c) 2012 phpBB Group
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
* The extension metadata manager validates and gets meta-data for extensions
*
* @package extension
*/
class phpbb_extension_metadata_manager
{
	protected $phpEx;
	protected $extension_manager;
	protected $db;
	protected $phpbb_root_path;
	protected $template;
	protected $ext_name;
	public $metadata;
	protected $metadata_file;

	/**
	* Array of validation regular expressions, see __call()
	*
	* @var mixed
	*/
	protected $validation = array(
		'name'					=> '#^[a-zA-Z0-9_\x7f-\xff]{2,}/[a-zA-Z0-9_\x7f-\xff]{2,}$#',
		'type'					=> '#^phpbb3-extension$#',
		'description'			=> '#.*#',
		'version'				=> '#.+#',
		'licence'				=> '#.+#',
		'extra'					=> array(
			'display-name'			=> '#.*#',
		),
	);

	/**
	* Magic method to catch validation calls
	*
	* @param string $name
	* @param mixed $arguments
	* @return int
	*/
	public function __call($name, $arguments)
    {
    	// Validation Magic methods
        if (strpos($name, 'validate_') === 0)
        {
        	// Remove validate_
        	$name = substr($name, 9);

        	// Replace underscores with dashes (underscores are not used)
        	$name = str_replace('_', '-', $name);

        	if (strpos($name, 'extra-') === 0)
        	{
        		// Remove extra_
        		$name = substr($name, 6);

        		if (isset($this->validation['extra'][$name]))
        		{
        			// Extra means it's optional, so return true if it does not exist
        			return (isset($this->metadata['extra'][$name])) ? preg_match($this->validation['extra'][$name], $this->metadata['extra'][$name]) : true;
				}
			}
			else if (isset($this->validation[$name]))
	        {
        		return preg_match($this->validation[$name], $this->metadata[$name]);
			}
		}
    }

	/**
	* Creates the metadata manager
	*
	* @param dbal $db A database connection
	* @param string $extension_manager An instance of the phpbb extension manager
	* @param string $phpbb_root_path Path to the phpbb includes directory.
	* @param string $phpEx php file extension
	*/
	public function __construct($ext_name, dbal $db, phpbb_extension_manager $extension_manager, $phpbb_root_path, $phpEx = '.php', phpbb_template $template, phpbb_config $config)
	{
		$this->phpbb_root_path = $phpbb_root_path;
		$this->db = $db;
		$this->config = $config;
		$this->phpEx = $phpEx;
		$this->template = $template;
		$this->extension_manager = $extension_manager;
		$this->ext_name = $ext_name;
		$this->metadata = array();
		$this->metadata_file = '';
	}

	/**
	 * Processes and gets the metadata requested
	 *
	 * @param  string $element			All for all metadata that it has and is valid, otherwise specify which section you want by its shorthand term.
	 * @return bool|array				Contains all of the requested metadata or bool False if not valid
	 */
	public function get_metadata($element = 'all')
	{
		// TODO: Check ext_name exists and is an extension that exists
		if (!$this->set_metadata_file())
		{
			return false;
		}

		// Fetch the metadata
		if (!$this->fetch_metadata())
		{
			return false;
		}

		// Clean the metadata
		if (!$this->clean_metadata_array())
		{
			return false;
		}

		switch ($element)
		{
			case 'all':
			default:
				// Validate the metadata
				if (!$this->validate_metadata_array())
				{
					return false;
				}

				return $this->metadata;
			break;

			case 'name':
				return ($this->validate_name()) ? $this->metadata['name'] : false;
			break;

			case 'display-name':
				if (isset($this->metadata['extra']['display-name']) && $this->validate_extra_display_name())
				{
					return $this->metadata['extra']['display-name'];
				}
				else
				{
					return ($this->validate_name()) ? $this->metadata['name'] : false;
				}
			break;
			// TODO: Add remaining cases as needed
		}
	}

	/**
	 * Sets the filepath of the metadata file
	 *
	 * @return boolean  Set to true if it exists
	 */
	private function set_metadata_file()
	{
		$ext_filepath = $this->extension_manager->get_extension_path($this->ext_name);
		$metadata_filepath = $this->phpbb_root_path . $ext_filepath . '/composer.json';

		$this->metadata_file = $metadata_filepath;

		if (!file_exists($this->metadata_file))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Gets the contents of the composer.json file
	 *
	 * @return bool True of false (if loading succeeded or failed)
	 */
	private function fetch_metadata()
	{
		if (!file_exists($this->metadata_file))
		{
			return false;
		}
		else
		{
			if (!($file_contents = file_get_contents($this->metadata_file)))
			{
				return false;
			}

			if (($metadata = json_decode($file_contents, true)) === NULL)
			{
				return false;
			}

			$this->metadata = $metadata;

			return true;
		}
	}

	/**
	 * This array handles the validation and cleaning of the array
	 *
	 * @return array Contains the cleaned and validated metadata array
	 */
	private function clean_metadata_array()
	{
//		TODO: Remove all parts of the array we don't want or shouldn't be there due to nub mod authors
//		$this->metadata = $metadata_finished;

		return $this->metadata;
	}

	/**
	 * This array handles the validation of strings
	 *
	 * @return bool True if validation succeeded, False if failed
	 */
	public function validate_metadata_array()
	{
		foreach ($this->validation as $name => $regex)
		{
			if (is_array($regex))
			{
				foreach ($regex as $extra_name => $extra_regex)
				{
					$type = 'validate_' . $name . '_' . $extra_name;

					if (!$this->$type())
					{
						return false;
					}
				}
			}
			else
			{

				$type = 'validate_' . $name;

				if (!$this->$type())
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * This array handles the verification that this extension can be enabled on this board
	 *
	 * @return bool True if validation succeeded, False if failed
	 */
	public function validate_enable()
	{
		$validate = array(
			'require_phpbb',
			'require_php',
		);

		foreach ($validate as $type)
		{
			$type = 'validate_' . $type;

			if (!$this->$type())
			{
				return false;
			}
		}

		return true;
	}


	/**
	 * Validates the contents of the phpbb requirement field
	 *
	 * @return boolean True when passes validation
	 */
	private function validate_require_phpbb()
	{
		if (!isset($this->metadata['require']['phpbb']))
		{
			return true;
		}

		return $this->_validate_version($this->metadata['require']['phpbb'], $this->config['version']);
	}

	/**
	 * Validates the contents of the php requirement field
	 *
	 * @return boolean True when passes validation
	 */
	private function validate_require_php()
	{
		if (!isset($this->metadata['require']['php']))
		{
			return true;
		}

		return $this->_validate_version($this->metadata['require']['php'], phpversion());
	}

	/**
	* Version validation helper
	*
	* @param string $string The string for comparing to a version
	* @param string $current_version The version to compare to
	* @return bool True/False if meets version requirements
	*/
	private function _validate_version($string, $current_version)
	{
		// Allow them to specify their own comparison operator (ex: <3.1.2, >=3.1.0)
		$comparison_matches = false;
		preg_match('#[=<>]+#', $string, $comparison_matches);

		if (!empty($comparison_matches))
		{
			return version_compare($current_version, str_replace(array($comparison_matches[0], ' '), '', $string), $comparison_matches[0]);
		}

		return version_compare($current_version, $string, '>=');
	}

	/**
	 * Validates the contents of the time field
	 *
	 * @return boolean True when passes validation
	 */
	private function validate_time()
	{
		// Need to validate
		return true;
	}

	/**
	 * Validates the contents of the homepage field
	 *
	 * @return boolean True when passes validation
	 */
	private function validate_homepage()
	{
		return preg_match('#([\d\w-.]+?\.(a[cdefgilmnoqrstuwz]|b[abdefghijmnorstvwyz]|c[acdfghiklmnoruvxyz]|d[ejkmnoz]|e[ceghrst]|f[ijkmnor]|g[abdefghilmnpqrstuwy]|h[kmnrtu]|i[delmnoqrst]|j[emop]|k[eghimnprwyz]|l[abcikrstuvy]|m[acdghklmnopqrstuvwxyz]|n[acefgilopruz]|om|p[aefghklmnrstwy]|qa|r[eouw]|s[abcdeghijklmnortuvyz]|t[cdfghjkmnoprtvwz]|u[augkmsyz]|v[aceginu]|w[fs]|y[etu]|z[amw]|aero|arpa|biz|com|coop|edu|info|int|gov|mil|museum|name|net|org|pro)(\b|\W(?<!&|=)(?!\.\s|\.{3}).*?))(\s|$)#', $this->metadata['homepage']);
	}

	/**
	 * Validates the contents of the authors field
	 *
	 * @return boolean True when passes validation
	 */
	private function validate_authors()
	{
		// Need to validate
		$number_authors = sizeof($this->metadata['authors']); // Might be helpful later on

		if (!isset($this->metadata['authors']['1']))
		{
			return false;
		}
		else
		{
			foreach ($this->metadata['authors'] as $author)
			{
				if (!isset($author['name']))
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Outputs the metadata into the template
	 *
	 * @return null
	 */
	public function output_template_data()
	{

		$this->template->assign_vars(array(
			'MD_NAME'			=> htmlspecialchars($this->metadata['name']),
			'MD_TYPE'			=> htmlspecialchars($this->metadata['type']),
			'MD_DESCRIPTION'	=> htmlspecialchars($this->metadata['description']),
			'MD_HOMEPAGE'		=> (isset($this->metadata['homepage'])) ? $this->metadata['homepage'] : '',
			'MD_VERSION'		=> htmlspecialchars($this->metadata['version']),
			'MD_TIME'			=> htmlspecialchars($this->metadata['time']),
			'MD_LICENCE'		=> htmlspecialchars($this->metadata['licence']),
			'MD_REQUIRE_PHP'	=> (isset($this->metadata['require']['php'])) ? htmlspecialchars($this->metadata['require']['php']) : '',
			'MD_REQUIRE_PHPBB'	=> (isset($this->metadata['require']['phpbb'])) ? htmlspecialchars($this->metadata['require']['phpbb']) : '',
			'MD_DISPLAY_NAME'	=> (isset($this->metadata['extra']['display-name'])) ? htmlspecialchars($this->metadata['extra']['display-name']) : '',
		));

		foreach ($this->metadata['authors'] as $author)
		{
			$this->template->assign_block_vars('md_authors', array(
				'AUTHOR_NAME'		=> htmlspecialchars($author['name']),
				'AUTHOR_EMAIL'		=> $author['email'],
				'AUTHOR_HOMEPAGE'	=> (isset($author['homepage'])) ? $author['homepage'] : '',
				'AUTHOR_ROLE'		=> (isset($author['role'])) ? htmlspecialchars($author['role']) : '',
			));
		}

		return;
	}
}
