<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

// Common installer pages
$lang = array_merge($lang, array(
	'INSTALL_PANEL'	=> 'Installation Panel',

	// Introduction page
	'INTRODUCTION_TITLE'	=> 'Introduction',
	'INTRODUCTION_BODY'		=> 'Welcome to phpBB3!<br /><br />phpBB® is the most widely used open source bulletin board solution in the world. phpBB3 is the latest installment in a package line started in 2000. Like its predecessors, phpBB3 is feature-rich, user-friendly, and fully supported by the phpBB Team. phpBB3 greatly improves on what made phpBB2 popular, and adds commonly requested features that were not present in previous versions. We hope it exceeds your expectations.<br /><br />This installation system will guide you through installing phpBB3, updating to the latest version of phpBB3 from past releases, as well as converting to phpBB3 from a different discussion board system (including phpBB2). For more information, we encourage you to read <a href="../docs/INSTALL.html">the installation guide</a>.<br /><br />To read the phpBB3 license or learn about obtaining support and our stance on it, please select the respective options from the side menu. To continue, please select the appropriate tab above.',

	// Support page
	'SUPPORT_TITLE'		=> 'Support',
	'SUPPORT_BODY'		=> 'Full support will be provided for the current stable release of phpBB3, free of charge. This includes:</p><ul><li>installation</li><li>configuration</li><li>technical questions</li><li>problems relating to potential bugs in the software</li><li>updating from Release Candidate (RC) versions to the latest stable version</li><li>converting from phpBB 2.0.x to phpBB3</li><li>converting from other discussion board software to phpBB3 (please see the <a href="https://www.phpbb.com/community/viewforum.php?f=486">Convertors Forum</a>)</li></ul><p>We encourage users still running beta versions of phpBB3 to replace their installation with a fresh copy of the latest version.</p><h2>Extensions / Styles</h2><p>For issues relating to Extensions, please post in the appropriate <a href="https://www.phpbb.com/community/viewforum.php?f=451">Extensions Forum</a>.<br />For issues relating to styles, templates and themes, please post in the appropriate <a href="https://www.phpbb.com/community/viewforum.php?f=471">Styles Forum</a>.<br /><br />If your question relates to a specific package, please post directly in the topic dedicated to the package.</p><h2>Obtaining Support</h2><p><a href="https://www.phpbb.com/community/viewtopic.php?f=14&amp;t=571070">The phpBB Welcome Package</a><br /><a href="https://www.phpbb.com/support/">Support Section</a><br /><a href="https://www.phpbb.com/support/docs/en/3.1/ug/quickstart/">Quick Start Guide</a><br /><br />To ensure you stay up to date with the latest news and releases, why not <a href="https://www.phpbb.com/support/">subscribe to our mailing list</a>?<br /><br />',

	// License
	'LICENSE_TITLE'		=> 'General Public License',
));

// Requirements translation
$lang = array_merge($lang, array(
	// Filesystem requirements
	'FILE_NOT_EXISTS'			=> 'File not exists',
	'FILE_NOT_EXISTS_EXPLAIN'	=> 'To be able to install phpBB %1$s file need to exist.',
	'FILE_NOT_WRITABLE'			=> 'File not writable',
	'FILE_NOT_WRITABLE_EXPLAIN'	=> 'To be able to install phpBB %1$s file need to be writable.',

	'DIRECTORY_NOT_EXISTS'				=> 'Directory not exists',
	'DIRECTORY_NOT_EXISTS_EXPLAIN'		=> 'To be able to install phpBB %1$s directory need to exist.',
	'DIRECTORY_NOT_WRITABLE'			=> 'Directory not writable',
	'DIRECTORY_NOT_WRITABLE_EXPLAIN'	=> 'To be able to install phpBB %1$s directory need to be writable.',

	// Server requirements
	'PHP_VERSION_REQD'					=> 'PHP version',
	'PHP_VERSION_REQD_EXPLAIN'			=> 'phpBB requires PHP version 5.3.9 or higher.',
	'PHP_GETIMAGESIZE_SUPPORT'			=> 'PHP getimagesize() function is required',
	'PHP_GETIMAGESIZE_SUPPORT_EXPLAIN'	=> 'In order for phpBB to function correctly, the getimagesize function needs to be available.',
	'PCRE_UTF_SUPPORT'					=> 'PCRE UTF-8 support',
	'PCRE_UTF_SUPPORT_EXPLAIN'			=> 'phpBB will not run if your PHP installation is not compiled with UTF-8 support in the PCRE extension.',
	'PHP_JSON_SUPPORT'					=> 'PHP JSON support',
	'PHP_JSON_SUPPORT_EXPLAIN'			=> 'In order for phpBB to function correctly, the PHP JSON extension needs to be available.',
	'PHP_SUPPORTED_DB'					=> 'Supported databases',
	'PHP_SUPPORTED_DB_EXPLAIN'			=> 'You must have support for at least one compatible database within PHP. If no database modules are shown as available you should contact your hosting provider or review the relevant PHP installation documentation for advice.',
));

