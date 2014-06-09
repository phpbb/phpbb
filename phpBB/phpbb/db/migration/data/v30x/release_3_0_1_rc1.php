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

namespace phpbb\db\migration\data\v30x;

class release_3_0_1_rc1 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return phpbb_version_compare($this->config['version'], '3.0.1-RC1', '>=');
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v30x\release_3_0_0');
	}

	public function update_schema()
	{
		return array(
			'add_columns' => array(
				$this->table_prefix . 'forums' => array(
					'display_subforum_list' => array('BOOL', 1),
				),
				$this->table_prefix . 'sessions' => array(
					'session_forum_id' => array('UINT', 0),
				),
			),
			'drop_keys' => array(
				$this->table_prefix . 'groups' => array(
					'group_legend',
				),
			),
			'add_index' => array(
				$this->table_prefix . 'sessions' => array(
					'session_forum_id' => array('session_forum_id'),
				),
				$this->table_prefix . 'groups' => array(
					'group_legend_name' => array('group_legend', 'group_name'),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_columns' => array(
				$this->table_prefix . 'forums' => array(
					'display_subforum_list',
				),
				$this->table_prefix . 'sessions' => array(
					'session_forum_id',
				),
			),
			'add_index' => array(
				$this->table_prefix . 'groups' => array(
					'group_legend' => array('group_legend'),
				),
			),
			'drop_keys' => array(
				$this->table_prefix . 'sessions' => array(
					'session_forum_id',
				),
				$this->table_prefix . 'groups' => array(
					'group_legend_name',
				),
			),
		);
	}

	public function update_data()
	{
		return array(
			array('custom', array(array(&$this, 'fix_unset_last_view_time'))),
			array('custom', array(array(&$this, 'reset_smiley_size'))),

			array('config.update', array('version', '3.0.1-RC1')),
		);
	}

	public function fix_unset_last_view_time()
	{
		$sql = 'UPDATE ' . $this->table_prefix . "topics
			SET topic_last_view_time = topic_last_post_time
			WHERE topic_last_view_time = 0";
		$this->sql_query($sql);
	}

	public function reset_smiley_size()
	{
		// Update smiley sizes
		$smileys = array('icon_e_surprised.gif', 'icon_eek.gif', 'icon_cool.gif', 'icon_lol.gif', 'icon_mad.gif', 'icon_razz.gif', 'icon_redface.gif', 'icon_cry.gif', 'icon_evil.gif', 'icon_twisted.gif', 'icon_rolleyes.gif', 'icon_exclaim.gif', 'icon_question.gif', 'icon_idea.gif', 'icon_arrow.gif', 'icon_neutral.gif', 'icon_mrgreen.gif', 'icon_e_ugeek.gif');

		foreach ($smileys as $smiley)
		{
			if (file_exists($this->phpbb_root_path . 'images/smilies/' . $smiley))
			{
				list($width, $height) = getimagesize($this->phpbb_root_path . 'images/smilies/' . $smiley);

				$sql = 'UPDATE ' . SMILIES_TABLE . '
					SET smiley_width = ' . $width . ', smiley_height = ' . $height . "
					WHERE smiley_url = '" . $this->db->sql_escape($smiley) . "'";

				$this->sql_query($sql);
			}
		}
	}
}
