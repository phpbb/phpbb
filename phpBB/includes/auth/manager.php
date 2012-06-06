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
* This class handles the selection of authentication provider to use.
*
* @package auth
*/
class phpbb_auth_manager
{
	protected $request;
	protected $db;

	public function __construct(phpbb_request $request, dbal $db)
	{
		$this->request = $request;
		$this->db = $db;
	}

	public function get_provider($auth_type)
	{
		$provider = 'phpbb_auth_provider_' . $auth_type;
		if (class_exists($provider))
		{
			return new $provider($this->request, $this->db);
		}
		else
		{
			throw new phpbb_auth_exception('Authentication provider, ' . $provider . ', not found.');
		}
	}
}
