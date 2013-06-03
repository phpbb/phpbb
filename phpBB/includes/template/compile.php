<?php
/**
*
* @package phpBB3
* @copyright (c) 2005 phpBB Group
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

stream_filter_register('phpbb_template', 'phpbb_template_filter');

/**
* Extension of template class - Functions needed for compiling templates only.
*
* @package phpBB3
* @uses template_filter As a PHP stream filter to perform compilation of templates
*/
class phpbb_template_compile
{
	/**
	* Array of parameters to forward to template filter
	*
	* @var array
	*/
	private $filter_params;

	/**
	* Array of default parameters
	*
	* @var array
	*/
	private $default_filter_params;

	/**
	* Constructor.
	*
	* @param bool $allow_php Whether PHP code will be allowed in templates (inline PHP code, PHP tag and INCLUDEPHP tag)
	* @param array $style_names Name of style to which the template being compiled belongs and parents in style tree order
	* @param phpbb_style_resource_locator $locator Resource locator
	* @param string $phpbb_root_path Path to phpBB root directory
	* @param phpbb_extension_manager $extension_manager Extension manager to use for finding template fragments in extensions; if null, template events will not be invoked
	* @param phpbb_user $user Current user
	*/
	public function __construct($allow_php, $style_names, $locator, $phpbb_root_path, $extension_manager = null, $user = null)
	{
		$this->filter_params = $this->default_filter_params = array(
			'allow_php'			=> $allow_php,
			'style_names'		=> $style_names,
			'locator'			=> $locator,
			'phpbb_root_path'	=> $phpbb_root_path,
			'extension_manager'	=> $extension_manager,
			'user'          	=> $user,
			'template_compile'	=> $this,
			'cleanup'			=> true,
		);
	}

	/**
	* Set filter parameters
	*
	* @param array $params Array of parameters (will be merged onto $this->filter_params)
	*/
	public function set_filter_params($params)
	{
		$this->filter_params = array_merge(
			$this->filter_params,
			$params
		);
	}

	/**
	* Reset filter parameters to their default settings
	*/
	public function reset_filter_params()
	{
		$this->filter_params = $this->default_filter_params;
	}

	/**
	* Compiles template in $source_file and writes compiled template to
	* cache directory
	*
	* @param string $handle Template handle to compile
	* @param string $source_file Source template file
	* @return bool Return true on success otherwise false
	*/
	public function compile_file_to_file($source_file, $compiled_file)
	{
		$lock = new phpbb_lock_flock($compiled_file);
		$lock->acquire();

		$source_handle = @fopen($source_file, 'rb');
		$destination_handle = @fopen($compiled_file, 'wb');

		if (!$source_handle || !$destination_handle)
		{
			return false;
		}

		$this->compile_stream_to_stream($source_handle, $destination_handle);

		@fclose($source_handle);
		@fclose($destination_handle);

		phpbb_chmod($compiled_file, CHMOD_READ | CHMOD_WRITE);

		$lock->release();

		clearstatcache();

		return true;
	}

	/**
	* Compiles a template located at $source_file.
	*
	* Returns PHP source suitable for eval().
	*
	* @param string $source_file Source template file
	* @return string|bool Return compiled code on successful compilation otherwise false
	*/
	public function compile_file($source_file)
	{
		$source_handle = @fopen($source_file, 'rb');
		$destination_handle = @fopen('php://temp' ,'r+b');

		if (!$source_handle || !$destination_handle)
		{
			return false;
		}

		$this->compile_stream_to_stream($source_handle, $destination_handle);

		@fclose($source_handle);

		rewind($destination_handle);
		$contents = stream_get_contents($destination_handle);
		@fclose($dest_handle);

		return $contents;
	}

	/**
	* Compiles contents of $source_stream into $dest_stream.
	*
	* A stream filter is appended to $source_stream as part of the
	* process.
	*
	* @param resource $source_stream Source stream
	* @param resource $dest_stream Destination stream
	* @return null
	*/
	private function compile_stream_to_stream($source_stream, $dest_stream)
	{
		stream_filter_append($source_stream, 'phpbb_template', null, $this->filter_params);
		stream_copy_to_stream($source_stream, $dest_stream);
	}
}
