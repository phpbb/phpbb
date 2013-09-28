<?php
/**
*
* @package testing
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/utf/utf_tools.php';

class phpbb_request_request_var_test extends phpbb_test_case
{
	/**
	* @dataProvider request_variables
	*/
	public function test_post($variable_value, $default, $multibyte, $expected)
	{
		$variable_name = 'name';
		$this->unset_variables($variable_name);

		$_POST[$variable_name] = $variable_value;
		$_REQUEST[$variable_name] = $variable_value;

		$result = request_var($variable_name, $default, $multibyte);

		$label = 'Requesting POST variable, converting from ' . gettype($variable_value) . ' to ' . gettype($default) . (($multibyte) ? ' multibyte' : '');
		$this->assertEquals($expected, $result, $label);
	}

	/**
	* @dataProvider request_variables
	*/
	public function test_get($variable_value, $default, $multibyte, $expected)
	{
		$variable_name = 'name';
		$this->unset_variables($variable_name);

		$_GET[$variable_name] = $variable_value;
		$_REQUEST[$variable_name] = $variable_value;

		$result = request_var($variable_name, $default, $multibyte);

		$label = 'Requesting GET variable, converting from ' . gettype($variable_value) . ' to ' . gettype($default) . (($multibyte) ? ' multibyte' : '');
		$this->assertEquals($expected, $result, $label);
	}

	/**
	* @dataProvider request_variables
	*/
	public function test_cookie($variable_value, $default, $multibyte, $expected)
	{
		$variable_name = 'name';
		$this->unset_variables($variable_name);

		$_GET[$variable_name] = false;
		$_POST[$variable_name] = false;
		$_REQUEST[$variable_name] = false;
		$_COOKIE[$variable_name] = $variable_value;

		$result = request_var($variable_name, $default, $multibyte, true);

		$label = 'Requesting COOKIE variable, converting from ' . gettype($variable_value) . ' to ' . gettype($default) . (($multibyte) ? ' multibyte' : '');
		$this->assertEquals($expected, $result, $label);
	}

	/**
	* Helper for unsetting globals
	*/
	private function unset_variables($var)
	{
		unset($_GET[$var], $_POST[$var], $_REQUEST[$var], $_COOKIE[$var]);
	}

	static public function request_variables()
	{
		return array(
			// strings
			array('abc', '', false, 'abc'),
			array('  some  spaces  ', '', true, 'some  spaces'),
			array("\r\rsome\rcarriage\r\rreturns\r", '', true, "some\ncarriage\n\nreturns"),
			array("\n\nsome\ncarriage\n\nreturns\n", '', true, "some\ncarriage\n\nreturns"),
			array("\r\n\r\nsome\r\ncarriage\r\n\r\nreturns\r\n", '', true, "some\ncarriage\n\nreturns"),
			array("we\xC2\xA1rd\xE1\x9A\x80ch\xCE\xB1r\xC2\xADacters", '', true, "we\xC2\xA1rd\xE1\x9A\x80ch\xCE\xB1r\xC2\xADacters"),
			array("we\xC2\xA1rd\xE1\x9A\x80ch\xCE\xB1r\xC2\xADacters", '', false, "we??rd???ch??r??acters"),
			array("Some <html> \"entities\" like &", '', true, "Some &lt;html&gt; &quot;entities&quot; like &amp;"),

			// integers
			array('1234', 0, false, 1234),
			array('abc', 12, false, 0),
			array('324abc', 0, false, 324),

			// string to array
			array('123', array(0), false, array()),
			array('123', array(''), false, array()),

			// 1 dimensional arrays
			array(
				// input:
				array('123', 'abc'),
				// default:
				array(''),
				false,
				// expected:
				array('123', 'abc')
			),
			array(
				// input:
				array('123', 'abc'),
				// default:
				array(999),
				false,
				// expected:
				array(123, 0)
			),
			array(
				// input:
				array('xyz' => '123', 'abc' => 'abc'),
				// default:
				array('' => ''),
				false,
				// expected:
				array('xyz' => '123', 'abc' => 'abc')
			),
			array(
				// input:
				array('xyz' => '123', 'abc' => 'abc'),
				// default:
				array('' => 0),
				false,
				// expected:
				array('xyz' => 123, 'abc' => 0)
			),

			// 2 dimensional arrays
			array(
				// input:
				'',
				// default:
				array(array(0)),
				false,
				// expected:
				array()
			),
			array(
				// input:
				array(
					'xyz' => array('123', 'def'),
					'abc' => 'abc'
				),
				// default:
				array('' => array('')),
				false,
				// expected:
				array(
					'xyz' => array('123', 'def'),
					'abc' => array()
				)
			),
			array(
				// input:
				array(
					'xyz' => array('123', 'def'),
					'abc' => 'abc'
				),
				// default:
				array('' => array(0)),
				false,
				// expected:
				array(
					'xyz' => array(123, 0),
					'abc' => array()
				)
			),
		);
	}

}

