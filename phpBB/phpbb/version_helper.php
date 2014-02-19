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
	protected $cache;
	protected $config;
	protected $user;

	public function __construct($cache, $config, $user)
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
		$version1 = strtolower($version1);
		$version2 = strtolower($version2);

		if (is_null($operator))
		{
			return version_compare($version1, $version2);
		}
		else
		{
			return version_compare($version1, $version2, $operator);
		}
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
			$info = $this->get_remote_file('version.phpbb.com', '/phpbb', 'versions.json');

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

	/**
	 * Get remote file
	 *
	 * @param string $host			Host, e.g. version.phpbb.com
	 * @param string $directory		Directory, e.g. /phpbb
	 * @param string $filename		Filename, e.g. versions.json
	 * @param int $port				Port
	 * @param int $timeout			Timeout (seconds)
	 * @return string				Remote file contents
	 * @throws \RuntimeException
	 */
	public function get_remote_file($host, $directory, $filename, $port = 80, $timeout = 6)
	{
		$errstr = $errno = false;

		if ($fsock = @fsockopen($host, $port, $errno, $errstr, $timeout))
		{
			@fputs($fsock, "GET $directory/$filename HTTP/1.0\r\n");
			@fputs($fsock, "HOST: $host\r\n");
			@fputs($fsock, "Connection: close\r\n\r\n");

			$timer_stop = time() + $timeout;
			stream_set_timeout($fsock, $timeout);

			$file_info = '';
			$get_info = false;

			while (!@feof($fsock))
			{
				if ($get_info)
				{
					$file_info .= @fread($fsock, 1024);
				}
				else
				{
					$line = @fgets($fsock, 1024);
					if ($line == "\r\n")
					{
						$get_info = true;
					}
					else if (stripos($line, '404 not found') !== false)
					{
						throw new \RuntimeException($this->user->lang('FILE_NOT_FOUND') . ': ' . $filename);
					}
				}

				$stream_meta_data = stream_get_meta_data($fsock);

				if (!empty($stream_meta_data['timed_out']) || time() >= $timer_stop)
				{
					throw new \RuntimeException($this->user->lang('FSOCK_TIMEOUT'));
				}
			}
			@fclose($fsock);
		}
		else
		{
			if ($errstr)
			{
				throw new \RuntimeException(utf8_convert_message($errstr));
			}
			else
			{
				throw new \RuntimeException($this->user->lang('FSOCK_DISABLED'));
			}
		}

		return $file_info;
	}
}
