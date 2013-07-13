<?php
/**
*
* @package auth
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

use OAuth\Common\Storage\TokenStorageInterface;
use OAuth\Common\Token\TokenInterface;

/**
* OAuth storage wrapper for phpbb's cache
*
* @package auth
*/
class phpbb_auth_oauth_token_storage implements TokenStorageInterface
{
	/**
	* Cache driver.
	*
	* @var phpbb_db_driver
	*/
	protected $db;

	/**
	* Name of the OAuth provider
	*
	* @var string
	*/
	protected $service_name;

	/**
	* @var object|TokenInterface
	*/
	protected $cachedToken;

	/**
	* Creates token storage for phpBB.
	*
	* @param phpbb_db_driver	$db
	*/
	public function __construct(phpbb_db_driver $db, $service_name)
	{
		$this->db = $db;
		$this->service_name = $service_name;
	}

	/**
	* {@inheritdoc}
	*/
	public function retrieveAccessToken()
	{
		if( $this->cachedToken instanceOf TokenInterface ) {
			return $this->token;
		}

		// TODO: check to see if the token is cached

		throw new TokenNotFoundException('Token not stored');
	}

	/**
	* {@inheritdoc}
	*/
	public function storeAccessToken(TokenInterface $token)
	{
		$this->cachedToken = $token;
		// TODO: actually store the token
	}

	/**
	* {@inheritdoc}
	*/
	public function hasAccessToken()
	{
		if( $this->cachedToken ) {
            return true;
        }

        // TODO: check cache for token
        return false;
	}

	/**
	* {@inheritdoc}
	*/
	public function clearToken()
	{
		$this->cachedToken = null;
		// TODO: clear cache of the token
	}
}
