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

namespace phpbb\db\migration\data\v320;

class extensions_composer extends \phpbb\db\migration\migration
{
	public function update_data()
	{
		return array(
			array('config.add', array('exts_composer_repositories', serialize([]))),
			array('config.add', array('exts_composer_packagist', true)),
			array('config.add', array('exts_composer_json_file', 'composer-ext.json')),
			array('config.add', array('exts_composer_vendor_dir', 'vendor-ext/')),
		);
	}
}
