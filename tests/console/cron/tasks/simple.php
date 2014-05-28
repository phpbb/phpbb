<?php

class phpbb_cron_task_simple extends \phpbb\cron\task\base
{
	public function get_name()
	{
		return get_class($this);
	}

	public function run()
	{
		global $cron_num_exec;
		$cron_num_exec++;
	}
}
