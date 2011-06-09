<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* Mock user class.
* This class is used when tests invoke phpBB code expecting to have a global
* user object, to avoid instantiating the actual user object.
* It has a minimum amount of functionality, just to make tests work.
*/
class phpbb_mock_user
{
	public $host = "testhost";
	public $page = array('root_script_path' => '/');
}
