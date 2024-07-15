<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

/**
* @group functional
*/
abstract class phpbb_functional_search_base extends phpbb_functional_test_case
{
	protected $search_backend;

	protected function assert_search_found($keywords, $posts_found, $words_highlighted, $sort_key = '')
	{
		$this->purge_cache();
		$crawler = self::request('GET', 'search.php?keywords=' . $keywords . ($sort_key ? "&sk=$sort_key" : ''));
		$this->assertEquals($posts_found, $crawler->filter('.postbody')->count(), $this->search_backend);
		$this->assertEquals($words_highlighted, $crawler->filter('.posthilit')->count(), $this->search_backend);
		$this->assertStringContainsString("Search found $posts_found match", $crawler->filter('.searchresults-title')->text(), $this->search_backend);
	}

	protected function assert_search_found_topics($keywords, $topics_found, $sort_key = '')
	{
		$this->purge_cache();
		$crawler = self::request('GET', 'search.php?sr=topics&keywords=' . $keywords . ($sort_key ? "&sk=$sort_key" : ''));
		$this->assertEquals($topics_found, $crawler->filter('.row')->count(), $this->search_backend);
		$this->assertStringContainsString("Search found $topics_found match", $crawler->filter('.searchresults-title')->text(), $this->search_backend);
	}

	protected function assert_search_posts_by_author($author, $posts_found, $sort_key = '')
	{
		$this->purge_cache();
		$crawler = self::request('GET', 'search.php?author=' . $author . ($sort_key ? "&sk=$sort_key" : ''));
		$this->assertEquals($posts_found, $crawler->filter('.postbody')->count(), $this->search_backend);
		$this->assertStringContainsString("Search found $posts_found match", $crawler->filter('.searchresults-title')->text(), $this->search_backend);
	}

	protected function assert_search_topics_by_author($author, $topics_found, $sort_key = '')
	{
		$this->purge_cache();
		$crawler = self::request('GET', 'search.php?sr=topics&author=' . $author . ($sort_key ? "&sk=$sort_key" : ''));
		$this->assertEquals($topics_found, $crawler->filter('.row')->count(), $this->search_backend);
		$this->assertStringContainsString("Search found $topics_found match", $crawler->filter('.searchresults-title')->text(), $this->search_backend);
	}

	protected function assert_search_in_topic($topic_id, $keywords, $posts_found, $sort_key = '')
	{
		$this->purge_cache();
		$crawler = self::request('GET', "search.php?t=$topic_id&sf=msgonly&keywords=$keywords" . ($sort_key ? "&sk=$sort_key" : ''));
		$this->assertEquals($posts_found, $crawler->filter('.postbody')->count(), $this->search_backend);
		$this->assertStringContainsString("Search found $posts_found match", $crawler->filter('.searchresults-title')->text(), $this->search_backend);
	}

	protected function assert_search_in_forum($forum_id, $keywords, $posts_found, $sort_key = '')
	{
		$this->purge_cache();
		$crawler = self::request('GET', "search.php?fid[]=$forum_id&keywords=$keywords" . ($sort_key ? "&sk=$sort_key" : ''));
		$this->assertEquals($posts_found, $crawler->filter('.postbody')->count(), $this->search_backend);
		$this->assertStringContainsString("Search found $posts_found match", $crawler->filter('.searchresults-title')->text(), $this->search_backend);
	}

	protected function assert_search_topics_in_forum($forum_id, $keywords, $topics_found, $sort_key = '')
	{
		$this->purge_cache();
		$crawler = self::request('GET', "search.php?fid[]=$forum_id&sr=topics&keywords=$keywords" . ($sort_key ? "&sk=$sort_key" : ''));
		$this->assertEquals($topics_found, $crawler->filter('.row')->count(), $this->search_backend);
		$this->assertStringContainsString("Search found $topics_found match", $crawler->filter('.searchresults-title')->text(), $this->search_backend);
	}

	protected function assert_search_not_found($keywords)
	{
		$crawler = self::request('GET', 'search.php?keywords=' . $keywords);
		$this->assertEquals(0, $crawler->filter('.postbody')->count(), $this->search_backend);
		$split_keywords_string = str_replace('+', ' ', $keywords);
		$this->assertEquals($split_keywords_string, $crawler->filter('#keywords')->attr('value'), $this->search_backend);
	}

