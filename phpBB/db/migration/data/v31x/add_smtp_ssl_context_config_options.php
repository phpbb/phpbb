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

namespace phpbb\db\migration\data\v31x;

class add_smtp_ssl_context_config_options extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v31x\v3110');
	}

	public function update_data()
	{
		return array(
			// See http://php.net/manual/en/context.ssl.php
			array('config.add', array('smtp_verify_peer', 1)),
			array('config.add', array('smtp_verify_peer_name', 1)),
			array('config.add', array('smtp_allow_self_signed', 0)),
		);
	}
}
