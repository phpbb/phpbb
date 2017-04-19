<?php

namespace vendor2\foo\mcp;

class a_info
{
	public function module()
	{
		return array(
			'filename'	=> 'vendor2\\foo\\mcp\\a_module',
			'title'		=> 'Foobar',
			'modes'		=> array(
				'config'		=> array('title' => 'Config',	'auth' => '', 'cat' => array('MCP_MAIN')),
			),
		);
	}
}
