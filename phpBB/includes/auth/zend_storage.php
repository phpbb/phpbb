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

	public function __construct($db)
	{
		$this->_db = $db;
		$this->_association_table = AUTH_OPENID_ASSOC_TABLE;
		$this->_discovery_table = AUTH_OPENID_DISCOVERY_TABLE;
		$this->_nonce_table = AUTH_OPENID_NONCE_TABLE;
	}
	/**
	 * {@inheritDoc}
	 */
	public function addAssociation($url, $handle, $macFunc, $secret, $expires)
	{
		$table = $this->_association_table;
		$secret = base64_encode($secret);
		$this->_db->insert($table, array(
			'assoc_url'     => $url,
			'assoc_handle'  => $handle,
			'assoc_mac_func' => $macFunc,
			'assoc_secret'  => $secret,
			'assoc_expires' => $expires,
		));
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getAssociation($url, &$handle, &$macFunc, &$secret, &$expires)
	{
		$table = $this->_association_table;
		$this->_db->delete(
			$table, $this->_db->quoteInto('expires < ?', time())
		);
		$select = $this->_db->select()
				->from($table, array('assoc_handle', 'assoc_mac_func', 'assoc_secret', 'assoc_expires'))
				->where('assoc_url = ?', $url);
		$res = $this->_db->fetchRow($select);

		if (is_array($res)) {
			$handle  = $res['assoc_handle'];
			$macFunc = $res['assoc_mac_func'];
			$secret  = base64_decode($res['assoc_secret']);
			$expires = $res['assoc_expires'];
			return true;
		}
		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getAssociationByHandle($handle, &$url, &$macFunc, &$secret, &$expires)
	{
		$table = $this->_association_table;
		$this->_db->delete(
			$table, $this->_db->quoteInto('assoc_expires < ', time())
		);
		$select = $this->_db->select()
				->from($table, array('assoc_url', 'assoc_mac_func', 'assoc_secret', 'assoc_expires'))
				->where('assoc_handle = ?', $handle);
		$res = $select->fetchRow($select);

		if (is_array($res)) {
			$url     = $res['assoc_url'];
			$macFunc = $res['assoc_mac_func'];
			$secret  = base64_decode($res['assoc_secret']);
			$expires = $res['assoc_expires'];
			return true;
		}
		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function delAssociation($url)
	{
		$table = $this->_association_table;
		$this->_db->query("delete from $table where assoc_url = '$url'");
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addDiscoveryInfo($id, $realId, $server, $version, $expires)
	{
		$table = $this->_discovery_table;
		$this->_db->insert($table, array(
			'discovery_id'      => $id,
			'discovery_real_id'  => $realId,
			'discovery_server'  => $server,
			'discovery_version' => $version,
			'discovery_expires' => $expires,
		));

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDiscoveryInfo($id, &$realId, &$server, &$version, &$expires)
	{
		$table = $this->_discovery_table;
		$this->_db->delete($table, $this->quoteInto('discovery_expires < ?', time()));
		$select = $this->_db->select()
				->from($table, array('discovery_real_id', 'discovery_server', 'discovery_version', 'discovery_expires'))
				->where('discovery_id = ?', $id);
		$res = $this->_db->fetchRow($select);

		if (is_array($res)) {
			$realId  = $res['discovery_real_id'];
			$server  = $res['discovery_server'];
			$version = $res['discovery_version'];
			$expires = $res['discovery_expires'];
			return true;
		}
		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function delDiscoveryInfo($id)
	{
		$table = $this->_discovery_table;
		$this->_db->delete($table, $this->_db->quoteInto('discovery_id = ?', $id));
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isUniqueNonce($provider, $nonce)
	{
		$table = $this->_nonce_table;
		try {
			$ret = $this->_db->insert($table, array(
				'nonce' => $nonce,
			));
		} catch (Zend_Db_Statement_Exception $e) {
			return false;
		}
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function purgeNonces($date = null)
	{

	}
}
