<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

namespace phpbb\db\migration\data\v400;

use phpbb\db\migration\container_aware_migration;

class disable_extensions extends container_aware_migration
{
	/**
	 * @var array List of extensions included with phpBB
	 */
	public static array $default_extensions = [
		'phpbb/viglink',
	];

	public static function depends_on(): array
	{
		return ['\phpbb\db\migration\data\v400\v400a1'];
	}

	public function update_data(): array
	{
		return [
			['custom', [[$this, 'disable_enabled_extensions']]],
		];
	}

	/**
	 * Disable all enabled extensions except those included with phpBB.
	 * This is a safety measure to prevent possible PHP fatal errors
	 * caused by extensions that are not compatible with the changes
	 * introduced with phpBB 4.0.0-a2.
	 */
	public function disable_enabled_extensions(): void
	{
		$ext_manager = $this->container->get('ext.manager');

		$enabled_extensions = array_keys($ext_manager->all_enabled());
		$enabled_extensions = array_diff($enabled_extensions, self::$default_extensions);

		foreach ($enabled_extensions as $extension)
		{
			$ext_manager->disable($extension);
		}
	}
}
