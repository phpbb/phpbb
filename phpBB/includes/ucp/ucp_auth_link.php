<?php
/**
*
* @package notifications
* @copyright (c) 2013 phpBB Group
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

class ucp_auth_link
{
	public $u_action;

	public function main($id, $mode)
	{
		$this->tpl_name = 'ucp_auth_link';
		$this->page_title = 'UCP_AUTH_LINK';
	}
}
