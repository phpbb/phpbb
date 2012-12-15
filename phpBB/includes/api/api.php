<?php
/**
 *
 * @package phpBB3
 * @copyright (c) 2012 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

class phpbb_api
{
	protected $plural;
	protected $method;
	protected $controller;
	protected $args;
	protected $format;

	/**
	 * Call relevant API function (will be in parents class).
	 *
	 * @param string $method Request method.
	 * @param array $path_data Array of path data.
	 * @param string $format Desired output format.
	 *
	 * @return boolean Whether API function was called successfully.
	 */
	public function call($method, $path_data, $format)
	{
		$this->plural = substr($path_data['controller'], -1) === 's';
		$this->method = $method;
		$this->controller = $path_data['controller'];
		$this->args = $path_data['args'];
		$this->format = $format;

		// @todo: Hook for custom methods
		$func_name = array(
			'GET'       => count($this->args) ? 'get' : 'list',
			'POST'      => count($this->args) ? 'new' : 'post',
			'PUT'       => 'edit',
			'DELETE'    => count($this->args) ? 'delete' : 'destroy'
		);
		$func_name = $this->controller . '_' . $func_name[$method];

		if (!method_exists($this, $func_name))
		{
			return false;
		}

		$result = call_user_func_array(array($this, $func_name), $this->args);
		$this->handle_output_data($result, $format);

		return true;
	}

	/**
	 * Handle output data - turn array into string of something like JSON or
	 * XML and then output it.
	 *
	 * @todo: Hook for adding other output formats.
	 *
	 * @param array $data Array of data to handle.
	 * @param string $format Format to turn the data into.
	 */
	protected function handle_output_data($data, $format)
	{
		if ($format === 'xml')
		{
			header('Content-type: text/xml');
			echo $this->toXML($this->controller, $data);
		}
		else
		{
			header('Content-type: application/json');
			echo json_encode($data);
		}
	}

	/**
	 * Throws a 404 error in the format used by the API.
	 *
	 * @param string $message The message to error with.
	 */
	public function show_404($message, $format = false)
	{
		send_status_line(404, 'Not Found');

		$this->handle_output_data(array(
			'error'     => $message
		), $format ?: $this->format);

		exit;
	}

	/**
	 * A very small library to turn an array into XML.
	 *
	 * @param string $main_element Name of main element.
	 * @param array $array The array to be turned into XML.
	 * @param boolean $include_header Include the header? (<?xml...)
	 * @return string XML!
	 */
	protected function toXML($main_element, $array, $include_header = true)
	{
		$string = $include_header ? '<?xml version="1.0" encoding="utf-8" ?>' : '';
		$string .= '<' . $main_element . '>';

		foreach ($array as $name => $value)
		{
			$string .= is_array($value)
				? $this->toXML($value, false)
				: '<' . $name . '>' . htmlspecialchars($value) . '</' . $name . '>';
		}

		$string .= '</' . $main_element . '>';

		return $string;
	}
}
