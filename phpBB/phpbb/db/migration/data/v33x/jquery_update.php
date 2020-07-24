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

namespace phpbb\db\migration\data\v33x;

class jquery_update extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->config['load_jquery_url'] === '//ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js';
	}

	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v33x\v331rc1',
		);
	}

	public function update_data()
	{
		return array(
			array('config.update', array('load_jquery_url', '//ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js')),
		);
	}

}
