<?php

class phpbb_cron_task_core_simple_ready extends phpbb_cron_task_base
{
	public function get_name()
	{
		return get_class($this);
	}

	public function run()
	{
	}
}
