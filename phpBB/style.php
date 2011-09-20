<?php
/**
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);

require($phpbb_root_path . 'includes/startup.' . $phpEx);
require($phpbb_root_path . 'config.' . $phpEx);

if (!defined('PHPBB_INSTALLED') || empty($dbms) || empty($acm_type))
{
	exit;
}

// Load Extensions
if (!empty($load_extensions) && function_exists('dl'))
{
	$load_extensions = explode(',', $load_extensions);

	foreach ($load_extensions as $extension)
	{
		@dl(trim($extension));
	}
}

// no $request here because it is not loaded yet
$id = (isset($_GET['id'])) ? intval($_GET['id']) : 0;

// This is a simple script to grab and output the requested CSS data stored in the DB
// We include a session_id check to try and limit 3rd party linking ... unless they
// happen to have a current session it will output nothing. We will also cache the
// resulting CSS data for five minutes ... anything to reduce the load on the SQL
// server a little
if ($id)
{
	// Include files
	require($phpbb_root_path . 'includes/class_loader.' . $phpEx);
	require($phpbb_root_path . 'includes/db/' . $dbms . '.' . $phpEx);
	require($phpbb_root_path . 'includes/constants.' . $phpEx);
	require($phpbb_root_path . 'includes/functions.' . $phpEx);
	require($phpbb_root_path . 'includes/utf/utf_tools.' . $phpEx);

	$class_loader = new phpbb_class_loader($phpbb_root_path, '.' . $phpEx);
	$class_loader->register();

	// set up caching
	$cache_factory = new phpbb_cache_factory($acm_type);
	$cache = $cache_factory->get_service();
	$class_loader->set_cache($cache->get_driver());

	$request = new phpbb_request();
	$db = new $sql_db();

	// make sure request_var uses this request instance
	request_var('', 0, false, false, $request); // "dependency injection" for a function

	// Connect to DB
	if (!@$db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname, $dbport, false, false))
	{
		exit;
	}
	unset($dbpasswd);

	$config = new phpbb_config_db($db, $cache->get_driver(), CONFIG_TABLE);
	set_config(null, null, null, $config);
	set_config_count(null, null, null, $config);

	$user = false;

	// try to get a session ID from REQUEST array
	$sid = request_var('sid', '');

	if (!$sid)
	{
		// if that failed, then look in the cookies
		$sid = request_var($config['cookie_name'] . '_sid', '', false, true);
	}

	if (strspn($sid, 'abcdefABCDEF0123456789') !== strlen($sid))
	{
		$sid = '';
	}

	if ($sid)
	{
		$sql = 'SELECT u.user_id, u.user_lang
			FROM ' . SESSIONS_TABLE . ' s, ' . USERS_TABLE . " u
			WHERE s.session_id = '" . $db->sql_escape($sid) . "'
				AND s.session_user_id = u.user_id";
		$result = $db->sql_query($sql);
		$user = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
	}

	$recompile = $config['load_tplcompile'];
	if (!$user)
	{
		$id			= ($id) ? $id : $config['default_style'];
//		Commented out because calls do not always include the SID anymore
//		$recompile	= false;
		$user		= array('user_id' => ANONYMOUS);
	}

	$sql = 'SELECT s.style_id, c.theme_id, c.theme_data, c.theme_path, c.theme_name, c.theme_mtime, t.template_path
		FROM ' . STYLES_TABLE . ' s, ' . STYLES_TEMPLATE_TABLE . ' t, ' . STYLES_THEME_TABLE . ' c
		WHERE s.style_id = ' . $id . '
			AND t.template_id = s.template_id
			AND c.theme_id = s.theme_id';
	$result = $db->sql_query($sql, 300);
	$theme = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	if (!$theme)
	{
		exit;
	}

	if ($user['user_id'] == ANONYMOUS)
	{
		$user['user_lang'] = $config['default_lang'];
	}

	// gzip_compression
	if ($config['gzip_compress'])
	{
		// IE6 is not able to compress the style (do not ask us why!)
		$browser = strtolower($request->header('User-Agent'));

		if ($browser && strpos($browser, 'msie 6.0') === false && @extension_loaded('zlib') && !headers_sent())
		{
			ob_start('ob_gzhandler');
		}
	}

	// Expire time of seven days if not recached
	$expire_time = 7*86400;
	$recache = false;

	// Re-cache stylesheet data if necessary
	if ($recompile || empty($theme['theme_data']))
	{
		$recache = (empty($theme['theme_data'])) ? true : false;
		$update_time = time();

		// We test for stylesheet.css because it is faster and most likely the only file changed on common themes
		if (!$recache && $theme['theme_mtime'] < @filemtime("{$phpbb_root_path}styles/" . $theme['theme_path'] . '/theme/stylesheet.css'))
		{
			$recache = true;
			$update_time = @filemtime("{$phpbb_root_path}styles/" . $theme['theme_path'] . '/theme/stylesheet.css');
		}
		else if (!$recache)
		{
			$last_change = $theme['theme_mtime'];
			$dir = @opendir("{$phpbb_root_path}styles/{$theme['theme_path']}/theme");

			if ($dir)
			{
				while (($entry = readdir($dir)) !== false)
				{
					if (substr(strrchr($entry, '.'), 1) == 'css' && $last_change < @filemtime("{$phpbb_root_path}styles/{$theme['theme_path']}/theme/{$entry}"))
					{
						$recache = true;
						break;
					}
				}
				closedir($dir);
			}
		}
	}

	if ($recache)
	{
		include_once($phpbb_root_path . 'includes/acp/acp_styles.' . $phpEx);

		$theme['theme_data'] = acp_styles::db_theme_data($theme);
		$theme['theme_mtime'] = $update_time;

		// Save CSS contents
		$sql_ary = array(
			'theme_mtime'	=> $theme['theme_mtime'],
			'theme_data'	=> $theme['theme_data']
		);

		$sql = 'UPDATE ' . STYLES_THEME_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
			WHERE theme_id = {$theme['theme_id']}";
		$db->sql_query($sql);

		$cache->destroy('sql', STYLES_THEME_TABLE);
	}

	// Only set the expire time if the theme changed data is older than 30 minutes - to cope with changes from the ACP
	if ($recache || $theme['theme_mtime'] > (time() - 1800))
	{
		header('Expires: 0');
	}
	else
	{
		header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + $expire_time));
	}

	header('Content-type: text/css; charset=UTF-8');

	// Echo Theme Data
	echo $theme['theme_data'];

	if (!empty($cache))
	{
		$cache->unload();
	}
	$db->sql_close();
}
