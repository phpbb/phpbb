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
*/
define('IN_PHPBB', true);
$phpbb_root_path = './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require($phpbb_root_path . 'config.'.$phpEx);

set_magic_quotes_runtime(0);

// Load Extensions
if (!empty($load_extensions))
{
	$load_extensions = explode(',', $load_extensions);

	foreach ($load_extensions as $extension)
	{
		@dl(trim($extension));
	}
}


$sid = (isset($_GET['sid'])) ? htmlspecialchars($_GET['sid']) : '';
$id = (isset($_GET['id'])) ? intval($_GET['id']) : 0;

if (!preg_match('/^[A-Za-z0-9]*$/', $sid)) 
{
	$sid = '';
}

// This is a simple script to grab and output the requested CSS data stored in the DB
// We include a session_id check to try and limit 3rd party linking ... unless they
// happen to have a current session it will output nothing. We will also cache the
// resulting CSS data for five minutes ... anything to reduce the load on the SQL
// server a little
if ($id && $sid)
{
	// Include files
	require($phpbb_root_path . 'includes/acm/acm_' . $acm_type . '.'.$phpEx);
	require($phpbb_root_path . 'includes/acm/acm_main.' . $phpEx);
	require($phpbb_root_path . 'includes/db/' . $dbms . '.'.$phpEx);

	$db = new $sql_db();
	$cache = new cache();
	
	// Connect to DB
	if (!@$db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname, $dbport, false))
	{
		exit;
	}

	$sql = "SELECT s.session_id, u.user_lang
		FROM {$table_prefix}sessions s, {$table_prefix}users u
		WHERE s.session_id = '" . $db->sql_escape($sid) . "'
			AND s.session_user_id = u.user_id";
	$result = $db->sql_query($sql);
	$user = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);
	
	if ($user)
	{
		$sql = "SELECT s.style_id, c.theme_data, c.theme_path, c.theme_name, c.theme_mtime, i.imageset_path, t.template_path
			FROM {$table_prefix}styles s, {$table_prefix}styles_template t, {$table_prefix}styles_theme c, {$table_prefix}styles_imageset i
			WHERE s.style_id = $id
				AND t.template_id = s.template_id
				AND c.theme_id = s.theme_id
				AND i.imageset_id = s.imageset_id";
		$result = $db->sql_query($sql, 300);
		$theme = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if (!$theme)
		{
			exit;
		}
		
		$force_load = true;	// Ideally this needs to be based on $config['load_tplcompile']

		if ($theme['theme_mtime'] < filemtime("{$phpbb_root_path}styles/" . $theme['theme_path'] . '/theme/stylesheet.css')  || $force_load)
		{
			$theme['theme_data'] = file_get_contents("{$phpbb_root_path}styles/" . $theme['theme_path'] . '/theme/stylesheet.css');
			
			// Match CSS imports
			$matches = array();
			preg_match_all('/@import url\(\"(.*)\"\);/i', $theme['theme_data'], $matches);
			
			if (sizeof($matches))
			{
				foreach ($matches[0] as $idx => $match)
				{
					$theme['theme_data'] = str_replace($match, file_get_contents("{$phpbb_root_path}styles/" . $theme['theme_path'] . '/theme/' . $matches[1][$idx]), $theme['theme_data']);
				}
			}
			
			$sql = "UPDATE {$table_prefix}styles_theme 
				SET theme_data = '" . $db->sql_escape($theme['theme_data']) . "', theme_mtime = " . time() . "
				WHERE theme_id = $id";
			$db->sql_query($sql);
		}

		header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 3600));
		header('Content-type: text/css');
		
		// Parse Theme Data
		$replace = array(
			'{T_THEME_PATH}'			=> "{$phpbb_root_path}styles/" . $theme['theme_path'] . '/theme',
			'{T_TEMPLATE_PATH}'			=> "{$phpbb_root_path}styles/" . $theme['template_path'] . '/template',
			'{T_IMAGESET_PATH}'			=> "{$phpbb_root_path}styles/" . $theme['imageset_path'] . '/imageset',
			'{T_IMAGESET_LANG_PATH}'	=> "{$phpbb_root_path}styles/" . $theme['imageset_path'] . '/imageset/' . $user['user_lang'],
			'{T_STYLESHEET_NAME}'		=> $theme['theme_name'],
			'{S_USER_LANG}'				=> $user['user_lang']
		);

		$theme['theme_data'] = str_replace(array_keys($replace), array_values($replace), $theme['theme_data']);

		echo $theme['theme_data'];
	}

	if (!empty($cache))
	{
		$cache->unload();
	}
	$db->sql_close();
}

?>