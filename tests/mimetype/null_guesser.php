<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\mimetype;

class null_guesser
{
	protected $is_supported;

	public function __construct($is_supported = true)
	{
		$this->is_supported = $is_supported;
	}

	public function is_supported()
	{
		return $this->is_supported;
	}

	public function guess($file)
	{
		return null;
	}
}
