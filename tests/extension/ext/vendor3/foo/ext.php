<?php

namespace vendor3\foo;

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
}
