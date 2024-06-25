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
class phpbb_functional_ucp_attachments_test extends phpbb_functional_test_case
{
	private $path;

	protected function setUp(): void
	{
		parent::setUp();
		$this->path = __DIR__ . '/fixtures/files/';
		$this->add_lang('posting');

		if (!$this->user_exists('ucp-file-test'))
		{
			$this->create_user('ucp-file-test');
		}
	}

	protected function tearDown(): void
	{
		$iterator = new DirectoryIterator(__DIR__ . '/../../phpBB/files/');
		foreach ($iterator as $fileinfo)
		{
			if ($fileinfo->isDot()
				|| $fileinfo->isDir()
				|| $fileinfo->getFilename() === 'index.htm'
				|| $fileinfo->getFilename() === '.htaccess'
			)
			{
				continue;
			}

			unlink($fileinfo->getPathname());
		}
	}

	private function upload_file($filename, $mimetype)
	{
		$crawler = self::$client->request(
			'GET',
			'posting.php?mode=reply&f=2&t=1&sid=' . $this->sid
		);

		$file_form_data = array_merge(['add_file' => $this->lang('ADD_FILE')], $this->get_hidden_fields($crawler, 'posting.php?mode=reply&f=2&t=1&sid=' . $this->sid));

		$file = array(
			'tmp_name' => $this->path . $filename,
			'name' => $filename,
			'type' => $mimetype,
			'size' => filesize($this->path . $filename),
			'error' => UPLOAD_ERR_OK,
		);

		$crawler = self::$client->request(
			'POST',
			'posting.php?mode=reply&t=1&sid=' . $this->sid,
			$file_form_data,
			array('fileupload' => $file)
		);

		return $crawler;
	}

	private function upload_file_pm($filename, $mimetype)
	{
		$crawler = self::$client->request(
			'GET',
			'ucp.php?i=pm&mode=compose&sid=' . $this->sid
		);

		$file_form_data = array_merge(['add_file' => $this->lang('ADD_FILE')], $this->get_hidden_fields($crawler, 'ucp.php?i=pm&mode=compose&sid=' . $this->sid));

		$file = array(
			'tmp_name' => $this->path . $filename,
			'name' => $filename,
			'type' => $mimetype,
			'size' => filesize($this->path . $filename),
			'error' => UPLOAD_ERR_OK,
		);

		$crawler = self::$client->request(
			'POST',
			'ucp.php?i=pm&mode=compose&sid=' . $this->sid,
			$file_form_data,
			array('fileupload' => $file)
		);

		return $crawler;
	}

	public function test_ucp_list_attachments()
	{
		$this->login('ucp-file-test');
		$this->add_lang(['common', 'posting']);
		$crawler = $this->upload_file('valid.jpg', 'image/jpeg');

		// Ensure there was no error message rendered
		$this->assertStringNotContainsString('<h2>' . $this->lang('INFORMATION') . '</h2>', $this->get_content());

		// Also the file name should be in the first row of the files table
		$this->assertEquals('valid.jpg', $crawler->filter('span.file-name > a')->text());

		$attach_link = $crawler->filter('span.file-name > a')->attr('href');
		preg_match('#download/attachment/([0-9]+)/valid.jpg#', $attach_link, $match);
		$attach_id = $match[1];

		// Submit post
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form([
			'message'		=> 'This is a test',
		]);
		$crawler = self::submit($form);

		$this->assertStringContainsString('This is a test', $crawler->text());
		$this->assertEquals('valid.jpg', $crawler->filter('img.postimage')->attr('alt'));

		// Navigate to ucp attachments for user
		$crawler = self::request('GET', 'ucp.php?i=ucp_attachments&mode=attachments&sid=' . $this->sid);
		$this->assertEquals(1, $crawler->filter('.attachment-filename')->count());

		$attachment_filename = $crawler->filter('.attachment-filename');
		$this->assertEquals('valid.jpg', $attachment_filename->attr('title'));
		$this->assertStringContainsString('app.php/download/attachment/' . $attach_id . '/valid.jpg', $attachment_filename->attr('href'));
		$this->assertFalse($crawler->filter('[name="attachment[' . $attach_id . ']"]')->getNode(0)->hasAttribute('disabled'));
	}

