<?php
/**
*
* @package phpBB3
* @copyright (c) 2011 phpBB Group
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
 * Class to generate confirm boxes for certain arrays
 *
* @package phpBB3
 */
class phpbb_confirm_box
{
	private $request;

	private $user;

	private $db;

	private $template;

	private $phpbb_root_path;

	private $unique;

	private $title;

	/**
	* @param boolean $unique Use unique key or use add_form_key
	* @param string $title Title/Message used for confirm box.
	* message text is _CONFIRM appended to title.
	* If title cannot be found in user->lang a default one is displayed
	* If title_CONFIRM cannot be found in user->lang the text given is used.
	*
	* @param phpbb_request_interface $request
	* @param session $user
	* @param dbal $db
	* @param template $template
	* @param string $phpbb_root_path
	*/
	public function __construct($unique, $title, phpbb_request_interface $request, session $user, dbal $db, template $template, $phpbb_root_path)
	{
		$this->unique			= $unique;
		$this->title			= $title;
		$this->request			= $request;
		$this->user			= $user;
		$this->db			= $db;
		$this->template			= $template;
		$this->phpbb_root_path		= $phpbb_root_path;
	}

	/**
	* Build Confirm box
	*
	* @param string $hidden Hidden variables
	* @param string $html_body Template used for confirm box
	* @param string $u_action Custom form action
	*/
	public function confirm_box($hidden = '', $html_body = 'confirm_body.html', $u_action = '')
	{
		// If activation key already exists, we better not re-use the key (something very strange is going on...)
		if ($this->request->variable('confirm_key', ''))
		{
			// This should not occur, therefore we cancel the operation to safeguard the user
			return false;
		}

		// generate activation key
		$confirm_key = gen_rand_string(10);	

		$this->render($hidden, $html_body, $u_action, $confirm_key);
	}

	/**
	 * Render the template for the confirm box, including the
	 * form keys if non unique mode is used
	 * 
	 * @param string $hidden Hidden variables
	 * @param string $html_body Template used for confirm box
	 * @param string $u_action Custom form action	 
	 * @param string $confirm_key Confirm key
	 */
	private function render($hidden, $html_body, $u_action, $confirm_key)
	{	
		$s_hidden_fields = build_hidden_fields(array(
			'confirm_uid'		=> $this->user->data['user_id'],
			'sess'			=> $this->user->session_id,
			'sid'			=> $this->user->session_id,
		));

		if (!$this->unique)
		{
			list($token, $time) = add_form_key($this->title);

			$s_hidden_fields .= build_hidden_fields(array(
				'creation_time' => $time,
				'form_token'	=> $token,				
			));
		}

		if (isset($this->user->lang[$this->title]))
		{
			$title = $this->user->lang[$this->title];
		}
		else
		{
			$title = $this->user->lang['CONFIRM'];
		}

		if (defined('IN_ADMIN') && isset($this->user->data['session_admin']) && $this->user->data['session_admin'])
		{
			$in_admin = true;
			adm_page_header($title);
		}
		else
		{
			$in_admin = false;
			page_header($title, false);
		}

		$this->template->set_filenames(array(
			'body' => $html_body)
		);

		// re-add sid / transform & to &amp; for user->page (user->page is always using &)
		$use_page = ($u_action) ? $this->phpbb_root_path . $u_action : $this->phpbb_root_path . str_replace('&', '&amp;', $this->user->page['page']);
		$u_action = reapply_sid($use_page);
		$u_action .= ((strpos($u_action, '?') === false) ? '?' : '&amp;') . 'confirm_key=' . $confirm_key;

		$this->template->assign_vars(array(
			'MESSAGE_TITLE'		=> (!isset($this->user->lang[$this->title])) ? $this->user->lang['CONFIRM'] : $this->user->lang[$this->title],
			'MESSAGE_TEXT'		=> (!isset($this->user->lang[$this->title . '_CONFIRM'])) ? $this->title : $this->user->lang[$this->title . '_CONFIRM'],

			'YES_VALUE'			=> $this->user->lang['YES'],
			'S_CONFIRM_ACTION'	=> $u_action,
			'S_HIDDEN_FIELDS'	=> $hidden . $s_hidden_fields)
		);

		if ($this->unique)
		{
			$sql = 'UPDATE ' . USERS_TABLE . " SET user_last_confirm_key = '" . $this->db->sql_escape($confirm_key) . "'
				WHERE user_id = " . $this->user->data['user_id'];

			$this->db->sql_query($sql);
		}

		if ($in_admin)
		{
			adm_page_footer();
		}
		else
		{
			page_footer();
		}
	}

	/**
	 * Check if a box is correctly confirmed.
	*
	 * @param boolean $unique Use unique check or else check_form_key
	 * @param string $title Title required for check_foru_key
	 */
	public function check()
	{
		if (!$this->unique)
		{
			return check_form_key($this->title);
		}

		$confirm = ($this->user->lang['YES'] === $this->request->variable('confirm', '', true, phpbb_request_interface::POST));

		if ($this->request->is_set_post('cancel'))
		{
			return false;
		}

		if (!$confirm)
		{
			return false;
		}

		$user_id = $this->request->variable('confirm_uid', 0);
		$session_id = $this->request->variable('sess', '');
		$confirm_key = $this->request->variable('confirm_key', '');

		if ($user_id != $this->user->data['user_id'] || $session_id != $this->user->session_id || !$confirm_key || !$this->user->data['user_last_confirm_key'] || $confirm_key != $this->user->data['user_last_confirm_key'])
		{
			return false;
		}

		// Reset user_last_confirm_key
		$sql = 'UPDATE ' . USERS_TABLE . " SET user_last_confirm_key = ''
			WHERE user_id = " . $this->user->data['user_id'];
		$this->db->sql_query($sql);
		return true;
	}
}
