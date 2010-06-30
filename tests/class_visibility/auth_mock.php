<?php
/**
*
* @package testing
* @version $Id$
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/*
* phpbb_acl_mock_none
* mock a user with no permissions
*/
class phpbb_acl_mock_none
{
	public function acl_get($opt, $f)
	{
		if ($opt[0] == '!')
		{
			return true;
		}

		return false;
	}

	public function acl_gets($opt, $f)
	{
		if ($opt[0] == '!')
		{
			return true;
		}

		return false;
	}

	public function acl_getf_global($opt)
	{
		if ($opt[0] == '!')
		{
			return true;
		}

		return false;
	}

	public function acl_getf($opt, $clean = false)
	{
		$invert = false;
		if ($opt[0] == '!')
		{
			$invert = true;
		}

		$array = array();
		if ($clean && !$invert)
		{
			return $array; // return empty
		}

		for ($i = 1; $i < 15; $i++)
		{
			$array[$i] = !$invert;
		}
		return $array;
	}
}

class phpbb_acl_mock_founder
{
	public function acl_get($opt, $f)
	{
		if ($opt[0] == '!')
		{
			return false;
		}

		return true;
	}

	public function acl_gets($opt, $f)
	{
		if ($opt[0] == '!')
		{
			return false;
		}

		return true;
	}

	public function acl_getf_global($opt)
	{
		if ($opt[0] == '!')
		{
			return false;
		}

		return true;
	}

	public function acl_getf($opt, $clean = false)
	{
		$invert = false;
		if ($opt[0] == '!')
		{
			$invert = true;
		}

		$array = array();
		if ($clean && $invert)
		{
			return $array; // return empty
		}

		for ($i = 1; $i < 15; $i++)
		{
			$array[$i] = !$invert;
		}
		return $array;
	}
}

class phpbb_acl_mock_moderator
{
	public function acl_get($opt, $f = 0)
	{
		// just don't ask.  Well, ok, I'll explain anyway
		// If we're negating an admin permission, we have it.
		if ($opt[0] == '!' && substr($opt, 0, 3) == '!a_')
		{
			return true;
		}
		// if we're negating something which is not an admin permission, we don't have it
		else if ($opt[0] == '!' && substr($opt, 0, 3) != '!a_')
		{
			return false;
		}
		// if we're not negating something that is an admin permission, we
		else if (substr($opt, 0, 2) == 'a_')
		{
			return false;
		}

		return true;
	}

	public function acl_gets()
	{
		$limit = func_num_args();
		for ($i = 0; $i < $limit; $i++)
		{
			$opt = func_get_arg($i);

			if ($opt[0] == '!' && substr($opt, 0, 3) == '!a_')
			{
				return true;
			}
			else if ($opt[0] != '!' && substr($opt, 0, 2) != 'a_')
			{
				return true;
			}
		}

		return false;
	}

	public function acl_getf_global($opt)
	{
		// this should only be called to check m_ permissions generally.  Our ideal
		// moderator has all m_permissions.
		if ($opt[0] == '!')
		{
			return false;
		}

		return true;
	}

	public function acl_getf($opt, $clean = false)
	{
		// again, acl_getf should not be called for admin permissions (which are global...)
		$invert = false;
		if ($opt[0] == '!')
		{
			$invert = true;
		}

		if ($clean && $invert)
		{
			return $array; // return empty
		}

		$array = array();
		for ($i = 1; $i < 15; $i++)
		{
			$array[$i] = !$invert;
		}
		return $array;
	}
}

class phpbb_acl_mock_user
{
	public function acl_get($opt, $f = 0)
	{
		// just don't ask.  Well, ok, I'll explain anyway
		// If we're negating an admin or moderator permission (which our "user" does not have), we now have it.
		if ($opt[0] == '!' && in_array(substr($opt, 0, 3), array('!a_', '!m_')))
		{
			return true;
		}
		// if we're negating something which is not an admin permission, we don't have it
		else if ($opt[0] == '!' && !in_array(substr($opt, 0, 3), array('!a_', '!m_')))
		{
			return false;
		}
		// if we're not negating something that is an admin permission, we don't have it
		else if (in_array(substr($opt, 0, 2), array('f_', 'u_')))
		{
			return true;
		}

		// for anything else (this leaves u_ and f_ perms), we have it.
		return false;
	}

	public function acl_gets()
	{
// NOT IMPLEMENTED YET for the USER class
		$limit = func_num_args();
		for ($i = 0; $i < $limit; $i++)
		{
			$opt = func_get_arg($i);

			if ($opt[0] == '!' && substr($opt, 0, 3) == '!a_')
			{
				return true;
			}
			else if ($opt[0] != '!' && substr($opt, 0, 2) != 'a_')
			{
				return true;
			}
		}

		return false;
	}

	public function acl_getf_global($opt)
	{
		// this should only be called to check m_ permissions generally.  Our user
		// has no m_ permissions.
		if ($opt[0] == '!')
		{
			return true;
		}

		return false;
	}

	public function acl_getf($opt, $clean = false)
	{
		// again, acl_getf should not be called for admin permissions (which are global...)
		$invert = false;
		if ($opt[0] == '!')
		{
			$invert = true;
			$opt = substr($opt, 0, 1);
		}

		$has_it = ($opt[0] == 'f');
		if ($invert)
		{
			$has_it = !$has_it;
		}

		$array = array();
		if (!$has_it && $clean)
		{
			return $array; // return empty
		}


		for ($i = 1; $i < 15; $i++)
		{
			$array[$i] = $has_it;
		}

		return $array;
	}
}