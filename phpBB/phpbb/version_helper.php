<?php
/**
*
* @package phpBB3
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb;

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

	/** @var \phpbb\user */
	protected $user;

	/**
	 * Constructor
	 *
	 * @param \phpbb\cache\service $cache
	 * @param \phpbb\config\config $config
	 * @param \phpbb\user $user
	 */
	public function __construct(\phpbb\cache\service $cache, \phpbb\config\config $config, \phpbb\user $user)
	{
		$this->cache = $cache;
		$this->config = $config;
		$this->user = $user;

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
	 * @return version_helper
	 */
	public function set_file_location($host, $path, $file = 'versions.json')
	{
		$this->host = $host;
		$this->path = $path;
		$this->file = $file;

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
	 * @return string
	 * @throws \RuntimeException
	 */
	public function get_latest_on_current_branch($force_update = false)
	{
		$versions = $this->get_versions_matching_stability($force_update);

		$self = $this;
		$current_version = $this->current_version;

		// Filter out any versions less than to the current version
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
	 * Obtains the latest version information
	 *
	 * @param bool $force_update Ignores cached data. Defaults to false.
	 * @return string
	 * @throws \RuntimeException
	 */
	public function get_suggested_updates($force_update = false)
	{
		$versions = $this->get_versions_matching_stability($force_update);

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
	 * @return string Version info
	 * @throws \RuntimeException
	 */
	public function get_versions_matching_stability($force_update = false)
	{
		$info = $this->get_versions($force_update);

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
	 * @return string Version info, includes stable and unstable data
	 * @throws \RuntimeException
	 */
	public function get_versions($force_update = false)
	{
		$cache_file = 'versioncheck_' . $this->host . $this->path . $this->file;

		$info = $this->cache->get($cache_file);

		if ($info === false || $force_update)
		{
			$errstr = $errno = '';
			$info = get_remote_file($this->host, $this->path, $this->file, $errstr, $errno);

			if (!empty($errstr))
			{
				throw new \RuntimeException($errstr);
			}

			$info = json_decode($info, true);

			if (empty($info['stable']) && empty($info['unstable']))
			{
				$this->user->add_lang('acp/common');

				throw new \RuntimeException($this->user->lang('VERSIONCHECK_FAIL'));
			}

			// Replace & with &amp; on announcement links
			foreach ($info as $stability => $branches)
			{
				foreach ($branches as $branch => $branch_data)
				{
					$info[$stability][$branch]['announcement'] = str_replace('&', '&amp;', $branch_data['announcement']);
				}
			}

			$info['stable'] = (empty($info['stable'])) ? array() : $info['stable'];
			$info['unstable'] = (empty($info['unstable'])) ? $info['stable'] : $info['unstable'];

			$this->cache->put($cache_file, $info, 86400); // 24 hours
		}

		return $info;
	}
}
