<?php
/**
 *
 * VigLink extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2016 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\viglink\migrations;

/**
 * Migration to install VigLink cron task data
 */
class viglink_cron extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\phpbb\viglink\migrations\viglink_data');
	}

	public function effectively_installed()
	{
		return isset($this->config['viglink_last_gc']);
	}

	public function update_data()
	{
		return array(
			array('config.add', array('viglink_last_gc', 0, true)),
		);
	}
}
