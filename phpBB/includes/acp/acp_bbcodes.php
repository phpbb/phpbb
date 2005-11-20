<?php
/** 
*
* @package acp
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @package acp
*/
class acp_bbcodes
{
	function main($id, $mode)
	{
		global $db, $user, $auth, $template, $cache;
		global $config, $SID, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$user->add_lang('acp/posting');

		// Set up general vars
		$action	= request_var('action', '');
		$bbcode_id = request_var('bbcode', 0);

		$this->tpl_name = 'acp_bbcodes';

		$u_action = "{$phpbb_admin_path}index.$phpEx$SID&amp;i=$id&amp;mode=$mode";

		// Set up mode-specific vars
		switch ($action)
		{
			case 'add':
				$bbcode_match = $bbcode_tpl = '';
			break;

			case 'edit':
				$sql = 'SELECT bbcode_match, bbcode_tpl
					FROM ' . BBCODES_TABLE . '
					WHERE bbcode_id = ' . $bbcode_id;
				$result = $db->sql_query($sql);

				if (!$row = $db->sql_fetchrow($result))
				{
					trigger_error('BBCODE_NOT_EXIST');
				}
				$db->sql_freeresult($result);

				$bbcode_match = $row['bbcode_match'];
				$bbcode_tpl = htmlspecialchars($row['bbcode_tpl']);
			break;

			case 'modify':
				$sql = 'SELECT bbcode_id
					FROM ' . BBCODES_TABLE . '
					WHERE bbcode_id = ' . $bbcode_id;
				$result = $db->sql_query($sql);

				if (!$row = $db->sql_fetchrow($result))
				{
					trigger_error('BBCODE_NOT_EXIST');
				}
				$db->sql_freeresult($result);

				// No break here

			case 'create':
				$bbcode_match = (isset($_POST['bbcode_match'])) ? htmlspecialchars(stripslashes($_POST['bbcode_match'])) : '';
				$bbcode_tpl = (isset($_POST['bbcode_tpl'])) ? stripslashes($_POST['bbcode_tpl']) : '';
			break;
		}

		// Do major work
		switch ($action)
		{
			case 'edit':
			case 'add':

				$template->assign_vars(array(
					'S_EDIT_BBCODE'		=> true,
					'U_BACK'			=> $u_action,
					'U_ACTION'			=> $u_action . '&amp;action=' . (($action == 'add') ? 'create' : 'modify') . (($bbcode_id) ? "&amp;bbcode=$bbcode_id" : ''),

					'BBCODE_MATCH'		=> $bbcode_match,
					'BBCODE_TPL'		=> $bbcode_tpl,
					)
				);

				foreach ($user->lang['tokens'] as $token => $token_explain)
				{
					$template->assign_block_vars('token', array(
						'TOKEN'		=> '{' . $token . '}',
						'EXPLAIN'	=> $token_explain)
					);
				}

				return;

			break;

			case 'modify':
			case 'create':

				$data = $this->build_regexp($bbcode_match, $bbcode_tpl);

				$sql_ary = array(
					'bbcode_tag'				=> $data['bbcode_tag'],
					'bbcode_match'				=> $bbcode_match,
					'bbcode_tpl'				=> $bbcode_tpl,
					'first_pass_match'			=> $data['first_pass_match'],
					'first_pass_replace'		=> $data['first_pass_replace'],
					'second_pass_match'			=> $data['second_pass_match'],
					'second_pass_replace'		=> $data['second_pass_replace']
				);

				if ($action == 'create')
				{
					$sql = 'SELECT MAX(bbcode_id) as bbcode_id
						FROM ' . BBCODES_TABLE;
					$result = $db->sql_query($sql);
					$row = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);

					if ($row)
					{
						$bbcode_id = $row['bbcode_id'] + 1;
					}
					else
					{
						$sql = 'SELECT MIN(bbcode_id) AS min_id, MAX(bbcode_id) AS max_id
							FROM ' . BBCODES_TABLE;
						$result = $db->sql_query($sql);
						$row = $db->sql_fetchrow($result);
						$db->sql_freeresult($result);

						if (empty($row['min_id']) || $row['min_id'] >= NUM_CORE_BBCODES)
						{
							$bbcode_id = NUM_CORE_BBCODES + 1;
						}
						else
						{
							$bbcode_id = $row['max_id'] + 1;
						}
					}

					if ($bbcode_id > 31)
					{
						trigger_error('TOO_MANY_BBCODES');
					}

					$sql_ary['bbcode_id'] = (int) $bbcode_id;

					$db->sql_query('INSERT INTO ' . BBCODES_TABLE . $db->sql_build_array('INSERT', $sql_ary));
					$lang = 'BBCODE_ADDED';
					$log_action = 'LOG_BBCODE_ADD';
				}
				else
				{
					$db->sql_query('UPDATE ' . BBCODES_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . ' WHERE bbcode_id = ' . $bbcode_id);
					$lang = 'BBCODE_EDITED';
					$log_action = 'LOG_BBCODE_EDIT';
				}

				add_log('admin', $log_action, $data['bbcode_tag']);

				trigger_error($user->lang[$lang] . adm_back_link($u_action));

			break;

			case 'delete':
				$sql = 'SELECT bbcode_tag
					FROM ' . BBCODES_TABLE . "
					WHERE bbcode_id = $bbcode_id";
				$result = $db->sql_query($sql);
				
				if ($row = $db->sql_fetchrow($result))
				{
					$db->sql_query('DELETE FROM ' . BBCODES_TABLE . " WHERE bbcode_id = $bbcode_id");
					add_log('admin', 'LOG_BBCODE_DELETE', $row['bbcode_tag']);
				}
				$db->sql_freeresult($result);

			break;
		}

