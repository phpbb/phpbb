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
		$this->_association_table = $association_table;
		$this->_discovery_table = $discovery_table;
		$this->_nonce_table = $nonce_table;
	}
	/**
	 * {@inheritDoc}
	 */
	public function addAssociation($url, $handle, $macFunc, $secret, $expires)
	{
		$table = $this->_association_table;
		$secret = base64_encode($secret);
		$this->_db->insert($table, array(
			'url'     => $url,
			'handle'  => $handle,
			'macFunc' => $macFunc,
			'secret'  => $secret,
			'expires' => $expires,
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
				->from($table, array('handle', 'macFunc', 'secret', 'expires'))
				->where('url = ?', $url);
		$res = $this->_db->fetchRow($select);

		if (is_array($res)) {
			$handle  = $res['handle'];
			$macFunc = $res['macFunc'];
			$secret  = base64_decode($res['secret']);
			$expires = $res['expires'];
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
			$table, $this->_db->quoteInto('expires < ', time())
		);
		$select = $this->_db->select()
				->from($table, array('url', 'macFunc', 'secret', 'expires'))
				->where('handle = ?', $handle);
		$res = $select->fetchRow($select);

		if (is_array($res)) {
			$url     = $res['url'];
			$macFunc = $res['macFunc'];
			$secret  = base64_decode($res['secret']);
			$expires = $res['expires'];
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
		$this->_db->query("delete from $table where url = '$url'");
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addDiscoveryInfo($id, $realId, $server, $version, $expires)
	{
		$table = $this->_discovery_table;
		$this->_db->insert($table, array(
			'id'      => $id,
			'realId'  => $realId,
			'server'  => $server,
			'version' => $version,
			'expires' => $expires,
		));

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDiscoveryInfo($id, &$realId, &$server, &$version, &$expires)
	{
		$table = $this->_discovery_table;
		$this->_db->delete($table, $this->quoteInto('expires < ?', time()));
		$select = $this->_db->select()
				->from($table, array('realId', 'server', 'version', 'expires'))
				->where('id = ?', $id);
		$res = $this->_db->fetchRow($select);

		if (is_array($res)) {
			$realId  = $res['realId'];
			$server  = $res['server'];
			$version = $res['version'];
			$expires = $res['expires'];
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
		$this->_db->delete($table, $this->_db->quoteInto('id = ?', $id));
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
