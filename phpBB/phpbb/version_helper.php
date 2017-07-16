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

namespace phpbb;

use phpbb\exception\version_check_exception;

/**
 * Class to handle version checking and comparison
 */
class version_helper
{
	/**
	 * @var string Host
	 */
	protected $host = 'version.phpbb.com';

	/**
	 * @var string Path to file
	 */
	protected $path = '/phpbb';

	/**
	 * @var string File name
	 */
	protected $file = 'versions.json';

	/**
	 * @var bool Use SSL or not
	 */
	protected $use_ssl = false;

	/**
	 * @var string Current version installed
	 */
	protected $current_version;

	/**
	 * @var null|string Null to not force stability, 'unstable' or 'stable' to
	 *					force the corresponding stability
	 */
	protected $force_stability;

	/** @var \phpbb\cache\service */
	protected $cache;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\file_downloader */
	protected $file_downloader;

	protected $version_schema = array(
		'stable' => array(
			'current'		=> 'version',
			'download'		=> 'url',
			'announcement'	=> 'url',
			'eol'			=> 'url',
			'security'		=> 'bool',
		),
		'unstable' => array(
			'current'		=> 'version',
			'download'		=> 'url',
			'announcement'	=> 'url',
			'eol'			=> 'url',
			'security'		=> 'bool',
		),
	);

	/**
	 * Constructor
	 *
	 * @param \phpbb\cache\service $cache
	 * @param \phpbb\config\config $config
	 * @param \phpbb\file_downloader $file_downloader
	 */
	public function __construct(\phpbb\cache\service $cache, \phpbb\config\config $config, \phpbb\file_downloader $file_downloader)
	{
		$this->cache = $cache;
		$this->config = $config;
		$this->file_downloader = $file_downloader;

		if (defined('PHPBB_QA'))
		{
			$this->force_stability = 'unstable';
		}

		$this->current_version = $this->config['version'];
	}

	/**
	 * Set location to the file
	 *
	 * @param string $host Host (e.g. version.phpbb.com)
	 * @param string $path Path to file (e.g. /phpbb)
	 * @param string $file File name (Default: versions.json)
	 * @param bool $use_ssl Use SSL or not (Default: false)
	 * @return version_helper
	 */
	public function set_file_location($host, $path, $file = 'versions.json', $use_ssl = false)
	{
		$this->host = $host;
		$this->path = $path;
		$this->file = $file;
		$this->use_ssl = $use_ssl;

		return $this;
	}

	/**
	 * Set current version
	 *
	 * @param string $version The current version
	 * @return version_helper
	 */
	public function set_current_version($version)
	{
		$this->current_version = $version;

		return $this;
	}

	/**
	 * Over-ride the stability to force check to include unstable versions
	 *
	 * @param null|string Null to not force stability, 'unstable' or 'stable' to
	 * 						force the corresponding stability
	 * @return version_helper
	 */
	public function force_stability($stability)
	{
		$this->force_stability = $stability;

		return $this;
	}

	/**
	 * Wrapper for version_compare() that allows using uppercase A and B
	 * for alpha and beta releases.
	 *
	 * See http://www.php.net/manual/en/function.version-compare.php
	 *
	 * @param string $version1		First version number
	 * @param string $version2		Second version number
	 * @param string $operator		Comparison operator (optional)
	 *
	 * @return mixed				Boolean (true, false) if comparison operator is specified.
	 *								Integer (-1, 0, 1) otherwise.
	 */
	public function compare($version1, $version2, $operator = null)
	{
		return phpbb_version_compare($version1, $version2, $operator);
	}

	/**
	 * Check whether or not a version is "stable"
	 *
	 * Stable means only numbers OR a pl release
	 *
	 * @param string $version
	 * @return bool Bool true or false
	 */
	public function is_stable($version)
	{
		$matches = false;
		preg_match('/^[\d\.]+/', $version, $matches);

		if (empty($matches[0]))
		{
			return false;
		}

		return $this->compare($version, $matches[0], '>=');
	}

	/**
	* Gets the latest version for the current branch the user is on
	*
	* @param bool $force_update Ignores cached data. Defaults to false.
	* @param bool $force_cache Force the use of the cache. Override $force_update.
	* @return string
	* @throws version_check_exception
	*/
	public function get_latest_on_current_branch($force_update = false, $force_cache = false)
	{
		$versions = $this->get_versions_matching_stability($force_update, $force_cache);

		$self = $this;
		$current_version = $this->current_version;

		// Filter out any versions less than the current version
		$versions = array_filter($versions, function($data) use ($self, $current_version) {
			return $self->compare($data['current'], $current_version, '>=');
		});

		// Get the lowest version from the previous list.
		return array_reduce($versions, function($value, $data) use ($self) {
			if ($value === null || $self->compare($data['current'], $value, '<'))
			{
				return $data['current'];
			}

			return $value;
		});
	}

