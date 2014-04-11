<?php
/**
*
* @package migration
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\db\migration\data\v310;

class contact_admin_form extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['contact_admin_form_enable']);
	}

	public function update_data()
	{
		return array(
			array('config.add', array('contact_admin_form_enable', 1)),
		);
	}
}
