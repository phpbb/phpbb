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
namespace phpbb\db\doctrine;

use InvalidArgumentException;

class table_helper
{
	/**
	 * Converts phpBB's column representation to Doctrine's representation.
	 *
	 * @param array $column_data Column data.
	 *
	 * @return array<string, array> A pair of type and array of column options.
	 * @psalm-return array{string, array}
	 */
	public static function convert_column_data(array $column_data, string $dbms_layer): array
	{
		$options = self::resolve_dbms_specific_options($column_data, $dbms_layer);
		list($type, $opts) = type_converter::convert($column_data[0], $dbms_layer);
		$options = array_merge($options, $opts);
		return [$type, $options];
	}

	/**
	 * Resolve DBMS specific options in column data.
	 *
	 * @param array  $column_data Original column data.
	 * @param string $dbms_layer  DBMS layer name.
	 *
	 * @return array Doctrine column options.
	 */
	private static function resolve_dbms_specific_options(array $column_data, string $dbms_layer): array
	{
		$doctrine_options = [];

		if (is_array($column_data[1]))
		{
			$column_data[1] = self::get_default_column_option($column_data[1], $dbms_layer);
		}

		if (!is_null($column_data[1]))
		{
			$doctrine_options['default'] = $column_data[1];
			$doctrine_options['notnull'] = true;
		}
		else
		{
			$doctrine_options['notnull'] = false;
		}

		$non_string_pattern = '/^[a-z]*(?:int|decimal|bool|timestamp)(?::[0-9]+)?$/';
		if ($dbms_layer === 'oracle'
			&& !preg_match($non_string_pattern, strtolower($column_data[0]))
			&& array_key_exists('default', $doctrine_options)
			&& $doctrine_options['default'] === '')
		{
			// Not null is true by default and Oracle does not allow empty strings in not null columns
			$doctrine_options['notnull'] = false;
		}

		if (isset($column_data[2]))
		{
			if ($column_data[2] === 'auto_increment')
			{
				$doctrine_options['autoincrement'] = true;
			}
			else if ($dbms_layer === 'mysql' && $column_data[2] === 'true_sort')
			{
				$doctrine_options['platformoptions']['collation'] = 'utf8_unicode_ci';
			}
		}

		return $doctrine_options;
	}

	/**
	 * Returns the DBMS specific default value for a column definition.
	 *
	 * @param array  $default_options Database specific default value options.
	 * @param string $dbms_layer      Name of the DBMS layer.
	 *
	 * @return mixed Default value for the current DBMS.
	 *
	 * @throws InvalidArgumentException When `$schema_name` contains an invalid legacy DBMS name.
	 */
	private static function get_default_column_option(array $default_options, string $dbms_layer)
	{
		switch ($dbms_layer)
		{
			case 'mysql':
				return array_key_exists('mysql_41', $default_options)
					? $default_options['mysql_41']
					: $default_options['default'];
			case 'oracle':
				return array_key_exists('oracle', $default_options)
					? $default_options['oracle']
					: $default_options['default'];
			case 'postgresql':
				return array_key_exists('postgres', $default_options)
					? $default_options['postgres']
					: $default_options['default'];
			case 'mssql':
				return array_key_exists('mssqlnative', $default_options)
					? $default_options['mssqlnative']
					: (array_key_exists('mssql', $default_options) ? $default_options['mssql'] : $default_options['default']);
			case 'sqlite':
				return array_key_exists('sqlite3', $default_options)
					? $default_options['sqlite3']
					: $default_options['default'];
			default:
				throw new InvalidArgumentException('Invalid schema name.');
		}
	}

