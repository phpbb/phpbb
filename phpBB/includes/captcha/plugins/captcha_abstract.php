<?php
/**
*
* @package VC
* @version $Id$
* @copyright (c) 2006 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}


/**
* This class holds the code shared by the two default 3.0 CAPTCHAs.
*/
abstract class phpbb_default_captcha implements phpbb_captcha_plugin
{
	protected $confirm_id;
	protected $confirm_code;
	protected $code;
	protected $seed;
	protected $type;
	protected $solved = false;


	function init($type)
	{
		global $config, $db, $user;

		// read input
		$this->confirm_id = request_var('confirm_id', '');
		$this->confirm_code = request_var('confirm_code', '');
		$this->type = (int) $type;

		if (!strlen($this->confirm_id))
		{
			// we have no confirm ID, better get ready to display something
			$this->generate_code();
		}
	}

	function execute_demo()
	{
		global $user;

		$this->code = gen_rand_string(mt_rand(5, 8));
		$this->seed = hexdec(substr(unique_id(), 4, 10));

		// compute $seed % 0x7fffffff
		$this->seed -= 0x7fffffff * floor($this->seed / 0x7fffffff);

		captcha::execute($this->code, $this->seed);
	}


	function execute()
	{
		if (empty($this->code))
		{
			if (!$this->load_code())
			{
				// invalid request, bail out
				return false;
			}
		}
		captcha::execute($this->code, $this->seed);
	}


	function get_template()
	{
		global $config, $user, $template;

		$template->set_filenames(array(
			'captcha' => 'captcha_default.html')
		);

		$template->assign_vars(array(
			'CONFIRM_IMAGE'				=> append_sid('ucp', 'mode=confirm&amp;confirm_id=' . $this->confirm_id . '&amp;type=' . $this->type),
			'CONFIRM_ID'				=> $this->confirm_id,
		));

		return $template->assign_display('captcha');
	}

	function get_demo_template($id)
	{
		global $config, $user, $template;

		$template->set_filenames(array(
			'captcha_demo' => 'captcha_default_acp_demo.html')
		);
		// acp_captcha has a delivery function; let's use it
		$template->assign_vars(array(
			'CONFIRM_IMAGE'		=> append_sid(PHPBB_ADMIN_PATH . 'index.' . PHP_EXT, 'captcha_demo=1&amp;mode=visual&amp;i=' . $id . '&amp;select_captcha=' . $this->get_class_name()),
			'CONFIRM_ID'		=> $this->confirm_id,
		));

		return $template->assign_display('captcha_demo');
	}

	function get_hidden_fields()
	{
		$hidden_fields = array();

		// this is required for postig.php - otherwise we would forget about the captcha being already solved
		if ($this->solved)
		{
			$hidden_fields['confirm_code'] = $this->confirm_code;
		}
		$hidden_fields['confirm_id'] = $this->confirm_id;
		return $hidden_fields;
	}

	static function garbage_collect($type)
	{
		global $db, $config;

		$sql = 'SELECT DISTINCT c.session_id
				FROM ' . CONFIRM_TABLE . ' c
				LEFT JOIN ' . SESSIONS_TABLE . ' s ON (c.session_id = s.session_id)
				WHERE s.session_id IS NULL' .
					((empty($type)) ? '' : ' AND c.confirm_type = ' . (int) $type);
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			$sql_in = array();
			do
			{
				$sql_in[] = (string) $row['session_id'];
			}
			while ($row = $db->sql_fetchrow($result));

			if (sizeof($sql_in))
			{
				$sql = 'DELETE FROM ' . CONFIRM_TABLE . '
					WHERE ' . $db->sql_in_set('session_id', $sql_in);
				$db->sql_query($sql);
			}
		}
		$db->sql_freeresult($result);
	}

	function uninstall()
	{
		self::garbage_collect(0);
	}

	function install()
	{
		return;
	}

	function validate()
	{
		global $config, $db, $user;

		$this->confirm_code = request_var('confirm_code', '');

		if (!$this->confirm_id)
		{
			$error = $user->lang['CONFIRM_CODE_WRONG'];
		}
		else
		{
			if ($this->check_code())
			{
				// $this->delete_code(); commented out to allow posting.php to repeat the question
				$this->solved = true;
			}
			else
			{
				$error = $user->lang['CONFIRM_CODE_WRONG'];
			}
		}

		if (strlen($error))
		{
			// okay, inorect answer. Let's ask a new question
			$this->generate_code();
			return $error;
		}
		else
		{
			return false;
		}
	}


	/**
	* The old way to generate code, suitable for GD and non-GD. Resets the internal state.
	*/
	protected function generate_code()
	{
		global $db, $user;

		$this->code = gen_rand_string(mt_rand(5, 8));
		$this->confirm_id = md5(unique_id($user->ip));
		$this->seed = hexdec(substr(unique_id(), 4, 10));
		$this->solved = false;
		// compute $seed % 0x7fffffff
		$this->seed -= 0x7fffffff * floor($this->seed / 0x7fffffff);

		$sql = 'INSERT INTO ' . CONFIRM_TABLE . ' ' . $db->sql_build_array('INSERT', array(
				'confirm_id'	=> (string) $this->confirm_id,
				'session_id'	=> (string) $user->session_id,
				'confirm_type'	=> (int) $this->type,
				'code'			=> (string) $this->code,
				'seed'			=> (int) $this->seed)
		);
		$db->sql_query($sql);
	}

	/**
	* Look up everything we need for painting&checking.
	*/
	protected function load_code()
	{
		global $db, $user;
		$sql = 'SELECT code, seed
				FROM ' . CONFIRM_TABLE . "
				WHERE confirm_id = '" . $db->sql_escape($this->confirm_id) . "'
				AND session_id = '" . $db->sql_escape($user->session_id) . "'
					AND confirm_type = " . $this->type;
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		if ($row)
		{
			$this->code = $row['code'];
			$this->seed = $row['seed'];
			return true;
		}
		return false;

	}

	protected function check_code()
	{
		global $db;

		if (empty($this->code))
		{
			if (!$this->load_code())
			{
				return false;
			}
		}
		return (strcasecmp($this->code, $this->confirm_code) === 0);
	}

	protected function delete_code()
	{
		global $db, $user;

		$sql = 'DELETE FROM ' . CONFIRM_TABLE . "
				WHERE confirm_id = '" . $db->sql_escape($confirm_id) . "'
					AND session_id = '" . $db->sql_escape($user->session_id) . "'
					AND confirm_type = " . $this->type;
		$db->sql_query($sql);
	}

	function get_attempt_count()
	{
		global $db, $user;

		$sql = 'SELECT COUNT(session_id) AS attempts
				FROM ' . CONFIRM_TABLE . "
				WHERE session_id = '" . $db->sql_escape($user->session_id) . "'
					AND confirm_type = " . $this->type;
		$result = $db->sql_query($sql);
		$attempts = (int) $db->sql_fetchfield('attempts');
		$db->sql_freeresult($result);

		return $attempts;
	}


	function reset()
	{
		global $db, $user;

		$sql = 'DELETE FROM ' . CONFIRM_TABLE . "
					WHERE session_id = '" . $db->sql_escape($user->session_id) . "'
						AND confirm_type = " . (int) $this->type;
		$db->sql_query($sql);

		// we leave the class usable by generating a new question
		$this->generate_code();
	}

}

?>