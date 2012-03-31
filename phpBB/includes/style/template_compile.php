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

stream_filter_register('phpbb_template', 'phpbb_style_template_filter');

/**
* Extension of template class - Functions needed for compiling templates only.
*
* @package phpBB3
* @uses template_filter As a PHP stream filter to perform compilation of templates
*/
class phpbb_style_template_compile
{
	/**
	* Whether <!-- PHP --> tags are allowed
	*
	* @var bool
	*/
	private $allow_php;

	/**
	* Constructor.
	*
	* @param bool @allow_php Whether PHP code will be allowed in templates (inline PHP code, PHP tag and INCLUDEPHP tag)
	*/
	public function __construct($allow_php)
	{
		$this->allow_php = $allow_php;
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
		$source_handle = @fopen($source_file, 'rb');
		$destination_handle = @fopen($compiled_file, 'wb');

		if (!$source_handle || !$destination_handle)
		{
			return false;
		}

		@flock($destination_handle, LOCK_EX);

		$this->compile_stream_to_stream($source_handle, $destination_handle);

		@fclose($source_handle);
		@flock($destination_handle, LOCK_UN);
		@fclose($destination_handle);

		phpbb_chmod($compiled_file, CHMOD_READ | CHMOD_WRITE);

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
	* @return void
	*/
	private function compile_stream_to_stream($source_stream, $dest_stream)
	{
		stream_filter_append($source_stream, 'phpbb_template', null, array('allow_php' => $this->allow_php));
		stream_copy_to_stream($source_stream, $dest_stream);
	}
}
