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

	protected $min_chars = 4;
	protected $max_chars = 7;

	function init($type)
	{
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
		$this->code = gen_rand_string(mt_rand($this->min_chars, $this->max_chars));
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
		phpbb::$template->set_filenames(array(
			'captcha' => 'captcha_default.html',
		));

		phpbb::$template->assign_vars(array(
			'CONFIRM_IMAGE'				=> append_sid('ucp', 'mode=confirm&amp;confirm_id=' . $this->confirm_id . '&amp;type=' . $this->type),
			'CONFIRM_ID'				=> $this->confirm_id,
		));

		return phpbb::$template->assign_display('captcha');
	}

	function get_demo_template($id)
	{
		phpbb::$template->set_filenames(array(
			'captcha_demo' => 'captcha_default_acp_demo.html',
		));
		// acp_captcha has a delivery function; let's use it
		phpbb::$template->assign_vars(array(
			'CONFIRM_IMAGE'		=> append_sid(PHPBB_ADMIN_PATH . 'index.' . PHP_EXT, 'captcha_demo=1&amp;mode=visual&amp;i=' . $id . '&amp;select_captcha=' . $this->get_class_name()),
			'CONFIRM_ID'		=> $this->confirm_id,
		));

		return phpbb::$template->assign_display('captcha_demo');
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
		$sql = 'SELECT DISTINCT c.session_id
				FROM ' . CONFIRM_TABLE . ' c
				LEFT JOIN ' . SESSIONS_TABLE . ' s ON (c.session_id = s.session_id)
				WHERE s.session_id IS NULL' .
					((empty($type)) ? '' : ' AND c.confirm_type = ' . (int) $type);
		$result = phpbb::$db->sql_query($sql);

		if ($row = phpbb::$db->sql_fetchrow($result))
		{
			$sql_in = array();
			do
			{
				$sql_in[] = (string) $row['session_id'];
			}
			while ($row = phpbb::$db->sql_fetchrow($result));

			if (sizeof($sql_in))
			{
				$sql = 'DELETE FROM ' . CONFIRM_TABLE . '
					WHERE ' . phpbb::$db->sql_in_set('session_id', $sql_in);
				phpbb::$db->sql_query($sql);
			}
		}
		phpbb::$db->sql_freeresult($result);
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
		$this->confirm_code = request_var('confirm_code', '');

		if (!$this->confirm_id)
		{
			$error = phpbb::$user->lang['CONFIRM_CODE_WRONG'];
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
				$error = phpbb::$user->lang['CONFIRM_CODE_WRONG'];
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
		$this->code = gen_rand_string(mt_rand($this->min_chars, $this->max_chars));
		$this->confirm_id = md5(unique_id(phpbb::$user->ip));
		$this->seed = hexdec(substr(unique_id(), 4, 10));
		$this->solved = false;

		// compute $seed % 0x7fffffff
		$this->seed -= 0x7fffffff * floor($this->seed / 0x7fffffff);

		$sql = 'INSERT INTO ' . CONFIRM_TABLE . ' ' . phpbb::$db->sql_build_array('INSERT', array(
				'confirm_id'	=> (string) $this->confirm_id,
				'session_id'	=> (string) phpbb::$user->session_id,
				'confirm_type'	=> (int) $this->type,
				'code'			=> (string) $this->code,
				'seed'			=> (int) $this->seed)
		);
		phpbb::$db->sql_query($sql);
	}

	/**
	* Look up everything we need for painting&checking.
	*/
	protected function load_code()
	{
		$sql = 'SELECT code, seed
				FROM ' . CONFIRM_TABLE . "
				WHERE confirm_id = '" . phpbb::$db->sql_escape($this->confirm_id) . "'
				AND session_id = '" . phpbb::$db->sql_escape(phpbb::$user->session_id) . "'
					AND confirm_type = " . $this->type;
		$result = phpbb::$db->sql_query($sql);
		$row = phpbb::$db->sql_fetchrow($result);
		phpbb::$db->sql_freeresult($result);
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
		$sql = 'DELETE FROM ' . CONFIRM_TABLE . "
				WHERE confirm_id = '" . phpbb::$db->sql_escape($this->confirm_id) . "'
					AND session_id = '" . phpbb::$db->sql_escape(phpbb::$user->session_id) . "'
					AND confirm_type = " . $this->type;
		phpbb::$db->sql_query($sql);
	}

	function get_attempt_count()
	{
		$sql = 'SELECT COUNT(session_id) AS attempts
				FROM ' . CONFIRM_TABLE . "
				WHERE session_id = '" . phpbb::$db->sql_escape(phpbb::$user->session_id) . "'
					AND confirm_type = " . $this->type;
		$result = phpbb::$db->sql_query($sql);
		$attempts = (int) phpbb::$db->sql_fetchfield('attempts');
		phpbb::$db->sql_freeresult($result);

		return $attempts;
	}


	function reset()
	{
		$sql = 'DELETE FROM ' . CONFIRM_TABLE . "
					WHERE session_id = '" . phpbb::$db->sql_escape(phpbb::$user->session_id) . "'
						AND confirm_type = " . (int) $this->type;
		phpbb::$db->sql_query($sql);

		// we leave the class usable by generating a new question
		$this->generate_code();
	}

}

?>