<?php

namespace phpbb\acp\info\management;

class index extends \phpbb\acp\info\management
{
	public function get_title()
	{
		return $this->lang->lang('ACP_INDEX');
	}

	public function get_route()
	{
		return $this->helper->route('phpbb_acp_index');
	}
}
