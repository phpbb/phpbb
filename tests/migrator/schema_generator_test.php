<?php
/**
*
* @package testing
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class schmema_generator_test extends phpbb_test_case
{
	public function setUp()
	{
		parent::setUp();

		$this->config = new \phpbb\config\config(array());
		$this->db = new \phpbb\db\driver\sqlite();
		$this->db_tools = new \phpbb\db\tools($this->db);
		$this->table_prefix = 'phpbb_';
	}

	protected function get_schema_generator(array $class_names)
	{
		$this->generator = new \phpbb\db\migration\schema_generator($class_names, $this->config, $this->db, $this->db_tools, $this->phpbb_root_path, $this->php_ext, $this->table_prefix);

		return $this->generator;
	}

	/**
	 * @expectedException \UnexpectedValueException
	 */
	public function test_check_dependencies_fail()
	{
		$this->get_schema_generator(array('\phpbb\db\migration\data\v310\forgot_password'));

		$this->generator->get_schema();
	}

	public function test_get_schema_success()
	{
		$this->get_schema_generator(array(
			'\phpbb\db\migration\data\v30x\release_3_0_1_rc1',
			'\phpbb\db\migration\data\v30x\release_3_0_0',
			'\phpbb\db\migration\data\v310\boardindex'
		));

		$this->assertArrayHasKey('phpbb_users', $this->generator->get_schema());
	}
}
