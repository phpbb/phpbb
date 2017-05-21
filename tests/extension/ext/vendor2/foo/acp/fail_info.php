<?php

namespace vendor2\foo\acp;

/*
* Due to the mismatch between the class name and the file name, this module
* file shouldn't be found by the extension finder
*/
class foo_info
{
	public function module()
	{
		return array(
			'filename'	=> 'vendor2\foo\acp\fail_module',
			'title'		=> 'Foobar',
			'modes'		=> array(
				'config'		=> array('title' => 'Config',	'auth' => '', 'cat' => array('ACP_MODS')),
			),
		);
	}
}
