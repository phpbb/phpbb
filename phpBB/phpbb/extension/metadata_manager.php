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
	* List of validations
	* @var array
	*/
	protected $validations;

	/**
	* Creates the metadata manager
	*
	* @param string				$ext_name			Name (including vendor) of the extension
	* @param string				$ext_path			Path to the extension directory including root path
	*/
	public function __construct($ext_name, $ext_path)
	{
		$this->ext_name = $ext_name;
		$this->metadata = array();
		$this->metadata_file = $ext_path . 'composer.json';

		// Initialize validations
		$this->validations = array(
			// Combined
			'all'			=> ['enable', 'display'],
			'enable'		=> ['dir', 'require_php', 'require_phpbb'],
			'display'		=> ['fields', 'authors'],
			'fields'		=> ['name', 'type', 'license', 'version'],
			'version-check'	=> ['version', 'version_check'],
			// Methods
			'authors'		=> null,
			'dir'			=> null,
			'require_php'	=> null,
			'require_phpbb'	=> null,
			'version_check'	=> null,
			// Fields
			'name'			=> '#^[a-zA-Z0-9_\x7f-\xff]{2,}/[a-zA-Z0-9_\x7f-\xff]{2,}$#',
			'type'			=> '#^phpbb-extension$#',
			'license'		=> '#.+#',
			'version'		=> '#.+#',
		);
	}

	/**
	* Processes and gets the metadata requested
	*
	* @param  string $element			All for all metadata that it has and is valid, otherwise specify which section you want by its shorthand term.
	* @return mixed						Array containing all of the requested metadata or string for specific metadata elements, throws an exception on failure
	* @throws \phpbb\extension\exception
	*/
	public function get_metadata($element = 'all')
	{
		// Fetch and clean the metadata if not done yet
		if ($this->metadata === array())
		{
			$this->fetch_metadata_from_file();
		}

		switch ($element)
		{
			case 'all':
			default:
				$this->validate();
				return $this->metadata;
			break;

			case 'version':
			case 'name':
				$this->validate($element);
				return $this->metadata[$element];
			break;

			case 'display-name':
				return (isset($this->metadata['extra']['display-name'])) ? $this->metadata['extra']['display-name'] : $this->get_metadata('name');
			break;

			case 'version-check':
				$this->validate($element);
				return array_merge(array('current_version' => $this->metadata['version']), $this->metadata['extra']['version-check']);
			break;
		}
	}

	/**
	* Gets the metadata file contents and cleans loaded file
	*
	* @throws \phpbb\extension\exception
	*/
	private function fetch_metadata_from_file()
	{
		if (!file_exists($this->metadata_file))
		{
			throw new \phpbb\extension\exception('FILE_NOT_FOUND', array($this->metadata_file));
		}

		if (!($file_contents = file_get_contents($this->metadata_file)))
		{
			throw new \phpbb\extension\exception('FILE_CONTENT_ERR', array($this->metadata_file));
		}

		if (($metadata = json_decode($file_contents, true)) === null)
		{
			throw new \phpbb\extension\exception('FILE_JSON_DECODE_ERR', array($this->metadata_file));
		}

		array_walk_recursive($metadata, array($this, 'sanitize_json'));
		$this->metadata = $metadata;
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
	* Validate fields
	*
	* @param string $name  ("all" for display and enable validation
	* 						"display" for name, type, license, version and authors
	* 						"name", "type", "license", "version" according to format
	*						"authors", "enable", "dir", "require_php", "require_phpbb" as proxy)
	* @return Bool True if valid, throws an exception if invalid
	* @throws \phpbb\extension\exception
	*/
	public function validate($name = 'display')
	{
		// Fetch and clean the metadata if not done yet
		if ($this->metadata === array())
		{
			$this->fetch_metadata_from_file();
		}

		// If there is a validation set, validate
		if (isset($this->validations[$name]))
		{
			$validation = $this->validations[$name];
			// Validate via a specific validation method
			if ($validation === null)
			{
				$this->{'validate_' . $name}();
			}
			// Base fields that have to adhere to a format
			else if (is_string($validation))
			{
				if (!isset($this->metadata[$name]))
				{
					throw new \phpbb\extension\exception('META_FIELD_NOT_SET', array($name));
				}
				if (!preg_match($validation, $this->metadata[$name]))
				{
					throw new \phpbb\extension\exception('META_FIELD_INVALID', array($name));
				}
			}
			// Composed validations
			else if (is_array($validation))
			{
				foreach ($validation as $val)
				{
					$this->validate($val);
				}
			}
		}

		return true;
	}

	/**
	* Validates the contents of the authors field
	*
	* @return boolean True when passes validation, throws exception if invalid
	* @throws \phpbb\extension\exception
	* @internal		Should not be used directly. As of phpBB 4.0, the method will become protected.
	*/
	public function validate_authors()
	{
		if (empty($this->metadata['authors']))
		{
			throw new \phpbb\extension\exception('META_FIELD_NOT_SET', array('authors'));
		}

		foreach ($this->metadata['authors'] as $author)
		{
			if (!isset($author['name']))
			{
				throw new \phpbb\extension\exception('META_FIELD_NOT_SET', array('author name'));
			}
		}

		return true;
	}

	/**
	* This array handles the verification that this extension can be enabled on this board
	*
	* @return bool True if validation succeeded, throws an exception if invalid
	* @throws \phpbb\extension\exception
	* @internal		Should not be used directly. As of phpBB 4.0, the method will become protected.
	*/
	public function validate_enable()
	{
		// Check for valid directory & phpBB, PHP versions
		return $this->validate_dir() && $this->validate_require_phpbb() && $this->validate_require_php();
	}

	/**
	* Validates the most basic directory structure to ensure it follows <vendor>/<ext> convention.
	*
	* @return boolean True when passes validation, throws an exception if invalid
	* @throws \phpbb\extension\exception
	* @internal		Should not be used directly. As of phpBB 4.0, the method will become protected.
	*/
	public function validate_dir()
	{
		if (substr_count($this->ext_name, '/') !== 1 || $this->ext_name != $this->get_metadata('name'))
		{
			throw new \phpbb\extension\exception('EXTENSION_DIR_INVALID');
		}

		return true;
	}


	/**
	* Validates the contents of the phpbb requirement field
	*
	* @return boolean True when passes validation, throws an exception if invalid
	* @throws \phpbb\extension\exception
	* @internal		Should not be used directly. As of phpBB 4.0, the method will become protected.
	*/
	public function validate_require_phpbb()
	{
		if (!isset($this->metadata['extra']['soft-require']['phpbb/phpbb']))
		{
			throw new \phpbb\extension\exception('META_FIELD_NOT_SET', array('soft-require'));
		}

		return true;
	}

	/**
	* Validates the contents of the php requirement field
	*
	* @return boolean True when passes validation, throws an exception if invalid
	* @throws \phpbb\extension\exception
	* @internal		Should not be used directly. As of phpBB 4.0, the method will become protected.
	*/
	public function validate_require_php()
	{
		if (!isset($this->metadata['require']['php']))
		{
			throw new \phpbb\extension\exception('META_FIELD_NOT_SET', array('require php'));
		}

		return true;
	}

	/**
	* Validates the version check information
	*
	* @return boolean True when passes validation, throws an exception if invalid
	* @throws \phpbb\extension\exception
	*/
	protected function validate_version_check()
	{
		if (!isset($this->metadata['extra']['version-check']) ||
			!isset($this->metadata['extra']['version-check']['host']) ||
			!isset($this->metadata['extra']['version-check']['directory']) ||
			!isset($this->metadata['extra']['version-check']['filename']))
		{
			throw new \phpbb\extension\exception('NO_VERSIONCHECK');
		}

		return true;
	}
}
