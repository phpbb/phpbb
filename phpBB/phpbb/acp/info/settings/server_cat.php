<?php

namespace phpbb\acp\info\settings;

class server_cat extends \phpbb\acp\info\settings
{
	public function get_title()
	{
		return $this->lang->lang('ACP_SERVER_CONFIGURATION');
	}

	public function get_auth()
	{
		return parent::get_auth();
	}

	public function get_route()
	{
		return $this->helper->route('phpbb_acp_settings_server');
	}
}
