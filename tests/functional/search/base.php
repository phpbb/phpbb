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
abstract class phpbb_functional_search_base extends phpbb_functional_test_case
{
	protected function assert_search_found($keywords, $posts_found, $words_highlighted)
	{
		$crawler = self::request('GET', 'search.php?keywords=' . $keywords);
		$this->assertEquals($posts_found, $crawler->filter('.postbody')->count());
		$this->assertEquals($words_highlighted, $crawler->filter('.posthilit')->count());
	}

	protected function assert_search_not_found($keywords)
	{
		$crawler = self::request('GET', 'search.php?keywords=' . $keywords);
		$this->assertEquals(0, $crawler->filter('.postbody')->count());
		$split_keywords_string = str_replace(array('+', '-'), ' ', $keywords);
		$this->assertEquals($split_keywords_string, $crawler->filter('#keywords')->attr('value'));
	}

	public function test_search_backend()
	{
		$this->login();
		$this->admin_login();

		$post = $this->create_topic(2, 'Test Topic 1 foosubject', 'This is a test topic posted by the barsearch testing framework.');

		$crawler = self::request('GET', 'adm/index.php?i=acp_search&mode=settings&sid=' . $this->sid);
		$form = $crawler->selectButton('Submit')->form();
		$values = $form->getValues();

		if ($values["config[search_type]"] != $this->search_backend)
		{
			$values["config[search_type]"] = $this->search_backend;
			$form->setValues($values);
			$crawler = self::submit($form);

			$form = $crawler->selectButton('Yes')->form();
			$values = $form->getValues();
			$crawler = self::submit($form);

			// check if search backend is not supported
			if ($crawler->filter('.errorbox')->count() > 0)
			{
				$this->delete_topic($post['topic_id']);
				$this->markTestSkipped("Search backend is not supported/running");
			}
			$this->create_search_index();
		}

		$this->logout();
		$this->assert_search_found('phpbb3+installation', 1, 3);
		$this->assert_search_found('foosubject+barsearch', 1, 2);
		$this->assert_search_not_found('loremipsumdedo');

		$this->login();
		$this->admin_login();
		$this->delete_search_index();
		$this->delete_topic($post['topic_id']);
	}

	protected function create_search_index()
	{
		$this->add_lang('acp/search');
		$crawler = self::request(
			'POST',
			'adm/index.php?i=acp_search&mode=index&sid=' . $this->sid,
			array(
				'search_type'	=> $this->search_backend,
				'action'		=> 'create',
				'submit'		=> true,
			)
		);
		$this->assertContainsLang('SEARCH_INDEX_CREATED', $crawler->text());
	}

	protected function delete_search_index()
	{
		$this->add_lang('acp/search');
		$crawler = self::request(
			'POST',
			'adm/index.php?i=acp_search&mode=index&sid=' . $this->sid,
			array(
				'search_type'	=> $this->search_backend,
				'action'		=> 'delete',
				'submit'		=> true,
			)
		);
		$this->assertContainsLang('SEARCH_INDEX_REMOVED', $crawler->text());
	}
}
