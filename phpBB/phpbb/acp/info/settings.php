<?php

namespace phpbb\acp\info;

class settings extends \phpbb\cp\info\base
{
	public function get_title()
	{
		return $this->lang->lang('ACP_BOARD_CONFIGURATION');
	}

	public function get_auth()
	{
		return parent::get_auth() && $this->auth->acl_get('a_board');
	}

	public function get_route()
	{
		return $this->helper->route('phpbb_acp_settings_board');
	}
}
