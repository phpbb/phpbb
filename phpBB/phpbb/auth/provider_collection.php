<?php
/**
*
* @package phpBB3
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\auth;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
* Collection of auth providers to be configured at container compile time.
*
* @package phpBB3
*/
class provider_collection extends \phpbb\di\service_collection
{
	/** @var \phpbb\config\config phpBB Config */
	protected $config;

	/**
	* Constructor
	*
	* @param ContainerInterface $container Container object
	* @param \phpbb\config\config $config phpBB config
	*/
	public function __construct($container, \phpbb\config\config $config)
	{
		$this->container = $container;
		$this->config = $config;
	}

	/**
	* Get an auth provider.
	*
	* @return object	Default auth provider selected in config if it
	*			does exist. Otherwise the standard db auth
	*			provider.
	* @throws \RuntimeException If neither the auth provider that
	*			is specified by the phpBB config nor the db
	*			auth provider exist. The db auth provider
	*			should always exist in a phpBB installation.
	*/
	public function get_provider()
	{
		if ($this->offsetExists('auth.provider.' . basename(trim($this->config['auth_method']))))
		{
			return $this->offsetGet('auth.provider.' . basename(trim($this->config['auth_method'])));
		}
		// Revert to db auth provider if selected method does not exist
		elseif ($this->offsetExists('auth.provider.db'))
		{
			return $this->offsetGet('auth.provider.db');
		}
		else
		{
			throw new \RuntimeException(sprintf('The authentication provider for the authentication method "%1$s" does not exist. It was not possible to recover from this by reverting to the database authentication provider.', $this->config['auth_method']));
		}
	}
}
