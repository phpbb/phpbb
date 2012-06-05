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

	protected $db;

	public function __construct($db)
	{
		$this->db = $db;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addAssociation($url, $handle, $macFunc, $secret, $expires)
	{
		$data = array(
			'assoc_url'     => $url,
			'assoc_handle'  => $handle,
			'assoc_mac_func' => $macFunc,
			'assoc_secret'  => base64_encode($secret),
			'assoc_expires' => $expires,
		);
		$sql = 'INSERT INTO ' . AUTH_OPENID_ASSOC_TABLE . ' ' . $this->db->sql_build_array('INSERT', $data);;
		$this->db->sql_query($sql);
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getAssociation($url, &$handle, &$macFunc, &$secret, &$expires)
	{
		$sql = 'DELETE FROM ' . AUTH_OPENID_ASSOC_TABLE . '
				WHERE assoc_expires < ' . time();
		$this->db->sql_query($sql);
		$sql = 'SELECT assoc_handle, assoc_mac_func, assoc_secret, assoc_expires
				FROM ' . AUTH_OPENID_ASSOC_TABLE . '
				WHERE assoc_url = \'' . $this->db->sql_escape($url) . '\'';
		$result = $this->db->sql_query($sql);
		$assoc = $this->db->sql_fetchrow($result);

		if (is_array($assoc))
		{
			$handle  = $assoc['assoc_handle'];
			$macFunc = $assoc['assoc_mac_func'];
			$secret  = base64_decode($assoc['assoc_secret']);
			$expires = $assoc['assoc_expires'];
			return true;
		}
		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getAssociationByHandle($handle, &$url, &$macFunc, &$secret, &$expires)
	{
		$sql = 'DELETE FROM ' . AUTH_OPENID_ASSOC_TABLE . '
				WHERE assoc_expires < ' . time();
		$this->db->sql_query($sql);
		$sql = 'SELECT assoc_url, assoc_mac_func, assoc_secret, assoc_expires 
				FROM ' . AUTH_OPENID_ASSOC_TABLE . '
				WHERE assoc_handle = \'' . $this->db->sql_escape($handle) . '\'';
		$result = $this->db->sql_query($sql);
		$assoc = $this->db->sql_fetchrow($result);

		if (is_array($assoc))
		{
			$url     = $assoc['assoc_url'];
			$macFunc = $assoc['assoc_mac_func'];
			$secret  = base64_decode($assoc['assoc_secret']);
			$expires = $assoc['assoc_expires'];
			return true;
		}
		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function delAssociation($url)
	{
		$sql = 'DELETE FROM ' . AUTH_OPENID_ASSOC_TABLE . '
				WHERE assoc_url = \'' . $this->db->sql_escape($url) . '\'';
		$this->db->sql_query($sql);
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addDiscoveryInfo($id, $realId, $server, $version, $expires)
	{
		$data = array(
			'discovery_id'      => $id,
			'discovery_real_id' => $realId,
			'discovery_server'  => $server,
			'discovery_version' => $version,
			'discovery_expires' => $expires,
		);
		$sql = 'INSERT INTO ' . AUTH_OPENID_DISCOVERY_TABLE . ' ' . $this->db->sql_build_array('INSERT', $data);
		$this->db->sql_query($sql);
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDiscoveryInfo($id, &$realId, &$server, &$version, &$expires)
	{
		$sql = 'DELETE FROM ' . AUTH_OPENID_DISCOVERY_TABLE . '
				WHERE discovery_expires < ' . time();
		$this->db->sql_query($sql);
		$sql = 'SELECT discovery_real_id, discovery_server, discovery_version, discovery_expires 
				FROM ' . AUTH_OPENID_DISCOVERY_TABLE . '
				WHERE discovery_id = \'' . $this->db->sql_escape($id) . '\'';
		$result = $this->db->sql_query($sql);
		$discovery = $this->db->sql_fetchrow($result);

		if (is_array($discovery))
		{
			$realId  = $discovery['discovery_real_id'];
			$server  = $discovery['discovery_server'];
			$version = $discovery['discovery_version'];
			$expires = $discovery['discovery_expires'];
			return true;
		}
		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function delDiscoveryInfo($id)
	{
		$sql = 'DELETE FROM ' . AUTH_OPENID_DISCOVERY_TABLE . '
				WHERE discovery_id = \'' . $this->db->sql_escape($id) . '\'';
		$this->db->sql_query($sql);
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isUniqueNonce($provider, $nonce)
	{
		try
		{
			$data = array(
				'nonce' => $nonce,
				'nonce_created' => time(),
			);
			$sql = 'INSERT INTO ' . AUTH_OPENID_NONCE_TABLE . ' ' . $this->db->sql_build_array('INSERT', $data);;
			$return = $this->db->sql_query($sql);
		}
		catch (Zend_Db_Statement_Exception $e)
		{
			return false;
		}
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function purgeNonces($date = null)
	{
		if(!is_int($date) && !is_string($date))
		{
			$time = 0;
		}
		elseif(is_string($date))
		{
			$time = time($date);
		}
		else
		{
			$time = $date;
		}

		// Discover what nonces have to be deleted.
		$sql = 'SELECT FROM' . AUTH_OPENID_NONCE_TABLE . '
				WHERE nonce_create < ' . $time;
		$res = $this->db->sql_query($sql);
		$link_manager = new phpbb_auth_link_manager();

		while ($nonce = $this->db->sql_fetchrow($res))
		{
			$sql = 'DELECT FROM' . AUTH_LINK_TABLE . '
					WHERE link_meth = \'open_id\' AND link_index = \'' . $nonce . '\'';
			$this->db->sql_query($sql);
		}

		$sql = 'DELETE FROM ' . AUTH_OPENID_NONCE_TABLE . '
				WHERE nonce_create < ' . $time;
		$this->db->sql_query($sql);
		return true;
	}
}
