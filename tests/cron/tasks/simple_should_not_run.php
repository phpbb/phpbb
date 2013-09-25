<?php

class phpbb_cron_task_core_simple_should_not_run extends \phpbb\cron\task\base
{
	public function get_name()
	{
		return get_class($this);
	}

	public function run()
	{
	}

	public function should_run()
	{
		return false;
	}
}
