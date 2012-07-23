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
	* Creates the metadata manager
	* 
	* @param dbal $db A database connection
	* @param string $extension_manager An instance of the phpbb extension manager
	* @param string $phpbb_root_path Path to the phpbb includes directory.
	* @param string $phpEx php file extension
	*/
	public function __construct($ext_name, dbal $db, phpbb_extension_manager $extension_manager, $phpbb_root_path, $phpEx = '.php', phpbb_template $template)
	{
		$this->phpbb_root_path = $phpbb_root_path;
		$this->db = $db;
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
	 * @param  string $element         All for all metadata that it has and is valid, otherwise specify which section you want by its shorthand term.
	 * @param  boolean $template_output   True if you want the requested metadata assigned to template vars
	 * @return array                   Contains all of the requested metadata
	 */
	public function get_metadata($element = 'all', $template_output = false)
	{
		// TODO: Check ext_name exists and is an extension that exists
		if (!$this->set_metadata_file())
		{
			return false;
		}

		if (!$this->fetch_metadata())
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
				if ($this->validate_name())
				{
					if ($template_output)
					{
						$template->assign_vars(array(
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
	 * This array handles the validation and cleaning of the array
	 * 
	 * @return array Contains the cleaned and validated metadata array
	 */
	private function clean_metadata_array()
	{		
		if (!$this->validate_name() || !$this->validate_type() || !$this->validate_licence() || !$this->validate_description() || !$this->validate_version() || !$this->validate_require_phpbb() || !$this->validate_extra_display_name())
		{
			return false;
		}
		
		$this->check_for_optional(true);

//		TODO: Remove all parts of the array we don't want or shouldn't be there due to nub mod authors
//		$this->metadata = $metadata_finished;

		return $this->metadata;
	}

	/**
	 * Validates the contents of the name field
	 * 
	 * @return boolean True when passes validation
	 */
	private function validate_name()
	{
		return preg_match('#^[a-zA-Z0-9_\x7f-\xff]{2,}/[a-zA-Z0-9_\x7f-\xff]{2,}$#', $this->metadata['name']);
	}

	/**
	 * Validates the contents of the type field
	 * 
	 * @return boolean True when passes validation
	 */
	private function validate_type()
	{
		return $this->metadata['type'] == 'phpbb3-extension';
	}

	/**
	 * Validates the contents of the description field
	 * 
	 * @return boolean True when passes validation
	 */
	private function validate_description()
	{
		return true;//preg_match('#^{10,}$#', $this->metadata['description']);
	}

	/**
	 * Validates the contents of the version field
	 * 
	 * @return boolean True when passes validation
	 */
	private function validate_version()
	{
		return preg_match('#^[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{1,2}#', $this->metadata['version']);
	}

	/**
	 * Validates the contents of the license field
	 * 
	 * @return boolean True when passes validation
	 */
	private function validate_licence()
	{
		// Nothing to validate except existence
		return isset($this->metadata['licence']);
	}

	/**
	 * Validates the contents of the phpbb requirement field
	 * 
	 * @return boolean True when passes validation
	 */
	private function validate_require_phpbb()
	{
		return (preg_match('#^[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{1,2}$#', $this->metadata['require']['phpbb']) && version_compare($this->metadata['require']['phpbb'], '3.1.0', '>='));
	}

	/**
	 * Validates the contents of the display name field
	 * 
	 * @return boolean True when passes validation
	 */
	private function validate_extra_display_name()
	{
		return true;//preg_match('#^[a-zA-Z0-9_]{2,0}$#', $this->metadata['name']);
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
	 * @return boolean True when passes validation
	 */
	private function validate_require_php()
	{
		return (preg_match('#^[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{1,2}$#', $this->metadata['require']['php']) && version_compare($this->metadata['require']['php'], phpversion(), '>='));
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
