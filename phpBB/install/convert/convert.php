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

namespace phpbb\convert;

/**
 * Class holding all convertor-specific details.
 *
 * WARNING: This file did not meant to be present in a production environment, so moving this file to a location which
 * 			is accessible after board installation might lead to security issues.
 */
class convert
{
	var $options = array();

	var $convertor_tag = '';
	var $src_dbms = '';
	var $src_dbhost = '';
	var $src_dbport = '';
	var $src_dbuser = '';
	var $src_dbpasswd = '';
	var $src_dbname = '';
	var $src_table_prefix = '';

	var $convertor_data = array();
	var $tables = array();
	var $config_schema = array();
	var $convertor = array();
	var $src_truncate_statement = 'DELETE FROM ';
	var $truncate_statement = 'DELETE FROM ';

	var $fulltext_search;

	// Batch size, can be adjusted by the conversion file
	// For big boards a value of 6000 seems to be optimal
	var $batch_size = 2000;
	// Number of rows to be inserted at once (extended insert) if supported
	// For installations having enough memory a value of 60 may be good.
	var $num_wait_rows = 20;

	// Mysqls internal recoding engine messing up with our (better) functions? We at least support more encodings than mysql so should use it in favor.
	var $mysql_convert = false;

	var $p_master;

	function __construct($p_master)
	{
		$this->p_master = $p_master;
	}
}
