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
	'ADMIN_PASSWORD_EXPLAIN'	=> '(Please enter a password between 6 and 30 characters in length)',
	'ADMIN_TEST'				=> 'Check administrator settings',
	'ADMIN_USERNAME'			=> 'Administrator username',
	'ADMIN_USERNAME_EXPLAIN'	=> '(Please enter a username between 3 and 20 characters in length)',
	'APP_MAGICK'				=> 'Imagemagick support [ Attachments ]',
	'AUTHOR_NOTES'				=> 'Author Notes<br />&#187; %s',
	'AVAILABLE'					=> 'Available',
	'AVAILABLE_CONVERTORS'		=> 'Available Convertors',

	'BEGIN_CONVERT'				=> 'Begin conversion',
	'BLANK_PREFIX_FOUND'		=> 'A scan of your tables has shown a valid installation using no table prefix.',

	'CACHE_STORE'				=> 'Cache type',
	'CACHE_STORE_EXPLAIN'		=> 'The physical location where data is cached, filesystem is prefered.',
	'CAT_CONVERT'				=> 'Convert',
	'CAT_INSTALL'				=> 'Install',
	'CAT_OVERVIEW'				=> 'Overview',
	'CHANGE'					=> 'Change',
	'CHECK_TABLE_PREFIX'		=> 'Please check your table prefix and try again.',
	'CLEAN_VERIFY'				=> 'Cleaning up and verifying the final structure',
	'CONFIG_CONVERT'			=> 'Converting the configuration',
	'CONFIG_FILE_UNABLE_WRITE'	=> 'It was not possible to write the configuration file. Alternative methods for this file to be created are presented below',
	'CONFIG_FILE_WRITTEN'		=> 'The configuration file has been written, you may now proceed to the next step of the installation',
	'CONFIG_RETRY'				=> 'Retry',
	'CONTACT_EMAIL_CONFIRM'		=> 'Confirm contact email',
	'CONTINUE_CONVERT'			=> 'Continue conversion',
	'CONTINUE_LAST'				=> 'Continue last statements',
	'CONVERT'					=> 'Convert',
	'CONVERT_COMPLETE'			=> 'Conversion completed',
	'CONVERT_COMPLETE_EXPLAIN'	=> 'You have now successfully converted your board to phpBB 3.0. You can now login and <a href="../">access your forum </a>. Remember that help on using phpBB is available online via the <a href="http://www.phpbb.com/support/documentation/3.0/">Userguide</a> and the <a href="http://www.phpbb.com/phpBB/viewforum.php?f=46">Beta support forum</a>',
	'CONVERT_INTRO'				=> 'Welcome to the phpBB Unified Convertor Framework',
	'CONVERT_INTRO_BODY'		=> 'From here, you are able to import data from other (installed) forum systems. The list below shows all the conversion modules currently available. If there is no convertor shown in this list for the forum software you wish to convert from, please check our website where further conversion modules may be available for download.',
	'CONVERT_NOT_EXIST'			=> 'The specified convertor does not exist',
	'CONVERT_SETTINGS_VERIFIED'	=> 'The information you entered has been verified. To start the conversion progress, push the button below to begin',
	'COULD_NOT_COPY'			=> 'Could not copy file <b>%1$s</b> to <b>%2$s</b><br /><br />Please check that the target directory exists and is writable by the webserver',
	'COULD_NOT_FIND_PATH'		=> 'Could not find path to your former forum. Please check your settings and try again.<br />&#187; Specified source path was %s',

	'DBMS'						=> 'Database type',
	'DB_CONFIG'					=> 'Database Configuration',
	'DB_CONNECTION'				=> 'Database Connection',
	'DB_ERR_INSERT'				=> 'Error while processing INSERT query',
	'DB_ERR_LAST'				=> 'Error while processing query_last',
	'DB_ERR_QUERY_FIRST'		=> 'Error while executing query_first',
	'DB_ERR_QUERY_FIRST_TABLE'	=> 'Error while executing query_first, %s ("%s")',
	'DB_ERR_SELECT'				=> 'Error while running SELECT query',
	'DB_HOST'					=> 'Database server hostname or DSN',
	'DB_HOST_EXPLAIN'			=> 'DSN stands for Data Source Name and is relevant only for ODBC installs.',
	'DB_NAME'					=> 'Database name',
	'DB_PASSWORD'				=> 'Database password',
	'DB_PORT'					=> 'Database server port',
	'DB_PORT_EXPLAIN'			=> 'Leave this blank unless you know the server operates on a non-standard port.',
	'DB_USERNAME'				=> 'Database username',
	'DB_TEST'					=> 'Test Connection',
	'DEFAULT_LANG'				=> 'Default board language',
	'DEFAULT_PREFIX_IS'			=> 'The default table prefix for %1$s is <strong>%2$s</strong>',
	'DEV_NO_TEST_FILE'			=> 'No value has been specified for the test_file variable in the convertor. If you are a user of this convertor, you should not be seeing this error, please report this message to the convertor author. If you are a convertor author, you must specify the name of a file which exists in the source forum to allow the path to it to be verified.',
	'DIRECTORIES_AND_FILES'		=> 'Directory and file setup',
	'DISABLE_KEYS'				=> 'Disabling keys',
	'DLL_FIREBIRD'				=> 'Firebird 1.5+',
	'DLL_FTP'					=> 'Remote FTP support [ Installation ]',
	'DLL_MBSTRING'				=> 'Multi-byte character support',
	'DLL_MSSQL'					=> 'MSSQL Server 2000+',
	'DLL_MSSQL_ODBC'			=> 'MSSQL Server 2000+ via ODBC',
	'DLL_MYSQL'					=> 'MySQL 3.23.x/4.x',
	'DLL_MYSQL4'				=> 'MySQL 4.x/5.x',
	'DLL_MYSQLI'				=> 'MySQL 4.1.x/5.x with MySQLi Extension',
	'DLL_ORACLE'				=> 'Oracle',
	'DLL_POSTGRES'				=> 'PostgreSQL 7.x/8.x',
	'DLL_SQLITE'				=> 'SQLite',
	'DLL_XML'					=> 'XML support [ Jabber ]',
	'DLL_ZLIB'					=> 'zlib Compression support [ gz, .tar.gz, .zip ]',
	'DL_CONFIG'					=> 'Download config',
	'DL_CONFIG_EXPLAIN'			=> 'You may download the complete config.php to your own PC. You will then need to upload the file manually, replacing any existing config.php in your phpBB 3.0 root directory. Please remember to upload the file in ASCII format (see your FTP application documentation if you are unsure how to achieve this). When you have uploaded the config.php please click "Done" to move to the next stage.',
	'DL_DOWNLOAD'				=> 'Download',
	'DONE'						=> 'Done',

	'ENABLE_KEYS'				=> 'Re-enabling keys. This can take a while',

	'FILES_OPTIONAL'			=> 'Optional Files and Directories',
	'FILES_OPTIONAL_EXPLAIN'	=> '<strong>Optional</strong> - These files, directories or permissions are not required. The installation routines will attempt to use various techniques to complete if they do not exist or cannot be written to. However, the presence of these files, directories or permissions will speed installation.',
	'FILES_REQUIRED'			=> 'Files and Directories',
	'FILES_REQUIRED_EXPLAIN'	=> '<strong>Required</strong> - In order to function correctly phpBB needs to be able to access or write to certain files or directories. If you see "Not Found" you need to create the relevant file or directory. If you see "Unwriteable" you need to change the permissions on the file or directory to allow phpBB to write to it.',
	'FILLING_TABLE'				=> 'Filling table <b>%s</b>',
	'FILLING_TABLES'			=> 'Filling Tables',
	'FINAL_STEP'				=> 'Process Final Step',
	'FORUM_ADDRESS'				=> 'Forum address',
	'FORUM_ADDRESS_EXPLAIN'		=> 'This is the http address of your former forum',
	'FORUM_PATH'				=> 'Forum path',
	'FORUM_PATH_EXPLAIN'		=> 'This is the <strong>relative</strong> path on disk to your former forum from the <strong>root of your phpBB install</strong>',
	'FOUND'						=> 'Found',
	'FTP_CONFIG'				=> 'Transfer config by FTP',
	'FTP_CONFIG_EXPLAIN'		=> 'phpBB has detected the presence of the FTP module on this server. You may attempt to install your config.php via this if you wish. You will need to supply the information listed below. Remember your username and password are those to your server! (ask your hosting provider for details if you are unsure what these are)',
	'FTP_PATH'					=> 'FTP Path',
	'FTP_PATH_EXPLAIN'			=> 'This is the path from your root directory to that of phpBB, e.g. htdocs/phpBB3/',
	'FTP_UPLOAD'				=> 'Upload',

	'GPL'						=> 'General Public License',
	
	'INITIAL_CONFIG'			=> 'Basic Configuration',
	'INITIAL_CONFIG_EXPLAIN'	=> 'Now that install has determined your server can run phpBB you need to supply some specific information. If you do not know how to connect to your database please contact your hosting provider (in the first instance) or  use the phpBB support forums. When entering data please ensure you check it thoroughly before continuing.',
	'INSTALL_CONGRATS'			=> 'Congratulations',
	'INSTALL_CONGRATS_EXPLAIN'	=> 'You have now successfully installed phpBB 3.0. Clicking the button below will take you to your Administration Control Panel (ACP). Take some time to examine the options available to you. Remember that help is available online via the <a href="http://www.phpbb.com/support/documentation/3.0/">Userguide</a> and the <a href="http://www.phpbb.com/phpBB/viewforum.php?f=46">Beta support forum</a>, see the %sREADME%s for further information.',
	'INSTALL_INTRO'				=> 'Welcome to Installation',
	'INSTALL_INTRO_BODY'		=> 'With this option, it is possible to install phpBB onto your server.</p><p>In order to proceed, you will need the following information to hand:</p>
	<ul>
	<li>Database server name</li>
	<li>Database name</li>
	<li>Database username and password</li>
	</ul>
	<p>Some more introductory text can go here...',
	'INSTALL_INTRO_NEXT'		=> 'To commence the installation, please press the button below.',
	'INSTALL_LOGIN'				=> 'Login',
	'INSTALL_NEXT'				=> 'Next stage',
	'INSTALL_NEXT_FAIL'			=> 'Some tests failed and you should correct these problems before proceeding to the next stage. Failure to do so may result in an incomplete installation.',
	'INSTALL_NEXT_PASS'			=> 'All the basic tests have been passed and you may proceed to the next stage of installation. If you have changed any permissions, modules, etc. and wish to re-test you can do so if you wish.',
	'INSTALL_PANEL'				=> 'Installation Panel',
	'INSTALL_SEND_CONFIG'		=> 'Unfortunately phpBB could not write the configuration information directly to your config.php. This may be because the file does not exist or is not writeable. A number of options will be listed below enabling you to complete installation of config.php.',
	'INSTALL_START'				=> 'Start Install',
	'INSTALL_TEST'				=> 'Test Again',
	'INST_ERR'					=> 'Installation error',
	'INST_ERR_DB_CONNECT'		=> 'Could not connect to the database, see error message below',
	'INST_ERR_DB_FORUM_PATH'	=> 'The database file specified is within your forum directory tree. You should put this file in a non web-accessible location',
	'INST_ERR_DB_NO_ERROR'		=> 'No error message given',
	'INST_ERR_DB_NO_MYSQL4'		=> 'The version of MySQL installed on this machine is incompatible with the "MySQL 4.x/5.x" option you have selected. Please try the "MySQL 3.23.x/4.x" option instead.',
	'INST_ERR_DB_NO_MYSQLI'		=> 'The version of MySQL installed on this machine is incompatible with the "MySQL 4.1.x/5.x with MySQLi Extension" option you have selected. Please try the "MySQL 4.x/5.x" or "MySQL 3.23.x/4.x" option instead.',
	'INST_ERR_DB_NO_NAME'		=> 'No database name specified',
	'INST_ERR_EMAIL_INVALID'	=> 'The email address you entered is invalid',
	'INST_ERR_EMAIL_MISMATCH'	=> 'The emails you entered did not match.',
	'INST_ERR_FATAL'			=> 'Fatal installation error',
	'INST_ERR_FATAL_DB'			=> 'A fatal and unrecoverable database error has occured. This may be because the specified user does not have appropriate rights to CREATE TABLES or INSERT data, etc. Further information may be given below. Please contact your hosting provider in the first instance or the support forums of phpBB for further assistance.',
	'INST_ERR_FTP_PATH'			=> 'Could not change to the given directory, please check the path.',
	'INST_ERR_FTP_LOGIN'		=> 'Could not login to FTP server, check your username and password',
	'INST_ERR_MISSING_DATA'		=> 'You must fill out all fields in this block',
	'INST_ERR_NO_DB'			=> 'Cannot load the PHP module for the selected database type',
	'INST_ERR_PASSWORD_MISMATCH' => 'The passwords you entered did not match.',
	'INST_ERR_PASSWORD_TOO_LONG' => 'The password you entered is too long. The maximum length is 30 characters.',
	'INST_ERR_PASSWORD_TOO_SHORT' => 'The password you entered is too short. The minimum length is 6 characters.',
	'INST_ERR_PREFIX'			=> 'Tables with the specified prefix already exist, please choose an alternative.',
	'INST_ERR_PREFIX_TOO_LONG'	=> 'The table prefix you have specified is too long. The maximum length is %d characters.',
	'INST_ERR_USER_TOO_LONG'	=> 'The username you entered is too long. The maximum length is 20 characters.',
	'INST_ERR_USER_TOO_SHORT'	=> 'The username you entered is too short. The minimum length is 3 characters.',
	'INVALID_PRIMARY_KEY'		=> 'Invalid primary key : %s',

	'MAKE_FOLDER_WRITABLE'		=> 'Please make sure that this folder exists and is writable by the webserver then try again:<br />&#187;<b>%s</b>',
	'MAKE_FOLDERS_WRITABLE'		=> 'Please make sure that these folders exist and are writable by the webserver then try again:<br />&#187;<b>%s</b>',

	'NAMING_CONFLICT'			=> 'Naming conflict: %s and %s are both aliases<br /><br />%s',
	'NEXT_STEP'					=> 'Proceed to next step',
	'NOT_FOUND'					=> 'Cannot find',
	'NOT_UNDERSTAND'			=> 'Could not understand %s #%d, table %s ("%s")',
	'NO_CONVERTORS'				=> 'No convertors are available for use',
	'NO_CONVERT_SPECIFIED'		=> 'No convertor specified',
	'NO_LOCATION'				=> 'Cannot determine location. If you know Imagemagick is installed, you may specify the location later within your Administration Panel',
	'NO_TABLES_FOUND'			=> 'No tables found.',
