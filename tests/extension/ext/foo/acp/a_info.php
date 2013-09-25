<?php

namespace foo\acp;

class a_info
{
	public function module()
	{
		return array(
			'filename'	=> 'foo\\acp\\a_module',
			'title'		=> 'Foobar',
			'version'	=> '3.1.0-dev',
			'modes'		=> array(
				'config'		=> array('title' => 'Config',	'auth' => '', 'cat' => array('ACP_MODS')),
			),
		);
	}
}
