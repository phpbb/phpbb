<?php

class phpbb_ext_foo_ext extends \phpbb\extension\base
{
	static public $disabled;

	public function disable_step($old_state)
	{
		self::$disabled = true;

		return false;
	}
}
