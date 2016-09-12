<?php
// phpBB 3.2.x auto-generated configuration file
// Do not change anything in this file!

$url = getenv('JAWSDB_URL');
$dbparts = parse_url($url);

$dbhost = $dbparts['host'];
$dbuser = $dbparts['user'];
$dbpasswd = $dbparts['pass'];
$dbname = ltrim($dbparts['path'],'/');

$dbms = 'phpbb\\db\\driver\\mysqli';
$dbport = '';
$table_prefix = 'phpbb_';
$phpbb_adm_relative_path = 'adm/';
$acm_type = 'phpbb\\cache\\driver\\file';

@define('DEBUG', true);
@define('PHPBB_INSTALLED', true);
@define('PHPBB_DISPLAY_LOAD_TIME', true);
@define('PHPBB_ENVIRONMENT', 'development');
// @define('DEBUG_CONTAINER', true);
