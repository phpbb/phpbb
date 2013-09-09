<?php
/**
*
* @package testing
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_test_case extends PHPUnit_Framework_TestCase
{
	protected $test_case_helpers;

	protected $phpbb_container;

	public function __construct($name = NULL, array $data = array(), $dataName = '')
	{
		parent::__construct($name, $data, $dataName);

		global $phpbb_container, $phpbb_root_path;

		// Setup the container for global usage
		$this->phpbb_container = $phpbb_container = new phpbb_mock_container_builder();

		// Set some commonly needed systems up
		$phpbb_container->set('filesystem', new phpbb_filesystem($phpbb_root_path));

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
}
