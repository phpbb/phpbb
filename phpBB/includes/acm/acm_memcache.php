<?php
/**
*
* @package acm
* @version $Id$
* @copyright (c) 2005, 2009 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (!extension_loaded('memcache') || !defined('PHPBB_ACM_MEMCACHE_HOST'))
{
	// Memcached will not work, include the null ACM at least the
	// board will still work.
	// @todo Could change this for a simple error though.
	require("${phpbb_root_path}includes/acm/acm_null.$phpEx");

	return;
}

// Include the abstract base
if (!class_exists('acm_memory'))
{
	require("${phpbb_root_path}includes/acm/acm_memory.$phpEx");
}

if (!defined('PHPBB_ACM_MEMCACHE_PORT'))
{
	define('PHPBB_ACM_MEMCACHE_PORT', 11211);
}

if (!defined('PHPBB_ACM_MEMCACHE_COMPRESS'))
{
	define('PHPBB_ACM_MEMCACHE_COMPRESS', false);
}

/**
* ACM for Memcached
* @package acm
*/
class acm extends acm_memory
{
	var $memcache;
	var $flags = 0;

	function acm()
	{
		// Call the parent constructor
		parent::acm_memory();

		$this->memcache = new Memcache;
		$this->memcache->connect(PHPBB_ACM_MEMCACHE_HOST, PHPBB_ACM_MEMCACHE_PORT);
		$this->flags = (PHPBB_ACM_MEMCACHE_COMPRESS) ? MEMCACHE_COMPRESSED : 0;
	}

	function unload()
	{
		parent::unload();

		$this->memcache->close();
	}

	/**
	* Purge cache data
	*/
	function purge()
	{
		$this->memcache->flush();

		parent::purge();
	}

	function read($var)
	{
		return $this->memcache->get($var);
	}

	function write($var, $data, $ttl = 2592000)
	{
		return $this->memcache->set($var, $data, $this->flags, $ttl);
	}

	function delete($var)
	{
		return $this->memcache->delete($var);
	}
}

?>