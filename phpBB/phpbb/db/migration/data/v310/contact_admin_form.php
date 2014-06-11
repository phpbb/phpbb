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
			array('custom', array(array($this, 'contact_admin_info'))),
		);
	}

	public function contact_admin_info()
	{
		$text_config = new \phpbb\config\db_text($this->db, $this->table_prefix . 'config_text');
		$text_config->set_array(array(
			'contact_admin_info'			=> '',
			'contact_admin_info_uid'		=> '',
			'contact_admin_info_bitfield'	=> '',
			'contact_admin_info_flags'		=> OPTION_FLAG_BBCODE + OPTION_FLAG_SMILIES + OPTION_FLAG_LINKS,
		));
	}
}
