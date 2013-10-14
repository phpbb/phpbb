<?php

namespace foo\mcp;

class a_info
{
	public function module()
	{
		return array(
			'filename'	=> 'foo\\mcp\\a_module',
			'title'		=> 'Foobar',
			'version'	=> '3.1.0-dev',
			'modes'		=> array(
				'config'		=> array('title' => 'Config',	'auth' => '', 'cat' => array('MCP_MAIN')),
			),
		);
	}
}