	public function test_ucp_delete_expired_attachment()
	{
		$this->login('ucp-file-test');
		$this->add_lang(['common', 'posting']);

		$this->set_flood_interval(0);

		$crawler = $this->upload_file('valid.jpg', 'image/jpeg');

		// Ensure there was no error message rendered
		$this->assertStringNotContainsString('<h2>' . $this->lang('INFORMATION') . '</h2>', $this->get_content());

		// Also the file name should be in the first row of the files table
		$this->assertEquals('valid.jpg', $crawler->filter('span.file-name > a')->text());

		$attach_link = $crawler->filter('span.file-name > a')->attr('href');
		preg_match('#download/attachment/([0-9]+)/valid.jpg#', $attach_link, $match);
		$attach_id = $match[1];

		// Submit post
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form([
			'message'		=> 'This is a test',
		]);
		$crawler = self::submit($form);
		$post_url = $crawler->getUri();
		$post_id = $this->get_parameter_from_link($post_url, 'p');

		$this->assertStringContainsString('This is a test', $crawler->text());
		$this->assertEquals('valid.jpg', $crawler->filter('img.postimage')->attr('alt'));
		$this->set_flood_interval(15);

		// Navigate to ucp attachments for user
		$crawler = self::request('GET', 'ucp.php?i=ucp_attachments&mode=attachments&sid=' . $this->sid);
		$crawler->filter('.attachment-filename')->each(function ($node, $i) use ($attach_id, &$attachment_node)
		{
			if (strpos($node->attr('href'), 'download/attachment/' . $attach_id . '/valid.jpg') !== false)
			{
				$attachment_node = $node;
			}
		});
		$this->assertNotNull($attachment_node);

		$this->assertEquals('valid.jpg', $attachment_node->attr('title'));
		$this->assertStringContainsString('download/attachment/' . $attach_id . '/valid.jpg', $attachment_node->attr('href'));

		$this->logout();

		// Switch to admin user
		$this->login();
		$this->admin_login();
		$this->add_lang(['acp/board', 'acp/common']);

		$crawler = self::request('GET', 'adm/index.php?i=acp_board&mode=post&sid=' . $this->sid);
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form([
			'config[edit_time]'		=> 1,
			'config[delete_time]'	=> 1,
		]);

		self::submit($form);

		// Update post time to at least one minute before current time
		$sql = 'UPDATE ' . POSTS_TABLE . '
			SET post_time = post_time - ' . 60 . '
			WHERE post_id = ' . (int) $post_id;
		$this->db->sql_query($sql);

		// Log out and back in as test user
		$this->logout();
		$this->login('ucp-file-test');

		// Navigate to ucp attachments for user, deleting should be disabled
		$crawler = self::request('GET', 'ucp.php?i=ucp_attachments&mode=attachments&sid=' . $this->sid);
		$crawler->filter('.attachment-filename')->each(function ($node, $i) use ($attach_id, &$attachment_node)
		{
			if (strpos($node->attr('href'), 'download/attachment/' . $attach_id . '/valid.jpg') !== false)
			{
				$attachment_node = $node;
			}
		});

		$this->assertEquals('valid.jpg', $attachment_node->attr('title'));
		$this->assertStringContainsString('download/attachment/' . $attach_id . '/valid.jpg', $attachment_node->attr('href'));
		$this->assertTrue($crawler->filter('[name="attachment[' . $attach_id . ']"]')->getNode(0)->hasAttribute('disabled'));

		// It should not be possible to delete the attachment
		$crawler = self::request('POST', 'ucp.php?i=ucp_attachments&mode=attachments&sid=' . $this->sid, [
			'delete'		=> true,
			'attachment[' . $attach_id . ']'	=> $attach_id,
		]);

		$this->assertNotContainsLang('DELETE_ATTACHMENT_CONFIRM', $crawler->text());

		$crawler->filter('.attachment-filename')->each(function ($node, $i) use ($attach_id, &$attachment_node)
		{
			if (strpos($node->attr('href'), 'download/attachment/' . $attach_id . '/valid.jpg') !== false)
			{
				$attachment_node = $node;
			}
		});
		$this->assertEquals('valid.jpg', $attachment_node->attr('title'));
		$this->assertStringContainsString('download/attachment/' . $attach_id . '/valid.jpg', $attachment_node->attr('href'));
		$this->assertTrue($crawler->filter('[name="attachment[' . $attach_id . ']"]')->getNode(0)->hasAttribute('disabled'));

		$this->logout();

		// Switch to admin user
		$this->login();
		$this->admin_login();
		$this->add_lang(['acp/board', 'acp/common']);

		$crawler = self::request('GET', 'adm/index.php?i=acp_board&mode=post&sid=' . $this->sid);
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form([
			'config[edit_time]'		=> 0,
			'config[delete_time]'	=> 0,
		]);

		self::submit($form);

		// Update post time to original one
		$sql = 'UPDATE ' . POSTS_TABLE . '
			SET post_time = post_time + ' . 60 . '
			WHERE post_id = ' . (int) $post_id;
		$this->db->sql_query($sql);
	}

