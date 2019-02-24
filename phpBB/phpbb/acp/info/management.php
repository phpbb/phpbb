<?php

namespace phpbb\acp\info;

class management extends \phpbb\cp\info\base
{
	public function get_title()
	{
		return $this->lang->lang('ACP_BOARD_MANAGEMENT');
	}

	public function get_auth()
	{
		return parent::get_auth();
	}

	public function get_route()
	{
		return $this->helper->route('phpbb_acp_index');
	}
}
