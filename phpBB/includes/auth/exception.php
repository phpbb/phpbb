<?php
/**
*
* @package auth
* @copyright (c) 2012 phpBB Group
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

/**
* A wrapper class to allow distinguishing authentication exceptions from other
* types of exceptions.
*
* @package auth
*/
class phpbb_auth_exception extends RuntimeException
{

}
