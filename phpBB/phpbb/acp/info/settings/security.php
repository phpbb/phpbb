<?php

namespace phpbb\acp\info\settings;

class security extends server_cat
{
	public function get_title()
	{
		return $this->lang->lang('ACP_SECURITY_SETTINGS');
	}

	public function get_auth()
	{
		return parent::get_auth() && $this->auth->acl_get('a_server');
	}

	public function get_route()
	{
		return $this->helper->route('phpbb_acp_settings_security');
	}
}
