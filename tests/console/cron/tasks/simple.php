<?php

class phpbb_cron_task_simple extends \phpbb\cron\task\base
{
	public $executed;

	public function __construct()
	{
		$executed = false;
		parent::__construct();
	}

	public function get_name()
	{
		return get_class($this);
	}

	public function run()
	{
		$this->executed = true;
	}
}
