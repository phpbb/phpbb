<?php
/**
*
* @package testing
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions_container.php';

class phpbb_test_case extends PHPUnit_Framework_TestCase
{
	protected $test_case_helpers;

	public function __construct($name = NULL, array $data = array(), $dataName = '')
	{
		parent::__construct($name, $data, $dataName);
		$this->backupStaticAttributesBlacklist += array(
			'PHP_CodeCoverage' => array('instance'),
			'PHP_CodeCoverage_Filter' => array('instance'),
			'PHP_CodeCoverage_Util' => array('ignoredLines', 'templateMethods'),
			'PHP_Timer' => array('startTimes',),
			'PHP_Token_Stream' => array('customTokens'),
			'PHP_Token_Stream_CachingFactory' => array('cache'),

			'phpbb_database_test_case' => array('already_connected'),
		);
	}

	public function get_test_case_helpers()
	{
		if (!$this->test_case_helpers)
		{
			$this->test_case_helpers = new phpbb_test_case_helpers($this);
		}

		return $this->test_case_helpers;
	}

	public function setExpectedTriggerError($errno, $message = '')
	{
		$this->get_test_case_helpers()->setExpectedTriggerError($errno, $message);
	}

	static public function create_container(array $services = array())
	{
		$phpbb_root_path = __DIR__ . '/../../phpBB/';
		$extensions = array(
			new phpbb_di_extension_config(__DIR__ . '/../di/fixtures/config.php'),
			new phpbb_di_extension_core($phpbb_root_path),
		);
		$passes = array(
			new phpbb_di_pass_replace_pass($services),
		);
		$container = phpbb_create_compiled_container($extensions, $passes, $phpbb_root_path, 'php');

		return $container;
	}
}