	protected function assert_search_for_author_not_found($author)
	{
		$this->add_lang('search');
		$crawler = self::request('GET', 'search.php?author=' . $author);
		$this->assertContainsLang('NO_SEARCH_RESULTS', $crawler->text(), $this->search_backend);
	}

	public function test_search_backend()
	{
		$this->add_lang('common');

		// Create a new standard user if needed, topic and post to test searh for author
		if (!$this->user_exists('searchforauthoruser'))
		{
			$searchforauthoruser_id = $this->create_user('searchforauthoruser');
		}
		$this->remove_user_group('NEWLY_REGISTERED', ['searchforauthoruser']);
		$this->set_flood_interval(0);
		$this->login('searchforauthoruser');
		$topic_by_author = $this->create_topic(2, 'Test Topic from searchforauthoruser', 'This is a test topic posted by searchforauthoruser to test searching by author.');
		$this->create_post(2, $topic_by_author['topic_id'], 'Re: Test Topic from searchforauthoruser', 'This is a test post posted by searchforauthoruser');
		$this->logout();

		$this->login();
		$this->admin_login();

		$this->create_search_index('phpbb\\search\\backend\\fulltext_native');

		$post = $this->create_topic(2, 'Test Topic 1 foosubject', 'This is a test topic posted by the barsearch testing framework.');
		$topic_multiple_results_count1 = $this->create_topic(2, 'Test Topic for multiple search results', 'This is a test topic posted to test multiple results count.');
		$this->create_post(2, $topic_multiple_results_count1['topic_id'], 'Re: Test Topic for multiple search results', 'This is a test post 2 posted to test multiple results count.');
		$topic_multiple_results_count2 = $this->create_topic(2, 'Test Topic 2 for multiple search results', 'This is a test topic 2 posted to test multiple results count.');
		$this->set_flood_interval(15);

		$crawler = self::request('GET', 'adm/index.php?i=acp_search&mode=settings&sid=' . $this->sid);
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$values = $form->getValues();

		if ($values["config[search_type]"] != $this->search_backend)
		{
			$values["config[search_type]"] = $this->search_backend;

			if (strpos($this->search_backend, 'fulltext_sphinx'))
			{
				// Set board Sphinx id in according to respective setup-sphinx.sh $ID value
				$values["config[fulltext_sphinx_id]"] = 'saw9zf2fdhp1goue';
			}

			try
			{
				$form->setValues($values);
			}
			catch(\InvalidArgumentException $e)
			{
				// Search backed is not supported because don't appear in the select
				$this->delete_topic($post['topic_id']);
				$this->delete_topic($topic_by_author['topic_id']);
				$this->delete_topic($topic_multiple_results_count1['topic_id']);
				$this->delete_topic($topic_multiple_results_count2['topic_id']);
				$this->markTestSkipped("Search backend is not supported/running");
			}

			$crawler = self::submit($form);
			$this->purge_cache();

			$form = $crawler->selectButton($this->lang('YES'))->form();
			$values = $form->getValues();
			$crawler = self::submit($form);

			// Unknown error selecting search backend
			if ($crawler->filter('.errorbox')->count() > 0)
			{
				$this->fail('Error when trying to select available search backend');
			}

			$this->create_search_index();
		}

		$this->logout();

		foreach (['', 'a', 't', 'f', 'i', 's'] as $sort_key)
		{
			$this->assert_search_found('phpbb3+installation', 1, 4, $sort_key);
			$this->assert_search_found('foosubject+barsearch', 1, 2, $sort_key);
			$this->assert_search_found('barsearch-testing', 1, 2, $sort_key); // test hyphen ignored
			$this->assert_search_found('barsearch+-+testing', 1, 2, $sort_key); // test hyphen wrapped with space ignored
			$this->assert_search_found('multiple+results+count', 3, 15, $sort_key); // test multiple results count - posts
			$this->assert_search_found_topics('multiple+results+count', 2, $sort_key); // test multiple results count - topics
			$this->assert_search_found_topics('phpbb3+installation', 1, $sort_key);
			$this->assert_search_found_topics('foosubject+barsearch', 1, $sort_key);

			$this->assert_search_in_forum(2, 'multiple+search+results', 3, $sort_key); // test multiple results count - forum search - posts
			$this->assert_search_topics_in_forum(2, 'multiple+search+results', 2, $sort_key); // test multiple results count - forum search - topics
			$this->assert_search_in_topic((int) $topic_multiple_results_count1['topic_id'], 'multiple+results', 2, $sort_key); // test multiple results count - topic search

			$this->assert_search_posts_by_author('searchforauthoruser', 2, $sort_key);
			$this->assert_search_topics_by_author('searchforauthoruser', 1, $sort_key);
		}

		$this->assert_search_not_found('loremipsumdedo');
		$this->assert_search_not_found('loremipsumdedo+-'); // test search query ending with the space followed by hyphen
		$this->assert_search_not_found('barsearch+-testing'); // test excluding keyword
		$this->assert_search_for_author_not_found('authornotexists');

		$this->login();
		$this->admin_login();
		$this->delete_search_index();
		$this->delete_topic($post['topic_id']);
		$this->delete_topic($topic_by_author['topic_id']);
		$this->delete_topic($topic_multiple_results_count1['topic_id']);
		$this->delete_topic($topic_multiple_results_count2['topic_id']);
	}

