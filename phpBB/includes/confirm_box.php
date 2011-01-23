<?php
/**
*
* @package phpbb_confirmbox
* @copyright (c) 2010 phpBB Group
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
 * Class to generate confirm boxes for certian arrays
 *
 * @package phpbb_confirmbox
 **/
class phpbb_confirm_box
{
	private $request = null;
	
	private $user = null;
	
	private $db = null;
	
	private $template = null;
	
	private $phpbb_root_path = null;
	
	public function __construct(phpbb_request_interface $request, session $user, dbal $db, template $template, $phpbb_root_path)
	{
		$this->request			= $request;
		$this->user				= $user;
		$this->db				= $db;
		$this->template			= $template;
		$this->phpbb_root_path	= $phpbb_root_path;
	}
	/**
	* Build Confirm box
	* @param boolean $unique Use unique variable, or add_form_key
	* @param string $title Title/Message used for confirm box.
	*		message text is _CONFIRM appended to title.
	*		If title cannot be found in user->lang a default one is displayed
	*		If title_CONFIRM cannot be found in user->lang the text given is used.
	* @param string $hidden Hidden variables
	* @param string $html_body Template used for confirm box
	* @param string $u_action Custom form action
	*/
	public function confirm_box($unique = true, $title = '', $hidden = '', $html_body = 'confirm_body.html', $u_action = '')
	{
		// If activation key already exist, we better do not re-use the key (something very strange is going on...)
		if ($this->request->variable('confirm_key', ''))
		{
			// This should not occur, therefore we cancel the operation to safe the user
			return false;
		}
			
		// generate activation key
		$confirm_key = gen_rand_string(10);	
		
		$this->build_template($title, $hidden, $html_body, $u_action, $confirm_key, $unique);
	}
	
	/**
	 *
 	 * @param string $title Title/Message used for confirm box.
	 *		message text is _CONFIRM appended to title.
	 *		If title cannot be found in user->lang a default one is displayed
	 *		If title_CONFIRM cannot be found in user->lang the text given is used.
	 * @param string $hidden Hidden variables
	 * @param string $html_body Template used for confirm box
	 * @param string $u_action Custom form action	 
	 * @param string $confirm_key Confirm key
	 * @param boolean $lite Lite box
	 **/
	private function build_template($title, $hidden, $html_body, $u_action, $confirm_key, $unique)
	{	
		$s_hidden_fields = build_hidden_fields(array(
			'confirm_uid'	=> $this->user->data['user_id'],
			'sess'			=> $this->user->session_id,
			'sid'			=> $this->user->session_id,
		));
		
		if (!$unique)
		{
			list($token, $time) = add_form_key($title);
			
			$s_hidden_fields .= build_hidden_fields(array(
				'creation_time' => $time,
				'form_token'	=> $token,				
			));
		}

		if (defined('IN_ADMIN') && isset($user->data['session_admin']) && $user->data['session_admin'])
		{
			adm_page_header((!isset($this->user->lang[$title])) ? $this->user->lang['CONFIRM'] : $this->user->lang[$title]);
		}
		else
		{
			page_header(((!isset($this->user->lang[$title])) ? $this->user->lang['CONFIRM'] : $this->user->lang[$title]), false);
		}

		$this->template->set_filenames(array(
			'body' => $html_body)
		);

		// re-add sid / transform & to &amp; for user->page (user->page is always using &)
		$use_page = ($u_action) ? $this->phpbb_root_path . $u_action : $this->phpbb_root_path . str_replace('&', '&amp;', $this->user->page['page']);
		$u_action = reapply_sid($use_page);
		$u_action .= ((strpos($u_action, '?') === false) ? '?' : '&amp;') . 'confirm_key=' . $confirm_key;

		$this->template->assign_vars(array(
			'MESSAGE_TITLE'		=> (!isset($this->user->lang[$title])) ? $this->user->lang['CONFIRM'] : $this->user->lang[$title],
			'MESSAGE_TEXT'		=> (!isset($this->user->lang[$title . '_CONFIRM'])) ? $title : $this->user->lang[$title . '_CONFIRM'],

			'YES_VALUE'			=> $this->user->lang['YES'],
			'S_CONFIRM_ACTION'	=> $u_action,
			'S_HIDDEN_FIELDS'	=> $hidden . $s_hidden_fields)
		);

		if ($unique)
		{
			$sql = 'UPDATE ' . USERS_TABLE . " SET user_last_confirm_key = '" . $this->db->sql_escape($confirm_key) . "'
				WHERE user_id = " . $this->user->data['user_id'];
		
			$this->db->sql_query($sql);
		}

		if (defined('IN_ADMIN') && isset($this->user->data['session_admin']) && $this->user->data['session_admin'])
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
	 * @param boolean $unique Use unique check or else check_form_key
	 * @param string $title Title required for check_foru_key
	 **/
	public function check_box($unique = true, $title = '')
	{
		if (!$unique)
		{
			return check_form_key($title);
		}
		
		$confirm = ($this->user->lang['YES'] === $this->request->variable('confirm', '', true, phpbb_request_interface::POST));
		
		if (isset($_POST['cancel']))
		{
			return false;
		}
		
		if ($confirm)
		{
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
		return false;	
	}
}

