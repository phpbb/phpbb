<?php

namespace vendor2\bar;

class ext extends \phpbb\extension\base
{
	static public $state;

	public function enable_step($old_state)
	{
		// run 4 steps, then quit
		if ($old_state === 4)
		{
			return false;
		}

		if ($old_state === false)
		{
			$old_state = 0;
		}

		self::$state = ++$old_state;

		return self::$state;
	}
}
