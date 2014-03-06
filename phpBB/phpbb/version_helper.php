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
	/** @var \phpbb\cache\service */
	protected $cache;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\user */
	protected $user;

	public function __construct(\phpbb\cache\service $cache, \phpbb\config\config $config, \phpbb\user $user)
	{
		$this->cache = $cache;
		$this->config = $config;
		$this->user = $user;
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
		$current_version = $this->config['version'];

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
		$current_version = $this->config['version'];

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

		return ($this->is_stable($this->config['version']) && !defined('PHPBB_QA')) ? $info['stable'] : $info['unstable'];
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
		$info = $this->cache->get('versioncheck');

		if ($info === false || $force_update)
		{
			$errstr = $errno = '';
			$info = get_remote_file('version.phpbb.com', '/phpbb', 'versions.json', $errstr, $errno);

			if (!empty($errstr))
			{
				throw new \RuntimeException($errstr);
			}

			$info = json_decode($info, true);

			if (empty($info['stable']) || empty($info['unstable']))
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

			$this->cache->put('versioncheck', $info, 86400); // 24 hours
		}

		return $info;
	}
}
