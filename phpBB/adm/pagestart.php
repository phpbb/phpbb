<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : pagestart.php
// STARTED   : Thu Aug 2, 2001
// COPYRIGHT : © 2001, 2004 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ] 
// 
// -------------------------------------------------------------

if (!defined('IN_PHPBB') || !isset($phpbb_root_path))
{
	die('Hacking attempt');
}

define('IN_ADMIN', true);
define('NEED_SID', true);
require($phpbb_root_path . 'common.'.$phpEx);
require($phpbb_root_path . 'includes/functions_admin.'.$phpEx);

// Start session management
$user->start();
$user->setup('admin');

// Did user forget to login? Give 'em a chance to here ...
if ($user->data['user_id'] == ANONYMOUS)
{
	login_box("./adm/index.$phpEx$SID", '', $user->lang['LOGIN_ADMIN']);
}

$auth->acl($user->data);
// End session management

// Some oft used variables
$safe_mode	= (@ini_get('safe_mode') || @strtolower(ini_get('safe_mode')) == 'on') ? true : false;
$file_uploads = (@ini_get('file_uploads') || strtolower(@ini_get('file_uploads')) == 'on') ? true : false; 


// -----------------------------
// Functions
function adm_page_header($sub_title, $meta = '', $table_html = true)
{
	global $config, $db, $user, $phpEx;

	define('HEADER_INC', true);

	// gzip_compression
	if ($config['gzip_compress'])
	{
		if (extension_loaded('zlib') && !headers_sent())
		{
			ob_start('ob_gzhandler');
		}
	}

	header("Content-type: text/html; charset=" . $user->lang['ENCODING']);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $user->lang['ENCODING']; ?>">
<meta http-equiv="Content-Style-Type" content="text/css">
<link rel="stylesheet" href="subSilver.css" type="text/css">
<?php

	echo $meta;

?>
<style type="text/css">
<!--
th		{ background-image: url('images/cellpic3.gif') }
td.cat	{ background-image: url('images/cellpic1.gif') }
//-->
</style>
<title><?php echo $config['sitename'] . ' - ' . $page_title; ?></title>
</head>
<body>

<?php

	if ($table_html)
	{

?>
<a name="top"></a>

<table width="100%" cellspacing="0" cellpadding="0" border="0">
	<tr>
		<td colspan="2" height="25" align="right" nowrap="nowrap"><span class="subtitle">&#0187; <i><?php echo $sub_title; ?></i></span> &nbsp;&nbsp;</td>
	</tr>
</table>

<table width="95%" cellspacing="0" cellpadding="0" border="0" align="center">
	<tr>
		<td><br clear="all" />

<?php

	}

}

function adm_page_footer($copyright_html = true)
{
	global $cache, $config, $db, $phpEx;

	if (!empty($cache))
	{
		$cache->unload();
	}

	// Close our DB connection.
	$db->sql_close();

?>

		</td>
	</tr>
</table>
<?php

	if ($copyright_html)
	{

?>

<div class="copyright" align="center">Powered by phpBB <?php echo $config['version']; ?> &copy; 2002 <a href="http://www.phpbb.com/" target="_phpbb">phpBB Group</a></div>

<br clear="all" />

</body>
</html>
<?php

	}

	exit;
}

