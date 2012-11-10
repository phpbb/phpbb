<?php
/**
*
* @package phpBB3
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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class phpbb_di_pass_collection implements CompilerPassInterface
{
	private $collection_service;
	private $service_tag;

	public function __construct($collection_service, $service_tag)
	{
		$this->collection_service = $collection_service;
		$this->service_tag = $service_tag;
	}

	/**
	* Modify the container before it is passed to the rest of the code
	*
	* @param ContainerBuilder $container ContainerBuilder object
	* @return null
	*/
	public function process(ContainerBuilder $container)
	{
		$definition = $container->getDefinition($this->collection_service);

		foreach ($container->findTaggedServiceIds($this->service_tag) as $id => $data)
		{
			$definition->addMethodCall('add', array($id));
		}
	}
}
