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

namespace phpbb\extension;

/**
* The extension metadata manager validates and gets meta-data for extensions
*/
class metadata_manager
{
	/**
	* phpBB Config instance
	* @var \phpbb\config\config
	*/
	protected $config;

	/**
	* phpBB Extension Manager
	* @var \phpbb\extension\manager
	*/
	protected $extension_manager;

	/**
	* phpBB Template instance
	* @var \phpbb\template\template
	*/
	protected $template;

	/**
	* phpBB User instance
	* @var \phpbb\user
	*/
	protected $user;

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
	* @param \phpbb\config\config		$config				phpBB Config instance
	* @param \phpbb\extension\manager	$extension_manager	An instance of the phpBB extension manager
	* @param \phpbb\template\template	$template			phpBB Template instance
	* @param \phpbb\user 		$user 				User instance
	* @param string				$phpbb_root_path	Path to the phpbb includes directory.
	*/
	public function __construct($ext_name, \phpbb\config\config $config, \phpbb\extension\manager $extension_manager, \phpbb\template\template $template, \phpbb\user $user, $phpbb_root_path)
	{
		$this->config = $config;
		$this->extension_manager = $extension_manager;
		$this->template = $template;
		$this->user = $user;
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
	* @throws \phpbb\extension\exception
	*/
	private function set_metadata_file()
	{
		$ext_filepath = $this->extension_manager->get_extension_path($this->ext_name);
		$metadata_filepath = $this->phpbb_root_path . $ext_filepath . 'composer.json';

		$this->metadata_file = $metadata_filepath;

		if (!file_exists($this->metadata_file))
		{
			throw new \phpbb\extension\exception($this->user->lang('FILE_NOT_FOUND', $this->metadata_file));
		}
	}

	/**
	* Gets the contents of the composer.json file
	*
	* @return bool True if success, throws an exception on failure
	* @throws \phpbb\extension\exception
	*/
	private function fetch_metadata()
	{
		if (!file_exists($this->metadata_file))
		{
			throw new \phpbb\extension\exception($this->user->lang('FILE_NOT_FOUND', $this->metadata_file));
		}
		else
		{
			if (!($file_contents = file_get_contents($this->metadata_file)))
			{
				throw new \phpbb\extension\exception($this->user->lang('FILE_CONTENT_ERR', $this->metadata_file));
			}

			if (($metadata = json_decode($file_contents, true)) === null)
			{
				throw new \phpbb\extension\exception($this->user->lang('FILE_JSON_DECODE_ERR', $this->metadata_file));
			}

			array_walk_recursive($metadata, array($this, 'sanitize_json'));
			$this->metadata = $metadata;

			return true;
		}
	}

	/**
	 * Sanitize input from JSON array using htmlspecialchars()
	 *
	 * @param mixed		$value	Value of array row
	 * @param string	$key	Key of array row
	 */
	public function sanitize_json(&$value, $key)
	{
		$value = htmlspecialchars($value);
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
	* @throws \phpbb\extension\exception
	*/
	public function validate($name = 'display')
	{
		// Basic fields
		$fields = array(
			'name'		=> '#^[a-zA-Z0-9_\x7f-\xff]{2,}/[a-zA-Z0-9_\x7f-\xff]{2,}$#',
			'type'		=> '#^phpbb-extension$#',
			'license'	=> '#.+#',
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
						throw new \phpbb\extension\exception($this->user->lang('META_FIELD_NOT_SET', $name));
					}

					if (!preg_match($fields[$name], $this->metadata[$name]))
					{
						throw new \phpbb\extension\exception($this->user->lang('META_FIELD_INVALID', $name));
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
	* @throws \phpbb\extension\exception
	*/
	public function validate_authors()
	{
		if (empty($this->metadata['authors']))
		{
			throw new \phpbb\extension\exception($this->user->lang('META_FIELD_NOT_SET', 'authors'));
		}

		foreach ($this->metadata['authors'] as $author)
		{
			if (!isset($author['name']))
			{
				throw new \phpbb\extension\exception($this->user->lang('META_FIELD_NOT_SET', 'author name'));
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
		// Check for valid directory & phpBB, PHP versions
		if (!$this->validate_dir() || !$this->validate_require_phpbb() || !$this->validate_require_php())
		{
			return false;
		}

		return true;
	}

	/**
	* Validates the most basic directory structure to ensure it follows <vendor>/<ext> convention.
	*
	* @return boolean True when passes validation
	*/
	public function validate_dir()
	{
		return (substr_count($this->ext_name, '/') === 1 && $this->ext_name == $this->get_metadata('name'));
	}


	/**
	* Validates the contents of the phpbb requirement field
	*
	* @return boolean True when passes validation
	*/
	public function validate_require_phpbb()
	{
		if (!isset($this->metadata['extra']['soft-require']['phpbb/phpbb']))
		{
			return false;
		}

		return true;
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
			return false;
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
			'META_NAME'			=> $this->metadata['name'],
			'META_TYPE'			=> $this->metadata['type'],
			'META_DESCRIPTION'	=> (isset($this->metadata['description'])) ? $this->metadata['description'] : '',
			'META_HOMEPAGE'		=> (isset($this->metadata['homepage'])) ? $this->metadata['homepage'] : '',
			'META_VERSION'		=> (isset($this->metadata['version'])) ? $this->metadata['version'] : '',
			'META_TIME'			=> (isset($this->metadata['time'])) ? $this->metadata['time'] : '',
			'META_LICENSE'		=> $this->metadata['license'],

			'META_REQUIRE_PHP'		=> (isset($this->metadata['require']['php'])) ? $this->metadata['require']['php'] : '',
			'META_REQUIRE_PHP_FAIL'	=> !$this->validate_require_php(),

			'META_REQUIRE_PHPBB'		=> (isset($this->metadata['extra']['soft-require']['phpbb/phpbb'])) ? $this->metadata['extra']['soft-require']['phpbb/phpbb'] : '',
			'META_REQUIRE_PHPBB_FAIL'	=> !$this->validate_require_phpbb(),

			'META_DISPLAY_NAME'	=> (isset($this->metadata['extra']['display-name'])) ? $this->metadata['extra']['display-name'] : '',
		));

		foreach ($this->metadata['authors'] as $author)
		{
			$this->template->assign_block_vars('meta_authors', array(
				'AUTHOR_NAME'		=> $author['name'],
				'AUTHOR_EMAIL'		=> (isset($author['email'])) ? $author['email'] : '',
				'AUTHOR_HOMEPAGE'	=> (isset($author['homepage'])) ? $author['homepage'] : '',
				'AUTHOR_ROLE'		=> (isset($author['role'])) ? $author['role'] : '',
			));
		}
	}
}
