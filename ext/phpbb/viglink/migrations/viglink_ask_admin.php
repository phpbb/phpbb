<?php
/**
 *
 * VigLink extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\viglink\migrations;

/**
 * Migration to ask admin about viglink
 */
class viglink_ask_admin extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\phpbb\viglink\migrations\viglink_data_v2');
	}

	public function effectively_installed()
	{
		return isset($this->config['viglink_ask_admin']);
	}

	public function update_data()
	{
		return array(
			array('if', array(
				(!$this->config->offsetExists('viglink_ask_admin')),
				array('config.add', array('viglink_ask_admin', '')),
			)),
		);
	}
}
