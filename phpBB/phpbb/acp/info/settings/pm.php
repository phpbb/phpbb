<?php

namespace phpbb\acp\info\settings;

class pm extends general
{
	public function get_title()
	{
		return $this->lang->lang('ACP_MESSAGE_SETTINGS');
	}

	public function get_auth()
	{
		return parent::get_auth() && $this->auth->acl_get('a_board');
	}

	public function get_route()
	{
		return $this->helper->route('phpbb_acp_settings_pm');
	}
}
