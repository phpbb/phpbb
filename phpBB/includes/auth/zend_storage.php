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
 * Implements the storage methods of the ZendFramework OpenID Consumer code.
 * This allows the saving of user tokens and logins for federated logins.
 *
 * @package auth
 */
class phpbb_auth_zend_storage extends \Zend\OpenId\Consumer\Storage\AbstractStorage
{
	/**
	 * {@inheritDoc}
	 */
	public function addAssociation($url, $handle, $macFunc, $secret, $expires)
	{

	}

	/**
	 * {@inheritDoc}
	 */
	public function getAssociation($url, &$handle, &$macFunc, &$secret, &$expires)
	{

	}

	/**
	 * {@inheritDoc}
	 */
	public function getAssociationByHandle($handle, &$url, &$macFunc, &$secret, &$expires)
	{

	}

	/**
	 * {@inheritDoc}
	 */
	public function delAssociation($url)
	{

	}

	/**
	 * {@inheritDoc}
	 */
	public function addDiscoveryInfo($id, $realId, $server, $version, $expires)
	{

	}

	/**
	 * {@inheritDoc}
	 */
	public function getDiscoveryInfo($id, &$realId, &$server, &$version, &$expires)
	{

	}

	/**
	 * {@inheritDoc}
	 */
	public function delDiscoveryInfo($id)
	{

	}

	/**
	 * {@inheritDoc}
	 */
	public function isUniqueNonce($provider, $nonce)
	{

	}

	/**
	 * {@inheritDoc}
	 */
	public function purgeNonces($date = null)
	{

	}
}
