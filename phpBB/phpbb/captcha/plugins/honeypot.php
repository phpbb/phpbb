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

namespace phpbb\captcha\plugins;

class honeypot
{
	/**
	 * CSS classes to hide honeypot question.
	 * Classes are used in rotation.
	 */
	const CLASSES = [
		'additional-question',
		'more-questions',
		'personal-data',
		'know-better',
	];

	/**
	* @var \phpbb\user	User object
	*/
	protected $user;

	/**
	* @var \phpbb\language\language	Language object
	*/
	protected $language;

	/**
	* @var \phpbb\request\request	Request object
	*/
	protected $request;

	/**
	* @var \phpbb\db\driver\driver_interface	DB object
	*/
	protected $db;

	/**
	* @var \phpbb\template\template	Template object
	*/
	protected $template;

	/**
	* @var string	Confirm table
	*/
	protected $confirm_table;

	/**
	* @var array	List of possible honeypot questions
	*/
	private $honeypot_bank;

	/**
	* @var int	Confirm type
	*/
	private $type;

	/**
	* @var string	Confirm ID
	*/
	private $confirm_id;

	/**
	* @var code	Key (name) of honeypot question
	*/
	private $code;

	/**
	* @var int|bool	0 in default state, true or false after validation
	*/
	private $solved = 0;

	/**
	* @var int	Number of attempts
	*/
	private $attempts = 0;

	/**
	* Constructor
	*
	* @param	\phpbb\user							$user			User object
	* @param	\phpbb\language\language			$language		Language object
	* @param	\phpbb\request\request				$request		Request object
	* @param	\phpbb\db\driver\driver_interface	$db				DB object
	* @param	\phpbb\template\template			$template		Template object
	* @param	string								$confirm_table	Confirm table
	*/
	public function __construct(\phpbb\user $user, \phpbb\language\language $language, \phpbb\request\request $request, \phpbb\db\driver\driver_interface $db, \phpbb\template\template $template, $confirm_table)
	{
		$this->user = $user;
		$this->language = $language;
		$this->request = $request;
		$this->db = $db;
		$this->template = $template;
		$this->confirm_table = $confirm_table;
	}

	/**
	 * Language key for ACP selectbox
	 *
	 * @return string
	 */
	static public function get_name()
	{
		return 'CAPTCHA_HONEYPOT';
	}

	/**
	 * Is this CAPTCHA available?
	 *
	 * @return boolean
	 */
	public function is_available()
	{
		$this->language->add_lang('captcha_honeypot');
		return true;
	}

	/**
	 * Does this CAPTCHA have a config page?
	 *
	 * @return boolean
	 */
	public function has_config()
	{
		return false;
	}

	/**
	 * Initialize CAPTCHA
	 *
	 * @param int $type Confirm type
	 */
	public function init($type)
	{
		$this->language->add_lang('captcha_honeypot');
		$lang_array = $this->language->get_lang_array();
		$this->honeypot_bank = $lang_array['honeypot_bank'];
		$this->type = $type;
		$this->confirm_id = $this->request->variable('confirm_id', '');

		if (!empty($this->confirm_id))
		{
			$sql = 'SELECT code, attempts
				FROM ' . $this->confirm_table . "
				WHERE confirm_id = '" . $this->db->sql_escape($this->confirm_id) . "'
					AND session_id = '" . $this->db->sql_escape($this->user->session_id) . "'
					AND confirm_type = " . (int) $this->type;
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if ($row)
			{
				$this->code = $row['code'];
				$this->attempts = $row['attempts'];

				// Don't generate new honeypot until we validate existing one
				return;
			}
		}

		$this->generate_honeypot();
	}

	/**
	 * Reset CAPTCHA
	 */
	public function reset()
	{
		$sql = 'DELETE FROM ' . $this->confirm_table . "
			WHERE session_id = '" . $this->db->sql_escape($this->user->session_id) . "'
				AND confirm_type = " . (int) $this->type;
		$db->sql_query($sql);

		// we leave the class usable by generating a new question
		$this->generate_honeypot();
	}

	// not needed
	public function execute_demo()
	{
	}

	// not needed
	public function acp_page($id, &$module)
	{
	}

	/**
	 * ACP template to be displayed as demo
	 *
	 * @param int $id Module ID
	 * @return string
	 */
	public function get_demo_template($id)
	{
		return 'captcha_honeypot.html';
	}

	/**
	 * Prepare template for display
	 *
	 * @return string Template filename
	 */
	public function get_template()
	{
		$this->template->assign_vars([
			'HONEYPOT_LABEL'	=> $this->honeypot_bank[$this->code],
			'HONEYPOT_NAME'		=> $this->code,
			'S_TYPE'			=> $this->type,
			'HONEYPOT_CLASS'	=> self::CLASSES[array_rand(self::CLASSES)],
		]);

		return 'captcha_honeypot.html';
	}

	// not needed
	public function execute()
	{
	}

	/**
	 * Get hidden fields for the current form
	 *
	 * @return array Hidden fields needed by this CAPTCHA
	 */
	public function get_hidden_fields()
	{
		return [
			'confirm_id'	=> $this->confirm_id,
		];
	}

	/**
	 * Validate user's response
	 *
	 * @return string|bool Error string or false if response is OK
	 */
	public function validate()
	{
		if (!$this->user->is_setup())
		{
			$this->user->setup();
		}

		$error = '';
		if (!$this->confirm_id)
		{
			$error = $this->language->lang('CONFIRM_CODE_WRONG');
		}
		else
		{
			if ($this->request->variable($this->code, '') === '')
			{
				$this->solved = true;
			}
			else
			{
				$error = $this->language->lang('CONFIRM_CODE_WRONG');
			}
		}

		if (strlen($error))
		{
			// okay, incorrect answer. Let's ask a new question.
			$this->new_attempt();
			return $error;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Is CAPTCHA correctly solved?
	 *
	 * @return boolean
	 */
	public function is_solved()
	{
		if ($this->solved === 0)
		{
			$this->validate();
		}
		return (bool) $this->solved;
	}

	/**
	 * Get number of attempts
	 *
	 * @return int
	 */
	public function get_attempt_count()
	{
		return $this->attempts;
	}

	// not needed
	public function install()
	{
	}

	/**
	 * Uninstall extension
	 */
	public function uninstall()
	{
		$this->garbage_collect(0);
	}

	/**
	 * Generate new honeypot
	 */
	protected function generate_honeypot()
	{
		$this->confirm_id = md5(unique_id($this->user->ip));
		$this->code = array_rand($this->honeypot_bank);
		$this->solved = 0;

		$sql = 'INSERT INTO ' . $this->confirm_table . ' ' . $this->db->sql_build_array('INSERT', [
			'confirm_id'	=> (string) $this->confirm_id,
			'session_id'	=> (string) $this->user->session_id,
			'confirm_type'	=> (int) $this->type,
			'code'			=> (string) $this->code,
		]);
		$this->db->sql_query($sql);
	}

	/**
	 * Regenerate honeypot
	 */
	protected function new_attempt()
	{
		$this->code = array_rand($this->honeypot_bank);
		$this->solved = 0;

		$sql = 'UPDATE ' . $this->confirm_table . ' SET ' . $this->db->sql_build_array('UPDATE', [
				'code'	=> (string) $this->code,
			]) . ",
				attempts = attempts + 1
			WHERE confirm_id = '" . $this->db->sql_escape($this->confirm_id) . "'
				AND session_id = '" . $this->db->sql_escape($this->user->session_id) . "'";
		$this->db->sql_query($sql);
	}
}
