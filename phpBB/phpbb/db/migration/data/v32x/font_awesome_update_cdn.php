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

namespace phpbb\db\migration\data\v32x;

class font_awesome_update_cdn extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return phpbb_version_compare($this->config['version'], '3.3.0', '>=');
	}

	static public function depends_on()
	{
		return [
			'\phpbb\db\migration\data\v32x\add_missing_config',
		];
	}

	public function update_data()
	{
		return [
			['config.update', ['load_font_awesome_url', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css']],
		];
	}
}