// TODO: Write some explanatory introduction text
	'OVERVIEW_BODY'					=> 'Welcome to our public beta of the next-generation of phpBB after 2.0.x, phpBB 3.0! This beta release is intended for advanced users to try out on dedicated development enviroments to help us finish creating the best Opensource Bulletin Board solution available.</p><p><strong style="text-transform: uppercase;">Note:</strong> This release is <strong style="text-transform: uppercase;">not final</strong> and made available for testing purposes <strong style="text-transform: uppercase;">only</strong>.</p><p>This installation system will guide you through the process of installing phpBB, converting from a different software package or updating to the latest version of phpBB. For more information on each option, select it from the menu above.',
	'PHP_OPTIONAL_MODULE'			=> 'Optional Modules',
	'PHP_OPTIONAL_MODULE_EXPLAIN'	=> '<strong>Optional</strong> - These modules or applications are optional, you do not need these to use phpBB 3.0. However if you do have them they will will enable greater functionality.',
	'PHP_SUPPORTED_DB'				=> 'Supported Databases',
	'PHP_SUPPORTED_DB_EXPLAIN'		=> '<strong>Required</strong> - You must have support for at least one compatible database within PHP. If no database modules are shown as available you should contact your hosting provider or review the relevant PHP installation documentation for advice.',
	'PHP_REGISTER_GLOBALS'			=> 'PHP setting "register_globals" is disabled',
	'PHP_REGISTER_GLOBALS_EXPLAIN'	=> 'phpBB will still run if this setting is enabled, but if possible, it is recommended that register_globals is disabled on your PHP install for security reasons.',
	'PHP_SAFE_MODE'					=> 'Safe Mode',
	'PHP_SETTINGS'					=> 'PHP Version and Settings',
	'PHP_SETTINGS_EXPLAIN'			=> '<strong>Required</strong> - You must be running at least version 4.3.3 of PHP in order to install phpBB. If "safe mode" is displayed below your PHP installation is running in that mode. This will impose limitations on remote administration and similar features.',
	'PHP_VERSION_REQD'				=> 'PHP version >= 4.3.3',
	'PREFIX_FOUND'					=> 'A scan of your tables has shown a valid installation using <strong>%s</strong> as table prefix.',
	'PREPROCESS_STEP'				=> 'Executing pre-processing functions/queries',
	'PRE_CONVERT_COMPLETE'			=> 'All pre-conversion steps have successfully been completed. You may now begin the actual conversion process.',
	'PROCESS_LAST'					=> 'Processing last statements',

