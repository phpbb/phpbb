<?php
/**
*
* @package acp
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
* @package acp
*/
class acp_auth_info {
	function module()
    {
        return array(
            'filename'    => 'acp_auth',
            'title'        => 'ACP_AUTH',
            'version'    => '1.0.0',
            'modes'        => array(
                'index'        => array('title' => 'ACP_AUTH_INDEX_TITLE', 'auth' => 'acl_a_server', 'cat' => array('ACP_CLIENT_COMMUNICATION')),
            ),
        );
    }

    function install()
    {
    }

    function uninstall()
    {
    }
}
