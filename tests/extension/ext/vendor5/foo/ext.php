<?php

namespace vendor5\foo;

class ext extends \phpbb\extension\base
{
	static public $enabled;

	public function is_enableable()
	{
		return array('Reason 1', 'Reason 2');
	}

	public function enable_step($old_state)
	{
		self::$enabled = true;
		return self::$enabled;
	}
}
