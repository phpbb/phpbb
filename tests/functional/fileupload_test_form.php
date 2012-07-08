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
class phpbb_functional_fileupload_test_form extends phpbb_functional_test_case
{
	private $path;

	protected function setUp()
	{
		$this->path = __DIR__ . '/fixtures/files/';
		$this->add_lang('posting');
		$this->login();
	}

	public function test_empty_file()
	{
		$crawler = $this->request('GET', 'posting.php?mode=reply&f=2&t=1&sid=' . $this->sid);
		$form = $crawler->selectButton('add_file')->form();
		$form['fileupload']->upload($this->path . 'empty.png');
		$crawler = $this->client->submit($form);
		$this->assertEquals('The image file you tried to attach is invalid.', $crawler->filter('div#message p')->text());
	}

	public function test_invalid_extension()
	{
		$crawler = $this->request('GET', 'posting.php?mode=reply&f=2&t=1&sid=' . $this->sid);
		$form = $crawler->selectButton('add_file')->form();
		$form['fileupload']->upload($this->path . 'illegal-extension.bif');
		$crawler = $this->client->submit($form);
		$this->assertEquals('The extension bif is not allowed.', $crawler->filter('p.error')->text());
	}

	public function test_too_large()
	{
		// Cannot be tested by an admin account which this functional framework
		// provides
		/*$crawler = $this->request('GET', 'posting.php?mode=reply&f=2&t=1&sid=' . $this->sid);
		$form = $crawler->selectButton('add_file')->form();
		$form['fileupload']->upload($path . 'too-large.png');
		$crawler = $this->client->submit($form);
		$this->assertEquals(1, $crawler->filter('div#message')->count());*/
	}

	public function test_valid_file()
	{
		$crawler = $this->request('GET', 'posting.php?mode=reply&f=2&t=1&sid=' . $this->sid);
		$form = $crawler->selectButton('add_file')->form();
		$form['fileupload']->upload($this->path . 'valid.jpg');
		$crawler = $this->client->submit($form);
		$this->assertEquals(0, $crawler->filter('p.error')->count());
		$this->assertContains($this->lang('POSTED_ATTACHMENTS'), $crawler->filter('#postform h3')->eq(1)->text());
	}
}