	/**
	 * Gets the latest update for the current branch the user is on
	 * Will suggest versions from newer branches when EoL has been reached
	 * and/or version from newer branch is needed for having all known security
	 * issues fixed.
	 *
	 * @param bool $force_update Ignores cached data. Defaults to false.
	 * @param bool $force_cache Force the use of the cache. Override $force_update.
	 * @return array Version info or empty array if there are no updates
	 * @throws \RuntimeException
	 */
	public function get_update_on_branch($force_update = false, $force_cache = false)
	{
		$versions = $this->get_versions_matching_stability($force_update, $force_cache);

		$self = $this;
		$current_version = $this->current_version;

		// Filter out any versions less than the current version
		$versions = array_filter($versions, function($data) use ($self, $current_version) {
			return $self->compare($data['current'], $current_version, '>=');
		});

		// Get the lowest version from the previous list.
		$update_info = array_reduce($versions, function($value, $data) use ($self, $current_version) {
			if ($value === null && $self->compare($data['current'], $current_version, '>='))
			{
				if (!$data['eol'] && (!$data['security'] || $self->compare($data['security'], $data['current'], '<=')))
				{
					return ($self->compare($data['current'], $current_version, '>')) ? $data : array();
				}
				else
				{
					return null;
				}
			}

			return $value;
		});

		return $update_info === null ? array() : $update_info;
	}

	/**
	 * Gets the latest extension update for the current phpBB branch the user is on
	 * Will suggest versions from newer branches when EoL has been reached
	 * and/or version from newer branch is needed for having all known security
	 * issues fixed.
	 *
	 * @param bool $force_update Ignores cached data. Defaults to false.
	 * @param bool $force_cache Force the use of the cache. Override $force_update.
	 * @return array Version info or empty array if there are no updates
	 * @throws \RuntimeException
	 */
	public function get_ext_update_on_branch($force_update = false, $force_cache = false)
	{
		$versions = $this->get_versions_matching_stability($force_update, $force_cache);

		$self = $this;
		$current_version = $this->current_version;

		// Get current phpBB branch from version, e.g.: 3.2
		preg_match('/^(\d+\.\d+).*$/', $this->config['version'], $matches);
		$current_branch = $matches[1];

		// Filter out any versions less than the current version
		$versions = array_filter($versions, function($data) use ($self, $current_version) {
			return $self->compare($data['current'], $current_version, '>=');
		});

		// Filter out any phpbb branches less than the current version
		$branches = array_filter(array_keys($versions), function($branch) use ($self, $current_branch) {
			return $self->compare($branch, $current_branch, '>=');
		});
		if (!empty($branches))
		{
			$versions = array_intersect_key($versions, array_flip($branches));
		}
		else
		{
			// If branches are empty, it means the current phpBB branch is newer than any branch the
			// extension was validated against. Reverse sort the versions array so we get the newest
			// validated release available.
			krsort($versions);
		}

		// Get the first available version from the previous list.
		$update_info = array_reduce($versions, function($value, $data) use ($self, $current_version) {
			if ($value === null && $self->compare($data['current'], $current_version, '>='))
			{
				if (!$data['eol'] && (!$data['security'] || $self->compare($data['security'], $data['current'], '<=')))
				{
					return $self->compare($data['current'], $current_version, '>') ? $data : array();
				}
				else
				{
					return null;
				}
			}

			return $value;
		});

		return $update_info === null ? array() : $update_info;
	}

	/**
	* Obtains the latest version information
	*
	* @param bool $force_update Ignores cached data. Defaults to false.
	* @param bool $force_cache Force the use of the cache. Override $force_update.
	* @return array
	* @throws version_check_exception
	*/
	public function get_suggested_updates($force_update = false, $force_cache = false)
	{
		$versions = $this->get_versions_matching_stability($force_update, $force_cache);

		$self = $this;
		$current_version = $this->current_version;

		// Filter out any versions less than or equal to the current version
		return array_filter($versions, function($data) use ($self, $current_version) {
			return $self->compare($data['current'], $current_version, '>');
		});
	}