//	'REQUIRED'					=> 'Required',
	'REQUIREMENTS_TITLE'		=> 'Installation Compatibility',
	'REQUIREMENTS_EXPLAIN'		=> 'Before proceeding with full installation phpBB will carry out some tests on your server configuration and files to ensure that you are able to install and run phpBB. Please ensure you read through the results thoroughly and do not proceed until all the required tests are passed. If you wish to enable any of the functionality listed by the optional tests, you should ensure that these tests are passed also.',
	'RETRY_WRITE'				=> 'Retry writing config',
	'RETRY_WRITE_EXPLAIN'		=> 'If you wish you can change the permissions on config.php to allow phpBB to write to it. Should you wish to do that you can click Retry below to try again. Remember to return the permissions on config.php after phpBB has finished installation.',

	'SCRIPT_PATH'				=> 'Script path',
	'SCRIPT_PATH_EXPLAIN'		=> 'The path where phpBB is located relative to the domain name',
	'SELECT_LANG'				=> 'Select language',
	'SERVER_CONFIG'				=> 'Server Configuration',
	'SOFTWARE'					=> 'Forum Software',
	'SPECIFY_OPTIONS'			=> 'Specify Conversion Options',
	'STAGE_ADMINISTRATOR'		=> 'Administrator Details',
	'STAGE_ADVANCED'			=> 'Advanced Settings',
	'STAGE_ADVANCED_EXPLAIN'	=> 'The settings on this page are only necessary to set if you know that you require something different from the default. If unsure, just proceed to the next page, this can be altered from the Administration Panel later.',
	'STAGE_CONFIG_FILE'			=> 'Configuration File',
	'STAGE_CREATE_TABLE'		=> 'Create Database Tables',
	'STAGE_CREATE_TABLE_EXPLAIN' => 'The database tables used by phpBB 3.0 have been created and populated with some initial data. Proceed to the next screen to finish installing phpBB.',
	'STAGE_DATABASE'			=> 'Database Settings',
	'STAGE_FINAL'				=> 'Final Stage',
	'STAGE_INTRO'				=> 'Introduction',
	'STAGE_IN_PROGRESS'			=> 'Conversion in progress',
	'STAGE_REQUIREMENTS'		=> 'Requirements',
	'STAGE_SETTINGS'			=> 'Settings',
	'STARTING_CONVERT'			=> 'Starting Conversion Process',
	'STEP_PERCENT_COMPLETED'	=> 'Step <b>%d</b> of <b>%d</b>: %d%% completed',
	'SUB_INTRO'					=> 'Introduction',
	'SUB_LICENSE'				=> 'License',
	'SUB_SUPPORT'				=> 'Support',
	'SUCCESSFUL_CONNECT'		=> 'Successful Connection',
