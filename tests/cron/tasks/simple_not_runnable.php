<?php

class phpbb_cron_task_core_simple_not_runnable extends phpbb_cron_task_base
{
	public function run()
	{
	}

	public function is_runnable()
	{
		return false;
	}
}
