<?php 
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : style.php 
// STARTED   : Thu Jul 24, 2003
// COPYRIGHT : © 2001, 2003 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ] 
// 
// -------------------------------------------------------------

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

// This is a simple script to grab and output the requested CSS data stored in the DB
// We include a session_id check to try and limit 3rd party linking ... unless they
// happen to have a current session it will output nothing. We will also cache the
// resulting CSS data for five minutes ... anything to reduce the load on the SQL
// server a little
if (!empty($_GET['id']) && !empty($_GET['sid']))
{
	// Include files
	require($phpbb_root_path . 'includes/acm/acm_' . $acm_type . '.'.$phpEx);
	require($phpbb_root_path . 'includes/db/' . $dbms . '.'.$phpEx);

	$db = new sql_db();
	$cache = new acm();

	// Connect to DB
	if (!@$db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname, $dbport, false))
	{
		exit;
	}

	$sid = htmlspecialchars($_GET['sid']);
	$id = intval($_GET['id']);

	$sql = "SELECT session_id 
		FROM {$table_prefix}sessions 
		WHERE session_id = '" . ((!get_magic_quotes_gpc()) ? $db->sql_escape($sid) : $sid) . "'";
	$result = $db->sql_query($sql);

	if ($db->sql_fetchrow($result))
	{
		$sql = "SELECT theme_data 
			FROM {$table_prefix}styles_theme
			WHERE theme_id = $id";
		$result2 = $db->sql_query($sql, 300);

		if ($row = $db->sql_fetchrow($result2))
		{
			header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 3600));
			header('Content-type: text/css');

			echo $row['theme_data'];
		}
		$db->sql_freeresult($result2);
	}
	$db->sql_freeresult($result);

	if (!empty($cache))
	{
		$cache->unload();
	}
	$db->sql_close();
}

?>