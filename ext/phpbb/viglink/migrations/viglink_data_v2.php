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
 * Migration to remove VigLink data
 */
class viglink_data_v2 extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\phpbb\viglink\migrations\viglink_data');
	}

	public function effectively_installed()
	{
		return !isset($this->config['viglink_api_key']);
	}

	public function update_data()
	{
		return array(
			array('config.remove', array('viglink_api_key')),
			array('config.remove', array('allow_viglink_global')),
		);
	}
}