function adm_page_message($title, $message, $show_header = false, $show_prev_info = true)
{
	global $phpEx, $SID, $user, $_SERVER, $_ENV;

	if ($show_header)
	{

?>

<table width="100%" cellspacing="0" cellpadding="0" border="0">
	<tr>
		<td><a href="<?php echo "../index.$phpEx$SID"; ?>"><img src="images/header_left.jpg" width="200" height="60" alt="phpBB Logo" title="phpBB Logo" border="0"/></a></td>
		<td width="100%" background="images/header_bg.jpg" height="60" align="right" nowrap="nowrap"><span class="maintitle"><?php echo $user->lang['ADMIN_TITLE']; ?></span> &nbsp; &nbsp; &nbsp;</td>
	</tr>
</table>

<?php

	}

	$page = (!empty($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : $_ENV['REQUEST_URI'];
?>

<br /><br />

<table class="bg" width="80%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th><?php echo $title; ?></th>
	</tr>
	<tr>
		<td class="row1" align="center"><?php echo $message; ?>
<?php
	if ($page && $show_prev_info)
	{
		echo '<br /><br />';
		echo sprintf($user->lang['RETURN_PAGE'], '<a href="' . $page . '">', '</a>');
	}
?>		</td>
	</tr>
</table>

<br />

<?php

}

function adm_page_confirm($title, $message)
{
	global $phpEx, $SID, $user;

	// Grab data from GET and POST arrays ... note this is _not_ 
	// validated! Everything is typed as string to ensure no
	// funny business on displayed hidden field data. Validation
	// will be carried out by whatever processes this form.
	$var_ary = array_merge($_GET, $_POST);

	$s_hidden_fields = '';
	foreach ($var_ary as $key => $var)
	{
		if (empty($var))
		{
			continue;
		}

		if (is_array($var))
		{
			foreach ($var as $k => $v)
			{
				if (is_array($v))
				{
					foreach ($v as $_k => $_v)
					{
						set_var($var[$k][$_k], $_v, 'string');
						$s_hidden_fields .= "<input type=\"hidden\" name=\"${key}[$k][$_k]\" value=\"" . addslashes($_v) . '" />';
					}
				}
				else
				{
					set_var($var[$k], $v, 'string');
					$s_hidden_fields .= "<input type=\"hidden\" name=\"${key}[$k]\" value=\"" . addslashes($v) . '" />';
				}
			}
		}
		else
		{
			set_var($var, $var, 'string');
			$s_hidden_fields .= '<input type="hidden" name="' . $key . '" value="' . addslashes($var) . '" />';
		}
		unset($var_ary[$key]);
	}

?>

<br /><br />

<form name="confirm" method="post" action="<?php echo $_SERVER['SCRIPT_NAME'] . $SID; ?>">
<table class="bg" width="80%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th><?php echo $title; ?></th>
	</tr>
	<tr>
		<td class="row1" align="center"><?php echo $message; ?><br /><br /><input class="btnlite" type="submit" name="confirm" value="<?php echo $user->lang['YES']; ?>" />&nbsp;&nbsp;<input class="btnmain" type="submit" name="cancel" value="<?php echo $user->lang['NO']; ?>" /></td>
	</tr>
</table>

<?php echo $s_hidden_fields; ?>
</form>

<br />

<?php

	adm_page_footer();

}

// General ACP module class
class module
{
	var $id = 0;
	var $type;
	var $name;
	var $mode;

	// Private methods, should not be overwritten
	function create($module_type, $module_url, $selected_mod = false, $selected_submod = false)
	{
		global $template, $auth, $db, $user, $config;

		$sql = 'SELECT module_id, module_title, module_filename, module_subs, module_acl
			FROM ' . MODULES_TABLE . "
			WHERE module_type = 'acp'
				AND module_enabled = 1
			ORDER BY module_order ASC";
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			// Authorisation is required for the basic module
			if ($row['module_acl'])
			{
				$is_auth = false;

				eval('$is_auth = (' . preg_replace(array('#acl_([a-z_]+)#e', '#cfg_([a-z_]+)#e'), array('$auth->acl_get("\\1")', '$config["\\1"]'), $row['module_acl']) . ');');

				// The user is not authorised to use this module, skip it
				if (!$is_auth)
				{
					continue;
				}
			}

			$selected = ($row['module_filename'] == $selected_mod || $row['module_id'] == $selected_mod || (!$selected_mod && !$i)) ?  true : false;
/*
			// Get the localised lang string if available, or make up our own otherwise
			$template->assign_block_vars($module_type . '_section', array(
				'L_TITLE'		=> (isset($user->lang[strtoupper($module_type) . '_' . $row['module_title']])) ? $user->lang[strtoupper($module_type) . '_' . $row['module_title']] : ucfirst(str_replace('_', ' ', strtolower($row['module_title']))),
				'S_SELECTED'	=> $selected, 
				'U_TITLE'		=> $module_url . '&amp;i=' . $row['module_id'])
			);
*/
			if ($selected)
			{
				$module_id = $row['module_id'];
				$module_name = $row['module_filename'];

				if ($row['module_subs'])
				{
					$j = 0;
					$submodules_ary = explode("\n", $row['module_subs']);
					foreach ($submodules_ary as $submodule)
					{
						$submodule = explode(',', trim($submodule));
						$submodule_title = array_shift($submodule);

						$is_auth = true;
						foreach ($submodule as $auth_option)
						{
							if (!$auth->acl_get($auth_option))
							{
								$is_auth = false;
							}
						}

						if (!$is_auth)
						{
							continue;
						}

						$selected = ($submodule_title == $selected_submod || (!$selected_submod && !$j)) ? true : false;
/*
						// Get the localised lang string if available, or make up our own otherwise
						$template->assign_block_vars("{$module_type}_section.{$module_type}_subsection", array(
							'L_TITLE'		=> (isset($user->lang[strtoupper($module_type) . '_' . strtoupper($submodule_title)])) ? $user->lang[strtoupper($module_type) . '_' . strtoupper($submodule_title)] : ucfirst(str_replace('_', ' ', strtolower($submodule_title))),
							'S_SELECTED'	=> $selected, 
							'U_TITLE'		=> $module_url . '&amp;i=' . $module_id . '&amp;mode=' . $submodule_title
						));
*/
						if ($selected)
						{
							$this->mode = $submodule_title;
						}

						$j++;
					}
				}
			}

			$i++;
		}
		$db->sql_freeresult($result);

		if (!$module_id)
		{
			trigger_error('MODULE_NOT_EXIST');
		}

		$this->type = $module_type;
		$this->id = $module_id;
		$this->name = $module_name;
	}

	// Public methods to be overwritten by modules
	function module()
	{
		// Module name
		// Module filename
		// Module description
		// Module version
		// Module compatibility
		return false;
	}

	function init()
	{
		return false;
	}

	function install()
	{
		return false;
	}

	function uninstall()
	{
		return false;
	}
}
// End Functions
// -----------------------------

?>