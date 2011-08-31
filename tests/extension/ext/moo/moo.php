<?php

class phpbb_ext_moo extends phpbb_extension_base
{
	static public $purged;

	public function purge_step($old_state)
	{
		self::$purged = true;

		return false;
	}
}
