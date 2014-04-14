<?php
/**
*
* @package extension
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\extension;

/**
 * Class to handle version checking and comparison for the extensions
 */
class version_helper
{
	/**
	 * @var string Host
	 */
	protected $host = '';

	/**
	 * @var string Path to file
	 */
	protected $path = '';

	/**
	 * @var string File name
	 */
	protected $file = '';

	/**
	 * @var string Extension name
	 */
	protected $extension = '';

	/**
	 * @var string Current version installed
	 */
	protected $current_version = '';

	/** @var \phpbb\cache\service */
	protected $cache;

	/** @var \phpbb\user */
	protected $user;

	/**
	 * @var array Metadata of the latest versio
	 */
	protected $latest_version_metadata;

	/**
	 * Constructor
	 *
	 * @param \phpbb\cache\service $cache
	 * @param \phpbb\user $user
	 */
	public function __construct(\phpbb\cache\service $cache, \phpbb\user $user)
	{
		$this->cache = $cache;
		$this->user = $user;
	}

	/**
	 * Set the informations concerning the current version from the metadata
	 *
	 * @param array $metadata
	 * @throws \RuntimeException
	 */
	public function set_metadata($metadata)
	{
		if (! isset($metadata['extra']['version-check']))
		{
			throw new \RuntimeException($this->user->lang('NO_VERSIONCHECK'));
		}

		$meta_vc = $metadata['extra']['version-check'];
		
		$this->set_file_location($meta_vc['host'], $meta_vc['directory'], $meta_vc['filename']);
		$this->set_extension($metadata['name']);
		$this->set_current_version($metadata['version']);
	}

	/**
	 * Set the name of the extention
	 *
	 * @param string Name of the extension
	 * @return version_helper
	 */
	public function set_extension($extension)
	{
		$this->extension = $extension;

		return $this;
	}

	/**
	 * Set location to the file
	 *
	 * @param string $host Host (e.g. version.phpbb.com)
	 * @param string $path Path to file (e.g. /phpbb)
	 * @param string $file File name (Default: composer.json)
	 * @return version_helper
	 */
	public function set_file_location($host, $path, $file = 'composer.json')
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
	 * Say if the extension is up to date or not
	 *
	 * The informations about the lastest version are retrieved if needed
	 * 
	 * @return bool true if the version is up to date
	 * @throws \RuntimeException
	 */
	public function is_uptodate()
	{
		if (empty($this->latest_version_metadata))
		{
			$this->get_version();
		}

		return $this->compare($this->current_version, $this->latest_version_metadata['version'], '>=');
	}

	/**
	 * Return the latest version number
	 *
	 * @return The latest version number ready to be displayed.
	 */
	public function get_latest_version() {
		if (empty($this->latest_version_metadata))
		{
			$this->get_version();
		}
		
		return htmlspecialchars($this->latest_version_metadata['version']);
	}

	/**
	 * Return the latest download link
	 *
	 * @return The latest download link if existed, an empty string otherwise.
	 */
	public function get_latest_download_link() {
		if (empty($this->latest_version_metadata))
		{
			$this->get_version();
		}
		
		return isset($this->latest_version_metadata['extra']['download']) ? $this->latest_version_metadata['extra']['download']: '';
	}

	/**
	 * Return the latest announcement link
	 *
	 * @return The latest announcement link if existed, an empty string otherwise.
	 */
	public function get_latest_announcement_link() {
		if (empty($this->latest_version_metadata))
		{
			$this->get_version();
		}
		
		return isset($this->latest_version_metadata['extra']['announcement']) ? $this->latest_version_metadata['extra']['announcement']: '';
	}

	/**
	 * Obtains the latest version information
	 *
	 * @param bool $force_update Ignores cached data. Defaults to false.
	 * @return string Version info
	 * @throws \RuntimeException
	 */
	public function get_version($force_update = false)
	{
echo 'Force : ' . $force_update;
		$cache_file = 'versioncheck_ext_' . $this->extension . ':' . $this->host . $this->path . $this->file;

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

			if (empty($info['version']))
			{
				$this->user->add_lang('acp/common');

				throw new \RuntimeException($this->user->lang('VERSIONCHECK_FAIL'));
			}

			// Replace & with &amp; on announcement and download links
			if (isset($info['extra']['announcement'])) 
			{
				$info['extra']['announcement'] = str_replace('&', '&amp;', $info['extra']['announcement']);
			}
			
			if (isset($info['extra']['download'])) 
			{
				$info['extra']['download'] = str_replace('&', '&amp;', $info['extra']['download']);
			}
			
			$this->cache->put($cache_file, $info, 86400); // 24 hours
		}

		$this->latest_version_metadata = $info;

		return $info;
	}
}
