<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : admin_bbcodes.php 
// STARTED   : Wed Aug 20, 2003
// COPYRIGHT : © 2001, 2003 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ] 
// 
// -------------------------------------------------------------

if (!empty($setmodules))
{
	if (!$auth->acl_get('a_bbcode'))
	{
		return;
	}

	$filename = basename(__FILE__);
	$module['POST']['BBCODES'] = $filename . $SID;

	return;
}

define('IN_PHPBB', 1);

// Include files
$phpbb_root_path = '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require('pagestart.' . $phpEx);

// Do we have general permissions?
if (!$auth->acl_get('a_bbcode'))
{
	trigger_error($user->lang['NO_ADMIN']);
}

// Set up general vars
$mode = (!empty($_REQUEST['mode'])) ? $_REQUEST['mode'] : '';
$bbcode_id = (!empty($_REQUEST['bbcode'])) ? intval($_REQUEST['bbcode']) : 0;

// Set up mode-specific vars
switch ($mode)
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

		// No break here

	case 'create':
		$bbcode_match = htmlspecialchars(stripslashes($_POST['bbcode_match']));
		$bbcode_tpl = stripslashes($_POST['bbcode_tpl']);
	break;
}

// Do major work
switch ($mode)
{
	case 'edit':
	case 'add':
		adm_page_header($user->lang['BBCODES']);

?>

<h1><?php echo $user->lang['BBCODES'] ?></h1>

<p><?php echo $user->lang['BBCODES_EXPLAIN'] ?></p>

<form method="post" action="admin_bbcodes.<?php echo $phpEx . $SID . '&amp;mode=' . (($mode == 'add') ? 'create' : 'modify') . (($bbcode_id) ? "&amp;bbcode=$bbcode_id" : '') ?>">
<table cellspacing="1" cellpadding="0" border="0" align="center" width="90%">
	<tr>
		<td><table class="bg" width="100%" cellspacing="1" cellpadding="4" border="0" align="center">
			<tr>
				<th colspan="2"><?php echo $user->lang['BBCODE_USAGE'] ?></th>
			</tr>
			<tr>
				<td class="row3" colspan="2"><?php echo $user->lang['BBCODE_USAGE_EXPLAIN'] ?></td>
			</tr>
			<tr valign="top">
				<td class="row1" width="40%"><b><?php echo $user->lang['EXAMPLES'] ?></b><br /><br /><?php echo $user->lang['BBCODE_USAGE_EXAMPLE'] ?></td>
				<td class="row2"><textarea name="bbcode_match" cols="60" rows="5"><?php echo $bbcode_match ?></textarea></td>
			</tr>
		</table></td>
	</tr>
</table>

<br clear="all" />

<table cellspacing="1" cellpadding="0" border="0" align="center" width="90%">
	<tr>
		<td><table class="bg" width="100%" cellspacing="1" cellpadding="4" border="0" align="center">
			<tr>
				<th colspan="2"><?php echo $user->lang['HTML_REPLACEMENT'] ?></th>
			</tr>
			<tr>
				<td class="row3" colspan="2"><?php echo $user->lang['HTML_REPLACEMENT_EXPLAIN'] ?></td>
			</tr>
			<tr valign="top">
				<td class="row1" width="40%"><b><?php echo $user->lang['EXAMPLES'] ?></b><br /><br /><?php echo $user->lang['HTML_REPLACEMENT_EXAMPLE'] ?></td>
				<td class="row2"><textarea name="bbcode_tpl" cols="60" rows="8"><?php echo $bbcode_tpl ?></textarea></td>
			</tr>
			<tr>
				<td class="cat" colspan="2" align="center"><input type="submit" value="<?php echo $user->lang['SUBMIT'] ?>" class="btnmain" /></td>
			</tr>
		</table></td>
	</tr>
</table>

<br clear="all" />

<table cellspacing="1" cellpadding="0" border="0" align="center" width="90%">
	<tr>
		<td><table class="bg" width="100%" cellspacing="1" cellpadding="4" border="0" align="center">
			<tr>
				<th colspan="2"><?php echo $user->lang['TOKENS'] ?></th>
			</tr>
			<tr>
				<td class="row3" colspan="2"><?php echo $user->lang['TOKENS_EXPLAIN'] ?></td>
			</tr>
			<tr>
				<th><?php echo $user->lang['TOKEN'] ?></th>
				<th><?php echo $user->lang['TOKEN_DEFINITION'] ?></th>
			</tr>
<?php

		foreach ($user->lang['tokens'] as $token => $token_explain)
		{
			?><tr valign="top">
				<td class="row1">{<?php echo $token ?>}</td>
				<td class="row2"><?php echo $token_explain ?></td>
			</tr><?php
		}

?>
		</table></td>
	</tr>
</table>
</form>

<?php

		adm_page_footer();
	break;

	case 'modify':
	case 'create':
		adm_page_header($user->lang['BBCODES']);

		$data = build_regexp($bbcode_match, $bbcode_tpl);

		$sql_ary = array(
			'bbcode_tag'					=>	$data['bbcode_tag'],
			'bbcode_match'				=>	$bbcode_match,
			'bbcode_tpl'					=>	$bbcode_tpl,
			'first_pass_match'			=>	$data['first_pass_match'],
			'first_pass_replace'		=>	$data['first_pass_replace'],
			'second_pass_match'	=>	$data['second_pass_match'],
			'second_pass_replace'	=>	$data['second_pass_replace']
		);

		if ($mode == 'create')
		{
			// TODO: look for SQL incompatibilities
			// NOTE: I'm sure there was another simpler (and obvious) way of finding a suitable bbcode_id
			$sql = 'SELECT b1.bbcode_id
				FROM ' . BBCODES_TABLE . ' b1, ' . BBCODES_TABLE . ' b2
				WHERE b2.bbcode_id > b1.bbcode_id
				GROUP BY b1.bbcode_id
				HAVING MIN(b2.bbcode_id) > b1.bbcode_id + 1
				ORDER BY b1.bbcode_id ASC';
			$result = $db->sql_query_limit($sql, 1);
			
			 if ($row = $db->sql_fetchrow($result))
			{
				 $bbcode_id = $row['bbcode_id'] + 1;
			}
			else
			{
				$sql = 'SELECT MIN(bbcode_id) AS min_id, MAX(bbcode_id) AS max_id
					FROM ' . BBCODES_TABLE;
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);

				if (empty($row['min_id']) || $row['min_id'] > 12)
				{
					$bbcode_id = 12;
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
		}
		else
		{
			$db->sql_query('UPDATE ' . BBCODES_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . ' WHERE bbcode_id = ' . $bbcode_id);
			$lang = 'BBCODE_EDITED';
		}

		trigger_error($lang);
	break;

	case 'delete':
		$db->sql_query('DELETE FROM ' . BBCODES_TABLE . " WHERE bbcode_id = $bbcode_id");

		// No break here

	default:

		adm_page_header($user->lang['BBCODES']);

?>

<h1><?php echo $user->lang['BBCODES'] ?></h1>

<p><?php echo $user->lang['BBCODES_EXPLAIN'] ?></p>


<form method="post" action="admin_bbcodes.<?php echo $phpEx . $SID ?>&amp;mode=add"><table cellspacing="1" cellpadding="0" border="0" align="center">
	<tr>
		<td><table class="bg" width="100%" cellspacing="1" cellpadding="4" border="0" align="center">
			<tr>
				<th><?php echo $user->lang['BBCODE_TAG'] ?></th>
				<th><?php echo $user->lang['ACTION'] ?></th>
			</tr><?php

		$sql = 'SELECT *
			FROM ' . BBCODES_TABLE . '
			ORDER BY bbcode_id';
		$result = $db->sql_query($sql);

		$row_class = '';
		while ($row = $db->sql_fetchrow($result))
		{
			$row_class = ($row_class == 'row1') ? 'row2' : 'row1';
?>
			<tr>
				<td class="<?php echo $row_class ?>" align="center"><?php echo $row['bbcode_tag'] ?></td>
				<td class="<?php echo $row_class ?>" align="center"><a href="admin_bbcodes.<?php echo $phpEx . $SID ?>&amp;mode=edit&amp;bbcode=<?php echo $row['bbcode_id'] ?>"><?php echo $user->lang['EDIT'] ?></a> | <a href="admin_bbcodes.<?php echo $phpEx . $SID ?>&amp;mode=delete&amp;bbcode=<?php echo $row['bbcode_id'] ?>"><?php echo $user->lang['DELETE'] ?></a></td>
			</tr>
<?php
		}

?>

			<tr>
				<td class="cat" colspan="2" align="center"><input type="submit" value="<?php echo $user->lang['ADD_BBCODE'] ?>" class="btnmain" /></td>
			</tr>
		</table></td>
	</tr>
</table></form>

<?php

	adm_page_footer();
}

// -----------------------------
// Functions
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

	$tokens = array(
		'URL'	 => array(
			'!([a-z0-9]+://)?(.*?[^ \t\n\r<"]*)!ise'	=>	"(('\$1') ? '\$1\$2' : 'http://\$2')"
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
				$repad = $pad + count(array_unique($repad[0]));
				$replace = preg_replace('/(?<!\\\\)\$([0-9]+)/e', "'\$' . (\$1 + \$pad)", $replace);
				$pad = $repad;
			}

			// Obtain pattern modifiers to use and alter the regex accordingly
			$regex = preg_replace('/!(.*)!([a-z]*)/', '$1', $match);
			$regex_modifiers = preg_replace('/!(.*)!([a-z]*)/', '$2', $match);

			for ($i = 0; $i < strlen($regex_modifiers); ++$i)
			{
				if (strpos($modifiers, $regex_modifiers[$i]) === FALSE)
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

		if (strpos($fp_match, 'e') !== FALSE)
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
		'bbcode_tag'					=>	$bbcode_tag,
		'first_pass_match'			=>	$fp_match,
		'first_pass_replace'		=>	$fp_replace,
		'second_pass_match'	=>	$sp_match,
		'second_pass_replace'	=>	$sp_replace
	);
}
// End Functions
// -----------------------------
?>