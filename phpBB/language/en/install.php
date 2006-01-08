<?php
/** 
*
* install [English]
*
* @package language
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* DO NOT CHANGE
*/
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang = array_merge($lang, array(
	'ADMIN_CONFIG'				=> 'Admin Configuration',
	'ADMIN_PASSWORD'			=> 'Administrator password',
	'ADMIN_PASSWORD_CONFIRM'	=> 'Confirm administrator password',
	'ADMIN_USERNAME'			=> 'Administrator username',
	'APP_MAGICK'				=> 'Imagemagick support [ Attachments ]',
	'AVAILABLE'					=> 'Available',

	'CACHE_STORE'				=> 'Cache type',
	'CACHE_STORE_EXPLAIN'		=> 'The physical location where data is cached, filesystem is prefered.',
	'CONFIG_RETRY'				=> 'Retry',
	'CONTACT_EMAIL'				=> 'Contact email address',
	'CONTACT_EMAIL_CONFIRM'		=> 'Confirm contact email',

	'DBMS'						=> 'Database type',
	'DB_CONFIG'					=> 'Database Configuration',
	'DB_HOST'					=> 'Database server hostname or DSN',
	'DB_HOST_EXPLAIN'			=> 'DSN stands for Data Source Name and is relevant only for ODBC installs.',
	'DB_NAME'					=> 'Database name',
	'DB_PASSWORD'				=> 'Database password',
	'DB_PORT'					=> 'Database server port',
	'DB_PORT_EXPLAIN'			=> 'Leave this blank unless you know the server operates on a non-standard port.',
	'DB_USERNAME'				=> 'Database username',
	'DB_TEST'					=> 'Test Connection',
	'DEFAULT_LANG'				=> 'Default board language',
	'DIRECTORIES_AND_FILES'		=> 'Directory and file setup',
	'DLL_FIREBIRD'				=> 'Firebird 1.5+',
	'DLL_FTP'					=> 'Remote FTP support [ Installation ]',
	'DLL_MBSTRING'				=> 'Multi-byte character support',
	'DLL_MHASH'					=> 'Mhash hashing support [ Jabber ]',
	'DLL_MSSQL'					=> 'MSSQL Server 2000',
	'DLL_MSSQL_ODBC'			=> 'MSSQL Server 2000 via ODBC',
	'DLL_MYSQL'					=> 'MySQL 3.23.x/4.x',
	'DLL_MYSQL4'				=> 'MySQL 4.1+',
	'DLL_MYSQLI'				=> 'MySQL 4.1+ with MySQLi Extension',
	'DLL_ORACLE'				=> 'Oracle',
	'DLL_POSTGRES'				=> 'PostgreSQL 7.x',
	'DLL_SQLITE'				=> 'SQLite',
	'DLL_XML'					=> 'XML support [ Jabber ]',
	'DLL_ZLIB'					=> 'zlib Compression support [ Visual confirmation, gz, .tar.gz, .zip ]',
	'DL_CONFIG'					=> 'Download config',
	'DL_CONFIG_EXPLAIN'			=> 'You may download the complete config.php to your own PC. You will then need to upload the file manually, replacing any existing config.php in your phpBB 3.0 root directory. Please remember to upload the file in ASCII format (see your FTP application documentation if you are unsure how to achieve this). When you have uploaded the config.php please click "Done" to move to the next stage.',
	'DL_DOWNLOAD'				=> 'Download',
	'DL_DONE'					=> 'Done',

	'FILE_FOUND'				=> 'Found',
	'FILE_NOT_FOUND'			=> 'Cannot find',
	'FILE_UNWRITEABLE'			=> 'Unwriteable',
	'FILE_WRITEABLE'			=> 'Writeable',
	'FTP_CONFIG'				=> 'Transfer config by FTP',
	'FTP_CONFIG_EXPLAIN'		=> 'phpBB has detected the presence of the ftp module on this server. You may attempt to install your config.php via this if you wish. You will need to supply the information listed below. Remember your username and password are those to your server! (ask your hosting provider for details if you are unsure what these are)',
	'FTP_PASSWORD'				=> 'FTP Password',
	'FTP_PATH'					=> 'FTP Path',
	'FTP_PATH_EXPLAIN'			=> 'This is the path from your root directory to that of phpBB2, e.g. htdocs/phpBB2/',
	'FTP_UPLOAD'				=> 'Upload',
	'FTP_USERNAME'				=> 'FTP Username',

	'INITIAL_CONFIG'			=> 'Basic Configuration',
	'INITIAL_CONFIG_EXPLAIN'	=> 'Now that install has determined your server can run phpBB you need to supply some specific information. If you do not know how to connect to your database please contact your hosting provider (in the first instance) or  use the phpBB support forums. When entering data please ensure you check it thoroughly before continuing.',
	'INSTALL_ADVICE'			=> 'Installation Compatibility',
	'INSTALL_ADVICE_EXPLAIN'	=> 'Before proceeding with full installation phpBB will carry out some tests on your server and basic install. Please ensure you read through the results thoroughly and do not proceed until all tests are passed.',
	'INSTALL_CONGRATS'			=> 'Congratulations',
	'INSTALL_CONGRATS_EXPLAIN'	=> 'You have now successfully installed phpBB 3.0. Clicking the button below will take you to your Administration Control Panel (ACP). Take some time to examine the options available to you. Remember that help is available online via the Userguide and the phpBB support forums, see the %sREADME%s for further information.',
	'INSTALL_DB_CONNECT'		=> 'Successfull Connection',
	'INSTALL_LOGIN'				=> 'Login',
	'INSTALL_NEXT'				=> 'Next stage',
	'INSTALL_NEXT_FAIL'			=> 'Some tests failed and you should correct these problems before proceeding to the next stage. Failure to do so may result in an incomplete installation.',
	'INSTALL_NEXT_PASS'			=> 'All the basic tests have been passed and you may proceed to the next stage of installation. If you have changed any permissions, modules, etc. and wish to re-test you can do so if you wish.',
	'INSTALL_OPTIONAL'			=> 'Optional',
	'INSTALL_OPTIONAL_FILES'	=> 'These files, directories or permissions are optional. The installation routines will attempt to use various techniques to complete if they do not exist or cannot be written to. However, the presence of these files, directories or permissions will speed installation.',
	'INSTALL_OPTIONAL_PHP'		=> 'These modules or applications are optional, you do not need these to use phpBB 3.0. However if you do have them they will will enable greater functionality.',
	'INSTALL_REQUIRED'			=> 'Required',
	'INSTALL_REQUIRED_FILES'	=> 'In order to function correctly phpBB needs to be able to access or write to certain files or directories. If you see "Not Found" you need to create the relevant file or directory. If you see "Unwriteable" you need to change the permissions on the file or directory to allow phpBB to write to it.',
	'INSTALL_REQUIRED_PHP'		=> 'You must be running at least PHP 4.3.3 with support for at least one compatible database. If no support modules are shown as available you should contact your hosting provider or review the relevant PHP installation documentation for advice. If "safe mode" is displayed below your PHP installation is running in that mode. This will impose limitations on remote administration and similar features.',
	'INSTALL_SEND_CONFIG'		=> 'Unfortunately phpBB could not write the configuration information directly to your config.php. This may be because the file does not exist or is not writeable. A number of options will be listed below enabling you to complete installation of config.php.',
	'INSTALL_START'				=> 'Start Install',
	'INSTALL_TEST'				=> 'Test Again',
	'INST_ERR_DB_CONNECT'		=> 'Could not connect to the database, see error message below',
	'INST_ERR_DB_NO_ERROR'		=> 'No error message given',
	'INST_ERR_EMAIL_MISMATCH'	=> 'The emails you entered did not match.',
	'INST_ERR_FATAL'			=> 'Fatal installation error',
	'INST_ERR_FATAL_DB'			=> 'A fatal and unrecoverable database error has occured. This may be because the specified user does not have appropriate rights to CREATE TABLES or INSERT data, etc. Further information may be given below. Please contact your hosting provider in the first instance or the support forums of phpBB for further assistance.',
	'INST_ERR_FTP_PATH'			=> 'Could not change to the given directory, please check the path.',
	'INST_ERR_FTP_LOGIN'		=> 'Could not login to ftp server, check your username and password',
	'INST_ERR_MISSING_DATA'		=> 'You must fill out all fields in this block',
	'INST_ERR_NO_DB'			=> 'Cannot load the PHP module for the selected database type',
	'INST_ERR_PASSWORD_MISMATCH'=> 'The passwords you entered did not match.',
	'INST_ERR_PREFIX'			=> 'Tables with the specified prefix already exist, please choose an alternative.',

	'NO_LOCATION'				=> 'Cannot determine location',

	'PHP_AND_APPS'				=> 'PHP and Applications',
	'PHP_REQD_DB'				=> 'Supported Databases',
	'PHP_REGISTER_GLOBALS'		=> 'PHP setting "register_globals" is not enabled',
	'PHP_SAFE_MODE'				=> 'Safe Mode',
	'PHP_VERSION_REQD'			=> 'PHP version >= 4.3.3',

	'RETRY_WRITE'				=> 'Retry writing config',
	'RETRY_WRITE_EXPLAIN'		=> 'If you wish you can change the permissions on config.php to allow phpBB to write to it. Should you wish to do that you can click Retry below to try again. Remember to return the permissions on config.php after phpBB2 has finished installation.',

	'SCRIPT_PATH'				=> 'Script path',
	'SCRIPT_PATH_EXPLAIN'		=> 'The path where phpBB2 is located relative to the domain name',
	'SERVER_CONFIG'				=> 'Server Configuration',
	'SERVER_NAME'				=> 'Domain name',
	'SERVER_NAME_EXPLAIN'		=> 'The domain name this board runs from',
	'SERVER_PORT'				=> 'Server port',
	'SERVER_PORT_EXPLAIN'		=> 'The port your server is running on, usually 80, only change if different',

	'TABLE_PREFIX'				=> 'Prefix for tables in database',
	'TESTS_PASSED'				=> 'Tests passed',
	'TESTS_FAILED'				=> 'Tests failed',

	'UNAVAILABLE'				=> 'Unavailable',

	'WELCOME_INSTALL'			=> 'Welcome to phpBB 3 Installation',
));

?>