<?php

namespace vendor4\foo;

class ext extends \phpbb\extension\base
{
	static public $enabled;

	public function enable_step($old_state)
	{
		self::$enabled = true;

		return self::$enabled;
	}

	public function is_enableable()
	{
		return false;
	}

	public function not_enableable_reason()
	{
		return array('Reason 1', 'Reason 2');
	}
}