	public function test_pm_attachment()
	{
		$this->set_flood_interval(0);
		// Switch to admin user
		$this->login();
		$this->admin_login();
		$this->add_lang(['common', 'acp/attachments', 'acp/board', 'acp/common']);

		$crawler = self::request('GET', 'adm/index.php?i=acp_attachments&mode=attach&sid=' . $this->sid);
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form([
			'config[allow_pm_attach]'		=> 1,
		]);
		self::submit($form);

		$crawler = self::request('GET', 'adm/index.php?i=acp_board&mode=message&sid=' . $this->sid);
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form([
			'config[pm_edit_time]'			=> 60,
		]);
		self::submit($form);

		$crawler = self::request('GET', 'adm/index.php?i=acp_attachments&mode=ext_groups&action=edit&g=1&sid=' . $this->sid);
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form([
			'allow_in_pm'		=> 1,
		]);
		self::submit($form);

		$this->logout();
		$this->login('ucp-file-test');
		$this->add_lang(['ucp']);
		$crawler = $this->upload_file_pm('valid.jpg', 'image/jpeg');

		$attach_link = $crawler->filter('span.file-name > a')->attr('href');
		preg_match('#download/attachment/([0-9]+)/valid.jpg#', $attach_link, $match);
		$attach_id = $match[1];

		$form = $crawler->selectButton($this->lang('ADD'))->form([
			'username_list'	=> 'admin'
		]);
		$crawler = self::submit($form);

		$form = $crawler->selectButton($this->lang('SUBMIT'))->form([
			'subject'		=> 'Test PM',
			'message'		=> 'This is a test',
		]);
		$crawler = self::submit($form);

		$this->assertContainsLang('MESSAGE_STORED', $crawler->text());
		$refresh_data = explode(';', $crawler->filterXpath("//meta[@http-equiv='refresh']")->attr('content'));
		$pm_url = trim($refresh_data[1]);

		$pm_id = $this->get_parameter_from_link($pm_url, 'p');

		// Navigate to ucp attachments for user
		$crawler = self::request('GET', 'ucp.php?i=ucp_attachments&mode=attachments&sid=' . $this->sid);
		$crawler->filter('.attachment-filename')->each(function ($node, $i) use ($attach_id, &$attachment_node)
		{
			if (strpos($node->attr('href'), 'download/attachment/' . $attach_id . '/valid.jpg') !== false)
			{
				$attachment_node = $node;
			}
		});
		$this->assertNotNull($attachment_node);

		$this->assertEquals('valid.jpg', $attachment_node->attr('title'));
		$this->assertStringContainsString('download/attachment/' . $attach_id . '/valid.jpg', $attachment_node->attr('href'));
		$this->assertFalse($crawler->filter('[name="attachment[' . $attach_id . ']"]')->getNode(0)->hasAttribute('disabled'));

		// Update message time to 60 minutes later
		$sql = 'UPDATE ' . PRIVMSGS_TABLE . '
			SET message_time = message_time - ' . 60 * 60 . '
			WHERE msg_id = ' . (int) $pm_id;
		$this->db->sql_query($sql);

		$crawler = self::request('GET', 'ucp.php?i=ucp_attachments&mode=attachments&sid=' . $this->sid);
		$crawler->filter('.attachment-filename')->each(function ($node, $i) use ($attach_id, &$attachment_node)
		{
			if (strpos($node->attr('href'), 'download/attachment/' . $attach_id . '/valid.jpg') !== false)
			{
				$attachment_node = $node;
			}
		});
		$this->assertNotNull($attachment_node);

		$this->assertEquals('valid.jpg', $attachment_node->attr('title'));
		$this->assertStringContainsString('download/attachment/' . $attach_id . '/valid.jpg', $attachment_node->attr('href'));
		$this->assertTrue($crawler->filter('[name="attachment[' . $attach_id . ']"]')->getNode(0)->hasAttribute('disabled'));

		$this->set_flood_interval(15);

		// Switch to admin user and disable extra settings again
		$this->logout();
		$this->login();
		$this->admin_login();
		$this->add_lang(['common', 'acp/attachments', 'acp/board', 'acp/common']);

		$crawler = self::request('GET', 'adm/index.php?i=acp_attachments&mode=attach&sid=' . $this->sid);
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form([
			'config[allow_pm_attach]'		=> 0,
		]);
		self::submit($form);

		$crawler = self::request('GET', 'adm/index.php?i=acp_board&mode=message&sid=' . $this->sid);
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form([
			'config[pm_edit_time]'			=> 0,
		]);
		self::submit($form);

		$crawler = self::request('GET', 'adm/index.php?i=acp_attachments&mode=ext_groups&action=edit&g=1&sid=' . $this->sid);
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$form['allow_in_pm']->untick();
		self::submit($form);
	}
}