// TODO: Write some text on obtaining support
	'SUPPORT_BODY'				=> 'During the beta phase a minimal level of support will be given at <a href="http://www.phpbb.com/phpBB/viewforum.php?f=46">the phpBB 3.0 Beta support forum</a>. We will provide answers to general setup questions, configuration problems and support for determining common problems mostly related to bugs. We will not support modifications, custom code/style additions or any users using the beta packages within a live environment.</p><p>For additional assistance, please refer to our <a href="http://www.phpbb.com/support/documentation/3.0/quickstart/">Quick Start Guide</a>.</p><p>To ensure you stay up to date with the latest news and releases, why not <a href="http://www.phpbb.com/support/" target="_new">subscribe to our mailing list</a>',
	'SYNC_FORUMS'				=> 'Starting to sync forums',
	'SYNC_TOPICS'				=> 'Starting to sync topics',
	'SYNC_TOPIC_ID'				=> 'Synchronising topics from topic_id $1%s to $2%s',

	'TABLES_MISSING'			=> 'Could not find these tables<br />&#187; <b>%s</b>.',
	'TABLE_PREFIX'				=> 'Prefix for tables in database',
	'TABLE_PREFIX_SAME'			=> 'The table prefix needs to be the one used by the software you are converting from.<br />&#187; Specified table prefix was %s',
	'TESTS_PASSED'				=> 'Tests passed',
	'TESTS_FAILED'				=> 'Tests failed',

	'UNAVAILABLE'				=> 'Unavailable',
	'UNWRITEABLE'				=> 'Unwriteable',

	'VERSION'					=> 'Version',

	'WELCOME_INSTALL'			=> 'Welcome to phpBB 3 Installation',
	'WRITEABLE'					=> 'Writeable',
));

?>