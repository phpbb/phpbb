<?php

class phpbb_cron_task_core_simple_not_runnable extends \phpbb\cron\task\base
{
	public function get_name()
	{
		return get_class($this);
	}

	public function run()
	{
	}

	public function is_runnable()
	{
		return false;
	}
}
