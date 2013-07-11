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

/**
* Replace services with custom ones for testing
*/
class phpbb_di_pass_replace_pass implements CompilerPassInterface
{
	public function __construct(array $services)
	{
		$this->services = $services;
	}

	/**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     */
	public function process(ContainerBuilder $container)
	{
		foreach ($this->services as $name => $service)
		{
			$container->set($name, $service);
		}
	}
}
