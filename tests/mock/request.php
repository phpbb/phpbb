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

	public function __construct($get = array(), $post = array(), $cookie = array(), $request = false)
	{
		$this->data[phpbb_request_interface::GET] = $get;
		$this->data[phpbb_request_interface::POST] = $post;
		$this->data[phpbb_request_interface::COOKIE] = $cookie;
		$this->data[phpbb_request_interface::REQUEST] = ($request === false) ? $post + $get : $request;
	}

	public function overwrite($var_name, $value, $super_global = phpbb_request_interface::REQUEST)
	{
		$this->data[$super_global][$var_name] = $value;
	}

	public function variable($var_name, $default, $multibyte = false, $super_global = phpbb_request_interface::REQUEST)
	{
		return isset($this->data[$super_global][$var_name]) ? $this->data[$super_global][$var_name] : $default;
	}

	public function is_set_post($name)
	{
		return $this->is_set($name, phpbb_request_interface::POST);
	}

	public function is_set($var, $super_global = phpbb_request_interface::REQUEST)
	{
		return isset($this->data[$super_global][$var]);
	}

	public function variable_names($super_global = phpbb_request_interface::REQUEST)
	{
		return array_keys($this->data[$super_global]);
	}
}
