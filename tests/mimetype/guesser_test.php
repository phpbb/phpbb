<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\mimetype;

require_once dirname(__FILE__) . '/null_guesser.php';
require_once dirname(__FILE__) . '/incorrect_guesser.php';

function function_exists($name)
{
	return guesser_test::$function_exists;
}

class guesser_test extends \phpbb_test_case
{
	public static $function_exists = true;

	public function setUp()
	{
		global $phpbb_root_path;

		$guessers = array(
			new \Symfony\Component\HttpFoundation\File\MimeType\FileinfoMimeTypeGuesser(),
			new \Symfony\Component\HttpFoundation\File\MimeType\FileBinaryMimeTypeGuesser(),
		);
		$this->guesser = new \phpbb\mimetype\guesser($guessers);
		$this->path = dirname(__FILE__);
		$this->jpg_file = $this->path . '/fixtures/jpg';
		$this->phpbb_root_path = $phpbb_root_path;
	}

	public function data_guess_files()
	{
		return array(
			array('image/gif', 'gif'),
			array('image/png', 'png'),
			array('image/jpeg', 'jpg'),
			array('image/tiff', 'tif'),
			array('text/html', 'txt'),
			array(false, 'foobar'),
		);
	}

	/**
	* @dataProvider data_guess_files
	*/
	public function test_guess_files($expected, $file)
	{
		$this->assertEquals($expected, $this->guesser->guess($this->path . '/../upload/fixture/' . $file));
	}

	public function test_file_not_readable()
	{
		@chmod($this->jpg_file, 0000);
		if (is_readable($this->jpg_file))
		{
			@chmod($this->jpg_file, 0644);
			$this->markTestSkipped('is_readable always returns true if user is superuser or chmod does not work');
		}
		$this->assertEquals(false, $this->guesser->guess($this->jpg_file));
		@chmod($this->jpg_file, 0644);
		$this->assertEquals('image/jpeg', $this->guesser->guess($this->jpg_file));
	}

	public function test_null_guess()
	{
		$guesser = new \phpbb\mimetype\guesser(array(new \phpbb\mimetype\null_guesser));
		$this->assertEquals('application/octet-stream', $guesser->guess($this->jpg_file));
	}

	public function data_incorrect_guessers()
	{
		return array(
			array(array(new \phpbb\mimetype\incorrect_guesser)),
			array(array(new \phpbb\mimetype\null_guesser(false))),
			array(array()),
		);
	}

	/**
	* @dataProvider data_incorrect_guessers
	*
	* @expectedException \LogicException
	*/
	public function test_incorrect_guesser($guessers)
	{
		$guesser = new \phpbb\mimetype\guesser($guessers);
	}

	public function data_content_guesser()
	{
		return array(
			array(
				array(
					'image/jpeg',
					'image/jpeg',
				),
				array(new \phpbb\mimetype\content_guesser),
				false,
			),
			array(
				array(
					'application/octet-stream',
					'application/octet-stream',
				),
				array(new \phpbb\mimetype\content_guesser),
				true,
			),
			array(
				array(
					'application/octet-stream',
					'image/jpeg',
				),
				array(new \phpbb\mimetype\extension_guesser),
			),
		);
	}

	/**
	* @dataProvider data_content_guesser
	*/
	public function test_content_guesser($expected, $guessers, $overload = false)
	{
		self::$function_exists = !$overload;
		$guesser = new \phpbb\mimetype\guesser($guessers);
		$this->assertEquals($expected[0], $guesser->guess($this->jpg_file));
		$this->assertEquals($expected[1], $guesser->guess($this->jpg_file, $this->jpg_file . '.jpg'));
		@copy($this->jpg_file, $this->jpg_file . '.jpg');
		$this->assertEquals($expected[1], $guesser->guess($this->jpg_file . '.jpg'));
		@unlink($this->jpg_file . '.jpg');
	}

	public function test_sort_priority()
	{
		$guessers = array(
			'FileinfoMimeTypeGuesser'	=> new \Symfony\Component\HttpFoundation\File\MimeType\FileinfoMimeTypeGuesser,
			'extension_guesser'		=> new \phpbb\mimetype\extension_guesser,
			'FileBinaryMimeTypeGuesser'	=> new \Symfony\Component\HttpFoundation\File\MimeType\FileBinaryMimeTypeGuesser,
			'content_guesser'		=> new \phpbb\mimetype\content_guesser,
		);
		$guessers['content_guesser']->set_priority(5);
		$guessers['extension_guesser']->set_priority(-5);
		usort($guessers, array($this->guesser, 'sort_priority'));
		$this->assertInstanceOf('\phpbb\mimetype\content_guesser', $guessers[0]);
		$this->assertInstanceOf('\phpbb\mimetype\extension_guesser', $guessers[3]);
	}
}