	/**
	* Obtains the latest version information matching the stability of the current install
	*
	* @param bool $force_update Ignores cached data. Defaults to false.
	* @param bool $force_cache Force the use of the cache. Override $force_update.
	* @return array Version info
	* @throws version_check_exception
	*/
	public function get_versions_matching_stability($force_update = false, $force_cache = false)
	{
		$info = $this->get_versions($force_update, $force_cache);

		if ($this->force_stability !== null)
		{
			return ($this->force_stability === 'unstable') ? $info['unstable'] : $info['stable'];
		}

		return ($this->is_stable($this->current_version)) ? $info['stable'] : $info['unstable'];
	}

	/**
	* Obtains the latest version information
	*
	* @param bool $force_update Ignores cached data. Defaults to false.
	* @param bool $force_cache Force the use of the cache. Override $force_update.
	* @return array Version info, includes stable and unstable data
	* @throws version_check_exception
	*/
	public function get_versions($force_update = false, $force_cache = false)
	{
		$cache_file = '_versioncheck_' . $this->host . $this->path . $this->file . $this->use_ssl;

		$info = $this->cache->get($cache_file);

		if ($info === false && $force_cache)
		{
			throw new version_check_exception('VERSIONCHECK_FAIL');
		}
		else if ($info === false || $force_update)
		{
			$info = $this->file_downloader->get($this->host, $this->path, $this->file, $this->use_ssl ? 443 : 80);
			$error_string = $this->file_downloader->get_error_string();

			if (!empty($error_string))
			{
				throw new version_check_exception($error_string);
			}

			$info = json_decode($info, true);

			// Sanitize any data we retrieve from a server
			if (!empty($info))
			{
				$json_sanitizer = function (&$value, $key) {
					$type_cast_helper = new \phpbb\request\type_cast_helper();
					$type_cast_helper->set_var($value, $value, gettype($value), true);
				};
				array_walk_recursive($info, $json_sanitizer);
			}

			if (empty($info['stable']) && empty($info['unstable']))
			{
				throw new version_check_exception('VERSIONCHECK_FAIL');
			}

			$info['stable'] = (empty($info['stable'])) ? array() : $info['stable'];
			$info['unstable'] = (empty($info['unstable'])) ? $info['stable'] : $info['unstable'];

			$info = $this->validate_versions($info);

			$this->cache->put($cache_file, $info, 86400); // 24 hours
		}

		return $info;
	}

	/**
	 * Validate versions info input
	 *
	 * @param array $versions_info Decoded json data array. Will be modified
	 *		and cleaned by this method
	 *
	 * @return array Versions info array
	 * @throws version_check_exception
	 */
	public function validate_versions($versions_info)
	{
		$array_diff = array_diff_key($versions_info, array($this->version_schema));

		// Remove excessive data
		if (count($array_diff) > 0)
		{
			$old_versions_info = $versions_info;
			$versions_info = array(
				'stable'	=> !empty($old_versions_info['stable']) ? $old_versions_info['stable'] : array(),
				'unstable'	=> !empty($old_versions_info['unstable']) ? $old_versions_info['unstable'] : array(),
			);
			unset($old_versions_info);
		}

		foreach ($versions_info as $stability_type => &$versions_data)
		{
			foreach ($versions_data as $branch => &$version_data)
			{
				if (!preg_match('/^[0-9a-z\-\.]+$/i', $branch))
				{
					unset($versions_data[$branch]);
					continue;
				}

				$stability_diff = array_diff_key($version_data, $this->version_schema[$stability_type]);

				if (count($stability_diff) > 0)
				{
					$old_version_data = $version_data;
					$version_data = array();
					foreach ($this->version_schema[$stability_type] as $key => $value)
					{
						if (isset($old_version_data[$key]))
						{
							$version_data[$key] = $old_version_data[$key];
						}
					}
					unset($old_version_data);
				}

				foreach ($version_data as $key => &$value)
				{
					if (!isset($this->version_schema[$stability_type][$key]))
					{
						unset($version_data[$key]);
						throw new version_check_exception('VERSIONCHECK_INVALID_ENTRY');
					}

					switch ($this->version_schema[$stability_type][$key])
					{
						case 'bool':
							$value = (bool) $value;
						break;

						case 'url':
							if (!empty($value) && !preg_match('#^' . get_preg_expression('url') . '$#iu', $value) &&
								!preg_match('#^' . get_preg_expression('www_url') . '$#iu', $value))
							{
								throw new version_check_exception('VERSIONCHECK_INVALID_URL');
							}
						break;

						case 'version':
							if (!empty($value) && !preg_match(get_preg_expression('semantic_version'), $value))
							{
								throw new version_check_exception('VERSIONCHECK_INVALID_VERSION');
							}
						break;

						default:
							// Shouldn't be possible to trigger this
							throw new version_check_exception('VERSIONCHECK_INVALID_ENTRY');
					}
				}
			}
		}

		return $versions_info;
	}
}