	/**
	 * Maps short table names for the purpose of prefixing tables' index names.
	 *
	 * @param array $additional_tables	Additional table names without prefix to add to the map.
	 * @param array $table_prefix		Tables prefix.
	 *
	 * @return array<string, string>	Pairs of table names and their short name representations.
	 */
	public static function map_short_table_names(array $additional_tables = [], string $table_prefix = ''): array
	{
		$short_table_names_map = [
			"{$table_prefix}acl_groups"	=> 'aclgrps',
			"{$table_prefix}acl_options"		=> 'aclopts',
			"{$table_prefix}acl_roles"			=> 'aclrls',
			"{$table_prefix}acl_roles_data"		=> 'aclrlsdt',
			"{$table_prefix}acl_users"			=> 'aclusrs',
			"{$table_prefix}attachments"		=> 'atchmnts',
			"{$table_prefix}backups"			=> 'bckps',
			"{$table_prefix}bans"				=> 'bans',
			"{$table_prefix}bbcodes"			=> 'bbcds',
			"{$table_prefix}bookmarks"			=> 'bkmrks',
			"{$table_prefix}bots"				=> 'bots',
			"{$table_prefix}captcha_answers"	=> 'cptchans',
			"{$table_prefix}captcha_questions"	=> 'cptchqs',
			"{$table_prefix}config"				=> 'cnfg',
			"{$table_prefix}config_text"		=> 'cnfgtxt',
			"{$table_prefix}confirm"			=> 'cnfrm',
			"{$table_prefix}disallow"			=> 'dslw',
			"{$table_prefix}drafts"				=> 'drfts',
			"{$table_prefix}ext"				=> 'ext',
			"{$table_prefix}extension_groups"	=> 'extgrps',
			"{$table_prefix}extensions"			=> 'exts',
			"{$table_prefix}forums"				=> 'frms',
			"{$table_prefix}forums_access"		=> 'frmsacs',
			"{$table_prefix}forums_track"		=> 'frmstrck',
			"{$table_prefix}forums_watch"		=> 'frmswtch',
			"{$table_prefix}groups"				=> 'grps',
			"{$table_prefix}icons"				=> 'icns',
			"{$table_prefix}lang"				=> 'lang',
			"{$table_prefix}log"				=> 'log',
			"{$table_prefix}login_attempts"		=> 'lgnatmpts',
			"{$table_prefix}migrations"			=> 'mgrtns',
			"{$table_prefix}moderator_cache"	=> 'mdrtche',
			"{$table_prefix}modules"			=> 'mdls',
			"{$table_prefix}notification_emails"=> 'ntfemls',
			"{$table_prefix}notification_push"	=> 'ntfpsh',
			"{$table_prefix}notification_types"	=> 'ntftps',
			"{$table_prefix}notifications"		=> 'nftcns',
			"{$table_prefix}oauth_accounts"		=> 'oauthacnts',
			"{$table_prefix}oauth_states"		=> 'oauthsts',
			"{$table_prefix}oauth_tokens"		=> 'oauthtkns',
			"{$table_prefix}poll_options"		=> 'pllopts',
			"{$table_prefix}poll_votes"			=> 'pllvts',
			"{$table_prefix}posts"				=> 'psts',
			"{$table_prefix}privmsgs"			=> 'pms',
			"{$table_prefix}privmsgs_folder"	=> 'pmsfldr',
			"{$table_prefix}privmsgs_rules"		=> 'pmsrls',
			"{$table_prefix}privmsgs_to"		=> 'pmsto',
			"{$table_prefix}profile_fields"		=> 'prflds',
			"{$table_prefix}profile_fields_data"=> 'prfldt',
			"{$table_prefix}profile_fields_lang"=> 'prfldlng',
			"{$table_prefix}profile_lang"		=> 'prflng',
			"{$table_prefix}push_subscriptions"	=> 'pshsbscrs',
			"{$table_prefix}qa_confirm"			=> 'qacnfm',
			"{$table_prefix}ranks"				=> 'rnks',
			"{$table_prefix}reports"			=> 'rprts',
			"{$table_prefix}reports_reasons"	=> 'rprtrsns',
			"{$table_prefix}search_results"		=> 'srchrslts',
			"{$table_prefix}search_wordlist"	=> 'wrdlst',
			"{$table_prefix}search_wordmatch"	=> 'wrdmtch',
			"{$table_prefix}sessions"			=> 'ssns',
			"{$table_prefix}sessions_keys"		=> 'ssnkeys',
			"{$table_prefix}sitelist"			=> 'sitelst',
			"{$table_prefix}smilies"			=> 'smls',
			"{$table_prefix}storage"			=> 'strg',
			"{$table_prefix}styles"				=> 'stls',
			"{$table_prefix}teampage"			=> 'teampg',
			"{$table_prefix}topics"				=> 'tpcs',
			"{$table_prefix}topics_posted"		=> 'tpcspstd',
			"{$table_prefix}topics_track"		=> 'tpcstrk',
			"{$table_prefix}topics_watch"		=> 'tpkswtch',
			"{$table_prefix}user_group"			=> 'usrgrp',
			"{$table_prefix}user_notifications"	=> 'usrntfs',
			"{$table_prefix}users"				=> 'usrs',
			"{$table_prefix}warnings"			=> 'wrns',
			"{$table_prefix}words"				=> 'wrds',
			"{$table_prefix}zebra"				=> 'zbra',
		];

		// Add table prefix to additional tables
		if (!empty($table_prefix && !empty($additional_tables)))
		{
			foreach ($additional_tables as $key => $value)
			{
				$additional_tables["{$table_prefix}{$key}"] = $value;
				unset($additional_tables[$key]);
			}
		}

		return array_merge($short_table_names_map, $additional_tables);
	}

	/**
	 * Private constructor. Call methods of table_helper statically.
	 */
	private function __construct()
	{
	}
}
