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

	static public function create_template(array $services = array())
	{
		$phpbb_root_path = __DIR__ . '/../../phpBB/';
		$phpEx = 'php';

		$config = isset($services['config']) ? $services['config'] : new phpbb_config(array());
		$user = isset($services['user']) ? $services['user'] : new phpbb_user();
		$ext_manager = isset($services['ext.manager']) ? $services['ext.manager'] : null;
		$template_options = array(
			'debug'			=> true,
			'autoescape'	=> false,
		);
		$template_context = new phpbb_template_context();
		$twig = new phpbb_template_twig_environment($config, $ext_manager, $phpbb_root_path, new Twig_Loader_Filesystem(''), $template_options);
		$twig->addExtension(new phpbb_template_twig_extension($template_context, $user));
		$twig->setLexer(new phpbb_template_twig_lexer($twig));
		$template = new phpbb_template_twig($phpbb_root_path, $config, $user, $template_context, $twig, $ext_manager);

		return $template;
	}
}
