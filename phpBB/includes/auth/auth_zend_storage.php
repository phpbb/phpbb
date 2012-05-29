<?php
/**
*
* @package phpBB3
* @copyright (c) 2005 phpBB Group
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
 * Implements the storage methods of the ZendFramework OpenID Consumer code.
 * This allows the saving of user tokens and logins for federated logins.
 *
 * @package phpBB3
 */
class auth_zend_storage extends \Zend\OpenId\Consumer\Storage\AbstractStorage
{
	/**
	 * @param string $url OpenID server URL
	 * @param string $handle assiciation handle
	 * @param string $macFunc HMAC function (sha1 or sha256)
	 * @param string $secret shared secret
	 * @param long $expires expiration UNIX time
	 * @return void
	 */
	public function addAssociation($url, $handle, $macFunc, $secret, $expires)
	{

	}

	/**
	 * @param string $url OpenID server URL
	 * @param string &$handle assiciation handle
	 * @param string &$macFunc HMAC function (sha1 or sha256)
	 * @param string &$secret shared secret
	 * @param long &$expires expiration UNIX time
	 * @return bool
	 */
	public function getAssociation($url, &$handle, &$macFunc, &$secret, &$expires)
	{

	}

	/**
	 * @param string $handle assiciation handle
	 * @param string &$url OpenID server URL
	 * @param string &$macFunc HMAC function (sha1 or sha256)
	 * @param string &$secret shared secret
	 * @param long &$expires expiration UNIX time
	 * @return bool
	 */
	public function getAssociationByHandle($handle, &$url, &$macFunc, &$secret, &$expires)
	{

	}

	/**
	 * @param string $url OpenID server URL
	 * @return void
	 */
	public function delAssociation($url)
	{

	}

	/**
	 * @param string $id identity
	 * @param string $realId discovered real identity URL
	 * @param string $server discovered OpenID server URL
	 * @param float $version discovered OpenID protocol version
	 * @param long $expires expiration UNIX time
	 * @return void
	 */
	public function addDiscoveryInfo($id, $realId, $server, $version, $expires)
	{

	}

	/**
	 * @param string $id identity
	 * @param string &$realId discovered real identity URL
	 * @param string &$server discovered OpenID server URL
	 * @param float &$version discovered OpenID protocol version
	 * @param long &$expires expiration UNIX time
	 * @return bool
	 */
	public function getDiscoveryInfo($id, &$realId, &$server, &$version, &$expires)
	{

	}

	/**
	 * @param string $id identity
	 * @return bool
	 */
	public function delDiscoveryInfo($id)
	{

	}

	/**
	 * @param string $provider openid.openid_op_endpoint field from authentication response
	 * @param string $nonce openid.response_nonce field from authentication response
	 * @return bool
	 */
	public function isUniqueNonce($provider, $nonce)
	{

	}

	/**
	 * @param string $date Date of expired data
	 */
	public function purgeNonces($date=null)
	{

	}
}

?>
