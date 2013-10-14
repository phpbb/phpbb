<?php
/**
 *
 * @package testing
 * @copyright (c) 2012 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

/**
 * @group functional
 */
class phpbb_functional_fileupload_form_test extends phpbb_functional_test_case
{
	private $path;

	public function setUp()
	{
		parent::setUp();
		$this->path = __DIR__ . '/fixtures/files/';
		$this->add_lang('posting');
		$this->login();
	}

	public function tearDown()
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
		$file = array(
			'tmp_name' => $this->path . $filename,
			'name' => $filename,
			'type' => $mimetype,
			'size' => filesize($this->path . $filename),
			'error' => UPLOAD_ERR_OK,
		);

		$crawler = self::$client->request(
			'POST',
			'posting.php?mode=reply&f=2&t=1&sid=' . $this->sid,
			array('add_file' => $this->lang('ADD_FILE')),
			array('fileupload' => $file)
		);

		return $crawler;
	}

	public function test_empty_file()
	{
		$this->markTestIncomplete('Test fails intermittently.');
		$crawler = $this->upload_file('empty.png', 'image/png');
		$this->assertEquals($this->lang('ATTACHED_IMAGE_NOT_IMAGE'), $this->assert_filter($crawler, 'div#message p')->text());
	}

	public function test_invalid_extension()
	{
		$crawler = $this->upload_file('illegal-extension.bif', 'application/octet-stream');
		$this->assertEquals($this->lang('DISALLOWED_EXTENSION', 'bif'), $crawler->filter('p.error')->text());
	}

	public function test_too_large()
	{
		$this->markTestIncomplete('Functional tests use an admin account which ignores maximum upload size.');
		$crawler = $this->upload_file('too-large.png', 'image/png');
		$this->assertEquals($this->lang('WRONG_FILESIZE', '256', 'KiB'), $crawler->filter('p.error')->text());
	}

	public function test_valid_file()
	{
		$this->markTestIncomplete('Test fails intermittently.');
		$crawler = $this->upload_file('valid.jpg', 'image/jpeg');
		// ensure there was no error message rendered
		$this->assertNotContains('<h2>' . $this->lang('INFORMATION') . '</h2>', $this->get_content());
		$this->assertContains($this->lang('POSTED_ATTACHMENTS'), $crawler->filter('#postform h3')->eq(1)->text());
	}
}
