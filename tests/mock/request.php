<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

class phpbb_mock_request implements phpbb_request_interface
{
	protected $data;

	public function __construct($get = array(), $post = array(), $cookie = array(), $server = array(), $request = false)
	{
		$this->data[phpbb_request_interface::GET] = $get;
		$this->data[phpbb_request_interface::POST] = $post;
		$this->data[phpbb_request_interface::COOKIE] = $cookie;
		$this->data[phpbb_request_interface::REQUEST] = ($request === false) ? $post + $get : $request;
		$this->data[phpbb_request_interface::SERVER] = $server;
	}

	public function overwrite($var_name, $value, $super_global = phpbb_request_interface::REQUEST)
	{
		$this->data[$super_global][$var_name] = $value;
	}

	public function variable($var_name, $default, $multibyte = false, $super_global = phpbb_request_interface::REQUEST)
	{
		return isset($this->data[$super_global][$var_name]) ? $this->data[$super_global][$var_name] : $default;
	}

	public function server($var_name, $default = '')
	{
		$super_global = phpbb_request_interface::SERVER;
		return isset($this->data[$super_global][$var_name]) ? $this->data[$super_global][$var_name] : $default;
	}

	public function header($header_name, $default = '')
	{
		$var_name = 'HTTP_' . str_replace('-', '_', strtoupper($header_name));
		return $this->server($var_name, $default);
	}

	public function is_set_post($name)
	{
		return $this->is_set($name, phpbb_request_interface::POST);
	}

	public function is_set($var, $super_global = phpbb_request_interface::REQUEST)
	{
		return isset($this->data[$super_global][$var]);
	}

	public function is_ajax()
	{
		return false;
	}

	public function is_secure()
	{
		return false;
	}

	public function variable_names($super_global = phpbb_request_interface::REQUEST)
	{
		return array_keys($this->data[$super_global]);
	}

	/* custom methods */

	public function set_header($header_name, $value)
	{
		$var_name = 'HTTP_' . str_replace('-', '_', strtoupper($header_name));
		$this->data[phpbb_request_interface::SERVER][$var_name] = $value;
	}

	public function merge($super_global = phpbb_request_interface::REQUEST, $values)
	{
		$this->data[$super_global] = array_merge($this->data[$super_global], $values);
	}
}
