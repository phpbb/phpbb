<?php

namespace barfoo\acp;

class a_info
{
	public function module()
	{
		return array(
			'filename'	=> 'barfoo\\acp\\a_module',
			'title'		=> 'Barfoo',
			'version'	=> '3.1.0-dev',
			'modes'		=> array(
				'config'		=> array('title' => 'Config',	'auth' => '', 'cat' => array('ACP_MODS')),
			),
		);
	}
}
