<?php

namespace phpbb\acp\info\settings;

class load extends server_cat
{
	public function get_title()
	{
		return $this->lang->lang('ACP_LOAD_SETTINGS');
	}

	public function get_auth()
	{
		return parent::get_auth() && $this->auth->acl_get('a_server');
	}

	public function get_route()
	{
		return $this->helper->route('phpbb_acp_settings_load');
	}
}
