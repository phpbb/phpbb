<?php
/**
*
* @package notifications
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

class ucp_auth_link
{
	public $u_action;

	public function main($id, $mode)
	{
		global $template, $phpbb_container;

		$error = array();
		$s_hidden_fields = array();
		add_form_key('ucp_auth_link');

		$submit	= $request->variable('submit', false, false, phpbb_request_interface::POST);

		if ($submit)
		{
			if (!check_form_key('ucp_reg_details'))
			{
				$error[] = 'FORM_INVALID';
			}

			if (!sizeof($error))
			{

			}
		}

		$s_hidden_fields = build_hidden_fields($s_hidden_fields);

		$template->assign_vars(array(
			'S_HIDDEN_FIELDS'	=> $s_hidden_fields,
			'S_UCP_ACTION'		=> $this->u_action,
		));

		$this->tpl_name = 'ucp_auth_link';
		$this->page_title = 'UCP_AUTH_LINK';
	}
}
