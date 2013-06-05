<?php

class phpbb_ext_barfoo_acp_a_info
{
	public function module()
	{
		return array(
			'filename'	=> 'phpbb_ext_barfoo_acp_a_module',
			'title'		=> 'Barfoo',
			'version'	=> '3.1.0-dev',
			'modes'		=> array(
				'config'		=> array('title' => 'Config',	'auth' => '', 'cat' => array('ACP_MODS')),
			),
		);
	}
}
