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

namespace phpbb\install\converter\controller;

use phpbb\install\helper\config;
use phpbb\install\helper\navigation\navigation_provider;
use phpbb\language\language;
use phpbb\language\language_file_helper;
use phpbb\path_helper;
use phpbb\request\request;
use phpbb\request\request_interface;
use phpbb\routing\router;
use phpbb\symfony_request;
use phpbb\template\template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * A duplicate of \phpbb\controller\helper
 *
 * This class is necessary because of controller\helper's legacy function calls
 * to page_header() page_footer() functions which has unavailable dependencies.
 */
class helper extends \phpbb\install\controller\helper
{
	/**
	 * @var config
	 */
	public function get_source_db()
	{
		$this->installer_config->load_config();
		return $this->installer_config->get('source_db_config');
	}

	public function get_destination_db()
	{
		$this->installer_config->load_config();
		return $this->installer_config->get('destination_db_config');
	}

	public function set_source_db($db_source)
	{
		$this->installer_config->set('source_db_config',$db_source);
		$this->installer_config->save_config();
	}

	public function set_destination_db($db_destination)
	{
		$this->installer_config->set('destination_db_config',$db_destination);
		$this->installer_config->save_config();
	}

	public function set_current_conversion_file($config_file)
	{
		$this->installer_config->set('current_conversion',$config_file);
		$this->installer_config->save_config();
	}

	public function get_current_conversion_file()
	{
		$this->installer_config->load_config();
		return $this->installer_config->get('current_conversion');
	}

	public function set_total_files($total_files)
	{
		$this->installer_config->set('current_conversion_total_files', $total_files);
		$this->installer_config->save_config();
	}

	public function get_total_files()
	{
		$this->installer_config->load_config();
		return $this->installer_config->get('current_conversion_total_files');
	}

	public function set_file_index($file_index)
	{
		$this->installer_config->set('current_conversion_file', $file_index);
		$this->installer_config->save_config();
	}

	public function get_file_index()
	{
		$this->installer_config->load_config();
		return $this->installer_config->get('current_conversion_file');
	}

	public function next_file()
	{
		$current = $this->get_file_index();
		if($current < $this->get_total_files())
		{
			$this->set_file_index($current+1);
		}
		else
		{
			$this->set_file_index(-1);
		}

	}


	public function set_conversion_status($status)
	{
		$this->installer_config->set('converter.conversion.status', $status);
		$this->installer_config->save_config();
	}

	public function get_conversion_status()
	{
		$this->installer_config->load_config();
		return $this->installer_config->get('converter.conversion.status');
	}


}
