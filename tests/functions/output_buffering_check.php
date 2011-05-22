<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* This script prints the result of phpbb_output_buffering_enabled
* for the current environment.
* The tests are meant to invoke this script with various php.ini
* configuration settings passed in via -d command-line argument.
*/

define('IN_PHPBB', 1);
require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

echo phpbb_output_buffering_enabled() ? 'true' : 'false';
