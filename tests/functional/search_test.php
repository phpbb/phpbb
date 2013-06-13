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
		$crawler = self::request('GET', 'search.php?keywords=phpbb3');
		$crawler->filter('.postbody')->text();
	}

	public function search_not_found()
	{
		$this->add_lang('search');
		$crawler = self::request('GET', 'search.php?keywords=loremipsumdedo');
		$this->assertContains($this->lang('NO_SEARCH_RESULTS'), $crawler->text());	

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

			$this->create_search_index($search_backend);
		}

		$this->search_found();
		$this->search_not_found();
		$this->delete_search_index($search_backend);
	}

	protected function create_search_index($search_backend)
	{
		$crawler = self::request(
			'POST',
			'adm/index.php?i=acp_search&mode=index&sid=' . $this->sid,
			array(
				'search_type'	=> $search_backend,
				'action'		=> 'create',
				'submit'		=> true,
			)
		);
	}

	protected function delete_search_index($search_backend)
	{
		$crawler = self::request(
			'POST',
			'adm/index.php?i=acp_search&mode=index&sid=' . $this->sid,
			array(
				'search_type'	=> $search_backend,
				'action'		=> 'delete',
				'submit'		=> true,
			)
		);
	}
}
