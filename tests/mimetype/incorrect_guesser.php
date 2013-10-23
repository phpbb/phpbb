<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\mimetype;

class incorrect_guesser
{
	public function guess($file)
	{
		return 'image/jpeg';
	}
}
