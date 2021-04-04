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

use phpbb\db\migration\migration;

class extensions_composer extends migration
{
	public function effectively_installed()
	{
		return $this->config->offsetExists('exts_composer_repositories');
	}

	public static function depends_on()
	{
		return [
			'\phpbb\db\migration\data\v400\dev',
		];
	}

	public function update_data()
	{
		return [
			['config.add', ['exts_composer_repositories', json_encode([
				'https://www.phpbb.com/customise/db/composer/',
			], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]],
			['config.add', ['exts_composer_packagist', false]],
			['config.add', ['exts_composer_json_file', 'composer-ext.json']],
			['config.add', ['exts_composer_vendor_dir', 'vendor-ext/']],
			['config.add', ['exts_composer_enable_on_install', false]],
			['config.add', ['exts_composer_purge_on_remove', true]],
			['module.add', [
				'acp',
				'ACP_EXTENSION_MANAGEMENT',
				[
					'module_basename'	=> 'acp_extensions',
					'modes'				=> ['catalog'],
				],
			]],
		];
	}
}