// General error messages
$lang = array_merge($lang, array(
	'INST_ERR_MISSING_DATA' => 'You must fill out all fields in this block.',
));

// Data obtaining translations
$lang = array_merge($lang, array(
	//
	// Admin data
	//
	'STAGE_ADMINISTRATOR'	=> 'Administrator details',

	// Form labels
	'ADMIN_CONFIG'				=> 'Administrator configuration',
	'ADMIN_PASSWORD'			=> 'Administrator password',
	'ADMIN_PASSWORD_CONFIRM'	=> 'Confirm administrator password',
	'ADMIN_PASSWORD_EXPLAIN'	=> 'Please enter a password between 6 and 30 characters in length.',
	'ADMIN_USERNAME'			=> 'Administrator username',
	'ADMIN_USERNAME_EXPLAIN'	=> 'Please enter a username between 3 and 20 characters in length.',

	// Errors
	'INST_ERR_EMAIL_INVALID'		=> 'The email address you entered is invalid.',
	'INST_ERR_PASSWORD_MISMATCH'	=> 'The passwords you entered did not match.',
	'INST_ERR_PASSWORD_TOO_LONG'	=> 'The password you entered is too long. The maximum length is 30 characters.',
	'INST_ERR_PASSWORD_TOO_SHORT'	=> 'The password you entered is too short. The minimum length is 6 characters.',
	'INST_ERR_USER_TOO_LONG'		=> 'The username you entered is too long. The maximum length is 20 characters.',
	'INST_ERR_USER_TOO_SHORT'		=> 'The username you entered is too short. The minimum length is 3 characters.',

	//
	// Board data
	//
	// Form labels
	'BOARD_CONFIG'		=> 'Bulletin board configuration',
	'DEFAULT_LANGUAGE'	=> 'Default language',
	'BOARD_NAME'		=> 'Title of the board',
	'BOARD_DESCRIPTION'	=> 'Short description of the board',

	//
	// Database data
	//
	'STAGE_DATABASE'	=> 'Database settings',

	// Form labels
	'DB_CONFIG'				=> 'Database configuration',
	'DBMS'					=> 'Database type',
	'DB_HOST'				=> 'Database server hostname or DSN',
	'DB_HOST_EXPLAIN'		=> 'DSN stands for Data Source Name and is relevant only for ODBC installs. On PostgreSQL, use localhost to connect to the local server via UNIX domain socket and 127.0.0.1 to connect via TCP. For SQLite, enter the full path to your database file.',
	'DB_PORT'				=> 'Database server port',
	'DB_PORT_EXPLAIN'		=> 'Leave this blank unless you know the server operates on a non-standard port.',
	'DB_PASSWORD'			=> 'Database password',
	'DB_NAME'				=> 'Database name',
	'DB_USERNAME'			=> 'Database username',
	'TABLE_PREFIX'			=> 'Prefix for tables in database',
	'TABLE_PREFIX_EXPLAIN'	=> 'The prefix must start with a letter and must only contain letters, numbers and underscores.',

	// Database options
	'DB_OPTION_MSSQL'		=> 'MSSQL Server 2000+',
	'DB_OPTION_MSSQL_ODBC'	=> 'MSSQL Server 2000+ via ODBC',
	'DB_OPTION_MSSQLNATIVE'	=> 'MSSQL Server 2005+ [ Native ]',
	'DB_OPTION_MYSQL'		=> 'MySQL',
	'DB_OPTION_MYSQLI'		=> 'MySQL with MySQLi Extension',
	'DB_OPTION_ORACLE'		=> 'Oracle',
	'DB_OPTION_POSTGRES'	=> 'PostgreSQL',
	'DB_OPTION_SQLITE'		=> 'SQLite 2',
	'DB_OPTION_SQLITE3'		=> 'SQLite 3',

	// Errors
	'INST_ERR_NO_DB'				=> 'Cannot load the PHP module for the selected database type.',
	'INST_ERR_DB_INVALID_PREFIX'	=> 'The prefix you entered is invalid. It must start with a letter and must only contain letters, numbers and underscores.',
	'INST_ERR_PREFIX_TOO_LONG'		=> 'The table prefix you have specified is too long. The maximum length is %d characters.',
	'INST_ERR_DB_NO_NAME'			=> 'No database name specified.',
	'INST_ERR_DB_FORUM_PATH'		=> 'The database file specified is within your board directory tree. You should put this file in a non web-accessible location.',
	'INST_ERR_DB_CONNECT'			=> 'Could not connect to the database, see error message below.',
	'INST_ERR_DB_NO_ERROR'			=> 'No error message given.',
	'INST_ERR_PREFIX'				=> 'Tables with the specified prefix already exist, please choose an alternative.',
	'INST_ERR_DB_NO_MYSQLI'			=> 'The version of MySQL installed on this machine is incompatible with the “MySQL with MySQLi Extension” option you have selected. Please try the “MySQL” option instead.',
	'INST_ERR_DB_NO_SQLITE'			=> 'The version of the SQLite extension you have installed is too old, it must be upgraded to at least 2.8.2.',
	'INST_ERR_DB_NO_SQLITE3'		=> 'The version of the SQLite extension you have installed is too old, it must be upgraded to at least 3.6.15.',
	'INST_ERR_DB_NO_ORACLE'			=> 'The version of Oracle installed on this machine requires you to set the <var>NLS_CHARACTERSET</var> parameter to <var>UTF8</var>. Either upgrade your installation to 9.2+ or change the parameter.',
	'INST_ERR_DB_NO_POSTGRES'		=> 'The database you have selected was not created in <var>UNICODE</var> or <var>UTF8</var> encoding. Try installing with a database in <var>UNICODE</var> or <var>UTF8</var> encoding.',

	//
	// Email data
	//
	'EMAIL_CONFIG'	=> 'E-mail configuration',

	//
	// Server data
	//
	// Form labels
	'SERVER_CONFIG'				=> 'Server configuration',
	'SCRIPT_PATH'				=> 'Script path',
	'SCRIPT_PATH_EXPLAIN'		=> 'The path where phpBB is located relative to the domain name, e.g. <samp>/phpBB3</samp>.',
));

