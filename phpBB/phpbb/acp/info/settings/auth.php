<?php

namespace phpbb\acp\info\settings;

class auth extends client
{
	public function get_title()
	{
		return $this->lang->lang('ACP_AUTH_SETTINGS');
	}

	public function get_auth()
	{
		return parent::get_auth() && $this->auth->acl_get('a_server');
	}

	public function get_route()
	{
		return $this->helper->route('phpbb_acp_settings_auth');
	}
}
