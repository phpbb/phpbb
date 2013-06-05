<?php

class phpbb_ext_foo_acp_a_info
{
	public function module()
	{
		return array(
			'filename'	=> 'phpbb_ext_foo_acp_a_module',
			'title'		=> 'Foobar',
			'version'	=> '3.1.0-dev',
			'modes'		=> array(
				'config'		=> array('title' => 'Config',	'auth' => '', 'cat' => array('ACP_MODS')),
			),
		);
	}
}