// Default database schema entries...
$lang = array_merge($lang, array(
	'CONFIG_BOARD_EMAIL_SIG'		=> 'Thanks, The Management',
	'CONFIG_SITE_DESC'				=> 'A short text to describe your forum',
	'CONFIG_SITENAME'				=> 'yourdomain.com',

	'DEFAULT_INSTALL_POST'			=> 'This is an example post in your phpBB3 installation. Everything seems to be working. You may delete this post if you like and continue to set up your board. During the installation process your first category and your first forum are assigned an appropriate set of permissions for the predefined usergroups administrators, bots, global moderators, guests, registered users and registered COPPA users. If you also choose to delete your first category and your first forum, do not forget to assign permissions for all these usergroups for all new categories and forums you create. It is recommended to rename your first category and your first forum and copy permissions from these while creating new categories and forums. Have fun!',

	'FORUMS_FIRST_CATEGORY'			=> 'Your first category',
	'FORUMS_TEST_FORUM_DESC'		=> 'Description of your first forum.',
	'FORUMS_TEST_FORUM_TITLE'		=> 'Your first forum',

	'RANKS_SITE_ADMIN_TITLE'		=> 'Site Admin',
	'REPORT_WAREZ'					=> 'The post contains links to illegal or pirated software.',
	'REPORT_SPAM'					=> 'The reported post has the only purpose to advertise for a website or another product.',
	'REPORT_OFF_TOPIC'				=> 'The reported post is off topic.',
	'REPORT_OTHER'					=> 'The reported post does not fit into any other category, please use the further information field.',

	'SMILIES_ARROW'					=> 'Arrow',
	'SMILIES_CONFUSED'				=> 'Confused',
	'SMILIES_COOL'					=> 'Cool',
	'SMILIES_CRYING'				=> 'Crying or Very Sad',
	'SMILIES_EMARRASSED'			=> 'Embarrassed',
	'SMILIES_EVIL'					=> 'Evil or Very Mad',
	'SMILIES_EXCLAMATION'			=> 'Exclamation',
	'SMILIES_GEEK'					=> 'Geek',
	'SMILIES_IDEA'					=> 'Idea',
	'SMILIES_LAUGHING'				=> 'Laughing',
	'SMILIES_MAD'					=> 'Mad',
	'SMILIES_MR_GREEN'				=> 'Mr. Green',
	'SMILIES_NEUTRAL'				=> 'Neutral',
	'SMILIES_QUESTION'				=> 'Question',
	'SMILIES_RAZZ'					=> 'Razz',
	'SMILIES_ROLLING_EYES'			=> 'Rolling Eyes',
	'SMILIES_SAD'					=> 'Sad',
	'SMILIES_SHOCKED'				=> 'Shocked',
	'SMILIES_SMILE'					=> 'Smile',
	'SMILIES_SURPRISED'				=> 'Surprised',
	'SMILIES_TWISTED_EVIL'			=> 'Twisted Evil',
	'SMILIES_UBER_GEEK'				=> 'Uber Geek',
	'SMILIES_VERY_HAPPY'			=> 'Very Happy',
	'SMILIES_WINK'					=> 'Wink',

	'TOPICS_TOPIC_TITLE'			=> 'Welcome to phpBB3',
));

// Common navigation items' translation
$lang = array_merge($lang, array(
	'MENU_OVERVIEW'		=> 'Overview',
	'MENU_INTRO'		=> 'Introduction',
	'MENU_LICENSE'		=> 'License',
	'MENU_SUPPORT'		=> 'Support',
));
