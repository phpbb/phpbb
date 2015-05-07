<?php

namespace vendor2\foo\acp;

class a_info
{
	public function module()
	{
		return array(
			'filename'	=> 'vendor2\\foo\\acp\\a_module',
			'title'		=> 'Foobar',
			'modes'		=> array(
				'config'		=> array('title' => 'Config',	'auth' => 'ext_vendor2/foo', 'cat' => array('ACP_MODS')),
			),
		);
	}
}
