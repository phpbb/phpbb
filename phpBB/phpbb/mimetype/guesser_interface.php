<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\mimetype;

/**
* @package mimetype
*/

interface guesser_interface
{
	/**
	* Returns whether this guesser is supported on the current OS
	*
	* @return bool True if guesser is supported, false if not
	*/
	public function is_supported();

	/**
	* Guess mimetype of supplied file
	*
	* @param string $file Path to file
	*
	* @return string Guess for mimetype of file
	*/
	public function guess($file, $file_name = '');

	/**
	* Get the guesser priority
	*
	* @return int Guesser priority
	*/
	public function get_priority();

	/**
	* Set the guesser priority
	*
	* @param int Guesser priority
	*
	* @return void
	*/
	public function set_priority($priority);
}