		$template->assign_vars(array(
			'U_ACTION'		=> $u_action . '&amp;mode=add')
		);

		$sql = 'SELECT *
			FROM ' . BBCODES_TABLE . '
			ORDER BY bbcode_id';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$template->assign_block_vars('bbcodes', array(
				'BBCODE_TAG'		=> $row['bbcode_tag'],
				'U_EDIT'			=> $u_action . '&amp;action=edit&amp;bbcode=' . $row['bbcode_id'],
				'U_DELETE'			=> $u_action . '&amp;action=delete&amp;bbcode=' . $row['bbcode_id'])
			);
		}
		$db->sql_freeresult($result);
	}

	/*
	* Build regular expression for custom bbcode
	*/
	function build_regexp($msg_bbcode, $msg_html)
	{
		$msg_bbcode = trim($msg_bbcode);
		$msg_html = trim($msg_html);

		$fp_match = preg_quote($msg_bbcode, '!');
		$fp_replace = preg_replace('#^\[(.*?)\]#', '[$1:$uid]', $msg_bbcode);
		$fp_replace = preg_replace('#\[/(.*?)\]$#', '[/$1:$uid]', $fp_replace);

		$sp_match = preg_quote($msg_bbcode, '!');
		$sp_match = preg_replace('#^\\\\\[(.*?)\\\\\]#', '\[$1:$uid\]', $sp_match);
		$sp_match = preg_replace('#\\\\\[/(.*?)\\\\\]$#', '\[/$1:$uid\]', $sp_match);
		$sp_replace = $msg_html;

		// @todo Make sure to change this too if something changed in message parsing
		$tokens = array(
			'URL'	 => array(
				'!([a-z0-9]+://)?([^?].*?[^ \t\n\r<"]*)!ie'	=>	"(('\$1') ? '\$1\$2' : 'http://\$2')"
			),
			'LOCAL_URL'	 => array(
				'!([^:]+/[^ \t\n\r<"]*)!'	=>	'$1'
			),
			'EMAIL' => array(
				'!([a-z0-9]+[a-z0-9\-\._]*@(?:(?:[0-9]{1,3}\.){3,5}[0-9]{1,3}|[a-z0-9]+[a-z0-9\-\._]*\.[a-z]+))!i'	=>	'$1'
			),
			'TEXT' => array(
				'!(.*?)!es'	 =>	"str_replace('\\\"', '&quot;', str_replace('\\'', '&#39;', '\$1'))"
			),
			'COLOR' => array(
				'!([a-z]+|#[0-9abcdef]+!i'	=>	'$1'
			),
			'NUMBER' => array(
				'!([0-9]+)!'	=>	'$1'
			)
		);

		if (preg_match_all('/\{(' . implode('|', array_keys($tokens)) . ')[0-9]*\}/i', $msg_bbcode, $m))
		{
			$pad = 0;
			$modifiers = 'i';

			foreach ($m[0] as $n => $token)
			{
				$token_type = $m[1][$n];

				reset($tokens[$token_type]);
				list($match, $replace) = each($tokens[$token_type]);

				// Pad backreference numbers from tokens
				if (preg_match_all('/(?<!\\\\)\$([0-9]+)/', $replace, $repad))
				{
					$repad = $pad + sizeof(array_unique($repad[0]));
					$replace = preg_replace('/(?<!\\\\)\$([0-9]+)/e', "'\$' . (\$1 + \$pad)", $replace);
					$pad = $repad;
				}

				// Obtain pattern modifiers to use and alter the regex accordingly
				$regex = preg_replace('/!(.*)!([a-z]*)/', '$1', $match);
				$regex_modifiers = preg_replace('/!(.*)!([a-z]*)/', '$2', $match);

				for ($i = 0, $size = strlen($regex_modifiers); $i < $size; ++$i)
				{
					if (strpos($modifiers, $regex_modifiers[$i]) === false)
					{
						$modifiers .= $regex_modifiers[$i];

						if ($regex_modifiers[$i] == 'e')
						{
							$fp_replace = "'" . str_replace("'", "\\'", $fp_replace) . "'";
						}
					}

					if ($regex_modifiers[$i] == 'e')
					{
						$replace = "'.$replace.'";
					}
				}

				$fp_match = str_replace(preg_quote($token, '!'), $regex, $fp_match);
				$fp_replace = str_replace($token, $replace, $fp_replace);

				$sp_match = str_replace(preg_quote($token, '!'), '(.*?)', $sp_match);
				$sp_replace = str_replace($token, '$' . ($n + 1), $sp_replace);
			}

			$fp_match = '!' . $fp_match . '!' . $modifiers;
			$sp_match = '!' . $sp_match . '!s';

			if (strpos($fp_match, 'e') !== false)
			{
				$fp_replace = str_replace("'.'", '', $fp_replace);
				$fp_replace = str_replace(".''.", '.', $fp_replace);
			}
		}
		else
		{
			// No replacement is present, no need for a second-pass pattern replacement
			// A simple str_replace will suffice
			$fp_match = '!' . $fp_match . '!' . $modifiers;
			$sp_match = $fp_replace;
			$sp_replace = '';
		}

		// Lowercase tags
		$bbcode_tag = preg_replace('/.*?\[([a-z]+).*/i', '$1', $msg_bbcode);
		$fp_match = preg_replace('#\[/?' . $bbcode_tag . '#ie', "strtolower('\$0')", $fp_match);
		$fp_replace = preg_replace('#\[/?' . $bbcode_tag . '#ie', "strtolower('\$0')", $fp_replace);
		$sp_match = preg_replace('#\[/?' . $bbcode_tag . '#ie', "strtolower('\$0')", $sp_match);
		$sp_replace = preg_replace('#\[/?' . $bbcode_tag . '#ie', "strtolower('\$0')", $sp_replace);

		return array(
			'bbcode_tag'				=> $bbcode_tag,
			'first_pass_match'			=> $fp_match,
			'first_pass_replace'		=> $fp_replace,
			'second_pass_match'			=> $sp_match,
			'second_pass_replace'		=> $sp_replace
		);
	}
}

/**
* @package module_install
*/
class acp_bbcodes_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_bbcodes',
			'title'		=> 'ACP_BBCODES',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'bbcodes'		=> array('title' => 'ACP_BBCODES', 'auth' => 'acl_a_bbcode'),
			),
		);
	}

	function install()
	{
	}

	function uninstall()
	{
	}
}

?>