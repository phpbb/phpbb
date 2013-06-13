<?php
/**
 *
 * @package testing
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

/**
 * @group functional
 */
class phpbb_functional_search_test extends phpbb_functional_test_case
{

	public function test_native()
	{
		$this->search_backend_test('phpbb_search_fulltext_native');
	}

	public function test_mysql_fulltext()
	{
		$this->search_backend_test('phpbb_search_fulltext_mysql');

	}

	public function test_postgres_fulltext()
	{
		$this->search_backend_test('phpbb_search_fulltext_postgres');

	}

	public function test_sphinx()
	{
		$this->search_backend_test('phpbb_search_fulltext_sphinx');
	}

	public function search_found()
	{

	}

	public function search_not_found()
	{

	}

	protected function search_backend_test($search_backend)
	{
		$this->login();
		$this->admin_login();

		$crawler = self::request('GET', 'adm/index.php?i=acp_search&mode=settings&sid=' . $this->sid);
		$form = $crawler->selectButton('Submit')->form();
		$values = $form->getValues();

		if ($values["config[search_type]"] != $search_backend)
		{
			$values["config[search_type]"] = $search_backend;
			$form->setValues($values);
			$crawler = self::submit($form);

			$form = $crawler->selectButton('Yes')->form();
			$values = $form->getValues();
			$crawler = self::submit($form);

			try
			{
				$crawler->filter('.errorbox')->text();
				self::markTestSkipped("Search backend is not supported/running");

			}
			catch (InvalidArgumentException $e) {}
		}

		$this->create_search_index($crawler);
	}

	protected function create_search_index($create_index_crawler)
	{
		var_dump($create_index_crawler->selectLink('Go to search index page'));
	}
}
