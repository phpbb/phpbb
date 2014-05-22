<?php
/**
*
* @package phpBB3
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\proxy;

/**
* Proxy Facotry, wrapper for Ocramius/ProxyManager
* @package phpBB3
*/
class factory extends \ProxyManager\Factory\LazyLoadingValueHolderFactory
{
	/**
	* Constructor
	*
	* @param string $phpbb_root_path Relative path to phpBB root
	*/
	public function __construct($phpbb_root_path)
	{
		$configuration = new \ProxyManager\Configuration();
		$configuration->setProxiesTargetDir($phpbb_root_path . '/cache');
		spl_autoload_register($configuration->getProxyAutoloader());
		parent::__construct($configuration);
	}
}
