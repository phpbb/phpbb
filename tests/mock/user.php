<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

/**
* Mock user class.
* This class is used when tests invoke phpBB code expecting to have a global
* user object, to avoid instantiating the actual user object.
* It has a minimum amount of functionality, just to make tests work.
*/
class phpbb_mock_user
{
	public $host = "testhost";
	public $page = array('root_script_path' => '/');
	
	private $options = array();
	public function optionget($item)
	{
		if (!isset($this->options[$item]))
		{
			throw new Exception(sprintf("You didn't set the option '%s' on the mock user using optionset.", $item));
		}
		
		return $this->options[$item];
	}
	
	public function optionset($item, $value)
	{
		$this->options[$item] = $value;
	}

	public function check_ban($user_id = false, $user_ips = false, $user_email = false, $return = false)
	{
		$banned_users = $this->optionget('banned_users');
		foreach ($banned_users as $banned)
		{
			if ($banned == $user_id || $banned == $user_ips || $banned == $user_email)
			{
				return true;
			}
		}
		return false;
	}

	public function lang()
	{
		return implode(' ', func_get_args());
	}
}
