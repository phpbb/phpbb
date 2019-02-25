<?php

namespace phpbb\acp\info\settings;

class client extends settings
{
	public function get_title()
	{
		return $this->lang->lang('ACP_CLIENT_COMMUNICATION');
	}

	public function get_auth()
	{
		return parent::get_auth();
	}

	public function get_route()
	{
		return $this->helper->route('phpbb_acp_settings_auth');
	}
}
