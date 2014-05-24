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
* Proxy Factory, wrapper for Ocramius/ProxyManager
* @package phpBB3
*/
class factory
{
	/**
	* @var \ProxyManager\Factory\LazyLoadingValueHolderFactory
	*/
	protected $factory;

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
		$this->factory = new \ProxyManager\Factory\LazyLoadingValueHolderFactory($configuration);
	}

	/**
	* @param string   $className name of the class to be proxied
	* @param \Closure $initializer initializer to be passed to the proxy
	*
	* @return \ProxyManager\Proxy\LazyLoadingInterface|\ProxyManager\Proxy\ValueHolderInterface
	*/
	public function createProxy($className, \Closure $initializer)
	{
		return $this->factory->createProxy($className, $initializer);
	}
}
