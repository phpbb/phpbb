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
 * Migration to only ask admin once per day
 */
class viglink_ask_admin_wait extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\phpbb\viglink\migrations\viglink_ask_admin');
	}

	public function effectively_installed()
	{
		return isset($this->config['viglink_ask_admin_last']);
	}

	public function update_data()
	{
		return array(
			array('if', array(
				(!$this->config->offsetExists('viglink_ask_admin_last')),
				array('config.add', array('viglink_ask_admin_last', '0')),
			)),
		);
	}
}
