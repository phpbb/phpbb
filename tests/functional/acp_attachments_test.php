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
class phpbb_functional_acp_attachments_test extends phpbb_functional_test_case
{
	private $path;

	protected function setUp(): void
	{
		parent::setUp();
		$this->path = __DIR__ . '/fixtures/files/';
		$this->add_lang('posting');
	}

	protected function tearDown(): void
	{
		$iterator = new DirectoryIterator(__DIR__ . '/../../phpBB/files/');
		foreach ($iterator as $fileinfo)
		{
			if (
				$fileinfo->isDot()
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

	public function test_orphaned_attachments()
	{
		$this->login();
		$this->add_lang(['common', 'acp/common', 'acp/attachments']);
		$crawler = $this->upload_file('valid.jpg', 'image/jpeg');

		// Ensure there was no error message rendered
		$this->assertStringNotContainsString('<h2>' . $this->lang('INFORMATION') . '</h2>', $this->get_content());

		// Also the file name should be in the first row of the files table
		$this->assertEquals('valid.jpg', $crawler->filter('span.file-name > a')->text());

		// Get attach id, the link looks similar to ./download/attachment/3
		$attach_link = $crawler->filter('span.file-name > a')->attr('href');
		$matches = [];
		preg_match('/\/([0-9]+)\/valid\.jpg$/', $attach_link, $matches);
		$attach_id = (int) $matches[1];

		// Set file time older than 3 hours to consider it orphan
		$sql = 'UPDATE ' . ATTACHMENTS_TABLE . '
			SET filetime = filetime - ' . 4*60*60 . '
			WHERE attach_id = ' . (int) $attach_id;
		$this->db->sql_query($sql);

		$this->admin_login();
		$crawler = self::request('GET', 'adm/index.php?sid=' . $this->sid . '&i=acp_attachments&mode=orphan');
		$this->assertContainsLang('ACP_ORPHAN_ATTACHMENTS_EXPLAIN', $this->get_content());
		$this->assertStringContainsString('valid.jpg', $crawler->filter('tbody a')->text());

		$form = $crawler->selectButton($this->lang('SUBMIT'))->form([
			"post_id[$attach_id]"	=> 99999, // Random
		]);
		$form["add[$attach_id]"]->tick();
		$crawler = self::submit($form);

		$this->assertContainsLang('UPLOADING_FILES', $this->get_content());
		$this->assertStringContainsString($this->lang('UPLOADING_FILE_TO', 'valid.jpg', 99999), $this->get_content());
		$this->assertStringContainsString($this->lang('UPLOAD_POST_NOT_EXIST', 'valid.jpg', 99999), $crawler->filter('span[class="error"]')->text());

		// Delete the file
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$form["delete[$attach_id]"]->tick();
		$crawler = self::submit($form);

		$this->assertContainsLang('NOTIFY', $crawler->filter('.successbox')->text());
		$this->assertStringContainsString(strip_tags($this->lang('LOG_ATTACH_ORPHAN_DEL', 'valid.jpg')), $crawler->filter('.successbox > p')->text());
	}
}