	protected function create_search_index($backend = null)
	{
		$this->add_lang('acp/search');
		$search_type = $backend ?? $this->search_backend;
		$crawler = self::request('GET', 'adm/index.php?i=acp_search&mode=index&sid=' . $this->sid);
		$form = $crawler->selectButton($this->lang('CREATE_INDEX'))->form();
		$form_values = $form->getValues();
		$form_values = array_merge($form_values,
			[
				'search_type'	=> $search_type,
				'action'		=> 'create',
			]
		);
		$form->setValues($form_values);
		$crawler = self::submit($form);

		$meta_refresh = $crawler->filter('meta[http-equiv="refresh"]');

		if ($meta_refresh->count() > 0)
		{
			// Wait for posts to be fully indexed
			while ($meta_refresh->count() > 0)
			{
				preg_match('#url=.+/(adm+.+)#', $meta_refresh->attr('content'), $match);
				$url = $match[1];
				$crawler = self::request('POST', $url);
				$meta_refresh = $crawler->filter('meta[http-equiv="refresh"]');
			}
		}

		$this->assertContainsLang('SEARCH_INDEX_CREATED', $crawler->text());

		// Ensure search index has been actually created
		$crawler = self::request('GET', 'adm/index.php?i=acp_search&mode=index&sid=' . $this->sid);
		$posts_indexed = (int) $crawler->filter('#acp_search_index_' . str_replace('\\', '-', $search_type) . ' td')->eq(1)->text();
		$this->assertTrue($posts_indexed > 0);
	}

	protected function delete_search_index()
	{
		$this->add_lang('acp/search');
		$crawler = self::request('GET', 'adm/index.php?i=acp_search&mode=index&sid=' . $this->sid);
		$form = $crawler->selectButton($this->lang('DELETE_INDEX'))->form();
		$form_values = $form->getValues();
		$form_values = array_merge($form_values,
			[
				'search_type'	=> $this->search_backend,
				'action'		=> 'delete',
			]
		);
		$form->setValues($form_values);
		$crawler = self::submit($form);

		$meta_refresh = $crawler->filter('meta[http-equiv="refresh"]');

		if ($meta_refresh->count() > 0)
		{
			// Wait for index to be fully deleted
			while ($meta_refresh->count() > 0)
			{
				preg_match('#url=.+/(adm+.+)#', $meta_refresh->attr('content'), $match);
				$url = $match[1];
				$crawler = self::request('POST', $url);
				$meta_refresh = $crawler->filter('meta[http-equiv="refresh"]');
			}
		}

		$this->assertContainsLang('SEARCH_INDEX_REMOVED', $crawler->text());

		// Ensure search index has been actually removed
		$crawler = self::request('GET', 'adm/index.php?i=acp_search&mode=index&sid=' . $this->sid);
		$posts_indexed = (int) $crawler->filter('#acp_search_index_' . str_replace('\\', '-', $this->search_backend) . ' td')->eq(1)->text();
		$this->assertEquals(0, $posts_indexed);
	}
}
