<?php

class phpbb_cron_task_core_simple_should_not_run extends phpbb_cron_task_base
{
	public function run()
	{
	}

	public function should_run()
	{
		return false;
	}
}
