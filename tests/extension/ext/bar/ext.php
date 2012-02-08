<?php

class phpbb_ext_bar_ext extends phpbb_extension_base
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
