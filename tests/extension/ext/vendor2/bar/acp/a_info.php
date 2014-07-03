<?php

namespace vendor2\bar\acp;

class a_info
{
	public function module()
	{
		return array(
			'filename'	=> 'vendor2\\bar\\acp\\a_module',
			'title'		=> 'Bar',
			'modes'		=> array(
				'config'		=> array('title' => 'Config',	'auth' => '', 'cat' => array('ACP_MODS')),
			),
		);
	}
}
