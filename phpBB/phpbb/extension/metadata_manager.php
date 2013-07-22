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
	/**
	* phpBB Config instance
	* @var phpbb_config
	*/
	protected $config;

	/**
	* phpBB Extension Manager
	* @var phpbb_extension_manager
	*/
	protected $extension_manager;

	/**
	* phpBB Template instance
	* @var phpbb_template
	*/
	protected $template;

	/**
	* phpBB root path
	* @var string
	*/
	protected $phpbb_root_path;

	/**
	* Name (including vendor) of the extension
	* @var string
	*/
	protected $ext_name;

	/**
	* Metadata from the composer.json file
	* @var array
	*/
	protected $metadata;

	/**
	* Link (including root path) to the metadata file
	* @var string
	*/
	protected $metadata_file;

	/**
	* Creates the metadata manager
	*
	* @param string				$ext_name			Name (including vendor) of the extension
	* @param phpbb_config		$config				phpBB Config instance
	* @param phpbb_extension_manager	$extension_manager An instance of the phpBBb extension manager
	* @param phpbb_template		$template			phpBB Template instance
	* @param string				$phpbb_root_path	Path to the phpbb includes directory.
	*/
	public function __construct($ext_name, phpbb_config $config, phpbb_extension_manager $extension_manager, phpbb_template $template, $phpbb_root_path)
	{
		$this->config = $config;
		$this->extension_manager = $extension_manager;
		$this->template = $template;
		$this->phpbb_root_path = $phpbb_root_path;

		$this->ext_name = $ext_name;
		$this->metadata = array();
		$this->metadata_file = '';
	}

	/**
	 * Processes and gets the metadata requested
	 *
	 * @param  string $element			All for all metadata that it has and is valid, otherwise specify which section you want by its shorthand term.
	 * @return array					Contains all of the requested metadata, throws an exception on failure
	 */
	public function get_metadata($element = 'all')
	{
		$this->set_metadata_file();

		// Fetch the metadata
		$this->fetch_metadata();

		// Clean the metadata
		$this->clean_metadata_array();

		switch ($element)
		{
			case 'all':
			default:
				// Validate the metadata
				if (!$this->validate())
				{
					return false;
				}

				return $this->metadata;
			break;

			case 'name':
				return ($this->validate('name')) ? $this->metadata['name'] : false;
			break;

			case 'display-name':
				if (isset($this->metadata['extra']['display-name']))
				{
					return $this->metadata['extra']['display-name'];
				}
				else
				{
					return ($this->validate('name')) ? $this->metadata['name'] : false;
				}
			break;
		}
	}

	/**
	 * Sets the filepath of the metadata file
	 *
	 * @return boolean  Set to true if it exists, throws an exception on failure
	 */
	private function set_metadata_file()
	{
		$ext_filepath = $this->extension_manager->get_extension_path($this->ext_name);
		$metadata_filepath = $this->phpbb_root_path . $ext_filepath . 'composer.json';

		$this->metadata_file = $metadata_filepath;

		if (!file_exists($this->metadata_file))
		{
    		throw new phpbb_extension_exception('The required file does not exist: ' . $this->metadata_file);
		}
	}

	/**
	 * Gets the contents of the composer.json file
	 *
	 * @return bool True if success, throws an exception on failure
	 */
	private function fetch_metadata()
	{
		if (!file_exists($this->metadata_file))
		{
			throw new phpbb_extension_exception('The required file does not exist: ' . $this->metadata_file);
		}
		else
		{
			if (!($file_contents = file_get_contents($this->metadata_file)))
			{
    			throw new phpbb_extension_exception('file_get_contents failed on ' . $this->metadata_file);
			}

			if (($metadata = json_decode($file_contents, true)) === NULL)
			{
    			throw new phpbb_extension_exception('json_decode failed on ' . $this->metadata_file);
			}

			$this->metadata = $metadata;

			return true;
		}
	}

	/**
	 * This array handles the cleaning of the array
	 *
	 * @return array Contains the cleaned metadata array
	 */
	private function clean_metadata_array()
	{
		return $this->metadata;
	}

	/**
	* Validate fields
	*
	* @param string $name  ("all" for display and enable validation
	* 						"display" for name, type, and authors
	* 						"name", "type")
	* @return Bool True if valid, throws an exception if invalid
	*/
	public function validate($name = 'display')
    {
    	// Basic fields
    	$fields = array(
    		'name'		=> '#^[a-zA-Z0-9_\x7f-\xff]{2,}/[a-zA-Z0-9_\x7f-\xff]{2,}$#',
    		'type'		=> '#^phpbb3-extension$#',
    		'licence'	=> '#.+#',
    		'version'	=> '#.+#',
    	);

    	switch ($name)
    	{
    		case 'all':
    			$this->validate('display');

				$this->validate_enable();
    		break;

    		case 'display':
    			foreach ($fields as $field => $data)
				{
					$this->validate($field);
				}

				$this->validate_authors();
    		break;

    		default:
    			if (isset($fields[$name]))
    			{
    				if (!isset($this->metadata[$name]))
    				{
    					throw new phpbb_extension_exception("Required meta field '$name' has not been set.");
					}

					if (!preg_match($fields[$name], $this->metadata[$name]))
					{
    					throw new phpbb_extension_exception("Meta field '$name' is invalid.");
					}
				}
			break;
		}

		return true;
    }

	/**
	 * Validates the contents of the authors field
	 *
	 * @return boolean True when passes validation, throws exception if invalid
	 */
	public function validate_authors()
	{
		if (empty($this->metadata['authors']))
		{
    		throw new phpbb_extension_exception("Required meta field 'authors' has not been set.");
		}

		foreach ($this->metadata['authors'] as $author)
		{
			if (!isset($author['name']))
			{
    			throw new phpbb_extension_exception("Required meta field 'author name' has not been set.");
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
		// Check for phpBB, PHP versions
		if (!$this->validate_require_phpbb() || !$this->validate_require_php())
		{
			return false;
		}

		return true;
	}


	/**
	 * Validates the contents of the phpbb requirement field
	 *
	 * @return boolean True when passes validation
	 */
	public function validate_require_phpbb()
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
	public function validate_require_php()
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
	 * Outputs the metadata into the template
	 *
	 * @return null
	 */
	public function output_template_data()
	{
		$this->template->assign_vars(array(
			'META_NAME'			=> htmlspecialchars($this->metadata['name']),
			'META_TYPE'			=> htmlspecialchars($this->metadata['type']),
			'META_DESCRIPTION'	=> (isset($this->metadata['description'])) ? htmlspecialchars($this->metadata['description']) : '',
			'META_HOMEPAGE'		=> (isset($this->metadata['homepage'])) ? $this->metadata['homepage'] : '',
			'META_VERSION'		=> (isset($this->metadata['version'])) ? htmlspecialchars($this->metadata['version']) : '',
			'META_TIME'			=> (isset($this->metadata['time'])) ? htmlspecialchars($this->metadata['time']) : '',
			'META_LICENCE'		=> htmlspecialchars($this->metadata['licence']),

			'META_REQUIRE_PHP'		=> (isset($this->metadata['require']['php'])) ? htmlspecialchars($this->metadata['require']['php']) : '',
			'META_REQUIRE_PHP_FAIL'	=> !$this->validate_require_php(),

			'META_REQUIRE_PHPBB'		=> (isset($this->metadata['require']['phpbb'])) ? htmlspecialchars($this->metadata['require']['phpbb']) : '',
			'META_REQUIRE_PHPBB_FAIL'	=> !$this->validate_require_phpbb(),

			'META_DISPLAY_NAME'	=> (isset($this->metadata['extra']['display-name'])) ? htmlspecialchars($this->metadata['extra']['display-name']) : '',
		));

		foreach ($this->metadata['authors'] as $author)
		{
			$this->template->assign_block_vars('meta_authors', array(
				'AUTHOR_NAME'		=> htmlspecialchars($author['name']),
				'AUTHOR_EMAIL'		=> (isset($author['email'])) ? $author['email'] : '',
				'AUTHOR_HOMEPAGE'	=> (isset($author['homepage'])) ? $author['homepage'] : '',
				'AUTHOR_ROLE'		=> (isset($author['role'])) ? htmlspecialchars($author['role']) : '',
			));
		}
	}
}
