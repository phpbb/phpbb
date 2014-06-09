<?php

namespace vendor2\foo;

class ext extends \phpbb\extension\base
{
	static public $disabled;

	public function disable_step($old_state)
	{
		self::$disabled = true;

		return false;
	}
}
