<?php
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

$provider = new provider_open_id($request);

// Enable super globals so Zend Framework does not throw errors.
$request->enable_super_globals();

$provider->verify($_GET, $id);
if ($consumer->verify($_GET, $id))
{
	$status = "VALID " . htmlspecialchars($id);
}
else
{
	$status = "INVALID " . htmlspecialchars($id);
}
print $status;

$request->disable_super_globals();
?>
