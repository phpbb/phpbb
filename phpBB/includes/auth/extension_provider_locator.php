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
* Finds installed authentication providers and makes them available to the authentication manager.
*
* @package auth
*/
class phpbb_auth_extension_provider_locator extends phpbb_extension_provider
{
	/**
	 * Finds provider names using the extension manager.
	 *
	 * All PHP files in includes/auth/provider/ are considered providers.
	 * Providers in extensions have to be located in a directory called auth or
	 * a subdir of a directory called auth. The class and filename must start in
	 * a provider_ prefix. Additionally all PHP files in includes/auth/provider/
	 * are providers.
	 *
	 * @return array List of provider names
	 */
	protected function find()
	{
		$finder = $this->extension_manager->get_finder();

		return $finder
			->extension_prefix('provider_')
			->extension_directory('/auth')
			->core_path('includes/auth/provider/')
			->get_classes();
	}
}
