<?php

namespace phpbb\acp\info\settings;

class server extends server_cat
{
	public function get_title()
	{
		return $this->lang->lang('ACP_SERVER_SETTINGS');
	}

	public function get_auth()
	{
		return parent::get_auth() && $this->auth->acl_get('a_server');
	}

	public function get_route()
	{
		return $this->helper->route('phpbb_acp_settings_server');
	}
}
