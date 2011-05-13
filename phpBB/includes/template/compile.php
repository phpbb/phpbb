<?php
/**
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2005 phpBB Group, sections (c) 2001 ispi of Lincoln Inc
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
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
* psoTFX, phpBB Development Team - Completion of file caching, decompilation
* routines and implementation of conditionals/keywords and associated changes
*
* The interface was inspired by PHPLib templates, and the template file (formats are
* quite similar)
*
* The keyword/conditional implementation is currently based on sections of code from
* the Smarty templating engine (c) 2001 ispi of Lincoln, Inc. which is released
* (on its own and in whole) under the LGPL. Section 3 of the LGPL states that any code
* derived from an LGPL application may be relicenced under the GPL, this applies
* to this source
*
* DEFINE directive inspired by a request by Cyberalien
*
* @package phpBB3
* @uses template_filter As a PHP stream filter to perform compilation of templates
*/
class phpbb_template_compile
{
	/**
	* Compiles template in $source_file and writes compiled template to
	* cache directory
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
	* Returns PHP source suitable for eval().
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
		stream_filter_append($source_stream, 'phpbb_template');
		stream_copy_to_stream($source_stream, $dest_stream);
	}
}
