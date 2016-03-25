<?php

class phpbb_cron_task_core_simple_ready extends \phpbb\cron\task\base
{
	public function get_name()
	{
		return get_class($this);
	}

	public function run()
	{
	}
}
