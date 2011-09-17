<?php
/**
*
* @package phpBB
* @version $Id$
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

class phpbb_error_collector
{
	var $errors;

	function phpbb_error_collector()
	{
		$this->errors = array();
	}

	function install()
	{
		set_error_handler(array(&$this, 'error_handler'));
	}

	function uninstall()
	{
		restore_error_handler();
	}

	function error_handler($errno, $msg_text, $errfile, $errline)
	{
		$this->errors[] = array($errno, $msg_text, $errfile, $errline);
	}

	function format_errors()
	{
		$phpbb_root_path = phpbb_realpath(dirname(__FILE__) . '/../');

		$text = '';
		foreach ($this->errors as $error)
		{
			if (!empty($text))
			{
				$text .= "<br />\n";
			}

			list($errno, $msg_text, $errfile, $errline) = $error;

			// Prevent leakage of local path to phpBB install
			$errfile = str_replace(array($phpbb_root_path, '\\'), array('', '/'), $errfile);

			$text .= "Errno $errno: $msg_text at $errfile line $errline";
		}

		return $text;
	}
}
