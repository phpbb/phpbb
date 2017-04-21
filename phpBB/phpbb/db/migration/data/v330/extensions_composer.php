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

namespace phpbb\db\migration\data\v330;

class extensions_composer extends \phpbb\db\migration\migration
{
	public function update_data()
	{
		return array(
			array('config.add', array('exts_composer_repositories', json_encode([
				'https://www.phpbb.com/customise/db/composer/',
			], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))),
			array('config.add', array('exts_composer_packagist', false)),
			array('config.add', array('exts_composer_json_file', 'composer-ext.json')),
			array('config.add', array('exts_composer_vendor_dir', 'vendor-ext/')),
			array('config.add', array('exts_composer_enable_on_install', false)),
			array('config.add', array('exts_composer_purge_on_remove', true)),
			array('module.add', array(
				'acp',
				'ACP_EXTENSION_MANAGEMENT',
				array(
					'module_basename'	=> 'acp_extensions',
					'modes'				=> array('catalog'),
				),
			)),
		);
	}
}
