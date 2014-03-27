<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_revisions_test extends phpbb_database_test_case
{
	protected $revision_defaults;
	protected $db;

	public function setUp()
	{
		$this->db = $this->new_dbal();
		$this->revision_defaults = array(
			'revision_subject'		=> '',
			'enable_bbcode'			=> '',
			'enable_smilies'		=> '',
			'enable_magic_url'		=> '',
			'bbcode_bitfield'		=> '',
			'bbcode_uid'			=> 0,
			'revision_time'			=> 0,
			'revision_reason'		=> '',
			'user_id'				=> 0,
			'username'				=> 'Anonymous',
			'user_colour'			=> '',
			'user_avatar'			=> '',
			'user_avatar_type'		=> 0,
			'user_avatar_width'		=> 0,
			'user_avatar_height'	=> 0,
			'poster_id'				=> 0,
			'post_id'				=> 0,
			'forum_id'				=> 0,
			'revision_protected'	=> false,
			'is_current'			=> false,
		);
	}

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/revisions.xml');
	}

	public function test_revision_comparison()
	{
		$original_data = array_merge($this->revision_defaults, array('revision_text' => 'I am a string of text.'));
		$addition_data = array_merge($this->revision_defaults, array('revision_text' => 'I am a modified string of text.'));
		$deletion_data = array_merge($this->revision_defaults, array('revision_text' => 'I am string of text.'));

		$original = new \phpbb\revisions\revision(0, $this->db, false);
		$original->set_data($original_data);
		$addition = new \phpbb\revisions\revision(0, $this->db, false);
		$addition->set_data($addition_data);
		$deletion = new \phpbb\revisions\revision(0, $this->db, false);
		$deletion->set_data($deletion_data);

		$addition_comparison = new \phpbb\revisions\comparison($original, $addition);
		$addition_diff = $addition_comparison->get_text_diff_rendered();
		$this->assertEquals("I am a <ins>modified </ins>string of text.", $addition_diff);
		$deletion_comparison = new \phpbb\revisions\comparison($original, $deletion);
		$deletion_diff = $deletion_comparison->get_text_diff_rendered();
		$this->assertEquals("I am <del>a </del>string of text.", $deletion_diff);
	}
}
