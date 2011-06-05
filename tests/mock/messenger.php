<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/template.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/functions_messenger.php';

class phpbb_mock_messenger extends messenger
{
	public function send($method = NOTIFY_EMAIL, $break = false)
	{
		return;
	}

	public function get_vars()
	{
		return $this->vars;
	}

	public function get_extra_headers()
	{
		return $this->extra_headers;
	}
}

