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
	protected $ext_name;
	protected $template;
	protected $metadata;
	protected $metadata_file;

	/**
	* Creates the metadata manager
	* 
	* @param dbal $db A database connection
	* @param string $extension_manager An instance of the phpbb extension manager
	* @param string $phpbb_root_path Path to the phpbb includes directory.
	* @param string $phpEx php file extension
	*/
	public function __construct($ext_name, dbal $db, phpbb_extension_manager $extension_manager, $phpbb_root_path, $phpEx = '.php', template $template)
	{
		$this->phpbb_root_path = $phpbb_root_path;
		$this->db = $db;
		$this->phpEx = $phpEx;
		$this->extension_manager = $extension_manager;
		$this->ext_name = $ext_name;
		$this->template = $template;
		$this->metadata = array();
		$this->metadata_file = '';
	}

	/**
	 * Processes and gets the metadata requested
	 * 
	 * @param  string $element         All for all metadata that it has and is valid, otherwise specify which section you want by its shorthand term.
	 * @param  bool $template_output   True if you want the requested metadata assigned to template vars
	 * @return array                   Contains all of the requested metadata
	 */
	public function get_meta_data($element = 'all', $template_output = false)
	{
		// TODO: Check ext_name exists and is an extension that exists
		if (!$this->set_meta_data_file())
		{
			return false;
		}

		switch ($element) 
		{
			case 'all':
			default:
				if (!$this->clean_metadata_array())
				{
					return false;
				}

				if ($template_output) 
				{
					$this->output_template_data();
				}

				return $this->metadata;
			break;
			
			case 'name':
				if($this->validate_name)
				{
					if ($template_output)
					{
						$this->template->assign_vars(array(
							'MD_NAME'	=> htmlspecialchars($this->metadata['name']),
						));
					}
					return $this->metadata['name'];
				}
				else
				{
					return false;
				}
			break;
			// TODO: Add remaining cases
		}
	}

	/**
	 * Sets the filepath of the metadata file
	 * 
	 * @return null
	 */
	private function set_meta_data_file()
	{
		$ext_filepath = $this->extension_manager->get_extension_path($this->ext_name);
		$metadata_filepath = $this->phpbb_root_path . $ext_filepath . '/composer.json';

		$this->metadata_file = $metadata_filepath;

		if(!file_exists($this->metadata_file))
		{
			return false;
		}
		else
		{
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
		if (!$this->validate_name() || !$this->validate_type() || !$this->validate_license() || !$this->validate_description() || !$this->validate_version() || !$this->validate_require_phpbb() || !$this->validate_extra_display_name())
		{
			return false;
		}
		
		$this->check_for_optional(true);

//		TODO: Remove all parts of the array we don't want or shouldn't be there due to nub mod authors
//		$this->metadata = $metadata_finished;
		$metadata_finished = $this->metadata;

		return $metadata_finished;
	}

	/**
	 * Validates the contents of the name field
	 * 
	 * @return bool True when passes validation
	 */
	private function validate_name()
	{
		if(preg_match('^[a-zA-Z0-9_\x7f-\xff]{2,}/[a-zA-Z0-9_\x7f-\xff]{2,}$', $this->metadata['name']))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Validates the contents of the type field
	 * 
	 * @return bool True when passes validation
	 */
	private function validate_type()
	{
		if ($this->metadata['type'] != 'phpbb3-extension')
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Validates the contents of the description field
	 * 
	 * @return bool True when passes validation
	 */
	private function validate_description()
	{
		if(preg_match('^{10,}$', $this->metadata['description']))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Validates the contents of the version field
	 * 
	 * @return bool True when passes validation
	 */
	private function validate_version()
	{
		if(preg_match('^[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{1,2}$', $this->metadata['version']))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Validates the contents of the license field
	 * 
	 * @return bool True when passes validation
	 */
	private function validate_license()
	{
		if ($this->metadata['license'] != 'GPLv2')
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Validates the contents of the phpbb requirement field
	 * 
	 * @return bool True when passes validation
	 */
	private function validate_require_phpbb()
	{
		if(preg_match('^[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{1,2}$', $this->metadata['require']['phpbb']) && version_compare($this->metadata['require']['phpbb']), '3.1.0', '>')
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Validates the contents of the display name field
	 * 
	 * @return bool True when passes validation
	 */
	private function validate_extra_display_name()
	{
		if(preg_match('^[a-zA-Z0-9_]{2,0}$', $this->metadata['name']))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Checks which optional fields exist
	 * 
	 * @return boolean           False if any that exist fail validation, otherwise true.
	 */
	public function check_for_optional()
	{
		if ((isset($this->metadata['require']['php']) && !$this->validate_require_php()) || (isset($this->metadata['time']) && !$this->validate_time()) || (isset($this->metadata['validate_homepage']) && !$this->validate_homepage()))
		{
			return false;
		}
	}

	/**
	 * Validates the contents of the php requirement field
	 * 
	 * @return bool True when passes validation
	 */
	private function validate_require_php()
	{

	}

	/**
	 * Validates the contents of the time field
	 * 
	 * @return bool True when passes validation
	 */
	private function validate_time()
	{

	}

	/**
	 * Validates the contents of the homepage field
	 * 
	 * @return bool True when passes validation
	 */
	private function validate_homepage()
	{

	}

	/**
	 * Validates the contents of the authors field
	 * 
	 * @return bool True when passes validation
	 */
	private function validate_authors()
	{

	}

	/**
	 * Gets the contents of the composer.json file and can also assign template vars
	 *
	 * @return array  Contains everything from the meta data file. Do not use without validating and cleaning first
	 */
	private function fetch_metadata()
	{
		// Read it
		$metadata_file = file_get_contents($metadata_filepath);
		$metadata = json_decode($metadata_file, true)

		$this->metadata = $metadata;

		return $metadata;
	}

	/**
	 * Outputs the metadata into the template
	 * 
	 * @return null
	 */
	public function output_template_data()
	{
		$template->assign_vars(array(
			'MD_NAME'			=> htmlspecialchars($this->metadata['name']),
			'MD_TYPE'			=> htmlspecialchars($this->metadata['type']),
			'MD_DESCRIPTION'	=> htmlspecialchars($this->metadata['description']),
			'MD_HOMEPAGE'		=> $this->metadata['homepage'],
			'MD_VERSION'		=> htmlspecialchars($this->metadata['version']),
			'MD_TIME'			=> htmlspecialchars($this->metadata['time']),
			'MD_LICENSE'		=> htmlspecialchars($this->metadata['license']),
			'MD_REQUIRE_PHP'	=> htmlspecialchars($this->metadata['require']['php']),
			'MD_REQUIRE_PHPBB'	=> htmlspecialchars($this->metadata['require']['phpbb']),
			'MD_DISPLAY_NAME'	=> htmlspecialchars($this->metadata['extra']['display-name']),
		));

		foreach ($this->metadata['authors'] as $author)
		{
			$template->assign_block_vars('md_authors', array(
				'AUTHOR_NAME'		=> htmlspecialchars($author['name']),
				'AUTHOR_EMAIL'		=> $author['email'],
				'AUTHOR_HOMEPAGE'	=> $author['homepage'],
				'AUTHOR_ROLE'		=> htmlspecialchars($author['role']),
			));
		}

		return;
	}
