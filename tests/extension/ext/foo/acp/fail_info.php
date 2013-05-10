<?php
/*
* Due to the mismatch between the class name and the file name, this module
* file shouldn't be found by the extension finder
*/
class phpbb_ext_foo_acp_foo_info
{
	public function module()
	{
		return array(
			'filename'	=> 'phpbb_ext_foo_acp_fail_module',
			'title'		=> 'Foobar',
			'version'	=> '3.1.0-dev',
			'modes'		=> array(
				'config'		=> array('title' => 'Config',	'auth' => '', 'cat' => array('ACP_MODS')),
			),
		);
	}
}
